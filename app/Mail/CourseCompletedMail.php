<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\CourseCompletion;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CourseCompletedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public CourseCompletion $completion,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'تهانينا! أكملت دورة — منصة المئة',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.course-completed',
        );
    }
}
