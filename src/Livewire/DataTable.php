<?php

namespace Nodus\Packages\LivewireDatatables\Livewire;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * DataTable Class
 *
 * @package Nodus\Packages\LivewireDatatables\Livewire
 */
abstract class DataTable extends BaseDataTable
{
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
     * Temporary Builder
     *
     * @var Builder|\Illuminate\Database\Query\Builder|null
     */
    protected $builder = null;

    /**
     * On mount handler
     *
     * @param Builder     $builder
     * @param string|null $sessionKeySuffix
     *
     * @return void
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
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
            $builder = $this->simpleScopes[$this->simpleScope]->addScope($builder);
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
                    $searchTokens = $this->getSearchTokens();

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
                                    function (Builder $b) use ($relationSearchKey, $searchTokens) {
                                        foreach ($searchTokens as $token) {
                                            $b->where($relationSearchKey, 'LIKE', '%' . $token . '%');
                                        }
                                    }
                                );
                            } else {
                                // Base table search
                                foreach ($searchTokens as $token) {
                                    $builder->orWhere($searchKey, 'LIKE', '%' . $token . '%');
                                }
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
        if (!isset($this->columns[$this->sort])) {
            return $builder->orderBy($this->sort, $this->sortDirection);
        }

        // if the sort key matches a column than use the columns sort keys
        foreach ($this->columns[$this->sort]->getSortKeys() as $sort) {
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
}
