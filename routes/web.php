<?php

// Kursus Laravel

use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\OrganizationUnitController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\Settings\ChatbotSettingsController;
use App\Http\Controllers\Admin\Settings\GeneralSettingsController;
use App\Http\Controllers\Admin\Settings\HolidayController;
use App\Http\Controllers\Admin\Settings\NotificationTemplateController;
use App\Http\Controllers\Admin\Settings\OperatingHourController;
use App\Http\Controllers\Admin\Settings\ReferenceValueController;
use App\Http\Controllers\Admin\Settings\RoleBookingRuleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Profile\ProfileController;
use App\Http\Controllers\Ticket\MyTicketController;
use App\Http\Controllers\Ticket\SupervisorTicketController;
use App\Http\Controllers\Ticket\TechnicianTicketController;
use App\Http\Controllers\Ticket\TicketAttachmentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->user()) {
        return redirect()->route('dashboard');
    }

    return view('welcome');
});

/**
 * Authentication (M02) - guest only.
 */
Route::middleware('guest')->group(function (): void {
    Route::get('login', [AuthenticatedSessionController::class, 'show'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store'])
        ->name('login.store');

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('password.store');
});

/**
 * Authenticated area.
 */
Route::middleware('auth')->group(function (): void {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');

    Route::get('dashboard', DashboardController::class)
        ->name('dashboard');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('password.confirm.store');

    /**
     * M10 — Tiket Aduan Kerosakan. Permissions follow the M10 row of the
     * module/role matrix; the TicketPolicy layer enforces record-level
     * ownership on top, so a permission alone never exposes another user's
     * ticket (FR-USR-05, NFR-S11).
     */
    Route::prefix('tiket')->name('tiket.')->middleware('can:tiket.lihat-sendiri')->group(function (): void {
        Route::get('saya', [MyTicketController::class, 'index'])->name('index');
        Route::get('saya/cipta', [MyTicketController::class, 'create'])->name('create');

        Route::post('saya', [MyTicketController::class, 'store'])
            ->middleware('can:tiket.buka')
            ->name('store');

        Route::get('{ticket}', [MyTicketController::class, 'show'])->whereNumber('ticket')->name('show');
        Route::post('{ticket}/sahkan', [MyTicketController::class, 'sahkan'])->name('sahkan');
        Route::post('{ticket}/buka-semula', [MyTicketController::class, 'bukaSemula'])->name('buka-semula');
        Route::get('{ticket}/lampiran/{indeks}', [TicketAttachmentController::class, 'muatTurun'])->name('lampiran');
    });

    Route::prefix('tiket')->name('tiket.')->middleware('can:tiket.kemas-kini')->group(function (): void {
        Route::get('tugasan', [TechnicianTicketController::class, 'tugasan'])->name('tugasan');
        Route::post('{ticket}/mula', [TechnicianTicketController::class, 'mula'])->name('mula');
        Route::put('{ticket}/kerja', [TechnicianTicketController::class, 'simpanKerja'])->name('kerja');
        Route::post('{ticket}/catatan', [TechnicianTicketController::class, 'catatan'])->name('catatan');
        Route::post('{ticket}/rujuk-vendor', [TechnicianTicketController::class, 'rujukVendor'])->name('rujuk-vendor');
        Route::post('{ticket}/sambung-vendor', [TechnicianTicketController::class, 'sambungVendor'])->name('sambung-vendor');
        Route::post('{ticket}/selesai', [TechnicianTicketController::class, 'selesai'])->name('selesai');
    });

    Route::prefix('tiket')->name('tiket.')->middleware('can:tiket.lihat-semua')->group(function (): void {
        Route::get('/', [SupervisorTicketController::class, 'index'])->name('senarai');

        Route::post('agih-pukal', [SupervisorTicketController::class, 'agihPukal'])
            ->middleware('can:tiket.agih')
            ->name('agih-pukal');

        Route::post('{ticket}/agih', [SupervisorTicketController::class, 'agih'])
            ->middleware('can:tiket.agih')
            ->name('agih');

        Route::post('{ticket}/keutamaan', [SupervisorTicketController::class, 'keutamaan'])
            ->middleware('can:tiket.keutamaan')
            ->name('keutamaan');

        Route::post('{ticket}/batal', [SupervisorTicketController::class, 'batal'])
            ->middleware('can:tiket.agih')
            ->name('batal');
    });

    /**
     * M17 — Pembantu AI (chatbot), open to every role holding chatbot.guna
     * (FR-CHB-01). Sending a message is throttled per user (FR-CHB-06).
     */
    Route::prefix('chatbot')
        ->name('chatbot.')
        ->middleware('can:chatbot.guna')
        ->group(function (): void {
            Route::get('/', [ChatbotController::class, 'index'])
                ->name('index');

            Route::post('sessions', [ChatbotController::class, 'storeSession'])
                ->name('sessions.store');

            Route::delete('sessions/{chat_session}', [ChatbotController::class, 'destroySession'])
                ->name('sessions.destroy');

            Route::post('sessions/{chat_session}/messages', [ChatbotController::class, 'send'])
                ->middleware('throttle:20,1')
                ->name('messages.store');
        });

    /**
     * Profile (self-service). Every user may only view and update their own
     * profile, so the routes do not bind a user model.
     */
    Route::prefix('profile')
        ->name('profile.')
        ->group(function (): void {
            Route::get('/', [ProfileController::class, 'edit'])
                ->name('edit');

            Route::put('/', [ProfileController::class, 'update'])
                ->name('update');

            Route::put('photo', [ProfileController::class, 'updatePhoto'])
                ->middleware('throttle:10,1')
                ->name('photo.update');

        });

    /**
     * Directory administration (M03). Shared by three roles, so access is
     * granted per permission rather than per role.
     */
    Route::prefix('admin')
        ->name('admin.')
        ->group(function (): void {
            Route::get('locations', [LocationController::class, 'index'])
                ->middleware('can:lokasi.lihat')
                ->name('locations.index');

            Route::get('locations/create', [LocationController::class, 'create'])
                ->middleware('can:lokasi.cipta')
                ->name('locations.create');

            Route::post('locations', [LocationController::class, 'store'])
                ->middleware('can:lokasi.cipta')
                ->name('locations.store');

            Route::get('locations/{location}/edit', [LocationController::class, 'edit'])
                ->middleware('can:lokasi.kemaskini')
                ->name('locations.edit');

            Route::put('locations/{location}', [LocationController::class, 'update'])
                ->middleware('can:lokasi.kemaskini')
                ->name('locations.update');

            Route::patch('locations/{location}/toggle', [LocationController::class, 'toggle'])
                ->middleware('can:lokasi.kemaskini')
                ->name('locations.toggle');

            Route::delete('locations/{location}', [LocationController::class, 'destroy'])
                ->middleware('can:lokasi.padam')
                ->name('locations.destroy');

            Route::get('organization-units', [OrganizationUnitController::class, 'index'])
                ->middleware('can:unit-organisasi.lihat')
                ->name('organization-units.index');

            Route::get('organization-units/create', [OrganizationUnitController::class, 'create'])
                ->middleware('can:unit-organisasi.cipta')
                ->name('organization-units.create');

            Route::post('organization-units', [OrganizationUnitController::class, 'store'])
                ->middleware('can:unit-organisasi.cipta')
                ->name('organization-units.store');

            Route::get('organization-units/{organization_unit}/edit', [OrganizationUnitController::class, 'edit'])
                ->middleware('can:unit-organisasi.kemaskini')
                ->name('organization-units.edit');

            Route::put('organization-units/{organization_unit}', [OrganizationUnitController::class, 'update'])
                ->middleware('can:unit-organisasi.kemaskini')
                ->name('organization-units.update');

            Route::patch('organization-units/{organization_unit}/toggle', [OrganizationUnitController::class, 'toggle'])
                ->middleware('can:unit-organisasi.kemaskini')
                ->name('organization-units.toggle');

            Route::delete('organization-units/{organization_unit}', [OrganizationUnitController::class, 'destroy'])
                ->middleware('can:unit-organisasi.padam')
                ->name('organization-units.destroy');

            /**
             * Room catalogue (M04). Access follows the module matrix: three
             * roles read, facility admin and system admin write.
             */
            Route::get('rooms', [RoomController::class, 'index'])
                ->middleware('can:bilik.lihat')
                ->name('rooms.index');

            Route::get('rooms/create', [RoomController::class, 'create'])
                ->middleware('can:bilik.cipta')
                ->name('rooms.create');

            Route::post('rooms', [RoomController::class, 'store'])
                ->middleware('can:bilik.cipta')
                ->name('rooms.store');

            Route::get('rooms/{room}/edit', [RoomController::class, 'edit'])
                ->middleware('can:bilik.kemaskini')
                ->name('rooms.edit');

            Route::put('rooms/{room}', [RoomController::class, 'update'])
                ->middleware('can:bilik.kemaskini')
                ->name('rooms.update');

            Route::patch('rooms/{room}/toggle', [RoomController::class, 'toggle'])
                ->middleware('can:bilik.kemaskini')
                ->name('rooms.toggle');

            Route::delete('rooms/{room}', [RoomController::class, 'destroy'])
                ->middleware('can:bilik.padam')
                ->name('rooms.destroy');

            /**
             * Configuration (M01). Four roles may read; only the system
             * administrator may write.
             */
            Route::prefix('settings')->name('settings.')->group(function (): void {
                Route::get('general', [GeneralSettingsController::class, 'edit'])
                    ->middleware('can:tetapan.lihat')
                    ->name('general');

                Route::put('general', [GeneralSettingsController::class, 'update'])
                    ->middleware('can:tetapan.kemaskini')
                    ->name('general.update');

                Route::get('operating-hours', [OperatingHourController::class, 'edit'])
                    ->middleware('can:tetapan.lihat')
                    ->name('operating-hours');

                Route::put('operating-hours', [OperatingHourController::class, 'update'])
                    ->middleware('can:tetapan.kemaskini')
                    ->name('operating-hours.update');

                Route::get('holidays', [HolidayController::class, 'index'])
                    ->middleware('can:tetapan.lihat')
                    ->name('holidays');

                Route::post('holidays', [HolidayController::class, 'store'])
                    ->middleware('can:tetapan.kemaskini')
                    ->name('holidays.store');

                Route::delete('holidays/{holiday}', [HolidayController::class, 'destroy'])
                    ->middleware('can:tetapan.kemaskini')
                    ->name('holidays.destroy');

                Route::get('booking-rules', [RoleBookingRuleController::class, 'edit'])
                    ->middleware('can:tetapan.lihat')
                    ->name('booking-rules');

                Route::put('booking-rules', [RoleBookingRuleController::class, 'update'])
                    ->middleware('can:tetapan.kemaskini')
                    ->name('booking-rules.update');

                Route::get('reference-values', [ReferenceValueController::class, 'index'])
                    ->middleware('can:tetapan.lihat')
                    ->name('reference-values');

                Route::post('reference-values', [ReferenceValueController::class, 'store'])
                    ->middleware('can:tetapan.kemaskini')
                    ->name('reference-values.store');

                Route::patch('reference-values/{reference_value}/toggle', [ReferenceValueController::class, 'toggle'])
                    ->middleware('can:tetapan.kemaskini')
                    ->name('reference-values.toggle');

                Route::get('notification-templates', [NotificationTemplateController::class, 'index'])
                    ->middleware('can:tetapan.lihat')
                    ->name('notification-templates');

                Route::get('notification-templates/{notification_template}', [NotificationTemplateController::class, 'edit'])
                    ->middleware('can:tetapan.lihat')
                    ->name('notification-templates.edit');

                Route::put('notification-templates/{notification_template}', [NotificationTemplateController::class, 'update'])
                    ->middleware('can:tetapan.kemaskini')
                    ->name('notification-templates.update');

                /**
                 * M17 — assistant configuration (FR-CHB-05). Only roles
                 * holding chatbot.tetapan may change these.
                 */
                Route::get('chatbot', [ChatbotSettingsController::class, 'edit'])
                    ->middleware('can:chatbot.tetapan')
                    ->name('chatbot');

                Route::put('chatbot', [ChatbotSettingsController::class, 'update'])
                    ->middleware('can:chatbot.tetapan')
                    ->name('chatbot.update');

                // Calls the Gemini API, so it is throttled per user.
                Route::post('chatbot/test', [ChatbotSettingsController::class, 'test'])
                    ->middleware(['can:chatbot.tetapan', 'throttle:10,1'])
                    ->name('chatbot.test');
            });
        });

    /**
     * Administration (R8 - Sistem Pentadbir only).
     */
    Route::prefix('admin')
        ->middleware('role:pentadbir-sistem')
        ->name('admin.')
        ->group(function (): void {
            Route::resource('users', UserController::class)
                ->except(['show']);

            Route::resource('roles', RoleController::class)
                ->except(['show']);
        });
});
