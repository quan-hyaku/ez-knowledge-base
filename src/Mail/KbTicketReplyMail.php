<?php

namespace EzKnowledgeBase\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use EzKnowledgeBase\Models\KbTicket;
use EzKnowledgeBase\Models\KbTicketReply;

class KbTicketReplyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public KbTicket $ticket,
        public KbTicketReply $reply,
        public ?string $replyToAddress = null,
        public ?string $messageId = null,
    ) {}

    public function envelope(): Envelope
    {
        $envelope = new Envelope(
            from: new \Illuminate\Mail\Mailables\Address(
                config('kb.reply.from_address', 'noreply@weeklify.io'),
                config('kb.reply.from_name', 'Weeklify Support'),
            ),
            subject: 'Re: [Ticket #' . $this->ticket->id . '] ' . $this->ticket->subject,
        );

        if ($this->replyToAddress) {
            $envelope->replyTo = [new \Illuminate\Mail\Mailables\Address($this->replyToAddress)];
        }

        return $envelope;
    }

    public function content(): Content
    {
        return new Content(
            view: 'kb::emails.ticket-reply',
        );
    }

    public function headers(): \Illuminate\Mail\Mailables\Headers
    {
        return new \Illuminate\Mail\Mailables\Headers(
            messageId: $this->messageId,
        );
    }
}
