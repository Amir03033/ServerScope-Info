<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ContactMessage extends Mailable
{
    use Queueable;

    /**
     * @param  array{name: string, email: string, subject: string, message: string}  $data
     */
    public function __construct(public array $data, public string $siteLocale) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address($this->data['email'], $this->data['name'])],
            subject: '[info.serverscope.nl] '.$this->data['subject'],
        );
    }

    public function content(): Content
    {
        return new Content(text: 'mail.contact');
    }
}
