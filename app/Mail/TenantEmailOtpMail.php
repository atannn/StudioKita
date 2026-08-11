<?php

namespace App\Mail;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TenantEmailOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Tenant $tenant,
        public readonly string $otpCode,
        public readonly string $expiresAt
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Kode OTP Verifikasi Studio - StudioKita',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.tenant-verification-otp',
            with: [
                'tenantName' => $this->tenant->nama,
                'otpCode' => $this->otpCode,
                'expiresAt' => $this->expiresAt,
            ]
        );
    }
}

