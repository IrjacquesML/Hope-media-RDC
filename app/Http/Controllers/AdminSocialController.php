<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\SocialPlatform;
use App\Services\SocialSyncManager;
use App\Support\SocialPlatforms;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Support\Site;
use Illuminate\View\View;

/**
 * Admin → Réseaux sociaux : configuration, test et synchronisation de
 * YouTube, Facebook, Instagram, TikTok et X.
 */
class AdminSocialController extends Controller
{
    public function __construct(private readonly SocialSyncManager $manager) {}

    public function index(): View
    {
        $platforms = [];
        foreach (SocialPlatforms::all() as $key => $def) {
            $model = SocialPlatform::for($key);
            $fields = [];
            foreach ($def['fields'] as $field => $meta) {
                $fields[$field] = $meta + [
                    'source' => $this->manager->source($key, $field),
                    // Les secrets ne sont jamais renvoyés dans la page
                    'value'  => $meta['secret'] ? '' : ($model->storedCredential($field) ?? ''),
                ];
            }
            $platforms[$key] = array_merge($def, [
                'model'      => $model,
                'fields'     => $fields,
                'missing'    => $this->manager->missing($key),
                'videoCount' => \App\Models\Video::where('platform', $key)->count(),
            ]);
        }

        $heartbeat = Cache::get(SocialSyncManager::SCHEDULER_HEARTBEAT);

        return view('admin.social.index', [
            'platforms'      => $platforms,
            'schedulerRunAt' => $heartbeat ? Carbon::parse($heartbeat) : null,
            'autoWithoutCron' => Site::get('sync_without_cron'),
            'tiktokRedirect' => route('admin.social.tiktok.callback'),
            'configuredCount' => count(array_filter(SocialPlatforms::keys(), fn ($k) => $this->manager->isConfigured($k))),
        ]);
    }

    public function update(Request $request, string $platform): RedirectResponse
    {
        $def = $this->definition($platform);

        $rules = [
            'enabled'        => ['nullable', 'boolean'],
            'max_results'    => ['required', 'integer', 'min:1', 'max:' . $def['max_limit']],
            'interval_hours' => ['required', 'integer', 'in:1,2,3,6,12,24'],
        ];
        foreach ($def['fields'] as $field => $_) {
            $rules["credentials.{$field}"] = ['nullable', 'string', 'max:4000'];
            $rules["forget.{$field}"] = ['nullable', 'boolean'];
        }
        $data = $request->validate($rules, [], ['max_results' => 'nombre de vidéos', 'interval_hours' => 'fréquence']);

        $model = SocialPlatform::for($platform);
        $credentials = $model->credentials ?? [];
        foreach ($def['fields'] as $field => $meta) {
            $value = trim((string) ($data['credentials'][$field] ?? ''));
            if (! empty($data['forget'][$field])) {
                unset($credentials[$field]);              // case « Effacer » cochée
            } elseif ($value !== '') {
                $credentials[$field] = $value;            // nouvelle valeur
            } elseif (! $meta['secret']) {
                unset($credentials[$field]);              // champ visible vidé volontairement
            }                                             // secret laissé vide : on garde l'ancien
        }

        $model->fill([
            'enabled'        => $request->boolean('enabled'),
            'max_results'    => $data['max_results'],
            'interval_hours' => $data['interval_hours'],
        ]);
        $model->credentials = $credentials;
        $model->save();

        $message = "{$def['label']} : réglages enregistrés.";
        if ($model->enabled && ($missing = $this->manager->missing($platform))) {
            return back()->with('error', "{$def['label']} : réglages enregistrés, mais la synchronisation automatique ne pourra pas fonctionner. Il manque : " . implode(', ', $missing) . '.');
        }

        return back()->with('success', $message);
    }

    public function test(string $platform): RedirectResponse
    {
        $def = $this->definition($platform);

        try {
            $account = $this->manager->test($platform);

            return back()->with('success', "{$def['label']} : connexion réussie avec le compte « {$account} ».");
        } catch (\Throwable $e) {
            return back()->with('error', "{$def['label']} : la connexion a échoué. {$e->getMessage()}");
        }
    }

    public function sync(string $platform): RedirectResponse
    {
        $def = $this->definition($platform);

        try {
            $count = $this->manager->sync($platform);

            return back()->with('success', "{$def['label']} : {$count} vidéo(s) synchronisée(s).");
        } catch (\Throwable $e) {
            return back()->with('error', "{$def['label']} : la synchronisation a échoué. {$e->getMessage()}");
        }
    }

    /** Synchronise tous les réseaux configurés, activés ou non. */
    public function syncAll(): RedirectResponse
    {
        @set_time_limit(300);
        $ok = [];
        $errors = [];
        foreach (SocialPlatforms::keys() as $platform) {
            if (! $this->manager->isConfigured($platform)) {
                continue;
            }
            $label = SocialPlatforms::get($platform)['label'];
            try {
                $ok[] = "{$label} : " . $this->manager->sync($platform);
            } catch (\Throwable $e) {
                $errors[] = "{$label} : {$e->getMessage()}";
            }
        }

        if (! $ok && ! $errors) {
            return back()->with('error', 'Aucun réseau n’est encore configuré.');
        }

        $redirect = back();
        if ($ok) {
            $redirect = $redirect->with('success', 'Vidéos synchronisées — ' . implode(' · ', $ok) . '.');
        }
        if ($errors) {
            $redirect = $redirect->with('error', 'Échecs — ' . implode(' · ', $errors));
        }

        return $redirect;
    }

    /**
     * TikTok : envoie l'administrateur sur TikTok pour autoriser l'application avec le compte
     * Hope Media. Au retour, les jetons sont enregistrés automatiquement.
     */
    public function tiktokConnect(Request $request): RedirectResponse
    {
        $c = $this->manager->credentials('tiktok');
        if (empty($c['client_key']) || empty($c['client_secret'])) {
            return redirect()->to(route('admin.social.index') . '#reseau-tiktok')
                ->with('error', 'TikTok : enregistrez d’abord le Client Key et le Client Secret de votre application.');
        }

        $state = Str::random(40);
        $request->session()->put('tiktok_oauth_state', $state);

        return redirect()->away('https://www.tiktok.com/v2/auth/authorize/?' . http_build_query([
            'client_key'    => $c['client_key'],
            'scope'         => 'user.info.basic,video.list',
            'response_type' => 'code',
            'redirect_uri'  => route('admin.social.tiktok.callback'),
            'state'         => $state,
        ]));
    }

    public function tiktokCallback(Request $request): RedirectResponse
    {
        $back = redirect()->to(route('admin.social.index') . '#reseau-tiktok');
        $expected = $request->session()->pull('tiktok_oauth_state');

        if (! $expected || ! hash_equals($expected, (string) $request->query('state'))) {
            return $back->with('error', 'TikTok : la demande d’autorisation a expiré ou n’est pas valide. Recommencez.');
        }
        if ($request->filled('error')) {
            return $back->with('error', 'TikTok : autorisation refusée (' . $request->query('error_description', $request->query('error')) . ').');
        }

        $c = $this->manager->credentials('tiktok');
        try {
            $response = Http::timeout(15)->asForm()->post('https://open.tiktokapis.com/v2/oauth/token/', [
                'client_key'    => $c['client_key'],
                'client_secret' => $c['client_secret'],
                'code'          => (string) $request->query('code'),
                'grant_type'    => 'authorization_code',
                'redirect_uri'  => route('admin.social.tiktok.callback'),
            ]);
        } catch (\Throwable $e) {
            return $back->with('error', 'TikTok : impossible de joindre TikTok. ' . $e->getMessage());
        }

        if ($response->failed() || empty($response->json('access_token'))) {
            $msg = $response->json('error_description') ?: ($response->json('error') ?: $response->body());
            return $back->with('error', "TikTok : l’échange du code a échoué. {$msg}");
        }

        $this->manager->storeCredentials('tiktok', [
            'access_token'  => $response->json('access_token'),
            'refresh_token' => $response->json('refresh_token'),
            'open_id'       => $response->json('open_id'),
        ]);

        try {
            $account = $this->manager->test('tiktok');
            return $back->with('success', "TikTok : compte « {$account} » connecté. Vous pouvez maintenant synchroniser.");
        } catch (\Throwable $e) {
            return $back->with('success', 'TikTok : jetons enregistrés. ' . $e->getMessage());
        }
    }

    private function definition(string $platform): array
    {
        abort_unless(in_array($platform, SocialPlatforms::keys(), true), 404);

        return SocialPlatforms::get($platform);
    }
}
