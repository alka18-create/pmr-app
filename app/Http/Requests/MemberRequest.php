<?php

namespace App\Http\Requests;

use App\Enums\Gender;
use App\Enums\MemberStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $memberId = $this->input('id');

        return [
            'full_name' => ['required', 'string', 'max:100'],
            'gender' => ['required', Rule::enum(Gender::class)],
            'nis' => ['nullable', 'string', 'max:20', Rule::unique('members', 'nis')->ignore($memberId)],
            'nisn' => ['nullable', 'string', 'max:20', Rule::unique('members', 'nisn')->ignore($memberId)],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'birth_place' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:2000'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email_pribadi' => ['nullable', 'email', 'max:255'],
            'parent_name' => ['nullable', 'string', 'max:100'],
            'parent_phone' => ['nullable', 'string', 'max:20'],
            'blood_type' => ['nullable', Rule::in(['A', 'B', 'AB', 'O'])],
            'batch_id' => ['required', 'integer', Rule::exists('batches', 'id')],
            'class_id' => ['required', 'integer', Rule::exists('classes', 'id')],
            'position_id' => ['nullable', 'integer', Rule::exists('positions', 'id')],
            'status' => ['required', Rule::enum(MemberStatus::class)],
            'joined_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'Nama lengkap wajib diisi.',
            'full_name.max' => 'Nama lengkap maksimal 100 karakter.',
            'gender.required' => 'Jenis kelamin wajib dipilih.',
            'gender.enum' => 'Jenis kelamin tidak valid.',
            'nis.unique' => 'NIS sudah terdaftar.',
            'nisn.unique' => 'NISN sudah terdaftar.',
            'birth_date.date' => 'Tanggal lahir tidak valid.',
            'birth_date.before' => 'Tanggal lahir harus sebelum hari ini.',
            'email_pribadi.email' => 'Format email tidak valid.',
            'blood_type.in' => 'Golongan darah tidak valid.',
            'batch_id.required' => 'Angkatan wajib dipilih.',
            'batch_id.exists' => 'Angkatan tidak ditemukan.',
            'class_id.required' => 'Kelas wajib dipilih.',
            'class_id.exists' => 'Kelas tidak ditemukan.',
            'position_id.exists' => 'Jabatan tidak ditemukan.',
            'status.required' => 'Status wajib dipilih.',
            'status.enum' => 'Status tidak valid.',
            'joined_at.required' => 'Tanggal bergabung wajib diisi.',
            'joined_at.date' => 'Tanggal bergabung tidak valid.',
        ];
    }
}
