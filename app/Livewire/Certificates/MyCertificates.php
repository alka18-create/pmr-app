<?php

namespace App\Livewire\Certificates;

use App\Models\Certificate;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.panel')]
class MyCertificates extends Component
{
    use AuthorizesRequests;

    public function download(Certificate $certificate)
    {
        $this->authorize('viewOwn', $certificate);

        if ($certificate->is_revoked) {
            session()->flash('error', 'Sertifikat ini sudah dicabut.');

            return;
        }

        if (! $certificate->file_path || ! Storage::disk('local')->exists($certificate->file_path)) {
            session()->flash('error', 'File PDF belum tersedia.');

            return;
        }

        return response()->download(
            Storage::disk('local')->path($certificate->file_path),
            'sertifikat-'.$certificate->code.'.pdf',
        );
    }

    public function render()
    {
        $user = auth()->user();
        $member = $user->member;

        $certificates = $member
            ? Certificate::where('member_id', $member->id)
                ->orderByDesc('id')
                ->get()
            : collect();

        return view('livewire.certificates.my-certificates', [
            'certificates' => $certificates,
        ]);
    }
}
