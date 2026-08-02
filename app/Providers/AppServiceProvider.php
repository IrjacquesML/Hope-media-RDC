<?php

namespace App\Providers;

use App\Services\FacebookSyncService;
use App\Services\InstagramSyncService;
use App\Services\TikTokSyncService;
use App\Services\YouTubeApiService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(YouTubeApiService::class, function () {
            return new YouTubeApiService(
                apiKey:     config('services.youtube.api_key'),
                channelId:  config('services.youtube.channel_id'),
                maxResults: (int) config('services.youtube.max_results', 10),
            );
        });

        $this->app->singleton(FacebookSyncService::class, function () {
            return new FacebookSyncService(
                pageId:           config('services.facebook.page_id'),
                pageAccessToken:  config('services.facebook.page_access_token'),
                maxResults:       (int) config('services.facebook.max_results', 20),
            );
        });

        $this->app->singleton(InstagramSyncService::class, function () {
            return new InstagramSyncService(
                igUserId:     config('services.instagram.user_id'),
                accessToken:  config('services.instagram.access_token'),
                maxResults:   (int) config('services.instagram.max_results', 20),
            );
        });

        $this->app->singleton(TikTokSyncService::class, function () {
            return new TikTokSyncService(
                clientKey:    config('services.tiktok.client_key'),
                clientSecret: config('services.tiktok.client_secret'),
                accessToken:  config('services.tiktok.access_token'),
                openId:       config('services.tiktok.open_id'),
                maxResults:   (int) config('services.tiktok.max_results', 20),
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useTailwind();
    }
}
