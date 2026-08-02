<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscription;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'name'  => 'nullable|string|max:100',
        ]);

        $existing = NewsletterSubscription::where('email', $validated['email'])->first();

        if ($existing) {
            if ($existing->is_active) {
                return back()->with('newsletter_info', 'Cette adresse e-mail est déjà abonnée à notre newsletter.');
            }
            // Réactiver l'abonnement désactivé
            $existing->update(['is_active' => true, 'name' => $validated['name'] ?? $existing->name]);

            return back()->with('newsletter_success', 'Votre abonnement a été réactivé. Merci !');
        }

        NewsletterSubscription::create([
            'email'    => $validated['email'],
            'name'     => $validated['name'] ?? null,
            'token'    => NewsletterSubscription::generateToken(),
            'is_active' => true,
        ]);

        return back()->with('newsletter_success', 'Merci ! Vous êtes maintenant abonné(e) à notre newsletter.');
    }

    public function unsubscribe(string $token)
    {
        $subscription = NewsletterSubscription::where('token', $token)->firstOrFail();
        $subscription->update(['is_active' => false]);

        return view('news.unsubscribed');
    }
}
