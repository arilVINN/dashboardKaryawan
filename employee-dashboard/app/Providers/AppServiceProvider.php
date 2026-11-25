<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\Login;

use Illuminate\Support\Facades\URL;
use App\Models\Jabatan;
use App\Models\Karyawan;
use App\Policies\JabatanPolicy;
use App\Policies\KaryawanPolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (str_starts_with(config('app.url', ''), 'https://')) {
            URL::forceScheme('https');
        }

        Gate::policy(Karyawan::class, KaryawanPolicy::class);
        Gate::policy(Jabatan::class, JabatanPolicy::class);

        // Remember which session version the user logged in with, so a
        // password reset can invalidate older sessions.
        Event::listen(Login::class, function (Login $event): void {
            if (app()->runningInConsole() && ! app()->runningUnitTests()) {
                return;
            }
            try {
                session()->put('session_version', $event->user->session_version);
            } catch (\Throwable) {
                // No session available (e.g. token login); safe to ignore.
            }
        });
        // Login: the per-IP limit alone can be bypassed by rotating
        // X-Forwarded-For, so also cap attempts per username.
        RateLimiter::for('login', function (Request $request) {
            $input = $request->input('username');
            $username = is_string($input) ? Str::lower(trim($input)) : '';

            return [
                Limit::perMinute(5)->by('login:pair:'.$username.'|'.$request->ip()),
                Limit::perMinute(20)->by('login:user:'.$username),
            ];
        });

        // Submit form: tambah/revisi tugas, balas pesan, update profil, CRUD divisi/staff HRD
        RateLimiter::for('submit-form', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });

        // Pencarian/filter list data (daftarKaryawan, daftarDivisi, daftarPesan)
        RateLimiter::for('pencarian', function (Request $request) {
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
        });

        // Umum: halaman biasa (dashboard, detail, profile)
        RateLimiter::for('umum', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
