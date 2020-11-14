<?php


namespace Nodus\Packages\LivewireDatatables;


trait Livewire
{
    /**
     * Create a new LivewireComponent instance
     *
     * @param string $componentName Livewire component name
     * @param array  $parameter     Livewire component parameter
     *
     * @return LivewireComponent
     */
    public function livewire(string $componentName, array $parameter)
    {
        $livewireComponent = new LivewireComponent($componentName, $parameter);

        if (property_exists($this, 'defaultLayout')) {
            $livewireComponent->layout($this->defaultLayout);
        }

        if (property_exists($this, 'defaultSection')) {
            $livewireComponent->section($this->defaultSection);
        }

        return $livewireComponent;
    }
}
