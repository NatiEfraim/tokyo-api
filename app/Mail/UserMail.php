<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

// use Illuminate\Mail\Mailables\Content;
// use Illuminate\Contracts\Queue\ShouldQueue;



class UserMail extends Mailable
{
    use Queueable, SerializesModels;


    protected $users;


    
    /**
     * Create a new message instance.
     */
    public function __construct($users)
    {
        //
        $this->users = is_array($users) ? collect($users) : $users;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'דו"ח משתמשי מערכת',
        );
    }


    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.users')->with([
            'users' => $this->users,
        ]);
    }


    // /**
    //  * Get the message content definition.
    //  */
    // public function content(): Content
    // {
    //     return new Content(
    //         markdown: 'emails.users',
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
