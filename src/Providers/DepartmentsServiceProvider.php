<?php

namespace Dpb\Departments\Providers;

use Dpb\Departments\Filament\Plugins\DepartmentSwitcherPlugin;
use Dpb\Departments\Livewire\DepartmentSwitcherComponent;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class DepartmentsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/dpb-departments.php', 'dpb-departments');
        $this->mergeConfigFrom(__DIR__ . '/../../config/permissions.php', 'dpb.permissions.dpb-departments');
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'dpb-departments');
        $this->loadTranslationsFrom(__DIR__ . '/../../resources/lang', 'dpb-departments');
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        $this->publishes([
            __DIR__ . '/../../config/dpb-departments.php' => config_path('dpb-departments.php'),
        ], 'dpb-departments-config');
        $this->registerFilamentPlugin();
    }

    private function registerFilamentPlugin(): void
    {
        config()->set('admin-panel.plugins', array_merge(
            [DepartmentSwitcherPlugin::class],
            config('admin-panel.plugins', [])
        ));
    }
}
