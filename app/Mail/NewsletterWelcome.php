<?php

namespace App\Mail;

use App\Models\NewsletterSubscription;
use App\Support\Site;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;

/** Bienvenue d'un nouvel abonné à la newsletter. */
class NewsletterWelcome extends Mailable
{
    public function __construct(public NewsletterSubscription $subscriber) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Bienvenue dans la newsletter de ' . Site::get('site_name'));
    }

    public function headers(): Headers
    {
        return new Headers(text: ['List-Unsubscribe' => '<' . route('newsletter.unsubscribe', $this->subscriber->token) . '>']);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.newsletter-welcome', with: [
            'unsubscribeUrl' => route('newsletter.unsubscribe', $this->subscriber->token),
        ]);
    }
}
