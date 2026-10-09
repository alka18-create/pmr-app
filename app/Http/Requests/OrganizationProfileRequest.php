<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrganizationProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'history' => ['nullable', 'string', 'max:10000'],
            'vision' => ['nullable', 'string', 'max:5000'],
            'mission' => ['nullable', 'string', 'max:5000'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'instagram' => ['nullable', 'url', 'max:255'],
            'youtube' => ['nullable', 'url', 'max:255'],
            'tiktok' => ['nullable', 'url', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'principles' => ['nullable', 'array', 'max:20'],
            'principles.*.title' => ['required_with:principles', 'string', 'max:100'],
            'principles.*.body' => ['required_with:principles', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama organisasi wajib diisi.',
            'name.max' => 'Nama organisasi maksimal 100 karakter.',
            'history.max' => 'Sejarah maksimal 10000 karakter.',
            'vision.max' => 'Visi maksimal 5000 karakter.',
            'mission.max' => 'Misi maksimal 5000 karakter.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal 255 karakter.',
            'phone.max' => 'Telepon maksimal 30 karakter.',
            'address.max' => 'Alamat maksimal 255 karakter.',
            'instagram.url' => 'Format URL Instagram tidak valid.',
            'youtube.url' => 'Format URL YouTube tidak valid.',
            'tiktok.url' => 'Format URL TikTok tidak valid.',
            'logo.image' => 'Logo harus berupa gambar.',
            'logo.mimes' => 'Logo harus berformat JPG, JPEG, PNG, atau WebP.',
            'logo.max' => 'Logo maksimal 2MB.',
            'principles.array' => 'Format daftar prinsip tidak valid.',
            'principles.*.title.required_with' => 'Judul prinsip wajib diisi.',
            'principles.*.title.max' => 'Judul prinsip maksimal 100 karakter.',
            'principles.*.body.required_with' => 'Isi prinsip wajib diisi.',
            'principles.*.body.max' => 'Isi prinsip maksimal 2000 karakter.',
        ];
    }
}
