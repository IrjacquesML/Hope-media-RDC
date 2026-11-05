<?php

namespace App\Http\Controllers;

use App\Mail\NewsletterDigest;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterSubscription;
use App\Services\NewsletterService;
use App\Support\Site;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

/**
 * Admin → Newsletter : état de l'envoi automatique, historique, aperçu, test, abonnés.
 */
class AdminNewsletterController extends Controller
{
    public function __construct(private readonly NewsletterService $newsletter) {}

    public function index(Request $request): View
    {
        $query = NewsletterSubscription::orderByDesc('created_at');
        if ($search = $request->get('q')) {
            $query->where(fn ($q) => $q->where('email', 'like', "%{$search}%")->orWhere('name', 'like', "%{$search}%"));
        }

        return view('admin.newsletter.index', [
            'subscribers'    => $query->paginate(25)->withQueryString(),
            'campaigns'      => NewsletterCampaign::latest('scheduled_for')->limit(10)->get(),
            'activeCount'    => NewsletterSubscription::where('is_active', true)->count(),
            'inactiveCount'  => NewsletterSubscription::where('is_active', false)->count(),
            'mailConfigured' => Site::mailConfigured(),
            'autoEnabled'    => Site::get('newsletter_auto'),
            'nextSlot'       => $this->newsletter->nextSlot(),
            'pendingItems'   => count($this->newsletter->previewCampaign()->items ?? []),
        ]);
    }

    /** Aperçu de la prochaine newsletter, dans le navigateur. */
    public function preview(): string
    {
        return (new NewsletterDigest($this->newsletter->previewCampaign()))->render();
    }

    /** Envoie la prochaine newsletter à l'adresse du compte connecté. */
    public function test(): RedirectResponse
    {
        if (! Site::mailConfigured()) {
            return back()->with('error', 'L’envoi d’e-mails n’est pas encore configuré (Paramètres → E-mails).');
        }

        $email = Auth::user()->email;
        try {
            Mail::to($email)->send(new NewsletterDigest($this->newsletter->previewCampaign()));
        } catch (\Throwable $e) {
            return back()->with('error', 'L’envoi a échoué : ' . NewsletterService::explainMailError($e));
        }

        return back()->with('success', "Newsletter de test envoyée à {$email}.");
    }

    /** Relance une newsletter en échec (après correction des réglages e-mail). */
    public function retry(NewsletterCampaign $campaign): RedirectResponse
    {
        abort_unless($campaign->status === 'failed', 404);
        $this->newsletter->retry($campaign);
        $result = $this->newsletter->runDue();

        return back()->with(str_starts_with($result, 'Échec') ? 'error' : 'success', $result);
    }
}
