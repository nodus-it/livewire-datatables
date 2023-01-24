<?php

namespace Nodus\Packages\LivewireDatatables\Livewire;

use Closure;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
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
abstract class DataTable extends Component
{
    use WithPagination;
    use SupportsTranslationsByModel;
    use SupportsAdditionalViewParameters;

    /**
     * Session key constants
     */
    public const SESSION_KEY_META_DATA = 'nodus-it.datatables.meta';

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
     * Result IDs
     *
     * @var array
     */
    public array $resultIds = [];

    /**
     * Result Model
     *
     * @var string
     */
    public string $resultModel;

    /**
     * Eager loading relations of the result
     *
     * @var array
     */
    public array $resultWith = [];

    /**
     * Removed global scopes of the result (e.g. SoftDelete)
     *
     * @var array
     */
    public array $resultWithoutGlobalScope = [];

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
    private array $columns = [];

    /**
     * Available simple scopes
     *
     * @var array
     */
    private array $simpleScopes = [];

    /**
     * Available buttons
     *
     * @var array
     */
    private array $buttons = [];

    /**
     * Temporary Builder
     *
     * @var Builder|\Illuminate\Database\Query\Builder|null
     */
    protected $builder = null;

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
     * Flag to enable/disable the counter UI widget
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
     *
     * @param null $id
     */
    public function __construct($id = null)
    {
        $this->paginationTheme = config('livewire-datatables.theme');

        parent::__construct($id);
    }

    /**
     * On mount handler
     *
     * @param Builder     $builder
     * @param string|null $sessionKeySuffix
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @return void
     */
    public function mount($builder, ?string $sessionKeySuffix = null): void
    {
        $this->resultModel = get_class($builder->getModel());
        $this->resultIds = $builder->pluck($this->prefixCol('id'))->toArray();
        $this->resultWith = array_keys($builder->getEagerLoads()) ?? [];
        $this->resultWithoutGlobalScope = $builder->removedScopes();

        $this->builder = $builder;
        $this->sessionKeySuffix = $sessionKeySuffix;

        $this->readSessionMetaData();
    }

    /**
     * Prefixes the column with the underlying database table
     *
     * @param string $column
     *
     * @return string
     */
    public function prefixCol(string $column): string
    {
        return $this->getBuilder()->getModel()->getTable() . '.' . $column;
    }

    /**
     * Renders the datatable
     *
     * @return View
     * @throws Exception
     */
    public function render()
    {
        $this->columns();
        if (method_exists($this, 'scopes')) {
            $this->scopes();
        }
        if (method_exists($this, 'buttons')) {
            $this->buttons();
        }

        $builder = $this->getBuilder();
        $themePath = $this->getThemePath();

        $this->applyScopes($builder);
        $this->applySearch($builder);
        $this->applySort($builder);
        $paginator = $this->applyPagination($builder);

        $this->writeSessionMetaData();

        return view(
            $themePath . '.datatable',
            [
                'results'      => $paginator,
                'columns'      => $this->columns,
                'simpleScopes' => $this->simpleScopes,
                'buttons'      => $this->buttons,
                'themePath'    => $themePath,
                'show'         => (object)[
                    'scopes'     => $this->showSimpleScopes,
                    'search'     => $this->showSearch,
                    'counter'    => $this->showCounter,
                    'pagination' => $this->showPagination,
                    'pageLength' => $this->showPageLength,
                ]
            ]
        );
    }

    /**
     * Sets the selected scopes
     *
     * @param Builder $builder Builder
     *
     * @return Builder Builder
     * @throws Exception Scope not found in model
     */
    protected function applyScopes($builder)
    {
        if ($this->simpleScope != '') {
            $builder = $this->simpleScopes[ $this->simpleScope ]->addScope($builder);
        }

        return $builder;
    }

    /**
     * Sets the where's for selected search
     *
     * @param Builder $builder Builder
     *
     * @return Builder Builder
     */
    protected function applySearch($builder)
    {
        if (!empty($this->search)) {
            $builder->where(
                function (Builder $builder) {
                    foreach ($this->columns as $column) {
                        if (!$column->isSearchEnabled()) {
                            continue;
                        }

                        foreach ($column->getSearchKeys() as $searchKey) {
                            if (Str::contains($searchKey, '.')) {
                                // Relation table search
                                [$relation, $relationSearchKey] = explode('.', $searchKey, 2);
                                $builder->orWhereHas(
                                    $relation,
                                    function (Builder $b) use ($relationSearchKey) {
                                        $b->where($relationSearchKey, 'LIKE', '%' . $this->search . '%');
                                    }
                                );
                            } else {
                                // Base table search
                                $builder->orWhere($searchKey, 'LIKE', '%' . $this->search . '%');
                            }
                        }
                    }
                }
            );
        }

        return $builder;
    }

    /**
     * Setts the orderBy for selected column
     *
     * @param Builder $builder
     *
     * @return Builder
     */
    protected function applySort($builder)
    {
        // Sort by default by id
        if ($this->sort === null) {
            return $builder->orderBy($this->prefixCol('id'), $this->sortDirection);
        }

        // if the sort key isn't a column use it directly for sorting
        if (!isset($this->columns[ $this->sort ])) {
            return $builder->orderBy($this->sort, $this->sortDirection);
        }

        // if the sort key matches a column than use the columns sort keys
        foreach ($this->columns[ $this->sort ]->getSortKeys() as $sort) {
            if (!Str::contains($sort, '.')) {
                $builder->orderBy($sort, $this->sortDirection);
            }
        }

        return $builder;
    }

    /**
     * Sets the limit and the offset
     *
     * @param Builder $builder
     *
     * @return Builder|LengthAwarePaginator
     */
    protected function applyPagination($builder)
    {
        return $builder->paginate($this->paginate)->onEachSide($this->paginateOnEachSide);
    }


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
     *  Returns the builder
     *
     * @return Builder Builder
     */
    protected function getBuilder()
    {
        if ($this->builder == null) {
            $model = new $this->resultModel();

            /**
             * @var Builder $query
             */
            $query = $model::query();

            foreach ($this->resultWithoutGlobalScope as $globalScope) {
                $query->withoutGlobalScope($globalScope);
            }

            return $query->whereIn($model->getModel()->getTable() . '.id', $this->resultIds)->with($this->resultWith);
        }

        return $this->builder;
    }

    /**
     * Returns the model class used for building the auto translation keys
     *
     * @return string
     */
    protected function getTranslationModelClass(): string
    {
        return $this->resultModel;
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
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @return bool
     */
    protected function readSessionMetaData(): bool
    {
        if (!session()->exists($this->getSessionMetaDataKey())) {
            return false;
        }

        $meta = session()->get($this->getSessionMetaDataKey());
        $this->paginate = $meta[ 'paginate' ];
        $this->sort = $meta[ 'sort' ];
        $this->sortDirection = $meta[ 'sortDirection' ];
        $this->simpleScope = $meta[ 'simpleScope' ];
        $this->search = $meta[ 'search' ];

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
    protected function addColumn(array|Closure|string $values, string $label = null)
    {
        if ($label == null) {
            if ($values instanceof Closure) {
                $label = 'closure';
            } else {
                $label = $this->getTranslationStringByModel('fields.' . ((is_array($values)) ? $values[ 0 ] : $values));
            }
        }

        /**
         * @var Column $column
         */
        $column = new $this->columnClass($values, $label);
        $column->checkForAutoDisableSortAndSearch($this->resultModel);

        $this->columns[ $column->getId() ] = $column;

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
    protected function addSimpleScope(string $scope, string $label = null)
    {
        if ($label == null) {
            $label = $this->getTranslationStringByModel('scopes.simple.' . $scope);
        }

        $simpleScope = new $this->simpleScopeClass($scope, $label);

        $this->simpleScopes[ $simpleScope->getId() ] = $simpleScope;

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
