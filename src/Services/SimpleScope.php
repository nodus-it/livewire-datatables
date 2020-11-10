<?php

namespace Nodus\Packages\LivewireDatatables\Services;

use Exception;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class Scope
 *
 * @package Nodus\Packages\LivewireDatatables\Services
 */
class SimpleScope
{
    /**
     * Scope Identifier
     *
     * @var string
     */
    private string $id;

    /**
     * Scope label
     *
     * @var string
     */
    protected string $label = '';

    /**
     * Scope name
     *
     * @var string
     */
    protected string $scope = '';

    /**
     * Creates an new scope object
     *
     * @param string $scope Scope name
     * @param string $label Scope label
     */
    public function __construct(string $scope, string $label)
    {
        $this->id = $label;
        $this->scope = $scope;
        $this->label = $label;
    }

    /**
     * Add the Scope to builder
     *
     * @param Builder $builder Builder
     *
     * @return Builder Builder
     * @throws Exception
     */
    public function addScope(Builder $builder)
    {
        $model = $builder->getModel();
        if (!method_exists(new $model, 'scope' . $this->scope)) {
            throw new Exception('Scope "' . $this->scope . '" not found in model ' . $model);
        }
        $builder->{$this->scope}();

        return $builder;
    }


    /**
     * Getter for blade
     */

    /**
     * Returns the scope identifier
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the scope label
     *
     * @return string
     */
    public function getLabel()
    {
        return trans($this->label);
    }
}
