<?php

namespace App\Http\Requests\Ticket;

use Illuminate\Foundation\Http\FormRequest;

/**
 * FR-TKT-15: vendor referral records, with the vendor reference kept on the
 * ticket. The full M12 vendor module arrives in phase 3.
 */
class ReferVendorRequest extends FormRequest
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
            'no_rujukan_vendor' => ['required', 'string', 'max:50'],
            'vendor_nama' => ['required', 'string', 'max:200'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'no_rujukan_vendor.required' => 'Nombor rujukan vendor wajib diisi.',
            'vendor_nama.required' => 'Nama vendor wajib diisi.',
        ];
    }
}
