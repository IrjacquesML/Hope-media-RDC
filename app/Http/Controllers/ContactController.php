<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;

/**
 * ContactController
 * 
 * Contrôleur pour la gestion des contacts et demandes de prière
 * Permet aux visiteurs de soumettre des messages
 */
class ContactController extends Controller
{
    /**
     * Afficher le formulaire de contact
     * 
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('contact.create');
    }

    /**
     * Stocker une nouvelle demande de contact
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Valider les données
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'request_type' => 'required|in:prayer,question,testimony,contact,feedback',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|min:10|max:10000',
            'accepted_terms' => 'required|boolean',
            'subscribed_to_newsletter' => 'nullable|boolean',
        ]);

        // Ajouter l'adresse IP de l'utilisateur
        $validated['ip_address'] = $request->ip();

        // Créer le contact
        $contact = Contact::create($validated);

        // TODO: Envoyer une notification (email, Slack, etc.)
        // Notification::send();

        return redirect()->route('contact.create')
            ->with('success', 'Merci pour votre message. Nous vous répondrons très bientôt.');
    }

    /**
     * Afficher la page de prière
     * 
     * @return \Illuminate\View\View
     */
    public function prayer()
    {
        return view('contact.prayer');
    }

    /**
     * Soumettre une demande de prière rapide (AJAX)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function submitPrayerRequest(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'message' => 'required|string|min:10',
        ]);

        $validated['request_type'] = 'prayer';
        $validated['subject'] = 'Demande de prière';
        $validated['ip_address'] = $request->ip();
        $validated['accepted_terms'] = true;

        Contact::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Votre demande de prière a été reçue. Nous prions pour vous.',
        ]);
    }
}
