<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

#[Signature('assets:ensure {--force : Rebuild the bundle even when a manifest already exists}')]
#[Description('Build the Vite bundle when public/build/manifest.json is missing so a fresh clone can boot')]
class EnsureFrontendAssets extends Command
{
    /**
     * Seconds allowed for each npm invocation.
     */
    private const PROCESS_TIMEOUT = 900;

    /**
     * Never fail the host process (for example `composer install`); an unbuilt
     * bundle is reported as a warning with recovery instructions instead.
     */
    public function handle(): int
    {
        if (file_exists(public_path('hot'))) {
            $this->components->info('Vite dev server is running; skipping build.');

            return self::SUCCESS;
        }

        if (! $this->option('force') && file_exists(public_path('build/manifest.json'))) {
            $this->components->info('Vite manifest already present; nothing to build.');

            return self::SUCCESS;
        }

        if (! $this->npmIsAvailable()) {
            $this->reportManualSteps('Node.js/npm was not found on this machine.');

            return self::SUCCESS;
        }

        if (! is_dir(base_path('node_modules')) && ! $this->runNpm('npm install --ignore-scripts')) {
            $this->reportManualSteps('`npm install` failed.');

            return self::SUCCESS;
        }

        if (! $this->runNpm('npm run build')) {
            $this->reportManualSteps('`npm run build` failed.');

            return self::SUCCESS;
        }

        $this->components->info('Vite assets built into public/build.');

        return self::SUCCESS;
    }

    private function npmIsAvailable(): bool
    {
        return Process::path(base_path())
            ->timeout(60)
            ->run('npm --version')
            ->successful();
    }

    private function runNpm(string $command): bool
    {
        $this->components->info("Running {$command} ...");

        return Process::path(base_path())
            ->timeout(self::PROCESS_TIMEOUT)
            ->run($command, function (string $type, string $output): void {
                $this->output->write($output);
            })
            ->successful();
    }

    private function reportManualSteps(string $reason): void
    {
        $this->components->warn($reason.' Frontend assets were not built.');
        $this->components->bulletList([
            'Install Node.js 20 or newer.',
            'Run `npm install` in the project root.',
            'Run `npm run build` (or `npm run dev` while developing).',
        ]);
    }
}
