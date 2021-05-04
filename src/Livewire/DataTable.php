<?php

namespace Nodus\Packages\LivewireDatatables\Livewire;

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

/**
 * DataTable Class
 *
 * @package Nodus\Packages\LivewireDatatables\Livewire
 */
abstract class DataTable extends Component
{
    use WithPagination;
    use SupportsAdditionalViewParameters;

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

    public array $resultWith = [];

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
     * @var Builder|null
     */
    private $builder = null;

    public function __construct($id = null)
    {
        $this->paginationTheme = config('livewire-datatables.theme');
        parent::__construct($id);
    }

    /**
     * On mount handler
     *
     * @param       $builder
     * @param array $additionalViewParameter
     */
    public function mount($builder)
    {
        $this->resultModel = get_class($builder->getModel());
        $this->resultIds = $builder->pluck($this->prefixCol('id'))->toArray();
        $this->resultWith = array_keys($builder->getEagerLoads()) ?? [];

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

        $this->applyScopes($builder);
        $this->applySearch($builder);
        $this->applySort($builder);

        $this->writeSessionMetaData();

        return view(
            'nodus.packages.livewire-datatables::livewire.' . config('livewire-datatables.theme') . '.datatable',
            [
                'results'      => $builder->paginate($this->paginate)->onEachSide($this->paginateOnEachSide),
                'columns'      => $this->columns,
                'simpleScopes' => $this->simpleScopes,
                'buttons'      => $this->buttons,
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

            return $model->whereIn($model->getModel()->getTable() . '.id', $this->resultIds)->with($this->resultWith);
        }

        return $this->builder;
    }

    /**
     * Generate a default translation string, based on model and column name
     *
     * @param string $lang Column name
     *
     * @return string
     */
    protected function getTranslationStringByModel(string $lang)
    {
        return Str::plural(Str::snake(Str::afterLast($this->resultModel, '\\'))) . '.' . $lang;
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
     * @param string|array|\Closure $values Values for column
     * @param string|null           $label  Label for column
     *
     * @return Column
     * @throws Exception
     */
    protected function addColumn($values, string $label = null)
    {
        if ($label == null) {
            if ($values instanceof \Closure) {
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
     * Write current table meta data in session
     */
    private function writeSessionMetaData()
    {
        $meta[ 'paginate' ] = $this->paginate;
        $meta[ 'sort' ] = $this->sort;
        $meta[ 'sortDirection' ] = $this->sortDirection;
        $meta[ 'simpleScope' ] = $this->simpleScope;
        $meta[ 'search' ] = $this->search;
        session()->put(self::SESSION_KEY_META_DATA . '.' . get_class($this), $meta);
    }

    /**
     * Read recent table meta data from session
     */
    private function readSessionMetaData()
    {
        if (session()->exists(self::SESSION_KEY_META_DATA . '.' . get_class($this))) {
            $meta = session()->get(self::SESSION_KEY_META_DATA . '.' . get_class($this));
            $this->paginate = $meta[ 'paginate' ];
            $this->sort = $meta[ 'sort' ];
            $this->sortDirection = $meta[ 'sortDirection' ];
            $this->simpleScope = $meta[ 'simpleScope' ];
            $this->search = $meta[ 'search' ];
        }
    }
}
