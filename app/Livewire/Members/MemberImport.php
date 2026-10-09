<?php

namespace App\Livewire\Members;

use App\Actions\Members\ImportMembers;
use App\Exports\MemberTemplateExport;
use App\Models\Member;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\UploadedFile;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('components.layouts.panel')]
class MemberImport extends Component
{
    use AuthorizesRequests, WithFileUploads;

    public $file = null;

    /** @var list<array{row: int, message: string}> */
    public array $rowErrors = [];

    /** @var list<array<string, mixed>> */
    public array $valid = [];

    public bool $previewed = false;

    public function mount(): void
    {
        $this->authorize('import', Member::class);
    }

    public function downloadTemplate()
    {
        $this->authorize('import', Member::class);

        return Excel::download(new MemberTemplateExport, 'template-import-anggota.xlsx');
    }

    public function validateFile(ImportMembers $import): void
    {
        $this->authorize('import', Member::class);

        $this->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:2048'],
        ]);

        /** @var UploadedFile|string $file */
        $file = $this->file;
        $result = $import->preview($file);

        $this->valid = $result['valid'];
        $this->rowErrors = $result['errors'];
        $this->previewed = true;
    }

    public function confirmImport(ImportMembers $import)
    {
        $this->authorize('import', Member::class);

        if (! $this->previewed || $this->valid === []) {
            session()->flash('error', 'Tidak ada baris valid untuk diimpor.');

            return redirect()->route('members.import');
        }

        try {
            $summary = $import->execute($this->valid);
        } catch (\DomainException $e) {
            session()->flash('error', $e->getMessage());

            return redirect()->route('members.import');
        }

        session()->flash('success', sprintf(
            'Import selesai: %d baru, %d diperbarui.',
            $summary['created'],
            $summary['updated'],
        ));

        return redirect()->route('members.index');
    }

    public function resetPreview(): void
    {
        $this->reset(['file', 'rowErrors', 'valid', 'previewed']);
    }

    public function render()
    {
        return view('livewire.members.member-import');
    }
}
