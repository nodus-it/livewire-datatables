<?php

namespace Nodus\Packages\LivewireDatatables\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Button class
 *
 * @package Nodus\Packages\LivewireDatatables\Services
 */
class Button
{
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
     * Set the target for <a> tag
     *
     * @param string $target Target
     *
     * @return $this
     */
    public function setTarget(string $target)
    {
        $this->target = $target;

        return $this;
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
            if(Str::startsWith($value,':')) {
                $parameter[ $key ] = $model->{ltrim($value,':')};
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
}
