<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;



class DistributionMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $distributions;
    /**
     * Create a new message instance.
     */
    public function __construct($distributions)
    {
        //
        $this->distributions = is_array($distributions) ? collect($distributions) : $distributions;

    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'דו"ח ניפוק פירטים',
        );
    }


    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.distributions')->with([
            'distributions' => $this->distributions,
        ]);
    }




    // /**
    //  * Get the message content definition.
    //  */
    // public function content(): Content
    // {
    //     return new Content(
    //         markdown: 'emails.distributions',
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
