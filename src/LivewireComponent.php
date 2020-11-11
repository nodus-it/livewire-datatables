<?php

namespace Nodus\Packages\LivewireDatatables;

use Illuminate\Contracts\Support\Responsable;

class LivewireComponent implements Responsable
{
    /**
     * @var string Livewire component name
     */
    private string $componentName;

    /**
     * @var array Livewire component parameters for mount
     */
    private array $parameter;

    /**
     * @var string Section name in extended layout
     */
    private string $section = 'content';

    /**
     * @var string Layout view name
     */
    private string $layout = 'layouts.app';

    /**
     * @var array Layout parameter
     */
    private array $layoutParameter = [];

    /**
     * Create a new LivewireComponent instance
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
     * Set section where livewire component should be included
     *
     * @param string $section section name
     *
     * @return $this
     */
    public function section($section)
    {
        $this->section = $section;

        return $this;
    }

    /**
     * Set layout which livewire should extend
     *
     * @param string $layout Layout name
     *
     * @return $this
     */
    public function layout($layout)
    {
        $this->layout = $layout;

        return $this;
    }

    /**
     * Set parameter used in layout
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
     * Render the view
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
