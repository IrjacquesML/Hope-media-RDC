<?php

namespace App\Mail;

use App\Models\NewsletterCampaign;
use App\Models\NewsletterSubscription;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;

/** Newsletter hebdomadaire : les nouveautés de la semaine. */
class NewsletterDigest extends Mailable
{
    public function __construct(public NewsletterCampaign $campaign, public ?NewsletterSubscription $subscriber = null) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->campaign->subject);
    }

    public function headers(): Headers
    {
        return new Headers(text: $this->subscriber
            ? ['List-Unsubscribe' => '<' . route('newsletter.unsubscribe', $this->subscriber->token) . '>']
            : []);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.newsletter-digest', with: [
            'items'          => $this->campaign->items ?? [],
            'unsubscribeUrl' => $this->subscriber ? route('newsletter.unsubscribe', $this->subscriber->token) : '#',
            'firstName'      => $this->subscriber?->name ? strtok($this->subscriber->name, ' ') : null,
        ]);
    }
}
