<div class="nodus-table-counter">
    @lang('nodus.packages.livewire-datatables::datatable.pagination.count',[
        'first_item'=>$results->firstItem() ?? 0,
        'last_item'=>$results->lastItem() ?? 0,
        'total_items'=>$results->total()
    ])
</div>