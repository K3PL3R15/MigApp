<?php

namespace App\Mail;

use App\Models\Sale;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable implements ShouldQueue
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
     * Usa la misma vista que el show de ventas
     */
    public function content(): Content
    {
        return new Content(
            view: 'sales.show', // Misma vista que el show de ventas
            with: [
                'sale' => $this->sale,
                'invoiceNumber' => $this->invoiceNumber,
                'isEmailView' => true, // Flag para distinguir si es email
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
