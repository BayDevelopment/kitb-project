<?php

namespace App\Mail;

use App\Models\PesanKontak;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PesanKontakBalasan extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public PesanKontak $pesanKontak,
        public string $balasan,
    ) {}

    public function envelope(): Envelope
    {
        $subjek = trim($this->pesanKontak->subjek);

        return new Envelope(
            subject: str_starts_with(strtolower($subjek), 're:')
                ? $subjek
                : 'Re: ' . $subjek,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.pesan-kontak-balasan',
        );
    }

    public function build()
    {
        $logoPath = public_path('logoside.png');

        return $this
            ->view('emails.pesan-kontak-balasan')
            ->with([
                'pesanKontak' => $this->pesanKontak,
                'balasan' => $this->balasan,
                'logoSrc' => file_exists($logoPath)
                    ? $this->embed($logoPath)
                    : rtrim(config('app.url'), '/') . '/logoside.png',
            ]);
    }
}
