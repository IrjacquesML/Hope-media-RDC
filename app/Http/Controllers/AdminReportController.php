<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Contact;
use App\Models\News;
use App\Models\NewsletterSubscription;
use App\Models\Post;
use App\Models\Resource;
use App\Models\Video;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Support\Site;

/**
 * Rapport d'activités PDF de Hope Media Network RDC, filtré sur une période.
 */
class AdminReportController extends Controller
{
    /** Nombre maximal de lignes détaillées par section (au-delà, le total reste exact). */
    private const MAX_ROWS = 200;

    public const SECTIONS = [
        'news'       => 'Écho de la mission',
        'posts'      => 'Enseignements',
        'videos'     => 'Vidéos',
        'resources'  => 'Ressources',
        'engagement' => 'Engagement et commentaires',
        'contacts'   => 'Messages et demandes de prière',
        'newsletter' => 'Newsletter',
    ];

    public function index()
    {
        $signatory = Cache::get('report.signatory', [
            'name'  => Auth::user()?->name,
            'role'  => '',
            'place' => 'Kinshasa',
        ]);

        return view('admin.reports.index', [
            'sections'  => self::SECTIONS,
            'signatory' => $signatory,
        ]);
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'date_from'       => 'required|date',
            'date_to'         => 'required|date|after_or_equal:date_from',
            'title'           => 'nullable|string|max:150',
            'sections'        => 'required|array|min:1',
            'sections.*'      => 'in:' . implode(',', array_keys(self::SECTIONS)),
            'signatory_name'  => 'required|string|max:120',
            'signatory_role'  => 'required|string|max:120',
            'signatory_place' => 'nullable|string|max:80',
            'signature'       => 'nullable|image|mimes:png,jpeg|max:1024',
            'observations'    => 'nullable|string|max:5000',
            'output'          => 'nullable|in:preview,download',
        ], [
            'date_to.after_or_equal' => 'La date de fin doit être postérieure ou égale à la date de début.',
            'sections.required'      => 'Sélectionnez au moins une section à inclure dans le rapport.',
            'signatory_name.required'=> 'Le nom du signataire est obligatoire.',
            'signatory_role.required'=> 'La fonction du signataire est obligatoire.',
        ]);

        $from = Carbon::parse($validated['date_from'])->startOfDay();
        $to = Carbon::parse($validated['date_to'])->endOfDay();
        $sections = $validated['sections'];

        // Mémorise le dernier signataire pour pré-remplir le formulaire
        Cache::forever('report.signatory', [
            'name'  => $validated['signatory_name'],
            'role'  => $validated['signatory_role'],
            'place' => $validated['signatory_place'] ?? 'Kinshasa',
        ]);

        $data = [
            'title'        => ($validated['title'] ?? null) ?: "Rapport d'activités",
            'from'         => $from,
            'to'           => $to,
            'days'         => (int) $from->diffInDays($to) + 1,
            'sections'     => $sections,
            'sectionNames' => self::SECTIONS,
            'reference'    => 'HMN-RPT-' . $from->format('Ymd') . '-' . $to->format('Ymd') . '-' . now()->format('His'),
            'generatedAt'  => now(),
            'generatedBy'  => Auth::user()?->name,
            'signatory'    => [
                'name'      => $validated['signatory_name'],
                'role'      => $validated['signatory_role'],
                'place'     => ($validated['signatory_place'] ?? null) ?: 'Kinshasa',
                'signature' => $request->hasFile('signature') ? $this->fileToDataUri($request->file('signature')->getRealPath()) : null,
            ],
            'observations' => $validated['observations'] ?? null,
            'logo'         => $this->logoDataUri(),
            'maxRows'      => self::MAX_ROWS,
        ];

        $data += $this->collect($from, $to, $sections);

        $pdf = Pdf::loadView('admin.reports.pdf', $data)
            ->setPaper('a4', 'portrait')
            ->setOption('isRemoteEnabled', false)
            ->setOption('defaultFont', 'DejaVu Sans')
            ->setOption('isFontSubsettingEnabled', true);

        // Pagination « Page X / Y » sur chaque page
        $pdf->render();
        $dompdf = $pdf->getDomPDF();
        $canvas = $dompdf->getCanvas();
        $font = $dompdf->getFontMetrics()->getFont('DejaVu Sans');
        $canvas->page_text($canvas->get_width() - 110, $canvas->get_height() - 37, 'Page {PAGE_NUM} / {PAGE_COUNT}', $font, 7.5, [0.35, 0.4, 0.5]);

        $filename = 'rapport-hope-media-' . $from->format('Y-m-d') . '-au-' . $to->format('Y-m-d') . '.pdf';

        return ($validated['output'] ?? 'download') === 'preview'
            ? $pdf->stream($filename)
            : $pdf->download($filename);
    }

    /**
     * Rassemble les données de chaque section demandée.
     */
    private function collect(Carbon $from, Carbon $to, array $sections): array
    {
        $inPeriod = fn (Builder $q, string $col = 'created_at') => $q->whereBetween($col, [$from, $to]);
        // Contenus : date de publication, à défaut date de création
        $published = function (Builder $q) use ($from, $to) {
            $t = $q->getModel()->getTable();
            $q->where(function ($w) use ($from, $to, $t) {
                $w->whereBetween("$t.published_at", [$from, $to])
                  ->orWhere(fn ($x) => $x->whereNull("$t.published_at")->whereBetween("$t.created_at", [$from, $to]));
            });
        };

        $out = ['summary' => []];

        if (in_array('news', $sections)) {
            $q = News::query()->where('status', 'published')->tap($published);
            $items = (clone $q)->withCount('comments')->orderBy('published_at')->limit(self::MAX_ROWS)->get();
            $out['news'] = [
                'count'      => (clone $q)->count(),
                'views'      => (int) (clone $q)->sum('views'),
                'likes'      => (int) (clone $q)->sum('likes'),
                'drafts'     => News::where('status', 'draft')->tap(fn ($b) => $inPeriod($b))->count(),
                'items'      => $items,
                'byCategory' => (clone $q)->selectRaw("COALESCE(category, 'Sans catégorie') as label, COUNT(*) as total, SUM(views) as views")
                                    ->groupBy('label')->orderByDesc('total')->get(),
            ];
            $out['summary'][] = ['Articles publiés (Écho de la mission)', $out['news']['count']];
        }

        if (in_array('posts', $sections)) {
            $q = Post::query()->where('posts.status', 'published')->tap($published);
            $out['posts'] = [
                'count'      => (clone $q)->count(),
                'views'      => (int) (clone $q)->sum('views'),
                'likes'      => (int) (clone $q)->sum('likes'),
                'items'      => (clone $q)->with('category')->withCount('comments')->orderBy('published_at')->limit(self::MAX_ROWS)->get(),
                'byType'     => (clone $q)->selectRaw('content_type as label, COUNT(*) as total')->groupBy('content_type')->orderByDesc('total')->get(),
                'byCategory' => (clone $q)->join('categories', 'categories.id', '=', 'posts.category_id')
                                    ->selectRaw('categories.name as label, COUNT(*) as total')->groupBy('categories.name')->orderByDesc('total')->get(),
            ];
            $out['summary'][] = ['Enseignements publiés', $out['posts']['count']];
        }

        if (in_array('videos', $sections)) {
            $q = Video::query()->tap($published);
            $out['videos'] = [
                'count'      => (clone $q)->count(),
                'views'      => (int) (clone $q)->sum('view_count'),
                'likes'      => (int) (clone $q)->sum('likes_count'),
                'comments'   => (int) (clone $q)->sum('comments_count'),
                'items'      => (clone $q)->orderBy('published_at')->limit(self::MAX_ROWS)->get(),
                'byPlatform' => (clone $q)->selectRaw('platform as label, COUNT(*) as total, SUM(view_count) as views')->groupBy('platform')->orderByDesc('total')->get(),
            ];
            $out['summary'][] = ['Vidéos publiées', $out['videos']['count']];
        }

        if (in_array('resources', $sections)) {
            $q = Resource::query()->where('status', 'published')->tap($published);
            $out['resources'] = [
                'count'     => (clone $q)->count(),
                'downloads' => (int) (clone $q)->sum('downloads'),
                'items'     => (clone $q)->orderBy('published_at')->limit(self::MAX_ROWS)->get(),
            ];
            $out['summary'][] = ['Ressources publiées', $out['resources']['count']];
        }

        if (in_array('engagement', $sections)) {
            $q = Comment::query()->tap(fn ($b) => $inPeriod($b));
            $topNews = News::published()->orderByDesc('views')->limit(5)->get(['title', 'views', 'likes', 'published_at']);
            $topPosts = Post::where('status', 'published')->orderByDesc('views')->limit(5)->get(['title', 'views', 'likes', 'published_at']);
            $out['engagement'] = [
                'total'        => (clone $q)->where('is_admin_reply', false)->count(),
                'approved'     => (clone $q)->where('is_admin_reply', false)->where('is_approved', true)->count(),
                'pending'      => (clone $q)->where('is_admin_reply', false)->where('is_approved', false)->count(),
                'adminReplies' => (clone $q)->where('is_admin_reply', true)->count(),
                'onNews'       => (clone $q)->where('is_admin_reply', false)->where('commentable_type', News::class)->count(),
                'onPosts'      => (clone $q)->where('is_admin_reply', false)->where('commentable_type', Post::class)->count(),
                'topNews'      => $topNews,
                'topPosts'     => $topPosts,
            ];
            $out['summary'][] = ['Commentaires reçus', $out['engagement']['total']];
        }

        if (in_array('contacts', $sections)) {
            $q = Contact::query()->tap(fn ($b) => $inPeriod($b));
            $total = (clone $q)->count();
            $replied = (clone $q)->where('status', 'replied')->count();
            $out['contacts'] = [
                'total'    => $total,
                'replied'  => $replied,
                'rate'     => $total ? round($replied * 100 / $total) : 0,
                'byType'   => (clone $q)->selectRaw('request_type as label, COUNT(*) as total')->groupBy('request_type')->orderByDesc('total')->get(),
                'byStatus' => (clone $q)->selectRaw('status as label, COUNT(*) as total')->groupBy('status')->orderByDesc('total')->get(),
            ];
            $out['summary'][] = ['Messages reçus (dont prière)', $total];
        }

        if (in_array('newsletter', $sections)) {
            $q = NewsletterSubscription::query()->tap(fn ($b) => $inPeriod($b));
            $out['newsletter'] = [
                'new'         => (clone $q)->count(),
                'newActive'   => (clone $q)->where('is_active', true)->count(),
                'totalActive' => NewsletterSubscription::where('is_active', true)->count(),
            ];
            $out['summary'][] = ['Nouveaux abonnés à la newsletter', $out['newsletter']['new']];
        }

        return $out;
    }

    /**
     * Logo encodé en PNG base64 (DomPDF ne lit pas toujours le WebP).
     */
    private function logoDataUri(): ?string
    {
        // Logo « fond clair » défini dans Paramètres → Identité
        $path = public_path((string) Site::get('logo_light', 'images/brand/logo-hope-media.webp'));
        if (!is_file($path)) {
            return null;
        }

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (in_array($ext, ['png', 'jpg', 'jpeg'], true)) {
            return $this->fileToDataUri($path);
        }

        // WebP : converti en PNG, que DomPDF lit toujours
        return Cache::rememberForever('report.logo.' . md5($path) . '.' . filemtime($path), function () use ($path) {
            if (function_exists('imagecreatefromwebp') && ($img = @imagecreatefromwebp($path))) {
                imagesavealpha($img, true);
                ob_start();
                imagepng($img);
                return 'data:image/png;base64,' . base64_encode((string) ob_get_clean());
            }
            return null;
        });
    }

    private function fileToDataUri(string $path): string
    {
        $mime = mime_content_type($path) ?: 'image/png';
        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
    }

    /** Retire les émojis et symboles que la police du PDF ne sait pas afficher. */
    public static function clean(?string $text): string
    {
        $text = preg_replace('/[\x{2600}-\x{27BF}\x{2B00}-\x{2BFF}\x{FE00}-\x{FE0F}\x{200D}\x{10000}-\x{10FFFF}]/u', '', (string) $text);

        return trim(preg_replace('/\s{2,}/u', ' ', $text));
    }

    /** Libellés lisibles pour les valeurs techniques. */
    public static function label(mixed $value): string
    {
        if ($value instanceof \BackedEnum) {
            return method_exists($value, 'label') ? $value->label() : (string) $value->value;
        }

        return [
            'text' => 'Texte', 'video' => 'Vidéo', 'audio' => 'Audio', 'image' => 'Image',
            'prayer' => 'Demande de prière', 'question' => 'Question', 'testimony' => 'Témoignage',
            'contact' => 'Contact', 'feedback' => 'Avis',
            'new' => 'Nouveau', 'read' => 'Lu', 'replied' => 'Répondu', 'archived' => 'Archivé',
            'youtube' => 'YouTube', 'facebook' => 'Facebook', 'tiktok' => 'TikTok', 'instagram' => 'Instagram',
        ][$value] ?? Str::ucfirst((string) $value);
    }
}
