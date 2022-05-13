<?php

namespace Nodus\Packages\LivewireDatatables\Services;

use Closure;
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
     * Confirmation data
     *
     * @var array
     */
    protected array $confirmation = [];

    /**
     * Dynamic render condition closure
     *
     * @var Closure|null
     */
    protected ?Closure $condition = null;

    /**
     * Creates a new scope object
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
    public function setTarget(string $target): static
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
    public function setIcon(string $icon, bool $showIconOnly = true): static
    {
        $this->icon = $icon;
        $this->renderMode = ($showIconOnly) ? self::RENDER_MODE_ICON : self::RENDER_MODE_ICON_LABEL;

        return $this;
    }

    /**
     * Sets the custom classes for button
     *
     * @param array|string $classes
     *
     * @return static
     */
    public function setClasses(array|string $classes): static
    {
        $this->classes = Arr::wrap($classes);

        return $this;
    }

    /**
     * Defines the current button as confirmation button and sets the related parameters
     *
     * @param string|null $text    Confirmation modal text
     * @param string|null $title   Confirmation modal title
     * @param string|null $confirm Confirmation modal confirm button label
     * @param string|null $cancel  Confirmation modal cancel button label
     * @param string|null $context Confirmation modal CSS styling context
     *
     * @return static
     */
    public function setConfirmation(string $text = null, string $title = null, string $confirm = null, string $cancel = null, string $context = null): static
    {
        // todo refactoring: maybe use extra ConfirmButton class instead
        $this->confirmation = [
            'enable' => true,
        ];

        if ($text !== null) {
            $this->confirmation[ 'text' ] = $text;
        }

        if ($title !== null) {
            $this->confirmation[ 'title' ] = $title;
        }

        if ($confirm !== null) {
            $this->confirmation[ 'confirm' ] = $confirm;
        }

        if ($cancel !== null) {
            $this->confirmation[ 'cancel' ] = $cancel;
        }

        if ($context !== null) {
            $this->confirmation[ 'context' ] = $context;
        }

        return $this;
    }

    /**
     * Sets a condition for displaying the button
     *
     * @param Closure $closure
     *
     * @return Button
     */
    public function setCondition(Closure $closure): static
    {
        $this->condition = $closure;

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
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Returns the button label
     *
     * @return string
     */
    public function getLabel(): string
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
    public function getRoute(Model $model): string
    {
        $parameter = [];

        foreach ($this->routeParameter as $key => $value) {
            if (Str::startsWith($value, ':')) {
                $dbKey = ltrim($value, ':');
                if (Str::contains($dbKey, '.')) {
                    $keys = explode('.', $dbKey);
                    $dbKey = $model;
                    foreach ($keys as $k) {
                        $dbKey = $dbKey->$k;
                    }
                    $parameter[ $key ] = $dbKey;
                } else {
                    $parameter[ $key ] = $model->$dbKey;
                }
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
    public function getTarget(): string
    {
        return $this->target;
    }

    /**
     * Returns the icon if available
     *
     * @return string|null
     */
    public function getIcon(): ?string
    {
        return $this->icon;
    }

    /**
     * Returns the button render mode
     *
     * @return int
     */
    public function getRenderMode(): int
    {
        return $this->renderMode;
    }

    /**
     * Returns the custom button classes
     *
     * @return string|null
     */
    public function getClasses(): ?string
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
    public function getConfirmation(): array
    {
        return $this->confirmation;
    }

    /**
     * Return whether this is a confirmation button
     *
     * @return bool
     */
    public function isConfirmationButton(): bool
    {
        return !empty($this->confirmation);
    }

    /**
     * Returns true if the button should be rendered
     *
     * @param Model $item
     *
     * @return bool
     */
    public function isAllowedToRender(Model $item): bool
    {
        if (!is_callable($this->condition)) {
            return true;
        }

        $closure = $this->condition;

        return $closure($item);
    }
}
