<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\PaymentRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentRejected extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public PaymentRequest $payment,
        public string $reason = '',
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'نأسف، تم رفض طلب الدفع — منصة المئة',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-rejected',
        );
    }
}
