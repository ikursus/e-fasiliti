<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AssetEventType;
use App\Enums\AssetStatus;
use App\Enums\ReferenceValueType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AssetStoreRequest;
use App\Http\Requests\Admin\AssetUpdateRequest;
use App\Models\Asset;
use App\Models\Location;
use App\Models\ReferenceValue;
use App\Models\User;
use App\Services\Audit\AuditRecorder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * M09 — inventori & pendaftaran aset ICT (FR-AST-01, 02, 04 hingga 07, 10).
 * Penulisan terhad kepada pegawai-aset dan pentadbir-sistem; peranan lain
 * membaca sahaja, dan kakitangan skop "sendiri" (keputusan 9 September 2026).
 */
class AssetController extends Controller
{
    public function __construct(private readonly AuditRecorder $audit) {}

    /**
     * Daftar aset dengan carian bebas dan empat penapis (FR-AST-10).
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $status = (string) $request->query('status', '');
        $categoryId = $request->query('category_id');
        $locationId = $request->query('location_id');
        $responsibleId = $request->query('responsible_user_id');

        $assets = Asset::query()
            ->with(['category', 'location', 'responsibleUser'])
            ->when($search !== '', fn ($query) => $query->search($search))
            ->when($status !== '' && AssetStatus::tryFrom($status) !== null, fn ($query) => $query->where('status', $status))
            ->when(is_numeric($categoryId), fn ($query) => $query->where('category_id', (int) $categoryId))
            ->when(is_numeric($locationId), fn ($query) => $query->where('location_id', (int) $locationId))
            ->when(is_numeric($responsibleId), fn ($query) => $query->where('responsible_user_id', (int) $responsibleId))
            ->visibleTo($this->actor())
            ->orderBy('registration_number')
            ->paginate(15)
            ->withQueryString();

        return view('admin.assets.index', [
            'assets' => $assets,
            'search' => $search,
            'statuses' => AssetStatus::cases(),
            'categories' => $this->categories(),
            'locations' => $this->locations(),
            'users' => $this->users(),
        ]);
    }

    public function create(): View
    {
        return view('admin.assets.create', [
            'asset' => new Asset(['status' => AssetStatus::Digunakan]),
            'statuses' => AssetStatus::cases(),
            'categories' => $this->categories(),
            'users' => $this->users(),
        ]);
    }

    /**
     * Daftar aset baharu; peristiwa "didaftar" dan jejak audit direkod
     * dalam satu transaksi (FR-AST-01, FR-AST-06).
     */
    public function store(AssetStoreRequest $request): RedirectResponse
    {
        $asset = DB::transaction(function () use ($request): Asset {
            $asset = Asset::create($request->safe()->all() + ['qr_code' => (string) Str::uuid()]);

            $asset->histories()->create([
                'event_type' => AssetEventType::Didaftar,
                'after' => $asset->refresh()->movementSnapshot(),
                'recorded_by' => $this->actor()->id,
            ]);

            return $asset;
        });

        // "Selepas" daripada model yang di-refresh supaya nilai audit sepadan
        // dengan apa yang sebenarnya tersimpan (peraturan .ai/rules/admin.md).
        $this->audit->record($this->actor(), 'asset.created', $asset, after: $asset->refresh()->auditSnapshot());

        return to_route('admin.assets.index')->with('status', 'Aset telah didaftarkan.');
    }

    public function show(Asset $asset): View
    {
        $this->ensureVisible($asset);

        $asset->load(['category', 'location', 'responsibleUser']);

        return view('admin.assets.show', [
            'asset' => $asset,
            'histories' => $asset->histories()->with('recorder')->orderByDesc('created_at')->orderByDesc('id')->get(),
            'warranty' => $asset->warrantyStatus(),
            'locationNames' => Location::query()->pluck('name', 'id'),
            'userNames' => User::query()->pluck('name', 'id'),
        ]);
    }

    public function edit(Asset $asset): View
    {
        $this->ensureVisible($asset);

        return view('admin.assets.edit', [
            'asset' => $asset,
            'statuses' => AssetStatus::cases(),
            'categories' => $this->categories(),
            'users' => $this->users(),
        ]);
    }

    /**
     * Kemas kini aset; setiap pertukaran lokasi, pemilik dan status
     * dijadikan rekod sejarah berasingan (FR-AST-06).
     */
    public function update(AssetUpdateRequest $request, Asset $asset): RedirectResponse
    {
        $beforeAudit = $asset->refresh()->auditSnapshot();
        $beforeMovement = $asset->movementSnapshot();
        $reason = trim((string) $request->input('reason'));

        $afterAudit = DB::transaction(function () use ($asset, $request, $beforeMovement, $reason): array {
            $asset->update($request->safe()->except('reason'));
            $asset->refresh();

            foreach ($this->movementEvents($beforeMovement, $asset->movementSnapshot()) as $event) {
                $asset->histories()->create([
                    'event_type' => $event['event'],
                    'before' => $event['before'],
                    'after' => $event['after'],
                    'reason' => $reason !== '' ? $reason : null,
                    'recorded_by' => $this->actor()->id,
                ]);
            }

            return $asset->auditSnapshot();
        });

        $this->audit->record($this->actor(), 'asset.updated', $asset, before: $beforeAudit, after: $afterAudit);

        return to_route('admin.assets.show', $asset)->with('status', 'Aset telah dikemas kini.');
    }

    /**
     * Padam kekal; sejarah mengikut aset (cascade) dan jejak audit
     * menyimpan snapshot sebelum padam.
     */
    public function destroy(Asset $asset): RedirectResponse
    {
        $before = $asset->refresh()->auditSnapshot();

        DB::transaction(fn (): bool => (bool) $asset->delete());

        $this->audit->record($this->actor(), 'asset.deleted', $asset, before: $before);

        return to_route('admin.assets.index')->with('status', 'Aset telah dipadam daripada daftar.');
    }

    /**
     * Kakitangan hanya boleh membuka aset yang didaftarkan atas namanya.
     */
    private function ensureVisible(Asset $asset): void
    {
        $user = $this->actor();

        if ($user->hasRole('kakitangan') && ! $user->can('aset.kemaskini') && (int) $asset->responsible_user_id !== $user->id) {
            abort(404);
        }
    }

    /**
     * Peristiwa pergerakan yang diterbitkan daripada perbezaan dua snapshot.
     *
     * @return array<int, array{event: AssetEventType, before: array<string, mixed>, after: array<string, mixed>}>
     */
    private function movementEvents(array $before, array $after): array
    {
        $events = [];

        if ($before['location_id'] !== $after['location_id']) {
            $events[] = ['event' => AssetEventType::PindahLokasi, 'before' => ['location_id' => $before['location_id']], 'after' => ['location_id' => $after['location_id']]];
        }

        if ($before['responsible_user_id'] !== $after['responsible_user_id']) {
            $events[] = ['event' => AssetEventType::TukarPemilik, 'before' => ['responsible_user_id' => $before['responsible_user_id']], 'after' => ['responsible_user_id' => $after['responsible_user_id']]];
        }

        if ($before['status'] !== $after['status']) {
            $events[] = [
                'event' => $after['status'] === AssetStatus::Dilupuskan->value ? AssetEventType::Dilupuskan : AssetEventType::TukarStatus,
                'before' => ['status' => $before['status']],
                'after' => ['status' => $after['status']],
            ];
        }

        return $events;
    }

    /**
     * @return Collection<int, ReferenceValue>
     */
    private function categories(): Collection
    {
        return ReferenceValue::query()
            ->ofType(ReferenceValueType::KategoriAset)
            ->active()
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get();
    }

    /**
     * @return Collection<int, Location>
     */
    private function locations(): Collection
    {
        return Location::query()->active()->orderBy('name')->get(['id', 'name', 'code']);
    }

    /**
     * @return Collection<int, User>
     */
    private function users(): Collection
    {
        return User::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']);
    }

    private function actor(): User
    {
        /** @var User $user */
        $user = Auth::user();

        return $user;
    }
}
