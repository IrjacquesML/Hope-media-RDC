<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\VideoPlatform;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VideoController extends Controller
{
    /**
     * Liste paginée des vidéos, avec filtre optionnel par plateforme.
     * Route : GET /videos?platform=youtube|facebook|instagram|tiktok
     */
    public function index(Request $request): View
    {
        $platform = $request->query('platform');

        // Valide que la plateforme est connue, sinon on ignore le filtre
        $validPlatforms = array_column(VideoPlatform::cases(), 'value');
        if ($platform && ! in_array($platform, $validPlatforms, true)) {
            $platform = null;
        }

        $query = Video::query()->orderByDesc('published_at');

        if ($platform) {
            $query->where('platform', $platform);
        }

        $videos = $query->paginate(12)->withQueryString();

        // Compteurs par plateforme pour les onglets
        $counts = Video::query()
            ->selectRaw('platform, COUNT(*) as total')
            ->groupBy('platform')
            ->pluck('total', 'platform')
            ->toArray();

        return view('videos.index', [
            'videos'          => $videos,
            'currentPlatform' => $platform,
            'counts'          => $counts,
            'total'           => array_sum($counts),
        ]);
    }

    /**
     * Affiche le détail d'une vidéo.
     * Route : GET /videos/{id}
     */
    public function show(Video $video): View
    {
        // Vidéos de la même plateforme (suggestions)
        $related = Video::where('platform', $video->platform)
            ->where('id', '!=', $video->id)
            ->orderByDesc('published_at')
            ->limit(6)
            ->get();

        return view('videos.show', compact('video', 'related'));
    }

    /**
     * Stub conservé pour compatibilité avec la route admin legacy.
     * La synchronisation des vidéos se fait désormais via les jobs de sync API.
     */
    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        abort(410, 'La saisie manuelle de vidéos est désactivée. Utilisez la synchronisation API.');
    }
}
