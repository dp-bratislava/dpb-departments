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
        $this->mergeConfigFrom(
            path: __DIR__ . '/../../config/dpb-departments.php',
            key: 'dpb-departments'
        );
        $this->mergeConfigFrom(
            path: __DIR__ . '/../../config/permissions.php',
            key: 'dpb.permissions.dpb-departments'
        );
    }

    public function boot(): void
    {
        $this->loadViewsFrom(
            path: __DIR__.'/../../resources/views',
            namespace: 'dpb-departments'
        );
        $this->loadTranslationsFrom(
            path: __DIR__ . '/../../resources/lang',
            namespace: 'dpb-departments'
        );
        $this->loadMigrationsFrom(
            paths: __DIR__ . '/../../database/migrations'
        );
        $this->publishes(
            paths: [__DIR__ . '/../../config/dpb-departments.php' => config_path(path: 'dpb-departments.php')],
            groups: 'dpb-departments-config'
        );
        Livewire::component(
            name: 'dpb.departments.livewire.department-switcher-component',
            class: DepartmentSwitcherComponent::class
        );

        $this->registerFilamentPlugin();
    }

    private function registerFilamentPlugin(): void
    {
        config()->set(key: 'admin-panel.plugins', value: array_merge(
            [DepartmentSwitcherPlugin::class],
            config(key: 'admin-panel.plugins', default: [])
        ));
    }
}
