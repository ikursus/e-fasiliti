<?php

namespace App\Http\Requests\Ticket;

use Illuminate\Foundation\Http\FormRequest;

class StoreNoteRequest extends FormRequest
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
            'catatan' => ['required', 'string', 'max:5000'],
            'boleh_dilihat_pelapor' => ['nullable', 'boolean'],
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
            'catatan.required' => 'Catatan tidak boleh kosong.',
            'lampiran.max' => 'Hanya sehingga 3 lampiran dibenarkan.',
            'lampiran.*.mimes' => 'Jenis fail tidak dibenarkan.',
        ];
    }
}
