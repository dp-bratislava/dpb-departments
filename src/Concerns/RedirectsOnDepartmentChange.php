<?php

namespace Dpb\Departments\Concerns;

use Dpb\Departments\Livewire\DepartmentSwitcherComponent;
use Livewire\Attributes\On;

trait RedirectsOnDepartmentChange
{
    protected string $accessLostRedirectUrl = '/';

    #[On(DepartmentSwitcherComponent::EVENT_DEPARTMENT_CHANGED)]
    public function handleDepartmentChanged(): void
    {
        if (! $this->canStillAccessPage()) {
            $this->redirect($this->getAccessLostRedirectUrl());

            return;
        }

        $this->dispatch('$refresh');
        $this->dispatch('refresh-topbar');
        $this->dispatch('refresh-sidebar');
    }

    protected function getAccessLostRedirectUrl(): string
    {
        return $this->accessLostRedirectUrl;
    }

    abstract protected function canStillAccessPage(): bool;
}