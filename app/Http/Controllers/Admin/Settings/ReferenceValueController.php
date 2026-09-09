<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Enums\ReferenceValueType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReferenceValueRequest;
use App\Models\ReferenceValue;
use App\Models\User;
use App\Services\Audit\AuditRecorder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * M01 — editable reference lists (FR-ADM-07).
 */
class ReferenceValueController extends Controller
{
    public function __construct(private readonly AuditRecorder $audit) {}

    public function index(Request $request): View
    {
        $type = $this->resolveType($request->string('type')->value());

        return view('admin.settings.reference-values', [
            'types' => ReferenceValueType::cases(),
            'activeType' => $type,
            'values' => ReferenceValue::query()
                ->ofType($type)
                ->orderBy('sort_order')
                ->orderBy('label')
                ->get(),
        ]);
    }

    public function store(ReferenceValueRequest $request): RedirectResponse
    {
        $value = ReferenceValue::create([
            'type' => $request->string('type')->value(),
            'code' => $request->string('code')->upper()->value(),
            'label' => $request->string('label')->value(),
            'sort_order' => (int) $request->integer('sort_order'),
            'is_active' => $request->boolean('is_active'),
        ]);

        $this->audit->record($this->actor(), 'reference_value.created', $value, after: $value->only([
            'type', 'code', 'label',
        ]));

        return to_route('admin.settings.reference-values', ['type' => $value->type])
            ->with('status', 'Nilai rujukan telah ditambah.');
    }

    /**
     * Reference values are never deleted, because historical records point
     * at them. They are switched off so new records cannot pick them.
     */
    public function toggle(ReferenceValue $referenceValue): RedirectResponse
    {
        $activating = ! $referenceValue->is_active;

        $referenceValue->update(['is_active' => $activating]);

        $this->audit->record(
            $this->actor(),
            $activating ? 'reference_value.activated' : 'reference_value.deactivated',
            $referenceValue,
            before: ['is_active' => ! $activating],
            after: ['is_active' => $activating],
        );

        return to_route('admin.settings.reference-values', ['type' => $referenceValue->type])
            ->with('status', $activating ? 'Nilai telah diaktifkan.' : 'Nilai telah dinyahaktifkan.');
    }

    private function resolveType(?string $raw): ReferenceValueType
    {
        if ($raw === null || $raw === '') {
            return ReferenceValueType::cases()[0];
        }

        $type = ReferenceValueType::tryFrom($raw);

        if ($type === null) {
            throw new NotFoundHttpException('Senarai nilai rujukan tidak wujud.');
        }

        return $type;
    }

    private function actor(): User
    {
        /** @var User $user */
        $user = Auth::user();

        return $user;
    }
}
