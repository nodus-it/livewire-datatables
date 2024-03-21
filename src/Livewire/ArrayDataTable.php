<?php

namespace Nodus\Packages\LivewireDatatables\Livewire;

use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * ArrayDataTable Class
 *
 * @package Nodus\Packages\LivewireDatatables\Livewire
 */
abstract class ArrayDataTable extends BaseDataTable
{
    /**
     * Raw data array
     *
     * @var array
     */
    public array $rawData = [];

    /**
     * On mount handler
     *
     * @param Collection|array $data
     * @param string|null      $sessionKeySuffix
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function mount(Collection|array $data, ?string $sessionKeySuffix = null): void
    {
        $this->setRawData($data);
        $this->sessionKeySuffix = $sessionKeySuffix;

        $this->readSessionMetaData();
    }

    public function setRawData(Collection|array $data): static
    {
        if ($data instanceof Collection) {
            $data = $data->toArray();
        }

        $this->rawData = $data;

        return $this;
    }

    public function getRawData(): Collection
    {
        return new Collection($this->rawData);
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

        $data = $this->getRawData();
        $themePath = $this->getThemePath();

        $data = $this->applyScopes($data);
        $data = $this->applySearch($data);
        $data = $this->applySort($data);
        $paginator = $this->applyPagination($data);

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
     * @param Collection $data
     *
     * @return Collection
     */
    protected function applyScopes($data)
    {
        // todo

        return $data;
    }

    /**
     * Sets the where's for selected search
     *
     * @param Collection $data
     *
     * @return Collection
     */
    protected function applySearch($data)
    {
        if (!empty($this->search)) {
           // $searchTokens = $this->getSearchTokens();
            $token = trim($this->search);

            $data = $data->filter(function ($item) use ($token) {
                $show = false;

                foreach ($this->getColumns() as $column) {
                    if (empty($column->getSearchKeys())) {
                        continue;
                    }

                    $keys = $column->getSearchKeys();

                    foreach ($keys as $key) {
                        //dump($item[$key], $token, Str::contains($item[$key], $token));
                        if (Str::contains($item[$key], $token)) {
                            $show = true;
                            break 2;
                        }
                    }
                }

                return $show;
            });

        }

        return $data;
    }

    /**
     * Setts the orderBy for selected column
     *
     * @param Collection $data
     *
     * @return Collection
     */
    protected function applySort($data)
    {
        if (!empty($this->sort)) {
            $data = $data->sort(function ($a, $b) {
                $dir = $this->sortDirection === 'ASC' ? 1 : -1;

                return $dir * ($a[$this->sort] <=> $b[$this->sort]);
            });
        }

        return $data;
    }

    /**
     * Sets the limit and the offset
     *
     * @param Collection $data
     *
     * @return LengthAwarePaginator
     */
    protected function applyPagination($data)
    {
        $count = count($data);
        $paginator = new LengthAwarePaginator([], $count, $this->paginate, $this->page);
        $pageData = $data->slice(($this->page - 1) * $this->paginate, $this->paginate);

        return $paginator
            ->setCollection($pageData)
            ->onEachSide($this->paginateOnEachSide);
    }
}
