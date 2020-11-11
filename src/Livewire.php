<?php

namespace Nodus\Packages\LivewireDatatables;

/**
 * Livewire Trait
 *
 * @package Nodus\Packages\LivewireDatatables
 */
trait Livewire
{
    /**
     * Default view layout name
     *
     * @var string|null
     */
    protected ?string $defaultLayout = null;

    /**
     * Default view section
     *
     * @var string|null
     */
    protected ?string $defaultSection = null;

    /**
     * Creates a new LivewireComponent instance
     *
     * @param string $componentName Livewire component name
     * @param array  $parameter     Livewire component parameter
     *
     * @return LivewireComponent
     */
    public function livewire(string $componentName, array $parameter)
    {
        $livewireComponent = new LivewireComponent($componentName, $parameter);

        if ($this->defaultLayout != null) {
            $livewireComponent->layout($this->defaultLayout);
        }

        if ($this->defaultSection != null) {
            $livewireComponent->section($this->defaultSection);
        }

        return $livewireComponent;
    }
}
