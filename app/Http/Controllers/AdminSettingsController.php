<?php

namespace App\Http\Controllers;

use App\Mail\TestMail;
use App\Models\Partner;
use App\Services\NewsletterService;
use Illuminate\Support\Facades\Mail;
use App\Support\Site;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Paramètres du site (super administrateur) : textes, logos, coordonnées, liens et partenaires.
 */
class AdminSettingsController extends Controller
{
    private const UPLOAD_DIR = 'images/settings';

    public function index(Request $request): View
    {
        $groups = Site::groups();
        $tab = $request->query('onglet', 'identity');
        if ($tab !== 'partners' && !isset($groups[$tab])) {
            $tab = 'identity';
        }

        $fields = collect(Site::schema())
            ->filter(fn ($f) => $f['group'] === $tab)
            // Les secrets ne quittent jamais le serveur : la vue sait seulement s'ils existent
            ->map(fn ($f, $key) => $f + [
                'value'  => $f['type'] === 'secret' ? (Site::get($key) ? true : null) : Site::get($key),
                'custom' => Site::isCustom($key),
            ]);

        return view('admin.settings.index', [
            'groups'   => $groups,
            'tab'      => $tab,
            'fields'   => $fields,
            'partners' => $tab === 'partners' ? Partner::ordered()->get() : collect(),
        ]);
    }

    public function update(Request $request, string $group): RedirectResponse
    {
        abort_unless(isset(Site::groups()[$group]), 404);

        $fields = array_filter(Site::schema(), fn ($f) => $f['group'] === $group);

        $rules = [];
        foreach ($fields as $key => $f) {
            $rules[$key] = match ($f['type']) {
                'boolean'  => ['nullable', 'boolean'],
                'url'      => ['nullable', 'url', 'max:500'],
                'email'    => ['nullable', 'email', 'max:255'],
                'image'    => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:3072'],
                'textarea', 'lines' => ['nullable', 'string', 'max:10000'],
                'select'   => ['nullable', 'in:' . implode(',', array_keys($f['options']))],
                default    => ['nullable', 'string', 'max:500'],
            };
            if (!empty($f['required']) && $f['type'] !== 'boolean' && $f['type'] !== 'image') {
                $rules[$key][0] = 'required';
            }
            if ($f['type'] === 'image') {
                $rules["{$key}_remove"] = ['nullable', 'boolean'];
            }
            $rules["{$key}_default"] = ['nullable', 'boolean'];
        }
        if (isset($fields['theme_color'])) {
            $rules['theme_color'][] = 'regex:/^#[0-9A-Fa-f]{6}$/';
        }

        $labels = array_map(fn ($f) => '« ' . $f['label'] . ' »', $fields);
        $data = $request->validate($rules, ['theme_color.regex' => 'La couleur doit être au format #RRGGBB.'], $labels);

        $values = [];
        foreach ($fields as $key => $f) {
            if ($f['type'] === 'boolean') {
                $values[$key] = $request->boolean($key);
                continue;
            }

            // Secret : jamais réaffiché ; vide = on garde la valeur enregistrée
            if ($f['type'] === 'secret') {
                if ($request->boolean("{$key}_default")) {
                    Site::reset($key);
                } elseif (trim((string) ($data[$key] ?? '')) !== '') {
                    $values[$key] = trim((string) $data[$key]);
                }
                continue;
            }

            if ($f['type'] === 'image') {
                if ($request->boolean("{$key}_default")) {
                    $this->deleteUpload(Site::get($key));
                    Site::reset($key);
                } elseif ($request->hasFile($key)) {
                    $this->deleteUpload(Site::get($key));
                    $values[$key] = $this->storeUpload($request->file($key), $key);
                } elseif ($request->boolean("{$key}_remove")) {
                    $this->deleteUpload(Site::get($key));
                    $values[$key] = '';
                }
                continue;
            }

            $value = $data[$key] ?? null;
            $value = is_string($value) ? trim(str_replace("\r\n", "\n", $value)) : $value;

            // « Rétablir la valeur d'origine », ou valeur identique à l'origine : rien n'est stocké
            if ($request->boolean("{$key}_default") || (string) $value === (string) $f['default']) {
                Site::reset($key);
            } else {
                $values[$key] = $value ?? '';   // champ vidé : l'élément est masqué sur le site
            }
        }

        Site::save($values);

        return redirect()->route('admin.settings.index', ['onglet' => $group])
            ->with('success', 'Paramètres « ' . Site::groups()[$group]['label'] . ' » enregistrés. Le site est à jour.');
    }

    /** Envoie un e-mail de test avec les réglages enregistrés. */
    public function testMail(Request $request): RedirectResponse
    {
        $data = $request->validate(['to' => ['required', 'email']], [], ['to' => 'adresse de test']);
        $back = redirect()->route('admin.settings.index', ['onglet' => 'mail']);

        if (!Site::mailConfigured()) {
            return $back->with('error', 'Renseignez d’abord le serveur, l’identifiant, la clé SMTP et l’adresse d’expéditeur, puis enregistrez.');
        }

        try {
            Mail::to($data['to'])->send(new TestMail());
        } catch (\Throwable $e) {
            return $back->with('error', 'L’envoi a échoué : ' . NewsletterService::explainMailError($e));
        }

        return $back->with('success', "E-mail de test envoyé à {$data['to']}. S’il n’arrive pas dans quelques minutes, regardez dans les spams et vérifiez que l’adresse d’expéditeur est validée dans Brevo.");
    }

    // PARTENAIRES

    public function storePartner(Request $request): RedirectResponse
    {
        $data = $this->validatePartner($request);
        $data['sort_order'] = $data['sort_order'] ?? ((int) Partner::max('sort_order') + 1);
        if ($request->hasFile('logo')) {
            $data['logo'] = $this->storeUpload($request->file('logo'), 'partner');
        }
        Partner::create($data);
        Site::flush();

        return redirect()->route('admin.settings.index', ['onglet' => 'partners'])->with('success', "Partenaire « {$data['name']} » ajouté.");
    }

    public function updatePartner(Request $request, Partner $partner): RedirectResponse
    {
        $data = $this->validatePartner($request);
        if ($request->hasFile('logo')) {
            $this->deleteUpload($partner->logo);
            $data['logo'] = $this->storeUpload($request->file('logo'), 'partner');
        } elseif ($request->boolean('logo_remove')) {
            $this->deleteUpload($partner->logo);
            $data['logo'] = null;
        }
        $data['sort_order'] = $data['sort_order'] ?? $partner->sort_order;
        $partner->update($data);
        Site::flush();

        return redirect()->route('admin.settings.index', ['onglet' => 'partners'])->with('success', "Partenaire « {$partner->name} » mis à jour.");
    }

    public function deletePartner(Partner $partner): RedirectResponse
    {
        $this->deleteUpload($partner->logo);
        $partner->delete();
        Site::flush();

        return redirect()->route('admin.settings.index', ['onglet' => 'partners'])->with('success', "Partenaire « {$partner->name} » supprimé.");
    }

    private function validatePartner(Request $request): array
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:150'],
            'url'         => ['nullable', 'url', 'max:500'],
            'logo'        => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'logo_remove' => ['nullable', 'boolean'],
            'sort_order'  => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active'   => ['nullable', 'boolean'],
        ], [], ['name' => 'nom', 'url' => 'site web']);

        unset($data['logo'], $data['logo_remove']);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }

    // FICHIERS

    private function storeUpload($file, string $prefix): string
    {
        $dir = public_path(self::UPLOAD_DIR);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $name = Str::slug($prefix) . '-' . now()->format('YmdHis') . '-' . Str::random(6) . '.' . strtolower($file->getClientOriginalExtension());
        $file->move($dir, $name);

        return self::UPLOAD_DIR . '/' . $name;
    }

    /** Supprime un fichier envoyé depuis l'admin (jamais les images d'origine du site). */
    private function deleteUpload(?string $path): void
    {
        if ($path && str_starts_with($path, self::UPLOAD_DIR . '/') && is_file(public_path($path))) {
            @unlink(public_path($path));
        }
    }
}
