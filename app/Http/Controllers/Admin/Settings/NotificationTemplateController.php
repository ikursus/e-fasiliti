<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\NotificationTemplateRequest;
use App\Models\NotificationTemplate;
use App\Models\User;
use App\Services\Audit\AuditRecorder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

/**
 * M01 — notification message templates (FR-ADM-06). Consumed by M14.
 */
class NotificationTemplateController extends Controller
{
    public function __construct(private readonly AuditRecorder $audit) {}

    public function index(): View
    {
        return view('admin.settings.notification-templates', [
            'templates' => NotificationTemplate::query()
                ->orderBy('key')
                ->orderBy('locale')
                ->get(),
            'template' => null,
        ]);
    }

    public function edit(NotificationTemplate $notificationTemplate): View
    {
        return view('admin.settings.notification-templates', [
            'templates' => NotificationTemplate::query()
                ->orderBy('key')
                ->orderBy('locale')
                ->get(),
            'template' => $notificationTemplate,
        ]);
    }

    public function update(NotificationTemplateRequest $request, NotificationTemplate $notificationTemplate): RedirectResponse
    {
        $before = $notificationTemplate->only(['subject', 'body', 'is_active']);

        $notificationTemplate->update([
            'subject' => $request->string('subject')->value(),
            'body' => $request->string('body')->value(),
            'is_active' => $request->boolean('is_active'),
        ]);

        /** @var User $actor */
        $actor = Auth::user();

        $this->audit->record(
            $actor,
            'notification_template.updated',
            $notificationTemplate,
            before: $before,
            after: $notificationTemplate->refresh()->only(['subject', 'body', 'is_active']),
        );

        return to_route('admin.settings.notification-templates')
            ->with('status', 'Templat notifikasi telah disimpan.');
    }
}
