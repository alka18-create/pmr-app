<?php

namespace App\Http\Requests;

use App\Enums\EventStatus;
use App\Enums\EventType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $eventId = $this->input('id');

        return [
            'title' => ['required', 'string', 'max:200'],
            'type' => ['required', Rule::enum(EventType::class)],
            'description' => ['nullable', 'string', 'max:10000'],
            'location' => ['nullable', 'string', 'max:200'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'cover_path' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'status' => ['required', Rule::enum(EventStatus::class)],
            'is_public' => ['boolean'],
            'max_participants' => ['nullable', 'integer', 'min:1', 'max:10000'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Judul kegiatan wajib diisi.',
            'title.max' => 'Judul kegiatan maksimal 200 karakter.',
            'type.required' => 'Jenis kegiatan wajib dipilih.',
            'type.enum' => 'Jenis kegiatan tidak valid.',
            'starts_at.required' => 'Waktu mulai wajib diisi.',
            'starts_at.date' => 'Waktu mulai tidak valid.',
            'ends_at.after_or_equal' => 'Waktu selesai tidak boleh sebelum waktu mulai.',
            'cover_path.mimes' => 'Poster harus berformat JPG, PNG, atau WebP.',
            'cover_path.max' => 'Poster maksimal 2 MB.',
            'status.required' => 'Status wajib dipilih.',
            'status.enum' => 'Status tidak valid.',
            'max_participants.integer' => 'Jumlah peserta maksimal harus bilangan bulat.',
        ];
    }
}
