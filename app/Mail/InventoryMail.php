<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
// use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
// use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;



class InventoryMail extends Mailable
{
    use Queueable, SerializesModels;

    public $inventories;
    public $user;


    /**
     * Create a new message instance.
     */
    public function __construct($inventories)
    {
        //
        $this->inventories = is_array($inventories) ? collect($inventories) : $inventories;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'דו"ח מלאי',
        );
    }


      /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.inventories')->with([
            'inventories' => $this->inventories,
        ]);
    }

    // /**
    //  * Get the message content definition.
    //  */
    // public function content(): Content
    // {
    //     return new Content(
    //         markdown: 'emails.inventories',
    //     );
    // }

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
