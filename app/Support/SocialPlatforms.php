<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Catalogue des réseaux synchronisés : champs à configurer, valeurs par défaut,
 * correspondance avec config/services.php (repli .env) et guide pour l'admin.
 */
final class SocialPlatforms
{
    /**
     * @return array<string, array{
     *   label: string, icon: string, color: string,
     *   interval: int, max_results: int, max_limit: int,
     *   fields: array<string, array{label: string, secret: bool, required: bool, placeholder?: string, help?: string}>,
     *   guide: string[], docs: string, note?: string
     * }>
     */
    public static function all(): array
    {
        return [
            'youtube' => [
                'label' => 'YouTube', 'icon' => 'fa-youtube', 'color' => '#E62117',
                'interval' => 6, 'max_results' => 20, 'max_limit' => 50,
                'fields' => [
                    'api_key'    => ['label' => 'Clé API YouTube Data v3', 'secret' => true, 'required' => true, 'placeholder' => 'AIza…'],
                    'channel_id' => ['label' => 'Chaîne', 'secret' => false, 'required' => true, 'placeholder' => '@Hopemedia-u4b ou UCxxxxxxxx', 'help' => 'Le @nom de la chaîne ou son identifiant qui commence par UC.'],
                ],
                'guide' => [
                    'Ouvrez console.cloud.google.com et créez un projet (ou choisissez-en un).',
                    'Menu « API et services » → « Bibliothèque » → activez « YouTube Data API v3 ».',
                    '« API et services » → « Identifiants » → « Créer des identifiants » → « Clé API ».',
                    'Collez la clé ici, puis le @nom de la chaîne (visible sur youtube.com).',
                ],
                'docs' => 'https://console.cloud.google.com/apis/library/youtube.googleapis.com',
                'note' => 'Quota gratuit : 10 000 unités par jour, largement suffisant (environ 3 unités par synchronisation).',
            ],
            'facebook' => [
                'label' => 'Facebook', 'icon' => 'fa-facebook', 'color' => '#1877F2',
                'interval' => 3, 'max_results' => 20, 'max_limit' => 100,
                'fields' => [
                    'page_id'           => ['label' => 'ID de la Page', 'secret' => false, 'required' => true, 'placeholder' => '123456789012345'],
                    'page_access_token' => ['label' => 'Jeton d’accès de la Page', 'secret' => true, 'required' => true, 'placeholder' => 'EAAG…', 'help' => 'Jeton « Page » longue durée, avec la permission pages_read_engagement.'],
                ],
                'guide' => [
                    'Sur developers.facebook.com, créez une app de type « Business ».',
                    'Ouvrez l’« Explorateur de l’API Graph », choisissez votre app, puis « Obtenir un jeton de Page » pour la Page Hope Media (permissions pages_show_list et pages_read_engagement).',
                    'Transformez-le en jeton longue durée avec l’outil « Débogueur de jeton d’accès » → « Prolonger le jeton d’accès ».',
                    'L’ID de la Page se trouve dans Facebook → votre Page → « À propos » → « Transparence de la Page ».',
                ],
                'docs' => 'https://developers.facebook.com/tools/explorer/',
                'note' => 'Seules les vidéos de la Page sont importées.',
            ],
            'instagram' => [
                'label' => 'Instagram', 'icon' => 'fa-instagram', 'color' => '#C13584',
                'interval' => 3, 'max_results' => 20, 'max_limit' => 100,
                'fields' => [
                    'access_token' => ['label' => 'Jeton d’accès Instagram', 'secret' => true, 'required' => true, 'placeholder' => 'IGAA… ou EAAG…'],
                    'user_id'      => ['label' => 'ID du compte Instagram professionnel', 'secret' => false, 'required' => false, 'placeholder' => '17841400000000000', 'help' => 'Obligatoire seulement avec un jeton Facebook (EAAG…). Inutile avec un jeton Instagram (IGAA…).'],
                ],
                'guide' => [
                    'Le compte Instagram doit être un compte professionnel (Créateur ou Entreprise).',
                    'Sur developers.facebook.com, dans votre app, ajoutez le produit « Instagram » → « API avec connexion Instagram ».',
                    'Ajoutez le compte Instagram de Hope Media et cliquez sur « Générer un jeton ». Copiez le jeton (il commence par IGAA).',
                    'Le jeton dure 60 jours : renouvelez-le avant l’échéance en en générant un nouveau.',
                ],
                'docs' => 'https://developers.facebook.com/docs/instagram-platform/instagram-api-with-instagram-login/',
                'note' => 'Seules les vidéos et Reels sont importés.',
            ],
            'tiktok' => [
                'label' => 'TikTok', 'icon' => 'fa-tiktok', 'color' => '#2B2B35',
                'interval' => 6, 'max_results' => 20, 'max_limit' => 20,
                'fields' => [
                    'client_key'    => ['label' => 'Client Key', 'secret' => false, 'required' => true],
                    'client_secret' => ['label' => 'Client Secret', 'secret' => true, 'required' => true],
                    'access_token'  => ['label' => 'Access Token', 'secret' => true, 'required' => true, 'placeholder' => 'act.…'],
                    'refresh_token' => ['label' => 'Refresh Token', 'secret' => true, 'required' => true, 'placeholder' => 'rft.…', 'help' => 'Permet de renouveler automatiquement l’Access Token (valable 24 h).'],
                    'open_id'       => ['label' => 'Open ID', 'secret' => false, 'required' => false],
                ],
                'guide' => [
                    'Sur developers.tiktok.com, créez une app et ajoutez le produit « Login Kit » avec les autorisations user.info.basic et video.list.',
                    'Faites autoriser l’app par le compte TikTok de Hope Media (connexion OAuth avec le compte).',
                    'Copiez ici le Client Key, le Client Secret, puis l’Access Token, le Refresh Token et l’Open ID obtenus.',
                    'Le site renouvelle ensuite l’Access Token tout seul grâce au Refresh Token (valable un an).',
                ],
                'docs' => 'https://developers.tiktok.com/doc/display-api-get-started',
            ],
            'x' => [
                'label' => 'X', 'icon' => 'fa-x-twitter', 'color' => '#111111',
                'interval' => 12, 'max_results' => 10, 'max_limit' => 100,
                'fields' => [
                    'bearer_token' => ['label' => 'Bearer Token', 'secret' => true, 'required' => true, 'placeholder' => 'AAAAAAAAAAAAAAAAAAAAA…'],
                    'username'     => ['label' => 'Compte X (sans @)', 'secret' => false, 'required' => true, 'placeholder' => 'HopeMediaRDC'],
                ],
                'guide' => [
                    'Sur developer.x.com, créez un projet et une app.',
                    'Dans l’app → « Keys and tokens », générez le « Bearer Token » et collez-le ici.',
                    'Indiquez le nom du compte X de Hope Media, sans le @.',
                ],
                'docs' => 'https://developer.x.com/en/portal/dashboard',
                'note' => 'Important : l’offre gratuite de l’API X ne permet pas de lire les publications d’un compte. Il faut l’offre « Basic » (payante). Seules les publications contenant une vidéo sont importées.',
            ],
        ];
    }

    public static function get(string $platform): array
    {
        return self::all()[$platform] ?? throw new \InvalidArgumentException("Réseau inconnu : {$platform}");
    }

    /** @return string[] */
    public static function keys(): array
    {
        return array_keys(self::all());
    }

    /** Correspondance champ → clé de config/services.php (valeurs de repli du .env). */
    public static function configKey(string $platform, string $field): string
    {
        return "services.{$platform}.{$field}";
    }
}
