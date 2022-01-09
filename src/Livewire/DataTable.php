<?php

namespace Nodus\Packages\LivewireDatatables\Livewire;

use Closure;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;
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
     * @var array
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
    private $builder = null;

    /**
     * Custom translation prefix
     *
     * @var string|null
     */
    protected ?string $translationPrefix = null;

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
     * @param Builder $builder
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function mount($builder)
    {
        $this->resultModel = get_class($builder->getModel());
        $this->resultIds = $builder->pluck($this->prefixCol('id'))->toArray();
        $this->resultWith = array_keys($builder->getEagerLoads()) ?? [];
        $this->resultWithoutGlobalScope = $builder->removedScopes();

        $this->builder = $builder;

        $this->readSessionMetaData();
    }

    /**
     * Prefixes the column with the underlaying database table
     *
     * @return string
     */
    public function prefixCol(string $column)
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

        $this->writeSessionMetaData();

        return view(
            $themePath . '.datatable',
            [
                'results'      => $builder->paginate($this->paginate)->onEachSide($this->paginateOnEachSide),
                'columns'      => $this->columns,
                'simpleScopes' => $this->simpleScopes,
                'buttons'      => $this->buttons,
                'themePath'    => $themePath,
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
        if ($this->search != '') {
            $builder->where(
                function (Builder $builder) {
                    foreach ($this->columns as $column) {
                        foreach ($column->getSearchKeys() as $searchKey) {
                            if (Str::contains($searchKey, '.')) {
                                $searchKeys = explode('.', $searchKey);
                                $builder->orWhereHas(
                                    $searchKeys[ 0 ],
                                    function (Builder $b) use ($searchKeys) {
                                        $b->where($searchKeys[ 1 ], 'LIKE', '%' . $this->search . '%');
                                    }
                                );
                            } else {
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
        if ($this->sort == null) {
            $builder->orderBy($this->prefixCol('id'), $this->sortDirection);
        } else {
            if (array_key_exists($this->sort, $this->columns)) {
                foreach ($this->columns[ $this->sort ]->getSortKeys() as $sort) {
                    if (!Str::contains($sort, '.')) {
                        $builder->orderBy($sort, $this->sortDirection);
                    }
                }
            } else {
                $builder->orderBy($this->sort, $this->sortDirection);
            }
        }

        return $builder;
    }


    /**
     * Events
     */

    /**
     * Changes the sort column
     *
     * @param string $key Column key
     */
    public function changeSort(string $key)
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
    public function updatingPaginate()
    {
        $this->resetPage();
    }

    /**
     * Reset's the pagination after changing the search
     */
    public function updatingSearch()
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
    private function getBuilder()
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
     * Sets the translation prefix
     *
     * @param string|null $prefix
     *
     * @return $this
     */
    protected function setTranslationPrefix(?string $prefix)
    {
        $this->translationPrefix = $prefix;

        return $this;
    }

    /**
     * Returns the translation prefix
     *
     * @return string
     */
    protected function getTranslationPrefix()
    {
        // TODO unify with form package
        if ($this->translationPrefix === null) {
            return Str::plural(Str::snake(Str::afterLast($this->resultModel, '\\')));
        }

        return $this->translationPrefix;
    }

    /**
     * Generates a default translation string, based on model and column name
     *
     * @param string $lang Column name
     *
     * @return string
     */
    protected function getTranslationStringByModel(string $lang)
    {
        return $this->getTranslationPrefix() . '.' . $lang;
    }

    /**
     * Returns the theme view path
     *
     * @return string
     */
    protected function getThemePath()
    {
        return 'nodus.packages.livewire-datatables::livewire.' . config('livewire-datatables.theme');
    }

    /**
     * Returns the CSS styles of this component
     *
     * @return string
     */
    public static function styles()
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
CSS;
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
     * @param string|array|Closure $values Values for column
     * @param string|null          $label  Label for column
     *
     * @throws Exception
     * @return Column
     */
    protected function addColumn($values, string $label = null)
    {
        if ($label == null) {
            if ($values instanceof Closure) {
                $label = 'closure';
            } else {
                $label = $this->getTranslationStringByModel('fields.' . ((is_array($values)) ? $values[ 0 ] : $values));
            }
        }

        $column = new $this->columnClass($values, $label);
        $column->checkForAutoDisableSortAndSearch($this->resultModel);

        $this->columns[ $column->getId() ] = $column;

        return $column;
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
     * Returns the key used for the metadata in the session
     *
     * @return string
     */
    protected function getSessionMetaDataKey()
    {
        return self::SESSION_KEY_META_DATA . '.' . get_class($this);
    }

    /**
     * Write current table meta data in session
     *
     * @return void
     */
    protected function writeSessionMetaData()
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
    protected function readSessionMetaData()
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
}
