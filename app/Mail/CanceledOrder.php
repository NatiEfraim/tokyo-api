<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;




class CanceledOrder extends Mailable
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
            subject: 'הזמנה בוטלה',
        );
    }



    /**
     * Get the message content definition.
     */

    public function build()
    {
        return $this->view('emails.canceled_order')
        ->with([
            'userName' => $this->user->name,
            'orderNumber' => $this->orderNumber,

        ])
            ->subject('Order Failed');
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
