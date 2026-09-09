<?php

use App\Services\Configuration\SettingsRepository;

if (! function_exists('setting')) {
    /**
     * Read one M01 configuration value.
     */
    function setting(string $key, mixed $default = null): mixed
    {
        return app(SettingsRepository::class)->get($key, $default);
    }
}
