<?php

namespace App\Http\Requests\Ticket;

use Illuminate\Foundation\Http\FormRequest;

/**
 * FR-TKT-13: diagnosis, tindakan, kos dan masa kerja juruteknik. Kos hanya
 * boleh direkod dengan sebab apabila aset masih dalam waranti — semakan
 * waranti (FR-TKT-16) menyertai modul M09.
 */
class StoreWorkRequest extends FormRequest
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
            'diagnosis' => ['required', 'string', 'max:5000'],
            'tindakan' => ['required', 'string', 'max:5000'],
            'kos_pembaikan' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'masa_kerja_minit' => ['nullable', 'integer', 'min:0', 'max:100000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'diagnosis.required' => 'Sila rekod diagnosis masalah.',
            'tindakan.required' => 'Sila rekod tindakan pembaikan yang diambil.',
            'kos_pembaikan.min' => 'Kos tidak boleh negatif.',
            'masa_kerja_minit.integer' => 'Masa kerja mesti dalam minit penuh.',
        ];
    }
}
