<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // -------------------------------------------------------------------------
    // YouTube Data API v3
    // Clés à définir dans .env (ne jamais les committer en dur ici).
    // -------------------------------------------------------------------------
    'youtube' => [
        'api_key'    => env('YOUTUBE_API_KEY'),
        'channel_id' => env('YOUTUBE_CHANNEL_ID'),
        'max_results' => (int) env('YOUTUBE_MAX_RESULTS', 10),
    ],

    // -------------------------------------------------------------------------
    // Instagram Graph API (Business / Creator account)
    // Générer via: https://developers.facebook.com/ → votre app → Instagram Graph API
    // INSTAGRAM_USER_ID  : l'ID numérique du compte IG Business (ex: 17841400008460056)
    // INSTAGRAM_ACCESS_TOKEN : Long-lived User Token ou System User Token
    // -------------------------------------------------------------------------
    'instagram' => [
        'user_id'      => env('INSTAGRAM_USER_ID'),
        'access_token' => env('INSTAGRAM_ACCESS_TOKEN'),
        'max_results'  => (int) env('INSTAGRAM_MAX_RESULTS', 20),
    ],

    // -------------------------------------------------------------------------
    // Facebook Graph API (Page)
    // FACEBOOK_PAGE_ID           : ID numérique de la Page Facebook (ex: 123456789)
    // FACEBOOK_PAGE_ACCESS_TOKEN : Page Access Token (long-lived, 60 jours ou permanent)
    //   Obtenir via: Graph API Explorer → choisir votre Page → générer le token
    // -------------------------------------------------------------------------
    'facebook' => [
        'page_id'            => env('FACEBOOK_PAGE_ID'),
        'page_access_token'  => env('FACEBOOK_PAGE_ACCESS_TOKEN'),
        'max_results'        => (int) env('FACEBOOK_MAX_RESULTS', 20),
    ],

    // -------------------------------------------------------------------------
    // TikTok Content API v2
    // Enregistrer votre app sur: https://developers.tiktok.com/
    // TIKTOK_CLIENT_KEY    : Client Key de votre app TikTok
    // TIKTOK_CLIENT_SECRET : Client Secret de votre app TikTok
    // TIKTOK_ACCESS_TOKEN  : Access Token OAuth 2.0 du propriétaire du compte
    //   (obtenu après flux OAuth — voir TikTok Login Kit)
    // TIKTOK_OPEN_ID       : open_id de l'utilisateur TikTok (retourné lors de l'OAuth)
    // -------------------------------------------------------------------------
    'tiktok' => [
        'client_key'    => env('TIKTOK_CLIENT_KEY'),
        'client_secret' => env('TIKTOK_CLIENT_SECRET'),
        'access_token'  => env('TIKTOK_ACCESS_TOKEN'),
        'refresh_token' => env('TIKTOK_REFRESH_TOKEN'),
        'open_id'       => env('TIKTOK_OPEN_ID'),
        'max_results'   => (int) env('TIKTOK_MAX_RESULTS', 20),
    ],

];
