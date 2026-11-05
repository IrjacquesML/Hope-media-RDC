<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Partner;
use App\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;

/**
 * Paramètres du site modifiables par le super administrateur, sans toucher au code.
 *
 * - schema() décrit chaque champ (onglet, type, libellé, valeur par défaut).
 * - Les valeurs par défaut reprennent les textes d'origine du site : tant qu'un
 *   paramètre n'est pas modifié, le site reste identique.
 * - Lecture : Site::get('contact_email') en PHP, $site->contact_email dans les vues.
 */
final class Site
{
    private const CACHE_KEY = 'site.settings.v1';

    private static ?array $values = null;

    /** Instance partagée avec les vues ($site). */
    public static function instance(): self
    {
        return new self();
    }

    public function __get(string $key): mixed
    {
        return self::get($key);
    }

    public function __isset(string $key): bool
    {
        return self::get($key) !== null;
    }

    /**
     * Onglets de l'écran Paramètres.
     *
     * @return array<string, array{label: string, icon: string, intro?: string}>
     */
    public static function groups(): array
    {
        return [
            'identity' => ['label' => 'Identité', 'icon' => 'fa-id-card', 'intro' => 'Nom, slogan, logos et organisation affichés partout sur le site, dans les e-mails et les rapports PDF.'],
            'header'   => ['label' => 'En-tête et menu', 'icon' => 'fa-window-maximize', 'intro' => 'Barre du haut du site : bouton « Direct », bouton prière, horloge et libellés du menu.'],
            'footer'   => ['label' => 'Pied de page', 'icon' => 'fa-window-minimize', 'intro' => 'Bas de chaque page : texte de présentation, second logo, bloc newsletter et mention de copyright. Les partenaires se gèrent dans l’onglet « Partenaires ».'],
            'contact'  => ['label' => 'Coordonnées', 'icon' => 'fa-address-book', 'intro' => 'Utilisées dans le pied de page, la page Contact et les rapports.'],
            'social'   => ['label' => 'Liens des réseaux', 'icon' => 'fa-share-nodes', 'intro' => 'Liens publics vers vos comptes, affichés dans le pied de page. Laissez vide pour masquer un réseau. (La synchronisation des vidéos se règle dans « Synchronisation des réseaux ».)'],
            'home'     => ['label' => 'Accueil', 'icon' => 'fa-house', 'intro' => 'Textes de la grande bannière d’accueil quand aucune vidéo ni article n’est à la une.'],
            'about'    => ['label' => 'Page « La chaîne »', 'icon' => 'fa-tv', 'intro' => 'Contenu de la page de présentation.'],
            'seo'      => ['label' => 'Référencement', 'icon' => 'fa-magnifying-glass', 'intro' => 'Description et image utilisées par Google, WhatsApp et Facebook quand une page n’a pas les siennes.'],
            'mail'       => ['label' => 'E-mails', 'icon' => 'fa-envelope', 'intro' => 'Service qui envoie les e-mails du site : newsletter, bienvenue des abonnés, réponses aux messages. Avec Brevo (gratuit jusqu’à 300 e-mails par jour) : brevo.com → Paramètres → « SMTP & API » → onglet SMTP. Copiez l’identifiant (login) et générez une « clé SMTP ». L’adresse d’expéditeur doit être validée dans Brevo → « Expéditeurs ».'],
            'newsletter' => ['label' => 'Newsletter', 'icon' => 'fa-envelope-open-text', 'intro' => 'La newsletter part automatiquement chaque semaine aux abonnés actifs, avec les nouveautés publiées depuis le dernier envoi. S’il n’y a aucune nouveauté, rien n’est envoyé.'],
            'system'     => ['label' => 'Système', 'icon' => 'fa-gears'],
        ];
    }

    /**
     * Description de tous les champs.
     * Types : text, textarea, url, email, tel, image, boolean, lines (une valeur par ligne), select, secret (chiffré).
     *
     * @return array<string, array{group: string, type: string, label: string, default: mixed, help?: string, options?: array, placeholder?: string}>
     */
    public static function schema(): array
    {
        return [
            // Identité
            'site_name'       => ['group' => 'identity', 'type' => 'text', 'required' => true, 'label' => 'Nom court du site', 'default' => 'Hope Media RDC', 'help' => 'Titre des onglets du navigateur, signature des réponses, etc.'],
            'site_full_name'  => ['group' => 'identity', 'type' => 'text', 'required' => true, 'label' => 'Nom officiel complet', 'default' => 'Hope Media Network RDC', 'help' => 'En-tête des rapports PDF et documents officiels.'],
            'slogan'          => ['group' => 'identity', 'type' => 'text', 'label' => 'Slogan', 'default' => 'La Parole qui éclaire'],
            'organization'    => ['group' => 'identity', 'type' => 'text', 'label' => 'Organisation de rattachement', 'default' => 'Église Adventiste du Septième Jour — Union du Congo Ouest (UCO)'],
            'city_country'    => ['group' => 'identity', 'type' => 'text', 'label' => 'Ville et pays', 'default' => 'Kinshasa, République Démocratique du Congo', 'help' => 'Affiché dans l’en-tête des rapports PDF.'],
            'logo_dark'       => ['group' => 'identity', 'type' => 'image', 'label' => 'Logo pour fond sombre', 'default' => 'images/brand/logo-hope-media-negatif.webp', 'help' => 'Version claire du logo, utilisée sur le thème sombre du site.'],
            'logo_light'      => ['group' => 'identity', 'type' => 'image', 'label' => 'Logo pour fond clair', 'default' => 'images/brand/logo-hope-media.webp', 'help' => 'Utilisé en mode clair, sur la page de connexion et dans les rapports PDF (PNG ou JPG recommandé pour le PDF).'],
            'favicon'         => ['group' => 'identity', 'type' => 'image', 'label' => 'Icône d’onglet (favicon)', 'default' => null, 'help' => 'Image carrée PNG, 64 × 64 px ou plus.'],
            'theme_color'     => ['group' => 'identity', 'type' => 'text', 'label' => 'Couleur de la barre du navigateur (mobile)', 'default' => '#0B1530', 'placeholder' => '#0B1530'],

            // En-tête et menu
            'live_enabled'    => ['group' => 'header', 'type' => 'boolean', 'label' => 'Afficher le bouton « Direct »', 'default' => true],
            'live_url'        => ['group' => 'header', 'type' => 'url', 'label' => 'Lien du direct', 'default' => 'https://www.youtube.com/@Hopemedia-u4b/live', 'help' => 'Lien vers le direct sur n’importe quel réseau : YouTube, Facebook, TikTok, Instagram…'],
            'live_label'      => ['group' => 'header', 'type' => 'text', 'required' => true, 'label' => 'Texte du bouton direct', 'default' => 'Direct'],
            'prayer_enabled'  => ['group' => 'header', 'type' => 'boolean', 'label' => 'Afficher le bouton « Prière »', 'default' => true],
            'prayer_label'    => ['group' => 'header', 'type' => 'text', 'required' => true, 'label' => 'Texte du bouton prière', 'default' => 'Prière'],
            'clock_enabled'   => ['group' => 'header', 'type' => 'boolean', 'label' => 'Afficher l’horloge', 'default' => true],
            'clock_city'      => ['group' => 'header', 'type' => 'text', 'required' => true, 'label' => 'Ville de l’horloge', 'default' => 'Kinshasa'],
            'nav_home'        => ['group' => 'header', 'type' => 'text', 'required' => true, 'label' => 'Menu : Accueil', 'default' => 'Accueil'],
            'nav_videos'      => ['group' => 'header', 'type' => 'text', 'required' => true, 'label' => 'Menu : Vidéos', 'default' => 'Vidéos'],
            'nav_posts'       => ['group' => 'header', 'type' => 'text', 'required' => true, 'label' => 'Menu : Enseignements', 'default' => 'Enseignements'],
            'nav_news'        => ['group' => 'header', 'type' => 'text', 'required' => true, 'label' => 'Menu : Écho de la mission', 'default' => 'Écho de la mission'],
            'nav_resources'   => ['group' => 'header', 'type' => 'text', 'required' => true, 'label' => 'Menu : Ressources', 'default' => 'Ressources'],
            'nav_about'       => ['group' => 'header', 'type' => 'text', 'required' => true, 'label' => 'Menu : La chaîne', 'default' => 'La chaîne'],
            'nav_contact'     => ['group' => 'header', 'type' => 'text', 'required' => true, 'label' => 'Menu : Contact', 'default' => 'Écrire à la rédaction'],

            // Pied de page
            'footer_text'          => ['group' => 'footer', 'type' => 'textarea', 'label' => 'Texte de présentation', 'default' => 'Vidéos, enseignements et actualités de la mission, sur tous vos écrans et tous vos réseaux.'],
            'footer_contact_title' => ['group' => 'footer', 'type' => 'text', 'required' => true, 'label' => 'Titre du bloc coordonnées', 'default' => 'La rédaction'],
            'footer_partners_title'=> ['group' => 'footer', 'type' => 'text', 'required' => true, 'label' => 'Titre du bloc partenaires', 'default' => 'Nos partenaires'],
            'footer_logo2_dark'    => ['group' => 'footer', 'type' => 'image', 'label' => 'Second logo (fond sombre)', 'default' => 'images/brand/logo-adventiste-blanc.webp'],
            'footer_logo2_light'   => ['group' => 'footer', 'type' => 'image', 'label' => 'Second logo (fond clair)', 'default' => 'images/brand/logo-adventiste.webp'],
            'footer_logo2_alt'     => ['group' => 'footer', 'type' => 'text', 'label' => 'Nom du second logo', 'default' => 'Église Adventiste du Septième Jour', 'help' => 'Texte lu par les lecteurs d’écran. Videz les deux images pour masquer ce logo.'],
            'footer_copyright'     => ['group' => 'footer', 'type' => 'text', 'label' => 'Mention de copyright', 'default' => 'Hope Media RDC. Église Adventiste du 7ème Jour - UCO', 'help' => '« © année » est ajouté automatiquement devant.'],
            'newsletter_enabled'   => ['group' => 'footer', 'type' => 'boolean', 'label' => 'Afficher le bloc newsletter', 'default' => true],
            'newsletter_title'     => ['group' => 'footer', 'type' => 'text', 'required' => true, 'label' => 'Titre du bloc newsletter', 'default' => 'Ne manquez aucune sortie'],
            'newsletter_text'      => ['group' => 'footer', 'type' => 'textarea', 'label' => 'Texte du bloc newsletter', 'default' => "Les nouvelles vidéos, les enseignements et l'Écho de la mission, chaque semaine dans votre boîte mail."],

            // Coordonnées
            'contact_address' => ['group' => 'contact', 'type' => 'text', 'label' => 'Adresse', 'default' => '01 Lubumbashi, Q. Pigeon, C. Ngaliema, Kinshasa'],
            'contact_map_url' => ['group' => 'contact', 'type' => 'url', 'label' => 'Lien Google Maps', 'default' => null, 'help' => 'Facultatif : rend l’adresse cliquable.'],
            'contact_phone'   => ['group' => 'contact', 'type' => 'tel', 'label' => 'Téléphone principal', 'default' => '+243 852 283 241'],
            'contact_phone2'  => ['group' => 'contact', 'type' => 'tel', 'label' => 'Second téléphone', 'default' => null],
            'contact_whatsapp'=> ['group' => 'contact', 'type' => 'tel', 'label' => 'Numéro WhatsApp', 'default' => null, 'help' => 'Format international, ex. +243 852 283 241. Ajoute un lien « Écrire sur WhatsApp ».'],
            'contact_email'   => ['group' => 'contact', 'type' => 'email', 'label' => 'E-mail public', 'default' => 'hopemediasociaux@gmail.com'],
            'contact_hours'   => ['group' => 'contact', 'type' => 'lines', 'label' => 'Horaires de la rédaction', 'default' => "Lundi - Vendredi : 9h00 - 17h00\nSamedi : Fermé\nDimanche : 9h00 - 12h00", 'help' => 'Une ligne par plage horaire.'],

            // Liens des réseaux
            'social_youtube'   => ['group' => 'social', 'type' => 'url', 'label' => 'YouTube', 'default' => 'https://www.youtube.com/@Hopemedia-u4b'],
            'social_facebook'  => ['group' => 'social', 'type' => 'url', 'label' => 'Facebook', 'default' => null],
            'social_instagram' => ['group' => 'social', 'type' => 'url', 'label' => 'Instagram', 'default' => null],
            'social_tiktok'    => ['group' => 'social', 'type' => 'url', 'label' => 'TikTok', 'default' => null],
            'social_x'         => ['group' => 'social', 'type' => 'url', 'label' => 'X (Twitter)', 'default' => null],
            'social_whatsapp'  => ['group' => 'social', 'type' => 'url', 'label' => 'Chaîne WhatsApp', 'default' => null],
            'social_telegram'  => ['group' => 'social', 'type' => 'url', 'label' => 'Telegram', 'default' => null],
            'social_linkedin'  => ['group' => 'social', 'type' => 'url', 'label' => 'LinkedIn', 'default' => null],

            // Accueil
            'home_kicker' => ['group' => 'home', 'type' => 'text', 'label' => 'Surtitre', 'default' => 'Hope Media RDC'],
            'home_title'  => ['group' => 'home', 'type' => 'text', 'required' => true, 'label' => 'Titre', 'default' => 'La Parole qui éclaire'],
            'home_text'   => ['group' => 'home', 'type' => 'textarea', 'label' => 'Texte', 'default' => 'Vidéos, enseignements et actualités de la mission, en continu sur nos réseaux.'],

            // Page « La chaîne »
            'about_title'     => ['group' => 'about', 'type' => 'text', 'required' => true, 'label' => 'Titre', 'default' => 'Hope Media RDC'],
            'about_lead'      => ['group' => 'about', 'type' => 'textarea', 'label' => 'Introduction', 'default' => "Une voix pour l'espoir. Des prédications, des témoignages, des études bibliques et l'Écho de la mission, publiés chaque semaine sur toutes les grandes plateformes."],
            'about_figures'   => ['group' => 'about', 'type' => 'lines', 'label' => 'Chiffres clés', 'default' => "500K+ | Abonnés cumulés\n4 | Plateformes actives\n1000+ | Vidéos publiées\n50+ | Pays touchés", 'help' => 'Une ligne par chiffre, au format « valeur | libellé ». Quatre lignes conseillées.'],
            'about_mission'   => ['group' => 'about', 'type' => 'textarea', 'label' => 'Notre mission', 'default' => "Proclamer le message des trois anges (Apocalypse 14) à travers les médias numériques modernes, en touchant chaque foyer, chaque cœur et chaque nation avec l'espérance de l'Évangile."],
            'about_vision'    => ['group' => 'about', 'type' => 'textarea', 'label' => 'Notre vision', 'default' => 'Être la référence médiatique adventiste en Afrique Centrale, produisant du contenu spirituel de haute qualité qui prépare les gens au retour imminent de Jésus-Christ.'],
            'about_values'    => ['group' => 'about', 'type' => 'lines', 'label' => 'Nos valeurs', 'default' => "Fidélité à la Parole de Dieu\nExcellence dans la production\nService communautaire\nIntégrité et transparence\nInnovation au service de l'Évangile", 'help' => 'Une valeur par ligne.'],
            'about_team'      => ['group' => 'about', 'type' => 'textarea', 'label' => 'Texte sur l’équipe', 'default' => 'Notre équipe de bénévoles et de techniciens dévoués travaille chaque jour pour produire du contenu de qualité qui édifie, encourage et transforme des vies. Nous croyons que la technologie est un outil puissant pour partager la Bonne Nouvelle.'],
            'about_church_title' => ['group' => 'about', 'type' => 'text', 'label' => 'Titre du bloc Église', 'default' => "Une production de l'Église Adventiste du 7ème Jour"],
            'about_church_text'  => ['group' => 'about', 'type' => 'textarea', 'label' => 'Texte du bloc Église', 'default' => "Hope Media RDC est la plateforme médiatique officielle de l'Église Adventiste du 7ème Jour — Union du Congo Ouest (UCO). Fondée pour répondre à la mission évangélique dans un monde de plus en plus connecté, notre plateforme diffuse des contenus spirituels, bibliques et communautaires sur toutes les grandes plateformes numériques.\n\nL'Église Adventiste du 7ème Jour est un mouvement chrétien mondial présent dans plus de 200 pays avec plus de 21 millions de membres. En RDC, l'Église est organisée en plusieurs unions, dont l'Union du Congo Ouest (UCO) à laquelle Hope Media RDC est rattachée. Les adventistes du 7ème jour accordent une importance particulière à l'étude des Écritures, à la santé holistique, au sabbat (le 7ème jour) et à la préparation au retour de Christ. Hope Media RDC est fière de porter haut les valeurs de cette église et de les partager avec le monde entier à travers les outils numériques modernes.", 'help' => 'Laissez une ligne vide entre deux paragraphes.'],

            // Référencement
            'seo_description' => ['group' => 'seo', 'type' => 'textarea', 'label' => 'Description par défaut', 'default' => 'Hope Media RDC : vidéos, enseignements et actualités de la mission, en continu sur YouTube, Facebook, Instagram et TikTok.', 'help' => '150 à 160 caractères conseillés.'],
            'seo_keywords'    => ['group' => 'seo', 'type' => 'text', 'label' => 'Mots-clés', 'default' => 'enseignements bibliques, église adventiste, prières, vidéos religieuses'],
            'seo_image'       => ['group' => 'seo', 'type' => 'image', 'label' => 'Image de partage par défaut', 'default' => 'images/hero-bible.jpg', 'help' => 'Format paysage 1200 × 630 px conseillé.'],

            // E-mails (Brevo par défaut)
            'mail_host'         => ['group' => 'mail', 'type' => 'text', 'label' => 'Serveur SMTP', 'default' => 'smtp-relay.brevo.com'],
            'mail_port'         => ['group' => 'mail', 'type' => 'select', 'label' => 'Port', 'default' => '587', 'options' => ['587' => '587 (recommandé, STARTTLS)', '465' => '465 (SSL)', '2525' => '2525 (si 587 est bloqué)']],
            'mail_username'     => ['group' => 'mail', 'type' => 'text', 'label' => 'Identifiant SMTP', 'default' => null, 'placeholder' => 'ex. 7a1b2c001@smtp-brevo.com', 'help' => 'Brevo : le « Login » affiché dans l’onglet SMTP (pas votre adresse de connexion).'],
            'mail_password'     => ['group' => 'mail', 'type' => 'secret', 'label' => 'Clé SMTP / mot de passe', 'default' => null, 'help' => 'Chiffré dans la base, jamais réaffiché. Laissez vide pour conserver la clé enregistrée.'],
            'mail_from_address' => ['group' => 'mail', 'type' => 'email', 'label' => 'Adresse d’expéditeur', 'default' => 'hopemediasociaux@gmail.com', 'help' => 'Doit être validée comme expéditeur dans Brevo.'],
            'mail_from_name'    => ['group' => 'mail', 'type' => 'text', 'label' => 'Nom d’expéditeur', 'default' => 'Hope Media RDC'],

            // Newsletter automatique
            'newsletter_auto'    => ['group' => 'newsletter', 'type' => 'boolean', 'label' => 'Envoyer la newsletter automatiquement', 'default' => true],
            'newsletter_day'     => ['group' => 'newsletter', 'type' => 'select', 'label' => 'Jour d’envoi', 'default' => 'friday', 'options' => ['monday' => 'Lundi', 'tuesday' => 'Mardi', 'wednesday' => 'Mercredi', 'thursday' => 'Jeudi', 'friday' => 'Vendredi', 'saturday' => 'Samedi', 'sunday' => 'Dimanche']],
            'newsletter_hour'    => ['group' => 'newsletter', 'type' => 'select', 'label' => 'Heure d’envoi (heure de Kinshasa)', 'default' => '9', 'options' => array_combine(array_map('strval', range(6, 21)), array_map(fn ($h) => $h . ' h 00', range(6, 21)))],
            'newsletter_subject' => ['group' => 'newsletter', 'type' => 'text', 'required' => true, 'label' => 'Objet de l’e-mail', 'default' => 'Cette semaine sur {site}', 'help' => '{site} est remplacé par le nom du site, {date} par la date du jour.'],
            'newsletter_intro'   => ['group' => 'newsletter', 'type' => 'textarea', 'label' => 'Message d’introduction', 'default' => 'Voici les nouveautés publiées cette semaine. Bonne lecture et bon visionnage !'],
            'newsletter_welcome' => ['group' => 'newsletter', 'type' => 'boolean', 'label' => 'Envoyer un e-mail de bienvenue aux nouveaux abonnés', 'default' => true],
            'newsletter_welcome_text' => ['group' => 'newsletter', 'type' => 'textarea', 'label' => 'Texte de bienvenue', 'default' => "Merci de vous être abonné(e) ! Chaque semaine, vous recevrez les nouvelles vidéos, les enseignements et l'Écho de la mission."],

            // Système
            'sync_without_cron' => ['group' => 'system', 'type' => 'boolean', 'label' => 'Synchroniser les réseaux sans tâche planifiée (cron)', 'default' => true, 'help' => 'Si le serveur n’a pas de cron, les visites du site déclenchent la synchronisation automatique (au plus toutes les 15 minutes, après l’envoi de la page). Sans effet si le cron fonctionne déjà.'],
        ];
    }

    /** Valeur d'un paramètre (valeur enregistrée, sinon valeur par défaut). */
    public static function get(string $key, mixed $fallback = null): mixed
    {
        $values = self::values();
        $def = self::schema()[$key] ?? null;

        if (array_key_exists($key, $values)) {
            $value = $values[$key];
        } else {
            $value = $def['default'] ?? $fallback;
        }

        if (($def['type'] ?? null) === 'boolean') {
            return (bool) $value;
        }

        // Secrets (mot de passe SMTP) : chiffrés dans la base
        if (($def['type'] ?? null) === 'secret' && $value !== null && $value !== '') {
            try {
                $value = Crypt::decryptString($value);
            } catch (\Throwable) {
                return $fallback;   // APP_KEY changée : la valeur doit être ressaisie
            }
        }

        return ($value === '' || $value === null) ? $fallback : $value;
    }

    /** URL publique d'un paramètre image, ou null. */
    public static function image(string $key): ?string
    {
        $path = self::get($key);

        return $path ? asset($path) : null;
    }

    /** Lignes non vides d'un paramètre « lines ». */
    public static function lines(string $key): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R/u', (string) self::get($key, '')))));
    }

    /** Paires « valeur | libellé » d'un paramètre « lines ». */
    public static function pairs(string $key): array
    {
        return array_map(function ($line) {
            [$a, $b] = array_pad(array_map('trim', explode('|', $line, 2)), 2, '');
            return [$a, $b];
        }, self::lines($key));
    }

    /** Paragraphes d'un texte (séparés par une ligne vide). */
    public static function paragraphs(string $key): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R\s*\R/u', (string) self::get($key, '')))));
    }

    /** Liens des réseaux renseignés : [clé => [label, icône, url]]. */
    public static function socialLinks(): array
    {
        $icons = [
            'youtube' => 'fa-youtube', 'facebook' => 'fa-facebook-f', 'instagram' => 'fa-instagram', 'tiktok' => 'fa-tiktok',
            'x' => 'fa-x-twitter', 'whatsapp' => 'fa-whatsapp', 'telegram' => 'fa-telegram', 'linkedin' => 'fa-linkedin-in',
        ];
        $links = [];
        foreach ($icons as $key => $icon) {
            if ($url = self::get("social_{$key}")) {
                $links[$key] = [self::schema()["social_{$key}"]['label'], $icon, $url];
            }
        }

        return $links;
    }

    /** Le service d'envoi d'e-mails est-il configuré ? */
    public static function mailConfigured(): bool
    {
        return (bool) (self::get('mail_host') && self::get('mail_username') && self::get('mail_password') && self::get('mail_from_address'));
    }

    /**
     * Applique les réglages e-mail de l'admin à la configuration de Laravel
     * (remplace MAIL_* du .env dès que le service est configuré).
     */
    public static function applyMailConfig(): void
    {
        if (! self::mailConfigured()) {
            return;
        }

        $port = (int) self::get('mail_port', 587);
        config([
            'mail.default'                => 'smtp',
            'mail.mailers.smtp.host'      => self::get('mail_host'),
            'mail.mailers.smtp.port'      => $port,
            'mail.mailers.smtp.scheme'    => $port === 465 ? 'smtps' : 'smtp',
            'mail.mailers.smtp.username'  => self::get('mail_username'),
            'mail.mailers.smtp.password'  => self::get('mail_password'),
            'mail.mailers.smtp.timeout'   => 20,
            'mail.from.address'           => self::get('mail_from_address'),
            'mail.from.name'              => self::get('mail_from_name') ?: self::get('site_name'),
        ]);
    }

    /** Lien tel: d'un numéro. */
    public static function telHref(?string $phone): ?string
    {
        return $phone ? 'tel:' . preg_replace('/[^\d+]/', '', $phone) : null;
    }

    /** Lien WhatsApp d'un numéro. */
    public static function whatsappHref(?string $phone): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $phone);

        return $digits ? 'https://wa.me/' . $digits : null;
    }

    /** Partenaires actifs, pour le pied de page. */
    public static function partners(): Collection
    {
        try {
            return Cache::rememberForever('site.partners.v1', fn () => Partner::active()->ordered()->get(['name', 'url', 'logo']));
        } catch (\Throwable) {
            return collect();
        }
    }

    /** Enregistre plusieurs valeurs puis vide le cache. */
    public static function save(array $values): void
    {
        foreach ($values as $key => $value) {
            if (! isset(self::schema()[$key])) {
                continue;
            }
            if (self::schema()[$key]['type'] === 'secret' && $value !== null && $value !== '') {
                $value = Crypt::encryptString((string) $value);
            }
            Setting::updateOrCreate(['key' => $key], ['value' => is_bool($value) ? ($value ? '1' : '0') : $value]);
        }
        self::flush();
    }

    /** Supprime la valeur enregistrée (retour à la valeur par défaut). */
    public static function reset(string $key): void
    {
        Setting::where('key', $key)->delete();
        self::flush();
    }

    /** La valeur vient-elle de l'admin (et non de la valeur par défaut) ? */
    public static function isCustom(string $key): bool
    {
        return array_key_exists($key, self::values());
    }

    public static function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
        Cache::forget('site.partners.v1');
        self::$values = null;
    }

    /** @return array<string, string|null> valeurs enregistrées */
    private static function values(): array
    {
        if (self::$values !== null) {
            return self::$values;
        }

        try {
            if (! Cache::has(self::CACHE_KEY) && ! Schema::hasTable('settings')) {
                return self::$values = [];   // avant migration : ne rien mettre en cache
            }

            return self::$values = Cache::rememberForever(self::CACHE_KEY, fn () => Setting::pluck('value', 'key')->all());
        } catch (\Throwable) {
            return self::$values = [];
        }
    }
}
