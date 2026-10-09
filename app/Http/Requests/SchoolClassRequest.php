<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SchoolClassRequest extends FormRequest
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
                Rule::unique('classes', 'name')->ignore($this->input('id')),
            ],
            'level' => ['required', Rule::in(['X', 'XI', 'XII'])],
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama kelas wajib diisi.',
            'name.max' => 'Nama kelas maksimal 100 karakter.',
            'name.unique' => 'Nama kelas sudah ada.',
            'level.required' => 'Tingkat wajib dipilih.',
            'level.in' => 'Tingkat tidak valid.',
            'year.required' => 'Tahun wajib diisi.',
            'year.integer' => 'Tahun harus berupa angka.',
        ];
    }
}
