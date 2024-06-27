<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

// use Illuminate\Contracts\Queue\ShouldQueue;
// use Illuminate\Mail\Mailables\Content;

class ApprovedOrder extends Mailable
{
    use Queueable, SerializesModels;


    public $user;
    public $orderNumber;

    /**
     * Create a new message instance.
     */
    public function __construct($user, $orderNumber)
    {
        //
        $this->user = $user;
        $this->orderNumber = $orderNumber;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'הזמנה אושרה',
        );
    }

    /**
     * Get the message content definition.
     */
    public function build()
    {
        return $this->view('emails.approved_order')
        ->with([
            'userName' => $this->user->name,
            'orderNumber' => $this->orderNumber,
        ])
            ->subject('Order Confirmation');
    }
    // public function content(): Content
    // {
    //     return new Content(
    //         view: 'view.name',
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
