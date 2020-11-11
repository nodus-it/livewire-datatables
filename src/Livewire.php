<?php


namespace Nodus\Packages\LivewireDatatables;


trait Livewire
{
    /**
     * @var null|string Default layout name
     */
    protected ?string $defaultLayout = null;

    /**
     * @var string|null Default
     */
    protected ?string $defaultSection = null;

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

        if ($this->defaultLayout != null) {
            $livewireComponent->layout($this->defaultLayout);
        }

        if ($this->defaultSection != null) {
            $livewireComponent->section($this->defaultSection);
        }

        return $livewireComponent;
    }
}
