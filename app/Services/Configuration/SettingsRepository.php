<?php

namespace App\Services\Configuration;

use App\Models\SystemSetting;
use App\Models\User;
use App\Services\Audit\AuditRecorder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * The only way the rest of the application reads or writes M01 scalar
 * settings. Everything is loaded in one query and cached, because the
 * number of settings is small and almost every request needs several.
 */
class SettingsRepository
{
    private const CACHE_KEY = 'm01.system_settings';

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
        $before = $existing === null ? null : $this->decode($existing->value, $existing->value_type);

        $setting = SystemSetting::query()->updateOrCreate(
            ['key' => $key],
            [
                'value' => $this->encode($value),
                'value_type' => $existing?->value_type ?? $valueType,
                'group' => $existing?->group ?? $group,
                'description' => $existing?->description,
                'updated_by' => $actor?->id,
            ]
        );

        $this->flush();

        if ($actor !== null) {
            $this->audit->record(
                $actor,
                'setting.updated',
                $setting,
                before: ['key' => $key, 'value' => $before],
                after: ['key' => $key, 'value' => $value],
            );
        }
    }

    public function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * @return Collection<string, array{value: string, value_type: string, group: string, description: ?string}>
     */
    private function rows(): Collection
    {
        return Cache::rememberForever(self::CACHE_KEY, fn () => SystemSetting::query()
            ->get(['key', 'value', 'value_type', 'group', 'description'])
            ->keyBy('key')
            ->map(fn (SystemSetting $setting) => [
                'value' => $setting->value,
                'value_type' => $setting->value_type,
                'group' => $setting->group,
                'description' => $setting->description,
            ]));
    }

    private function decode(string $raw, string $type): mixed
    {
        $decoded = json_decode($raw, true);

        return match ($type) {
            'nombor' => is_numeric($decoded) ? $decoded + 0 : 0,
            'boolean' => (bool) $decoded,
            'json' => is_array($decoded) ? $decoded : [],
            default => is_string($decoded) ? $decoded : $raw,
        };
    }

    private function encode(mixed $value): string
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE) ?: '""';
    }
}
