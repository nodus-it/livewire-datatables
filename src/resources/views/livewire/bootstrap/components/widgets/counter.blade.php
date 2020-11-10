<div class="nodus-table-counter">
    @lang('nodus.packages.livewire-datatables::datatable.pagination.count',[
        'first_item'=>$results->firstItem(),
        'last_item'=>$results->lastItem(),
        'total_items'=>$results->total()
    ])
</div>