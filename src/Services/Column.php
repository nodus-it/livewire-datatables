<?php

namespace Nodus\Packages\LivewireDatatables\Services;

use Carbon\Carbon;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Traits\Macroable;

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
    public const BREAKPOINT_400 = 400;
    public const BREAKPOINT_600 = 600;
    public const BREAKPOINT_750 = 750;
    public const BREAKPOINT_1000 = 1000;

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
     * Values for show
     *
     * @var array
     */
    protected array $values = [];

    /**
     * Keys for sorting
     *
     * @var array
     */
    protected array $sortKeys = [];

    /**
     * Keys for searching
     *
     * @var array
     */
    protected array $searchKeys = [];

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

    private static $customDataTypes = [];

    /**
     * Creates an new column object
     *
     * @param string|array $values Values for show
     * @param string       $label  Column label
     */
    public function __construct($values, string $label)
    {
        $this->id = $label;
        $this->values = Arr::wrap($values);
        $this->label = $label;
        $this->sortKeys = $this->values; // ToDo: Disable SortKeys oder manuell aktivieren?!
        $this->searchKeys = $this->values; // ToDo: Disable SearchKeys oder manuell aktivieren?!
    }

    /**
     * Sets the search keys for this column
     *
     * @param array|string $keys
     *
     * @return Column
     */
    public function setSearchKeys($keys)
    {
        $this->searchKeys = Arr::wrap($keys);

        return $this;
    }

    /**
     * Sets the sort keys for this column
     *
     * @param array|string $keys
     *
     * @return Column
     */
    public function setSortKeys($keys)
    {
        $this->sortKeys = Arr::wrap($keys);

        return $this;
    }

    /**
     * Sets the sort and search keys for this column
     *
     * @param array|string $keys
     *
     * @return $this
     */
    public function setSortAndSearchKeys($keys)
    {
        $this->sortKeys = Arr::wrap($keys);
        $this->searchKeys = Arr::wrap($keys);

        return $this;
    }

    /**
     * Sets the html flag
     *
     * @param bool $html Allow html
     *
     * @return Column
     */
    public function enableHtml(bool $html = true)
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
    public function setBreakpoint(int $breakpoint)
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
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the column label
     *
     * @return string
     */
    public function getLabel()
    {
        return trans($this->label);
    }

    /**
     * Returns the parsed values for show
     *
     * @param Model $item Data Model
     *
     * @return string
     */
    public function getValues(Model $item)
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
    public function getSortKeys()
    {
        return $this->sortKeys;
    }

    /**
     * Returns the search keys
     *
     * @return array
     */
    public function getSearchKeys()
    {
        return $this->searchKeys;
    }

    /**
     * Returns the html flag
     *
     * @return bool
     */
    public function isHtmlEnabled()
    {
        return $this->html;
    }

    /**
     * Builds the value
     *
     * @param Model          $item  Data Model
     * @param string|Closure $value value string
     *
     * @return Model|string
     */
    protected function getValue(Model $item, $value)
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

        switch ($this->datatype) {
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
    public function getClasses()
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
     * Marks this column as data type 'date'
     *
     * @return Column
     */
    public function setDataTypeDate()
    {
        $this->datatype = 'date';

        return $this;
    }

    /**
     * Marks this column as data type 'datetime'
     *
     * @return Column
     */
    public function setDataTypeDateTime()
    {
        $this->datatype = 'datetime';

        return $this;
    }

    /**
     * Marks this column as data type 'time'
     *
     * @return Column
     */
    public function setDataTypeTime()
    {
        $this->datatype = 'time';

        return $this;
    }

    /**
     * Marks this column as data type 'bool'
     *
     * @return Column
     */
    public function setDataTypeBool()
    {
        $this->enableHtml();
        $this->datatype = 'bool';

        return $this;
    }

    /**
     * Marks this column as the given data type
     *
     * @param string $dataType
     *
     * @throws \Exception
     * @return $this
     */
    public function setDataTypeCustom(string $dataType)
    {
        if (!array_key_exists($dataType, self::$customDataTypes)) {
            throw new \Exception('Custom datatype "' . $dataType . '" not found!');
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
     * @throws \Exception
     * @return $this|false
     */
    public function __call(string $name, array $arguments)
    {
        $dataType = strtolower(str_replace('setDataType', '', $name));

        return $this->setDataTypeCustom($dataType);
    }

    /**
     * Adds an custom data type to column class
     *
     * @param string  $name    Name
     * @param Closure $closure Custom Data Type Closure
     */
    public static function addCustomDataType(string $name, Closure $closure)
    {
        self::$customDataTypes[ $name ] = $closure;
    }
}
