<?php

namespace App\Http\Controllers;

use App\Models\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ResourceController extends Controller
{
    public function index(Request $request)
    {
        $query = Resource::published()->orderByDesc('published_at');

        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($category = $request->get('categorie')) {
            $query->where('category', $category);
        }

        $resources  = $query->paginate(12)->withQueryString();
        $categories = Resource::categoryLabels();

        return view('ressources.index', compact('resources', 'search', 'categories', 'category'));
    }

    public function show(string $slug)
    {
        $resource = Resource::where('slug', $slug)->published()->firstOrFail();

        $related = Resource::published()
            ->where('id', '!=', $resource->id)
            ->where('category', $resource->category)
            ->orderByDesc('published_at')
            ->limit(4)
            ->get();

        return view('ressources.show', compact('resource', 'related'));
    }

    public function download(Resource $resource): StreamedResponse
    {
        abort_unless($resource->status === 'published', 404);

        $path = $resource->file_path;

        abort_unless(Storage::disk('public')->exists($path), 404);

        // Incrémenter le compteur de téléchargements
        $resource->increment('downloads');

        return Storage::disk('public')->download($path, $resource->file_name);
    }
}
