<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FileUploadedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $group;
    public $file;

    /**
     * Create a new message instance.
     */
    public function __construct($group, $file)
    {
        //
        $this->group = $group;
        $this->file = $file;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Notification de group ',
            from: new Address('accounts@unetah.net', 'AMEK INFORMATIQUE')
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mails.file_uploaded',
            with: [
                'groupName' => $this->group->name,
                'filePath' => $this->file->path,
               

            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
