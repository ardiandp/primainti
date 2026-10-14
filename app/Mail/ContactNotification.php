<?php

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Notifikasi email untuk pesan kontak baru.
 * Dikirim sinkron saat submit; kegagalan SMTP tidak boleh
 * menghilangkan pesan yang sudah tersimpan (lihat controller).
 */
class ContactNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly ContactMessage $contactMessage,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Kontak Website] '.$this->contactMessage->subject,
            replyTo: [$this->contactMessage->email],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.contact-notification',
            text: 'mail.contact-notification-text',
        );
    }
}
