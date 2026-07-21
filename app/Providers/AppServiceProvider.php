<?php

namespace App\Providers;

use App\Models\Scopes\LembagaScope;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
        Model::addGlobalScope(new LembagaScope);

        // Permission gates for guru tugas tambahan
        Gate::define('validator-jurnal', fn(User $user) => $user->guru?->hasPermission('validator_jurnal'));
        Gate::define('perizinan-siswa', fn(User $user) => $user->guru?->hasPermission('perizinan_siswa'));
        Gate::define('presensi-ptk', fn(User $user) => $user->guru?->hasPermission('presensi_ptk'));
    }
}
