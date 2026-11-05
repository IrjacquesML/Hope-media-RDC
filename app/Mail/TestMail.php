<?php

namespace App\Mail;

use App\Support\Site;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** E-mail de test envoyé depuis Paramètres → E-mails. */
class TestMail extends Mailable
{
    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Test d’envoi — ' . Site::get('site_name'));
    }

    public function content(): Content
    {
        return new Content(view: 'emails.test');
    }
}
