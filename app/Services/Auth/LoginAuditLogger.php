<?php

namespace App\Services\Auth;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Records authentication events in the audit log (FR-AUD-03, NFR-S30).
 * A fuller audit service (M16) will build on this later.
 */
class LoginAuditLogger
{
    public function record(string $event, ?User $user, Request $request, string $status = 'success'): void
    {
        AuditLog::create([
            'user_id' => $user?->id,
            'action' => $event,
            'status' => $status,
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit($request->userAgent() ?? '', 255),
            'metadata' => [
                'email' => $user?->email ?? $request->string('email'),
            ],
        ]);
    }
}
