<?php

namespace Nodus\Packages\LivewireDatatables;

use Illuminate\Contracts\Support\Responsable;

/**
 * Livewire Component Class
 *
 * @package Nodus\Packages\LivewireDatatables
 */
class LivewireComponent implements Responsable
{
    /**
     * Livewire component name
     *
     * @var string
     */
    private string $componentName;

    /**
     * Livewire component parameters for mount
     *
     * @var array
     */
    private array $parameter;

    /**
     * Section name in extended layout
     *
     * @var string
     */
    private string $section = 'content';

    /**
     * Layout view name
     *
     * @var string
     */
    private string $layout = 'layouts.app';

    /**
     * Layout parameter
     *
     * @var array
     */
    private array $layoutParameter = [];

    /**
     * Creates a new LivewireComponent instance
     *
     * @param string $componentName Livewire component name
     * @param array  $parameter     Livewire component parameter
     *
     */
    public function __construct(string $componentName, array $parameter = [])
    {
        $this->componentName = $componentName;
        $this->parameter = $parameter;
    }

    /**
     * Sets the view section where the livewire component should be included
     *
     * @param string $section section name
     *
     * @return $this
     */
    public function section(string $section)
    {
        $this->section = $section;

        return $this;
    }

    /**
     * Sets the layout which livewire should extend
     *
     * @param string $layout Layout name
     *
     * @return $this
     */
    public function layout(string $layout)
    {
        $this->layout = $layout;

        return $this;
    }

    /**
     * Sets the parameter used in layout
     *
     * @param array $layoutParameter Layout parameter
     *
     * @return $this
     */
    public function layoutParameter(array $layoutParameter)
    {
        $this->layoutParameter = $layoutParameter;

        return $this;
    }

    /**
     * Renders the view
     *
     * @return mixed
     */
    public function render()
    {
        return view(
            'nodus.packages.livewire-datatables::livewire.base-component',
            array_merge(
                $this->layoutParameter,
                [
                    'livewire__component_name' => $this->componentName,
                    'livewire__parameter'      => $this->parameter,
                    'livewire__section'        => $this->section,
                    'livewire__layout'         => $this->layout,
                ]
            )
        );
    }

    /**
     * Automatic response handling
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed|\Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        return $this->render();
    }
}
