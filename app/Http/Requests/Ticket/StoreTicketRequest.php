<?php

namespace App\Http\Requests\Ticket;

use App\Enums\TicketPriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * FR-TKT-02: at most five required fields on the fault report form. Users
 * pick the disturbance level in their own words; the value maps directly to
 * the internal priority (UI spec §4.4).
 */
class StoreTicketRequest extends FormRequest
{
    /**
     * Access is enforced by the can: middleware on the route.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'lokasi_id' => ['required', 'integer', 'exists:locations,id'],
            'kategori_masalah' => [
                'required',
                'string',
                'max:50',
                Rule::exists('reference_values', 'code')
                    ->where('type', 'jenis_kerosakan')
                    ->where('is_active', true),
            ],
            'keutamaan' => ['required', Rule::enum(TicketPriority::class)],
            'keterangan' => ['required', 'string', 'min:10', 'max:5000'],
            'telefon_hubungan' => ['nullable', 'string', 'max:30'],
            'lampiran' => ['nullable', 'array', 'max:3'],
            'lampiran.*' => [
                'nullable',
                'file',
                'max:10240',
                'mimes:jpg,jpeg,png,webp,pdf,doc,docx,xls,xlsx',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'lokasi_id.required' => 'Sila pilih lokasi masalah.',
            'kategori_masalah.required' => 'Sila pilih jenis masalah.',
            'kategori_masalah.exists' => 'Jenis masalah yang dipilih tidak sah.',
            'keutamaan.required' => 'Sila nyatakan tahap gangguan.',
            'keterangan.required' => 'Sila terangkan masalah yang berlaku.',
            'keterangan.min' => 'Keterangan terlalu pendek. Berikan sekurang-kurangnya 10 aksara.',
            'lampiran.max' => 'Hanya sehingga 3 lampiran dibenarkan.',
            'lampiran.*.max' => 'Setiap lampiran mesti bawah 10 MB.',
            'lampiran.*.mimes' => 'Jenis fail tidak dibenarkan. Gunakan imej, PDF atau dokumen Office.',
        ];
    }
}
