<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Vite;
use Illuminate\Foundation\ViteManifestNotFoundException;
use Tests\TestCase;

class ViteManifestFallbackTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A fresh clone has no public/build directory, so the Vite helper throws
     * while the Blade layout renders. The handler must turn that into setup
     * instructions rather than a stack trace.
     */
    public function test_missing_vite_manifest_renders_setup_instructions(): void
    {
        $this->withExceptionHandling();

        $this->app->instance(Vite::class, new class extends Vite
        {
            public function __invoke($entrypoints, $buildDirectory = null)
            {
                throw new ViteManifestNotFoundException('Vite manifest not found at: public/build/manifest.json');
            }
        });

        $this->get(route('login'))
            ->assertStatus(500)
            ->assertSee('Aset frontend belum dibina')
            ->assertSee('npm run build');
    }
}
