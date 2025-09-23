<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class PurchaseConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $product;

    /**
     * Create a new message instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
        $this->product = $order->getProduct();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Satın Alımınız Tamamlandı - ' . $this->product->title,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.purchase-confirmation',
            with: [
                'order' => $this->order,
                'product' => $this->product,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];

        // Tam PDF'i attachment olarak ekle
        if ($this->product->full_pdf) {
            $pdfPath = $this->product->full_pdf;
            
            // S3'ten PDF'i al
            if (Storage::disk('s3')->exists($pdfPath)) {
                $attachments[] = Attachment::fromStorageDisk('s3', $pdfPath)
                    ->as($this->product->title . '.pdf')
                    ->withMime('application/pdf');
            }
        }

        return $attachments;
    }
}
