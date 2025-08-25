<?php

namespace Dpb\Departments\Filament\Plugins;

use Dpb\Departments\Livewire\DepartmentSwitcherComponent;
use Dpb\DpbUtils\Helpers\UserPermissionHelper;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Livewire\Livewire;

class DepartmentSwitcherPlugin implements Plugin
{
    public function getId(): string
    {
        return 'dpb-departments';
    }

    public function register(
        Panel $panel
    ): void {
        $panel->renderHook(
            'panels::global-search.before',
            fn () =>
                UserPermissionHelper::hasPermission('dpb-departments.department-switcher.view')
                    ? Livewire::mount(DepartmentSwitcherComponent::class)
                    : null
        );
    }

    public function boot(
        Panel $panel
    ): void {

    }
}
