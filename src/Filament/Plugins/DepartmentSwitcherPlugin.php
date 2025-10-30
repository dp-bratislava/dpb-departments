<?php

namespace Dpb\Departments\Filament\Plugins;

use Dpb\Departments\Livewire\DepartmentSwitcherComponent;
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
            name: 'panels::global-search.before',
            hook: fn (): string => Livewire::mount(
                name: DepartmentSwitcherComponent::class
            )
        );
    }

    public function boot(
        Panel $panel
    ): void {

    }
}
