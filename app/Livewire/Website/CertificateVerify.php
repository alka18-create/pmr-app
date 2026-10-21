<?php

namespace App\Livewire\Website;

use App\Models\Certificate;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('components.layouts.public', ['title' => 'Verifikasi Sertifikat'])]
class CertificateVerify extends Component
{
    use AuthorizesRequests;

    public string $code = '';

    #[Locked]
    public ?Certificate $certificate = null;

    #[Locked]
    public bool $searched = false;

    public function mount(string $code = ''): void
    {
        $this->code = $code;

        if ($code !== '') {
            $this->search();
        }
    }

    public function search(): void
    {
        $this->validate([
            'code' => ['required', 'string', 'max:32'],
        ], [
            'code.required' => 'Kode sertifikat wajib diisi.',
        ]);

        $this->searched = true;
        $this->certificate = Certificate::with('member:id,full_name')
            ->where('code', $this->code)
            ->first();
    }

    public function render()
    {
        $certificate = $this->certificate;
        $isValid = $certificate !== null && $certificate->isValid();
        $memberName = $certificate?->member?->full_name;

        return view('livewire.website.certificate-verify', [
            'certificate' => $certificate,
            'isValid' => $isValid,
            'memberName' => $memberName,
        ]);
    }
}
