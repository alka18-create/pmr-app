<?php

namespace App\Livewire\Settings;

use App\Actions\Settings\SaveSetting;
use App\Enums\SettingGroup;
use App\Http\Requests\SettingRequest;
use App\Models\Setting;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.panel')]
class SettingIndex extends Component
{
    use AuthorizesRequests;

    public string $activeGroup = 'general';

    public string $key = '';

    public string $value = '';

    public ?int $editId = null;

    public function mount(): void
    {
        $this->authorize('manage', Setting::class);
    }

    public function updatedActiveGroup(): void
    {
        $this->reset(['editId', 'key', 'value']);
    }

    public function edit(Setting $setting): void
    {
        $this->authorize('manage', Setting::class);

        $this->editId = $setting->id;
        $this->key = $setting->key;
        $this->value = $setting->value ?? '';
        $this->activeGroup = $setting->group;
    }

    public function cancelEdit(): void
    {
        $this->reset(['editId', 'key', 'value']);
    }

    public function save(SaveSetting $saveSetting)
    {
        $this->authorize('manage', Setting::class);

        $validated = $this->validatedData();

        $saveSetting->execute($validated);

        session()->flash('success', 'Pengaturan berhasil disimpan.');

        return redirect()->route('admin.settings.index', ['group' => $this->activeGroup]);
    }

    public function delete(Setting $setting)
    {
        $this->authorize('manage', Setting::class);

        $setting->delete();

        session()->flash('success', sprintf('Pengaturan %s dihapus.', $setting->key));

        return redirect()->route('admin.settings.index', ['group' => $this->activeGroup]);
    }

    private function validatedData(): array
    {
        $request = SettingRequest::create('/', 'POST', [
            'id' => $this->editId,
            'key' => $this->key,
            'value' => $this->value,
            'group' => $this->activeGroup,
        ]);
        $request->setContainer(app());
        $request->setRedirector(app('redirect'));
        $request->validateResolved();

        return $request->validated();
    }

    public function render()
    {
        return view('livewire.settings.setting-index', [
            'settings' => Setting::query()
                ->where('group', $this->activeGroup)
                ->orderBy('key')
                ->get(),
            'groups' => SettingGroup::cases(),
        ]);
    }
}
