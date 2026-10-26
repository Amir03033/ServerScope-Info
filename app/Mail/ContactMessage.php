<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * A message from the website contact form. Plain text only, so nothing
 * the visitor typed is ever rendered as HTML.
 */
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
            subject: '[ServerScope Info] '.str_replace(["\r", "\n"], ' ', $this->data['subject']),
        );
    }

    public function content(): Content
    {
        return new Content(text: 'mail.contact');
    }
}
