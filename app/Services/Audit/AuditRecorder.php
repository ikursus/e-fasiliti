<?php

namespace App\Services\Audit;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Records configuration and reference-data changes in the audit trail with
 * before and after values (FR-ADM-08, FR-AUD-01). Login events keep using
 * the dedicated LoginAuditLogger.
 */
class AuditRecorder
{
    public function __construct(private readonly Request $request) {}

    /**
     * Both snapshots must hold Eloquent-cast values, not raw request input,
     * because fields are compared strictly. Snapshot the "after" side from a
     * refreshed model so an integer column never reads back as a form string.
     *
     * Pass "before" alone to record a deletion, and "after" alone to record a
     * creation; the missing side is stored as null per field.
     *
     * Use "context" for facts about the operation that are not fields of the
     * target record, such as the ids a cascade also touched. Context is
     * merged into the metadata alongside the before and after payloads, and
     * never takes part in the field comparison.
     *
     * @param  array<string, mixed>  $before
     * @param  array<string, mixed>  $after
     * @param  array<string, mixed>  $context
     */
    public function record(
        User $actor,
        string $action,
        Model $target,
        array $before = [],
        array $after = [],
        array $context = [],
    ): AuditLog {
        $changed = $this->changedFields($before, $after);

        $metadata = $context;

        if ($changed !== []) {
            $metadata['before'] = $this->pick($before, $changed);
            $metadata['after'] = $this->pick($after, $changed);
        }

        return AuditLog::create([
            'user_id' => $actor->id,
            'action' => $action,
            'status' => 'success',
            'record_type' => $this->recordType($target),
            'record_id' => $target->getKey(),
            'ip_address' => $this->request->ip(),
            'user_agent' => Str::limit($this->request->userAgent() ?? '', 255, ''),
            'metadata' => $metadata === [] ? null : $metadata,
        ]);
    }

    /**
     * Snake-cased short class name, e.g. "location", "system_setting".
     */
    private function recordType(Model $target): string
    {
        return Str::snake(class_basename($target));
    }

    /**
     * Names of the fields whose value differs between the two snapshots.
     *
     * Both sides are scanned, so a field dropped from "after" counts as a
     * change and a delete-time snapshot is not silently discarded.
     *
     * @param  array<string, mixed>  $before
     * @param  array<string, mixed>  $after
     * @return array<int, string>
     */
    private function changedFields(array $before, array $after): array
    {
        $changed = [];

        foreach (array_keys($before + $after) as $field) {
            if (($before[$field] ?? null) !== ($after[$field] ?? null)) {
                $changed[] = $field;
            }
        }

        return $changed;
    }

    /**
     * The given fields from a snapshot, with absent fields recorded as null so
     * the before and after payloads always share the same keys.
     *
     * @param  array<string, mixed>  $values
     * @param  array<int, string>  $fields
     * @return array<string, mixed>
     */
    private function pick(array $values, array $fields): array
    {
        $picked = [];

        foreach ($fields as $field) {
            $picked[$field] = $values[$field] ?? null;
        }

        return $picked;
    }
}
