<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DistributionSuccess extends Mailable
{
    use Queueable, SerializesModels;


    public $user;
    public $client;
    public $orderNumber;

    /**
     * Create a new message instance.
     */
    public function __construct($user, $client, $orderNumber)
    {
        //
        $this->user = $user;
        $this->client = $client;
        $this->orderNumber = $orderNumber;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Distribution Success',
        );
    }

    /**
     * Get the message content definition.
     */

    public function build()
    {
        return $this->view('emails.distribution_success')
                    ->with([
                        'userName' => $this->user->name,
                        'clientName' => $this->client->name,
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
