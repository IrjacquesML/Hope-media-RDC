<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Services\VideoParserService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Form Request pour la création d'une vidéo intégrée.
 *
 * La validation se fait en deux temps :
 *  1. Règles Laravel standard (required, string, max).
 *  2. Règle personnalisée inline qui délègue au VideoParserService
 *     pour s'assurer que l'URL est réellement reconnue et parsable.
 */
class StoreVideoRequest extends FormRequest
{
    /**
     * Tout utilisateur authentifié est autorisé à soumettre ce formulaire.
     * Adaptez selon votre politique d'autorisation.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Règles de validation.
     *
     * @return array<string, list<string|ValidationRule|\Closure>>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],

            /*
             * L'URL brute soumise par l'utilisateur.
             * La règle closure ci-dessous appelle le service de parsing :
             * si parse() retourne null, l'URL n'est pas reconnue.
             */
            'url' => [
                'required',
                'string',
                'max:2048',
                // Validation HTTPS/HTTP basique avant d'invoquer le service.
                'url:http,https',
                $this->videoUrlRule(),
            ],
        ];
    }

    /**
     * Messages d'erreur personnalisés.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'url.required' => "L'URL de la vidéo est obligatoire.",
            'url.url'      => "Le format de l'URL est invalide.",
            'url.max'      => "L'URL ne doit pas dépasser 2048 caractères.",
        ];
    }

    // -------------------------------------------------------------------------
    // Règle personnalisée (closure)
    // -------------------------------------------------------------------------

    /**
     * Construit la règle de validation personnalisée qui vérifie
     * que l'URL est effectivement parsable par VideoParserService.
     *
     * L'utilisation d'une closure (plutôt qu'un objet Rule séparé)
     * permet ici de garder la logique de validation cohérente avec
     * le Form Request sans créer un fichier supplémentaire.
     */
    private function videoUrlRule(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            /** @var VideoParserService $parser */
            $parser = app(VideoParserService::class);

            if ($parser->parse((string) $value) === null) {
                $fail(
                    "L'URL fournie n'est pas reconnue. "
                    . 'Seules les URLs YouTube, TikTok et Facebook sont acceptées.'
                );
            }
        };
    }
}
