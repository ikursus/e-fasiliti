<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'channel',
        'locale',
        'subject',
        'body',
        'placeholders',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'placeholders' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Placeholder names this template is allowed to use.
     *
     * @return array<int, string>
     */
    public function allowedPlaceholders(): array
    {
        return $this->placeholders ?? [];
    }

    /**
     * Every placeholder actually written in a piece of text. Surrounding
     * spaces are tolerated, so {{ nama }} and {{nama}} are the same token.
     *
     * @return array<int, string>
     */
    public static function placeholdersIn(string $text): array
    {
        preg_match_all('/\{\{\s*([a-z0-9_]+)\s*\}\}/i', $text, $matches);

        return array_values(array_unique($matches[1] ?? []));
    }

    public function channelLabel(): string
    {
        return $this->channel === 'emel' ? 'E-mel' : 'Dalam aplikasi';
    }
}
