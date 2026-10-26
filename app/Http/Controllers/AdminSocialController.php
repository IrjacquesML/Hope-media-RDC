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

    private function definition(string $platform): array
    {
        abort_unless(in_array($platform, SocialPlatforms::keys(), true), 404);

        return SocialPlatforms::get($platform);
    }
}
