<?php

namespace App\Livewire\Website;

use App\Enums\MessageStatus;
use App\Models\Message;
use App\Models\Setting;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.public', ['title' => 'Kontak'])]
class ContactPage extends Component
{
    public string $name = '';

    public string $email = '';

    public string $subject = '';

    public string $body = '';

    public string $website = ''; // honeypot

    public function submit()
    {
        // Honeypot: bot mengisi field tersembunyi.
        if ($this->website !== '') {
            session()->flash('success', 'Pesan Anda telah dikirim.');
            $this->reset(['name', 'email', 'subject', 'body']);

            return;
        }

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'subject' => ['required', 'string', 'max:200'],
            'body' => ['required', 'string', 'max:5000'],
            'website' => ['nullable', 'max:0'],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'subject.required' => 'Subjek wajib diisi.',
            'body.required' => 'Pesan wajib diisi.',
        ]);

        $ip = request()->ip();
        $key = 'contact:'.$ip;

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            $this->addError('body', "Terlalu banyak permintaan. Coba lagi dalam {$seconds} detik.");

            return;
        }

        RateLimiter::hit($key, 60);

        Message::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'subject' => $validated['subject'],
            'body' => $validated['body'],
            'status' => MessageStatus::Unread,
            'ip_address' => $ip,
        ]);

        session()->flash('success', 'Terima kasih! Pesan Anda telah kami terima.');
        $this->reset(['name', 'email', 'subject', 'body']);
    }

    public function render()
    {
        return view('livewire.website.contact-page', [
            'email' => Setting::get('contact_email', ''),
            'phone' => Setting::get('contact_phone', ''),
            'address' => Setting::get('contact_address', ''),
            'instagram' => Setting::get('social_instagram', ''),
            'youtube' => Setting::get('social_youtube', ''),
            'tiktok' => Setting::get('social_tiktok', ''),
        ]);
    }
}
