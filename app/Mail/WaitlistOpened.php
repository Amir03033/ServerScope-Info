<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** The one mail people on the waiting list consented to: registration is open. */
class WaitlistOpened extends Mailable
{
    public function __construct(public string $siteLocale, public string $registerUrl, public string $removeUrl) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: trans('site.waitlist.opened_subject', [], $this->siteLocale));
    }

    public function content(): Content
    {
        return new Content(text: 'mail.waitlist-opened');
    }
}
