<?php

namespace App\Http\Requests\Admin;

use App\Enums\AssetStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssetStoreRequest extends FormRequest
{
    /**
     * Access is enforced by the can: middleware on the route.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Field lengths follow the ASET entity in DRD §4.3. FR-AST-01 lists the
     * mandatory fields; FR-AST-02 keeps the registration number unique
     * across every status, including dilupuskan (DI-04).
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'registration_number' => ['required', 'string', 'max:50', 'unique:assets,registration_number'],
            'category_id' => ['required', 'integer', Rule::exists('reference_values', 'id')->where('type', 'kategori_aset')->where('is_active', true)],
            'brand' => ['required', 'string', 'max:100'],
            'model' => ['required', 'string', 'max:100'],
            'serial_number' => ['nullable', 'string', 'max:100', 'unique:assets,serial_number'],
            'specifications' => ['nullable', 'array'],
            'acquisition_date' => ['required', 'date'],
            'acquisition_cost' => ['nullable', 'numeric', 'min:0'],
            'order_number' => ['nullable', 'string', 'max:50'],
            'warranty_start_date' => ['nullable', 'date'],
            'warranty_months' => ['nullable', 'integer', 'min:0', 'max:600'],
            'location_id' => ['required', 'integer', Rule::exists('locations', 'id')->where('is_active', true)],
            'responsible_user_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('is_active', true)],
            'status' => ['required', Rule::enum(AssetStatus::class)],
            'mac_address' => ['nullable', 'string', 'max:20'],
            'ip_address' => ['nullable', 'ip'],
            'hostname' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ];
    }

    /**
     * Malay messages for the rules users actually trip over.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'registration_number.required' => 'Nombor pendaftaran wajib diisi.',
            'registration_number.unique' => 'Nombor pendaftaran ini telah digunakan oleh aset lain.',
            'serial_number.unique' => 'Nombor siri ini telah digunakan oleh aset lain.',
            'category_id.required' => 'Kategori aset wajib dipilih.',
            'acquisition_date.required' => 'Tarikh perolehan wajib diisi.',
            'location_id.required' => 'Lokasi aset wajib dipilih.',
            'status.required' => 'Status aset wajib dipilih.',
        ];
    }
}
