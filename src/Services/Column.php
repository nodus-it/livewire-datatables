<?php

namespace Nodus\Packages\LivewireDatatables\Services;

use Carbon\Carbon;
use Closure;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;

/**
 * Column class
 *
 * @package Nodus\Packages\LivewireDatatables\Services
 */
class Column
{
    /**
     * Default breakpoint constants
     */
    public const int BREAKPOINT_400 = 400;
    public const int BREAKPOINT_600 = 600;
    public const int BREAKPOINT_750 = 750;
    public const int BREAKPOINT_1000 = 1000;

    /**
     * Column Identifier
     *
     * @var string
     */
    private string $id;

    /**
     * Column Label
     *
     * @var string
     */
    protected string $label = '';

    /**
     * Values to be displayed
     *
     * @var array
     */
    protected array $values = [];

    /**
     * Keys for sorting
     *
     * @var array|null
     */
    protected ?array $sortKeys = [];

    /**
     * Keys for searching
     *
     * @var array|null
     */
    protected ?array $searchKeys = [];

    /**
     * Flag for enabling/disabling html output
     *
     * @var bool
     */
    protected bool $html = false;

    /**
     * Column data type
     *
     * @var string
     */
    protected string $datatype = 'text';

    /**
     * Breakpoint for column in px
     *
     * @var int
     */
    protected int $breakpoint = 0;

    /**
     * Array of registered custom datatypes
     *
     * @var array
     */
    private static array $customDataTypes = [];

    /**
     * Creates a new column object
     *
     * @param string|array|Closure $values Values to be displayed or closure
     * @param string               $label  Column label
     */
    public function __construct($values, string $label)
    {
        $this->id = $label;
        $this->values = Arr::wrap($values);
        $this->label = $label;

        if (!$values instanceof Closure) {
            $this->setSortAndSearchKeys($this->values);
        }
    }

    /**
     * Sets the search keys for this column
     *
     * @param array|string|null $keys
     *
     * @return Column
     */
    public function setSearchKeys(array|string|null $keys): static
    {
        $this->searchKeys = ($keys === null) ? null : Arr::wrap($keys);

        return $this;
    }

    /**
     * Sets the sort keys for this column
     *
     * @param array|string|null $keys
     *
     * @return Column
     */
    public function setSortKeys(array|string|null $keys): static
    {
        $this->sortKeys = ($keys === null) ? null : Arr::wrap($keys);

        return $this;
    }

    /**
     * Sets the sort and search keys for this column
     *
     * @param array|string|null $keys
     *
     * @return $this
     */
    public function setSortAndSearchKeys(array|string|null $keys): static
    {
        $this->setSortKeys($keys);
        $this->setSearchKeys($keys);

        return $this;
    }

    /**
     * Checks if the sort and search handling needs to be auto-disabled for that column as default
     *
     * @param string|null $model
     *
     * @return bool
     */
    public function checkForAutoDisableSortAndSearch(?string $model = null): bool
    {
        foreach ($this->values as $value) {
            if ($value instanceof Closure || ($model !== null && method_exists(new $model(), $value))) {
                $this->setSortAndSearchKeys(null);

                return true;
            }
        }

        return false;
    }

    /**
     * Sets the HTML flag
     *
     * @param bool $html Allow HTML
     *
     * @return Column
     */
    public function enableHtml(bool $html = true): static
    {
        $this->html = $html;

        return $this;
    }

    /**
     * Sets the breakpoint for the column
     *
     * @param int $breakpoint
     *
     * @return Column
     */
    public function setBreakpoint(int $breakpoint): static
    {
        $this->breakpoint = $breakpoint;

        return $this;
    }


    /**
     * Getter for blade or datatable
     */

    /**
     * Returns the column identifier
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Returns the column label
     *
     * @return string
     */
    public function getLabel(): string
    {
        return trans($this->label);
    }

    /**
     * Returns the resolved values to be displayed
     *
     * @param Model|array $item Data Model
     *
     * @return string
     */
    public function getValues(Model|array $item): string
    {
        $results = [];
        foreach ($this->values as $value) {
            $results[] = $this->getValue($item, $value);
        }

        return implode(' ', $results);
    }

    /**
     * Returns the sort keys
     *
     * @return array
     */
    public function getSortKeys(): array
    {
        return $this->sortKeys ?? [];
    }

    /**
     * Returns if the sort function is enabled for this column
     *
     * @return bool
     */
    public function isSortEnabled(): bool
    {
        return $this->sortKeys !== null;
    }

    /**
     * Returns the search keys
     *
     * @return array
     */
    public function getSearchKeys(): array
    {
        return $this->searchKeys ?? [];
    }

    /**
     * Returns if the search is enabled for this column
     *
     * @return bool
     */
    public function isSearchEnabled(): bool
    {
        return $this->searchKeys !== null;
    }

    /**
     * Returns the HTML flag
     *
     * @return bool
     */
    public function isHtmlEnabled(): bool
    {
        return $this->html;
    }

    /**
     * Builds the value
     *
     * @param Model|array          $item  Data Model
     * @param string|Closure $value value string
     *
     * @return Model|string
     */
    protected function getValue(Model|array $item, $value): mixed
    {
        if ($value instanceof Closure) {
            return $value($item);
        }

        if (str_contains($value, '.')) {
            $values = explode('.', $value);
        } else {
            $values[] = $value;
        }

        $var = $item;
        foreach ($values as $v) {
            if ($var === null) {
                break;
            }

            if (is_array($var)) {
                $var = $var[$v] ?? null;
            } elseif (is_object($var)) {
                if (method_exists($var, $v)) {
                    if (!is_a($var->$v(), Relation::class)) {
                        $var = $var->$v();
                    } else {
                        $var = $var->$v;
                    }
                } else {
                    $var = $var->$v;
                }
            }
        }

        return $this->applyDatatype($var);
    }

    /**
     * Applies the set datatype cast and output format to the given data
     *
     * @param mixed $var
     *
     * @return mixed|string
     */
    protected function applyDatatype($var): mixed
    {
        switch ($this->datatype) {
            case 'text':
                return $var;

            case 'date':
                if ($var === null) {
                    return '-';
                }

                if (!$var instanceof Carbon) {
                    $var = Carbon::parse($var);
                }

                return $var->isoFormat('L');

            case 'datetime':
                if ($var === null) {
                    return '-';
                }

                if (!$var instanceof Carbon) {
                    $var = Carbon::parse($var);
                }

                return $var->isoFormat('L') . ' ' . $var->isoFormat('LTS');

            case 'time':
                if ($var === null) {
                    return '-';
                }

                if (!$var instanceof Carbon) {
                    $var = Carbon::parse($var);
                }

                return $var->isoFormat('LTS');

            case 'bool':
                return view(
                    'nodus.packages.livewire-datatables::livewire.' . config('livewire-datatables.theme') . '.components.datatypes.bool',
                    ['bool' => $var]
                );

            default:
                if (array_key_exists($this->datatype, self::$customDataTypes)) {
                    $var = self::$customDataTypes[ $this->datatype ]($var);
                }

                return $var;
        }
    }

    /**
     * Returns additional classes for <th> and <td>
     *
     * @return string
     */
    public function getClasses(): string
    {
        $classes = [];
        if ($this->breakpoint != 0) {
            $classes[] = 'breakpoint-' . $this->breakpoint;
        }

        return implode(' ', $classes);
    }


    /**
     * DataTypes
     */

    /**
     * Sets this column to the data type 'date'
     *
     * @return Column
     */
    public function setDataTypeDate(): static
    {
        $this->datatype = 'date';

        return $this;
    }

    /**
     * Sets this column to the data type 'datetime'
     *
     * @return Column
     */
    public function setDataTypeDateTime(): static
    {
        $this->datatype = 'datetime';

        return $this;
    }

    /**
     * Sets this column to the data type 'time'
     *
     * @return Column
     */
    public function setDataTypeTime(): static
    {
        $this->datatype = 'time';

        return $this;
    }

    /**
     * Sets this column to the data type 'bool'
     *
     * @return Column
     */
    public function setDataTypeBool(): static
    {
        $this->enableHtml();
        $this->datatype = 'bool';

        return $this;
    }

    /**
     * Sets this column to the given data type
     *
     * @param string $dataType
     *
     * @throws Exception
     * @return $this
     */
    public function setDataTypeCustom(string $dataType): static
    {
        if (!array_key_exists($dataType, self::$customDataTypes)) {
            throw new Exception('Custom datatype "' . $dataType . '" not found!');
        }

        $this->datatype = $dataType;

        return $this;
    }

    /**
     * Magic Method for custom data types
     *
     * @param string $name
     * @param array  $arguments
     *
     * @throws Exception
     * @return $this
     */
    public function __call(string $name, array $arguments): static
    {
        $dataType = strtolower(str_replace('setDataType', '', $name));

        return $this->setDataTypeCustom($dataType);
    }

    /**
     * Adds a custom data type to the column class
     *
     * @param string  $name    Name
     * @param Closure $closure Custom Data Type Closure
     */
    public static function addCustomDataType(string $name, Closure $closure): void
    {
        self::$customDataTypes[ $name ] = $closure;
    }
}
