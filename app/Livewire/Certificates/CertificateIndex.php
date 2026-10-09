<?php

namespace App\Livewire\Certificates;

use App\Actions\Certificates\IssueCertificate;
use App\Actions\Certificates\RevokeCertificate;
use App\Enums\CertificateSourceType;
use App\Jobs\GenerateCertificate;
use App\Models\Certificate;
use App\Models\Event;
use App\Models\Member;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.panel')]
class CertificateIndex extends Component
{
    use AuthorizesRequests, WithPagination;

    public string $search = '';

    public string $sourceFilter = '';

    // form issue manual
    public $issueMemberId = null;

    public string $issueSourceType = 'event';

    public $issueSourceId = null;

    public string $issueTitle = '';

    public function mount(): void
    {
        $this->authorize('manage', Certificate::class);
        $this->issueTitle = 'Sertifikat Kegiatan';
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'sourceFilter'], true)) {
            $this->resetPage();
        }
    }

    public function issue(IssueCertificate $issueCertificate)
    {
        $this->authorize('manage', Certificate::class);

        $this->validate([
            'issueMemberId' => ['required', 'integer', 'exists:members,id'],
            'issueSourceType' => ['required', Rule::enum(CertificateSourceType::class)],
            'issueSourceId' => ['nullable', 'integer'],
            'issueTitle' => ['required', 'string', 'max:200'],
        ], [
            'issueMemberId.required' => 'Anggota wajib dipilih.',
            'issueSourceType.required' => 'Sumber sertifikat wajib dipilih.',
            'issueTitle.required' => 'Judul sertifikat wajib diisi.',
        ]);

        $sourceType = CertificateSourceType::from($this->issueSourceType);
        $sourceId = $this->issueSourceId ? (int) $this->issueSourceId : null;

        if ($sourceType === CertificateSourceType::Event && $sourceId) {
            Event::findOrFail($sourceId);
        }

        $issueCertificate->execute(
            (int) $this->issueMemberId,
            $sourceType,
            $sourceId,
            $this->issueTitle,
            auth()->id(),
        );

        session()->flash('success', 'Sertifikat diterbitkan. PDF sedang diproses.');

        $this->reset(['issueMemberId', 'issueSourceId']);
        $this->issueTitle = 'Sertifikat Kegiatan';

        return redirect()->route('certificates.manage');
    }

    public function revoke(int $id, RevokeCertificate $revokeCertificate)
    {
        $this->authorize('manage', Certificate::class);

        $certificate = Certificate::findOrFail($id);
        $revokeCertificate->execute($certificate);

        session()->flash('success', 'Sertifikat dicabut.');

        return redirect()->route('certificates.manage');
    }

    public function regenerate(int $id): void
    {
        $this->authorize('manage', Certificate::class);

        $certificate = Certificate::findOrFail($id);

        if ($certificate->is_revoked) {
            session()->flash('error', 'Sertifikat yang dicabut tidak bisa digenerate ulang.');

            return;
        }

        GenerateCertificate::dispatch($certificate->id)->onQueue('certificates');

        session()->flash('success', 'PDF sertifikat sedang digenerate ulang.');
    }

    public function download(Certificate $certificate)
    {
        $this->authorize('manage', Certificate::class);

        if (! $certificate->file_path || ! Storage::disk('public')->exists($certificate->file_path)) {
            session()->flash('error', 'File PDF belum tersedia. Coba regenerate.');

            return;
        }

        return response()->download(
            Storage::disk('public')->path($certificate->file_path),
            'sertifikat-'.$certificate->code.'.pdf',
        );
    }

    public function render()
    {
        $certificates = Certificate::with(['member:id,full_name'])
            ->when($this->search !== '', function ($query) {
                $term = '%'.mb_strtolower($this->search).'%';
                $query->whereRaw('LOWER(code) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(title) LIKE ?', [$term]);
            })
            ->when($this->sourceFilter !== '', fn ($query) => $query->where('source_type', $this->sourceFilter))
            ->orderByDesc('id')
            ->paginate(10);

        return view('livewire.certificates.certificate-index', [
            'certificates' => $certificates,
            'members' => Member::orderBy('full_name')->get(['id', 'full_name']),
            'events' => Event::orderByDesc('starts_at')->limit(50)->get(['id', 'title']),
            'sourceTypes' => CertificateSourceType::cases(),
        ]);
    }
}
