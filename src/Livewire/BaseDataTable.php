<?php

namespace Nodus\Packages\LivewireDatatables\Livewire;

use Closure;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;
use Nodus\Packages\LivewireCore\Services\SupportsTranslationsByModel;
use Nodus\Packages\LivewireCore\SupportsAdditionalViewParameters;
use Nodus\Packages\LivewireDatatables\Services\Button;
use Nodus\Packages\LivewireDatatables\Services\Column;
use Nodus\Packages\LivewireDatatables\Services\SimpleScope;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * DataTable Class
 *
 * @package Nodus\Packages\LivewireDatatables\Livewire
 */
abstract class BaseDataTable extends Component
{
    use WithPagination;
    use SupportsTranslationsByModel;
    use SupportsAdditionalViewParameters;

    /**
     * Session key constants
     */
    public const string SESSION_KEY_META_DATA = 'nodus-it.datatables.meta';

    /**
     * The Column class to be used in the addColumn method
     *
     * @var string
     */
    protected string $columnClass = Column::class;

    /**
     * The Button class to be used in the addButton method
     *
     * @var string
     */
    protected string $buttonClass = Button::class;

    /**
     * The SimpleScope class to be used in the addSimpleScope method
     *
     * @var string
     */
    protected string $simpleScopeClass = SimpleScope::class;

    /**
     * Laravel theme for the built-in pagination
     *
     * @var string
     */
    protected string $paginationTheme = 'bootstrap';

    /**
     * Paginate Count
     *
     * @var int
     */
    public int $paginate = 10;

    /**
     * Paginate on each side
     *
     * @var int
     */
    public int $paginateOnEachSide = 2;

    /**
     * Current Sort Column
     *
     * @var string|null
     */
    public ?string $sort = null;

    /**
     * Current sort direction
     *
     * @var string
     */
    public string $sortDirection = 'ASC';

    /**
     * Current simple scope
     *
     * @var string
     */
    public string $simpleScope = '';

    /**
     * Current search keywords
     *
     * @var string
     */
    public string $search = '';

    /**
     * Available columns
     *
     * @var array|Column[]
     */
    protected array $columns = [];

    /**
     * Available simple scopes
     *
     * @var array
     */
    protected array $simpleScopes = [];

    /**
     * Available buttons
     *
     * @var array
     */
    protected array $buttons = [];

    /**
     * Flag to enable/disable the simple scope UI widget
     *
     * @var bool
     */
    public bool $showSimpleScopes = true;

    /**
     * Flag to enable/disable the search UI widget
     *
     * @var bool
     */
    public bool $showSearch = true;

    /**
     * Flag to enable/disable the Counter UI-widget
     *
     * @var bool
     */
    public bool $showCounter = true;

    /**
     * Flag to enable/disable the pagination UI widget
     *
     * @var bool
     */
    public bool $showPagination = true;

    /**
     * Flag to enable/disable the page length UI widget
     *
     * @var bool
     */
    public bool $showPageLength = true;

    /**
     * Suffix for the session key
     *
     * @var string|null
     */
    public ?string $sessionKeySuffix = null;

    /**
     * DataTable constructor.
     */
    public function __construct()
    {
        $this->paginationTheme = config('livewire-datatables.theme');
    }

    /**
     * Renders the datatable
     *
     * @return View
     * @throws Exception
     */
    abstract public function render();

    /**
     * Sets the selected scopes
     *
     * @param Builder|Collection $builderOrData
     *
     * @return Builder|Collection
     * @throws Exception Scope wasn't found for the model
     */
    abstract protected function applyScopes($builderOrData);

    /**
     * Sets the where's for selected search
     *
     * @param Builder|Collection $builderOrData
     *
     * @return Builder|Collection
     */
    abstract protected function applySearch($builderOrData);

    /**
     * Setts the orderBy for selected column
     *
     * @param Builder|Collection $builderOrData
     *
     * @return Builder|Collection
     */
    abstract protected function applySort($builderOrData);

    /**
     * Sets the limit and the offset
     *
     * @param Builder|Collection $builderOrData
     *
     * @return Builder|LengthAwarePaginator
     */
    abstract protected function applyPagination($builderOrData);


    /**
     * Events
     */

    /**
     * Changes the sort column
     *
     * @param string $key Column key
     */
    public function changeSort(string $key): void
    {
        if ($this->sort == $key) {
            if ($this->sortDirection == 'ASC') {
                $this->sortDirection = 'DESC';
            } else {
                $this->sortDirection = 'ASC';
            }
        } else {
            $this->sort = $key;
            $this->sortDirection = 'ASC';
        }
    }

    /**
     * Reset's the pagination after changing the number of results
     */
    public function updatingPaginate(): void
    {
        $this->resetPage();
    }

    /**
     * Reset's the pagination after changing the search
     */
    public function updatingSearch(): void
    {
        $this->resetPage();
    }


    /**
     * Internal methods
     */

    /**
     * Returns the model class used for building the auto translation keys
     *
     * @return string
     */
    // todo
    protected function getTranslationModelClass(): string
    {
        return $this->resultModel ?? '';
    }

    /**
     * Returns the theme view path
     *
     * @return string
     */
    protected function getThemePath(): string
    {
        return 'nodus.packages.livewire-datatables::livewire.' . config('livewire-datatables.theme');
    }

    /**
     * Returns an array of search tokens
     *
     * @return string[]
     */
    protected function getSearchTokens(): array
    {
        return explode(' ', trim($this->search));
    }

    /**
     * Returns the CSS styles of this component
     *
     * @return string
     */
    public static function styles(): string
    {
        return <<<CSS
        /** Livewire datatable styles **/
        .nodus-table-pagination-change .custom-select {
            max-width: 100px;
        }

        .nodus-table-simple-scopes .custom-select {
            max-width: 300px;
        }

        .nodus-table-search .form-control {
            max-width: 300px;
        }

        .nodus-table-disabled {
            pointer-events: none;
        }
CSS;
    }

    /**
     * Returns the key used for the metadata in the session
     *
     * @return string
     */
    protected function getSessionMetaDataKey(): string
    {
        $sessionKey = self::SESSION_KEY_META_DATA . '.' . get_class($this);

        if ($this->sessionKeySuffix !== null) {
            $sessionKey .= '-' . $this->sessionKeySuffix;
        }

        return $sessionKey;
    }

    /**
     * Write current table meta data in session
     *
     * @return void
     */
    protected function writeSessionMetaData(): void
    {
        session()->put($this->getSessionMetaDataKey(), [
            'paginate'      => $this->paginate,
            'sort'          => $this->sort,
            'sortDirection' => $this->sortDirection,
            'simpleScope'   => $this->simpleScope,
            'search'        => $this->search,
        ]);
    }

    /**
     * Read recent table meta data from session
     *
     * @return bool
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    protected function readSessionMetaData(): bool
    {
        if (!session()->exists($this->getSessionMetaDataKey())) {
            return false;
        }

        $meta = session()->get($this->getSessionMetaDataKey());
        $this->paginate = $meta['paginate'];
        $this->sort = $meta['sort'];
        $this->sortDirection = $meta['sortDirection'];
        $this->simpleScope = $meta['simpleScope'];
        $this->search = $meta['search'];

        return true;
    }


    /**
     * Interface
     */

    /**
     * Method which should add the columns in derived classes
     *
     * @return void
     */
    abstract protected function columns();

    /**
     * Add a column to datatable
     *
     * @param array|Closure|string $values Values for column
     * @param string|null          $label  Label for column
     *
     * @return Column
     */
    protected function addColumn(array|Closure|string $values, ?string $label = null)
    {
        if ($label == null) {
            if ($values instanceof Closure) {
                $label = 'closure';
            } else {
                $firstValue = ((is_array($values)) ? $values[0] : $values);

                if (empty($this->getTranslationModelClass())) {
                    $label = $firstValue;
                } else {
                    $label = $this->getTranslationStringByModel('fields.' . $firstValue);
                }
            }
        }

        /**
         * @var Column $column
         */
        $column = new $this->columnClass($values, $label);
        $column->checkForAutoDisableSortAndSearch($this->resultModel ?? null);

        $this->columns[$column->getId()] = $column;

        return $column;
    }

    /**
     * Returns the array of registered columns
     *
     * @return array
     */
    protected function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * Add a simple scope to datatable
     *
     * @param string      $scope Scope name
     * @param string|null $label Scope label
     *
     * @return SimpleScope
     */
    protected function addSimpleScope(string $scope, ?string $label = null)
    {
        if ($label == null) {
            $label = $this->getTranslationStringByModel('scopes.simple.' . $scope);
        }

        $simpleScope = new $this->simpleScopeClass($scope, $label);

        $this->simpleScopes[$simpleScope->getId()] = $simpleScope;

        return $simpleScope;
    }

    /**
     * Returns the array of registered simple scopes
     *
     * @return array
     */
    protected function getSimpleScopes(): array
    {
        return $this->simpleScopes;
    }

    /**
     * Add a button to datatable
     *
     * @param string $label          Button name
     * @param string $route          Route name
     * @param array  $routeParameter Route Parameter
     *
     * @return Button
     */
    protected function addButton(string $label, string $route, array $routeParameter = [])
    {
        $button = new $this->buttonClass($label, $route, $routeParameter);
        $this->buttons[] = $button;

        return $button;
    }

    /**
     * Returns the array of registered buttons
     *
     * @return array
     */
    protected function getButtons(): array
    {
        return $this->buttons;
    }
}
