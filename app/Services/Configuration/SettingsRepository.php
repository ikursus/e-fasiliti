<?php

namespace App\Services\Configuration;

use App\Models\SystemSetting;
use App\Models\User;
use App\Services\Audit\AuditRecorder;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

/**
 * The only way the rest of the application reads or writes M01 scalar
 * settings. Everything is loaded in one query and cached, because the
 * number of settings is small and almost every request needs several.
 */
class SettingsRepository
{
    private const CACHE_KEY = 'm01.system_settings';

    /**
     * Value type for a setting held encrypted at rest, such as an API key.
     * Its plaintext never reaches the cache, the audit trail or a view.
     */
    public const SECRET_TYPE = 'rahsia';

    public function __construct(private readonly AuditRecorder $audit) {}

    /**
     * Decoded value for a key, or $default when the key does not exist.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $rows = $this->rows();

        if (! $rows->has($key)) {
            return $default;
        }

        return $this->decode($rows->get($key)['value'], $rows->get($key)['value_type']);
    }

    /**
     * Every setting, optionally limited to one group, as key => decoded value.
     *
     * @return Collection<string, mixed>
     */
    public function all(?string $group = null): Collection
    {
        return $this->rows()
            ->when($group !== null, fn (Collection $rows) => $rows->where('group', $group))
            ->map(fn (array $row) => $this->decode($row['value'], $row['value_type']));
    }

    /**
     * Write one setting, record the change, and drop the cache (FR-ADM-08).
     *
     * The value type and group apply only when the key is new. An existing
     * row owns its own metadata, so a caller cannot reclassify it by writing
     * a value.
     */
    public function set(
        string $key,
        mixed $value,
        ?User $actor = null,
        string $valueType = 'teks',
        string $group = 'umum',
    ): void {
        $existing = SystemSetting::query()->find($key);

        // The type decides how the value is encoded, and an existing row owns
        // its own type, so it has to be resolved before anything is written.
        $type = $existing?->value_type ?? $valueType;
        $before = $existing === null ? null : $this->decode($existing->value, $type);

        $setting = SystemSetting::query()->updateOrCreate(
            ['key' => $key],
            [
                'value' => $this->encode($value, $type),
                'value_type' => $type,
                'group' => $existing?->group ?? $group,
                'description' => $existing?->description,
                'updated_by' => $actor?->id,
            ]
        );

        $this->flush();

        if ($actor === null) {
            return;
        }

        if ($type === self::SECRET_TYPE) {
            // Recording the values here would write the plaintext secret into
            // audit_logs. Context still leaves a trail that the key changed.
            $this->audit->record(
                $actor,
                'setting.updated',
                $setting,
                context: ['key' => $key, 'value_redacted' => true],
            );

            return;
        }

        $this->audit->record(
            $actor,
            'setting.updated',
            $setting,
            before: ['key' => $key, 'value' => $before],
            after: ['key' => $key, 'value' => $value],
        );
    }

    public function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Every row, from the cache when it holds a usable payload.
     *
     * Only plain arrays go into the cache. `cache.serializable_classes` is
     * false, so the store refuses to unserialize any object and hands back
     * an __PHP_Incomplete_Class instead; caching the Collection itself made
     * every request after the first one fail on this method's return type.
     * A payload that is not an array is treated as a miss, which also lets a
     * cache warmed before this was fixed heal itself.
     *
     * @return Collection<string, array{value: string, value_type: string, group: string, description: ?string}>
     */
    private function rows(): Collection
    {
        $rows = Cache::get(self::CACHE_KEY);

        if (! is_array($rows)) {
            $rows = $this->readRows();

            Cache::forever(self::CACHE_KEY, $rows);
        }

        return collect($rows);
    }

    /**
     * @return array<string, array{value: string, value_type: string, group: string, description: ?string}>
     */
    private function readRows(): array
    {
        return SystemSetting::query()
            ->get(['key', 'value', 'value_type', 'group', 'description'])
            ->keyBy('key')
            ->map(fn (SystemSetting $setting) => [
                'value' => $setting->value,
                'value_type' => $setting->value_type,
                'group' => $setting->group,
                'description' => $setting->description,
            ])
            ->all();
    }

    private function decode(string $raw, string $type): mixed
    {
        $decoded = json_decode($raw, true);

        return match ($type) {
            'nombor' => is_numeric($decoded) ? $decoded + 0 : 0,
            'boolean' => (bool) $decoded,
            'json' => is_array($decoded) ? $decoded : [],
            self::SECRET_TYPE => $this->decrypt($decoded),
            default => is_string($decoded) ? $decoded : $raw,
        };
    }

    /**
     * Plaintext behind an encrypted value, or an empty string when it cannot
     * be read. A rotated APP_KEY is what failure looks like here, and the
     * caller should then treat the secret as unset rather than have every
     * settings read blow up.
     */
    private function decrypt(mixed $encrypted): string
    {
        if (! is_string($encrypted) || $encrypted === '') {
            return '';
        }

        try {
            return Crypt::decryptString($encrypted);
        } catch (DecryptException) {
            return '';
        }
    }

    private function encode(mixed $value, string $type = 'teks'): string
    {
        if ($type === self::SECRET_TYPE) {
            $secret = is_string($value) ? trim($value) : '';

            return json_encode($secret === '' ? '' : Crypt::encryptString($secret)) ?: '""';
        }

        return json_encode($value, JSON_UNESCAPED_UNICODE) ?: '""';
    }
}
