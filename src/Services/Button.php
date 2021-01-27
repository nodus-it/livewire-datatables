<?php

namespace Nodus\Packages\LivewireDatatables\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Button class
 *
 * @package Nodus\Packages\LivewireDatatables\Services
 */
class Button
{
    /**
     * Render mode constants
     */
    public const RENDER_MODE_LABEL = 1;
    public const RENDER_MODE_ICON = 2;
    public const RENDER_MODE_ICON_LABEL = 3;

    /**
     * Button identifier
     *
     * @var string
     */
    private string $id;

    /**
     * Button label
     *
     * @var string
     */
    protected string $label = '';

    /**
     * Route name
     *
     * @var string
     */
    protected string $route = '';

    /**
     * Route parameter
     *
     * @var array
     */
    protected array $routeParameter = [];

    /**
     * Target for <a> tag
     *
     * @var string
     */
    protected string $target = '_self';

    /**
     * Icon CSS class
     *
     * @var string|null
     */
    protected ?string $icon = null;

    /**
     * Render mode
     *
     * @var int
     */
    protected int $renderMode = self::RENDER_MODE_LABEL;

    /**
     * CSS classes array
     *
     * @var array
     */
    protected array $classes = [];

    /**
     * @var array Confirmation data
     */
    private array $confirmation = [];

    private \Closure $condition;

    /**
     * Creates an new scope object
     *
     * @param string $label          Scope label
     * @param string $route          Route name
     * @param array  $routeParameter Route Parameter
     */
    public function __construct(string $label, string $route, array $routeParameter = [])
    {
        $this->id = $label;
        $this->label = $label;
        $this->route = $route;
        $this->routeParameter = $routeParameter;
    }

    /**
     * Sets the target for <a> tag
     *
     * @param string $target Target
     *
     * @return Button
     */
    public function setTarget(string $target)
    {
        $this->target = $target;

        return $this;
    }

    /**
     * Sets an icon for button and choose the render mode
     *
     * @param string $icon
     * @param bool   $showIconOnly
     *
     * @return Button
     */
    public function setIcon(string $icon, bool $showIconOnly = true)
    {
        $this->icon = $icon;
        if ($showIconOnly) {
            $this->renderMode = self::RENDER_MODE_ICON;
        } else {
            $this->renderMode = self::RENDER_MODE_ICON_LABEL;
        }

        return $this;
    }

    /**
     * Sets the custom classes for button
     *
     * @param array|string $classes
     *
     * @return static
     */
    public function setClasses($classes)
    {
        $this->classes = Arr::wrap($classes);

        return $this;
    }

    /**
     * Defines the current button as confirmation button and sets the related parameters
     *
     * @param string|null $message
     * @param string|null $title
     * @param string|null $confirm
     * @param string|null $abort
     *
     * @return static
     */
    public function setConfirmation(string $message = null, string $title = null, string $confirm = null, string $abort = null)
    {
        $this->confirmation = [
            'message' => $message ?? trans('nodus.packages.livewire-datatables::datatable.confirm.message'),
            'title'   => $title ?? trans('nodus.packages.livewire-datatables::datatable.confirm.title'),
            'confirm' => $confirm ?? trans('nodus.packages.livewire-datatables::datatable.confirm.confirm'),
            'abort'   => $abort ?? trans('nodus.packages.livewire-datatables::datatable.confirm.abort'),
        ];

        return $this;
    }

    /**
     * Sets a condition for displaying the button
     *
     * @param \Closure $closure
     */
    public function setCondition(\Closure $closure)
    {
        $this->condition = $closure;
    }


    /**
     * Getter for blade
     */

    /**
     * Returns the button identifier
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the button label
     *
     * @return string
     */
    public function getLabel()
    {
        return trans($this->label);
    }

    /**
     * Returns the finished url
     *
     * @param Model $model Data Model
     *
     * @return string URL
     */
    public function getRoute(Model $model)
    {
        $parameter = [];

        foreach ($this->routeParameter as $key => $value) {
            if (Str::startsWith($value, ':')) {
                $parameter[ $key ] = $model->{ltrim($value, ':')};
            } else {
                $parameter[ $key ] = $value;
            }
        }

        return route($this->route, $parameter);
    }

    /**
     * Returns the <a> tag target
     *
     * @return string Target
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Returns the icon if available
     *
     * @return string|null
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * Returns the button render mode
     *
     * @return int
     */
    public function getRenderMode()
    {
        return $this->renderMode;
    }

    /**
     * Returns the custom button classes
     *
     * @return string
     */
    public function getClasses()
    {
        if (count($this->classes) == 0) {
            return null;
        }

        return implode(' ', $this->classes);
    }

    /**
     * Returns the confirmation parameter array
     *
     * @return array
     */
    public function getConfirmation()
    {
        return $this->confirmation;
    }

    /**
     * Returns true if the button should be rendered
     *
     * @param Model $item
     *
     * @return bool|mixed
     */
    public function getDisplayButton(Model $item)
    {
        if (empty($this->condition)) {
            return true;
        } else {
            $closure = $this->condition;

            return $closure($item);
        }
    }
}
