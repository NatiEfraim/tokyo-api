<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

// use Illuminate\Mail\Mailables\Content;
// use Illuminate\Contracts\Queue\ShouldQueue;

class DistributionFailure extends Mailable
{
    use Queueable, SerializesModels;

    public $user;


    /**
     * Create a new message instance.
     */
    public function __construct($user)
    {
        //
        $this->user = $user;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Distribution Failure',
        );
    }

    /**
     * Get the message content definition.
     */

    public function build()
    {
        return $this->view('emails.distribution_failure')
                    ->with([
                        'userName' => $this->user->name,
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
