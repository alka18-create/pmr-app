<?php

namespace App\Livewire\Settings;

use App\Actions\Settings\SaveOrganizationProfile;
use App\Http\Requests\OrganizationProfileRequest;
use App\Models\Setting;
use App\Services\OrganizationPrinciples;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\UploadedFile;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.panel')]
class OrganizationProfile extends Component
{
    use AuthorizesRequests, WithFileUploads;

    public string $name = '';

    public string $history = '';

    public string $vision = '';

    public string $mission = '';

    public string $email = '';

    public string $phone = '';

    public string $address = '';

    public string $instagram = '';

    public string $youtube = '';

    public string $tiktok = '';

    public string $logoPath = '';

    /** @var array<int, array{title: string, body: string}> */
    public array $principles = [];

    /** @var UploadedFile|string|null */
    public $logo = null;

    public function mount(): void
    {
        $this->authorize('manage', Setting::class);

        $this->name = (string) Setting::get('org_name', '');
        $this->history = (string) Setting::get('org_history', '');
        $this->vision = (string) Setting::get('org_vision', '');
        $this->mission = (string) Setting::get('org_mission', '');
        $this->email = (string) Setting::get('contact_email', '');
        $this->phone = (string) Setting::get('contact_phone', '');
        $this->address = (string) Setting::get('contact_address', '');
        $this->instagram = (string) Setting::get('social_instagram', '');
        $this->youtube = (string) Setting::get('social_youtube', '');
        $this->tiktok = (string) Setting::get('social_tiktok', '');
        $this->logoPath = (string) Setting::get('org_logo', '');
        $this->principles = OrganizationPrinciples::resolve(Setting::get('org_principles'));
    }

    public function addPrinciple(): void
    {
        $this->authorize('manage', Setting::class);

        $this->principles[] = ['title' => '', 'body' => ''];
    }

    public function removePrinciple(int $index): void
    {
        $this->authorize('manage', Setting::class);

        unset($this->principles[$index]);
        $this->principles = array_values($this->principles);
    }

    public function save(SaveOrganizationProfile $saveProfile)
    {
        $this->authorize('manage', Setting::class);

        $validated = $this->validatedData();

        $logo = $this->logo instanceof UploadedFile ? $this->logo : null;

        $saveProfile->execute($validated, $logo);

        session()->flash('success', 'Profil organisasi berhasil disimpan.');

        return redirect()->route('admin.settings.organization');
    }

    private function validatedData(): array
    {
        $files = $this->logo instanceof UploadedFile
            ? ['logo' => $this->logo]
            : [];

        $request = OrganizationProfileRequest::create('/', 'POST', [
            'name' => $this->name,
            'history' => $this->history,
            'vision' => $this->vision,
            'mission' => $this->mission,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'instagram' => $this->instagram,
            'youtube' => $this->youtube,
            'tiktok' => $this->tiktok,
            'principles' => $this->principles,
        ], [], $files);
        $request->setContainer(app());
        $request->setRedirector(app('redirect'));
        $request->validateResolved();

        return $request->validated();
    }

    public function render()
    {
        return view('livewire.settings.organization-profile');
    }
}
