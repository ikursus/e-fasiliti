---
paths:
  - '{composer.json,bootstrap/app.php,resources/views/errors/**}'
---

# Errors

## Vite assets are built automatically after composer install
public/build stays gitignored, so a fresh clone or pull has no Vite manifest. Two guards keep that from producing a stack trace:

1. composer.json post-install-cmd runs `php artisan assets:ensure`, which runs `npm install --ignore-scripts` and `npm run build` when public/build/manifest.json is missing. The command always exits 0 so a missing Node install never breaks `composer install`.
2. bootstrap/app.php registers a render callback that walks the previous-exception chain for ViteManifestNotFoundException and returns resources/views/errors/vite-manifest-missing.blade.php. Match on the chain, not the exception type directly: Blade wraps it in a ViewException, so a `render(function (ViteManifestNotFoundException $e) ...)` callback never fires.

The fallback view must not use @vite or extend the app layouts. It carries its own inline CSS.
