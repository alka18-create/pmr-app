<?php

namespace App\Http\Requests;

use App\Enums\SettingGroup;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'key' => [
                'required',
                'string',
                'max:100',
                Rule::unique('settings', 'key')->ignore($this->input('id')),
            ],
            'value' => ['nullable', 'string', 'max:65000'],
            'group' => ['required', Rule::enum(SettingGroup::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'key.required' => 'Key wajib diisi.',
            'key.max' => 'Key maksimal 100 karakter.',
            'key.unique' => 'Key sudah digunakan.',
            'value.max' => 'Nilai maksimal 65000 karakter.',
            'group.required' => 'Group wajib dipilih.',
            'group.enum' => 'Group tidak valid.',
        ];
    }
}
