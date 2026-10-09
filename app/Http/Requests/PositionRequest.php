<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PositionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('positions', 'name')->ignore($this->input('id')),
            ],
            'description' => ['nullable', 'string', 'max:500'],
            'level' => ['required', 'integer', 'min:1', 'max:9'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama jabatan wajib diisi.',
            'name.max' => 'Nama jabatan maksimal 100 karakter.',
            'name.unique' => 'Nama jabatan sudah ada.',
            'description.max' => 'Deskripsi maksimal 500 karakter.',
            'level.required' => 'Level wajib diisi.',
            'level.integer' => 'Level harus berupa angka.',
            'level.min' => 'Level minimal 1.',
            'level.max' => 'Level maksimal 9.',
        ];
    }
}
