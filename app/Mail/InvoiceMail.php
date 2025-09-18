<?php

namespace App\Mail;

use App\Models\Sale;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

// Temporalmente sin cola para envío inmediato
class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public Sale $sale;
    public string $invoiceNumber;

    /**
     * Create a new message instance.
     */
    public function __construct(Sale $sale, string $invoiceNumber)
    {
        $this->sale = $sale;
        $this->invoiceNumber = $invoiceNumber;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Factura #{$this->invoiceNumber} - {$this->sale->branch->name} - MigApp",
            from: config('mail.from.address'),
        );
    }

    /**
     * Get the message content definition.
     * Usa vista específica para email
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice', // Vista específica para email
            with: [
                'sale' => $this->sale,
                'invoiceNumber' => $this->invoiceNumber,
                'isEmailView' => true,
                'issueDate' => now()->format('d/m/Y H:i'),
                'bakery' => $this->sale->branch->name
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
