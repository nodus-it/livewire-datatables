@if($show->counter === true)
<div class="nodus-table-counter">
    <span wire:loading.remove>
        @lang('nodus.packages.livewire-datatables::datatable.pagination.count', [
            'first_item'=>$results->firstItem() ?? 0,
            'last_item'=>$results->lastItem() ?? 0,
            'total_items'=>$results->total()
        ])
    </span>
    <span wire:loading>
        <i class="far fa-fw fa-spinner fa-spin"></i> @lang('nodus.packages.livewire-datatables::datatable.table.loading')
    </span>
</div>
@endif
