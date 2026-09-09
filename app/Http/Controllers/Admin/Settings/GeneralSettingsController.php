<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Configuration\SettingsRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * M01 — organisation-wide scalar settings (FR-ADM-01, partial).
 */
class GeneralSettingsController extends Controller
{
    public function __construct(private readonly SettingsRepository $settings) {}

    public function edit(): View
    {
        return view('admin.settings.general', [
            'organisationName' => $this->settings->get('umum.nama_organisasi', config('app.name')),
            'timezone' => $this->settings->get('umum.zon_masa', 'Asia/Kuala_Lumpur'),
            'locale' => $this->settings->get('umum.bahasa_lalai', 'ms'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'umum_nama_organisasi' => ['required', 'string', 'max:150'],
            'umum_zon_masa' => ['nullable', 'string', 'timezone'],
            'umum_bahasa_lalai' => ['nullable', 'string', 'in:ms,en'],
        ]);

        /** @var User $actor */
        $actor = Auth::user();

        $this->settings->set('umum.nama_organisasi', $validated['umum_nama_organisasi'], $actor, 'teks', 'umum');
        $this->settings->set('umum.zon_masa', $validated['umum_zon_masa'] ?? 'Asia/Kuala_Lumpur', $actor, 'teks', 'umum');
        $this->settings->set('umum.bahasa_lalai', $validated['umum_bahasa_lalai'] ?? 'ms', $actor, 'teks', 'umum');

        return to_route('admin.settings.general')->with('status', 'Tetapan umum telah disimpan.');
    }
}
