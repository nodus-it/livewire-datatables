<div class="nodus-table">
    <div class="row">
        <div class="col-6">
            @include($themePath . '.components.widgets.scopes')
        </div>
        <div class="col-6">
            @include($themePath . '.components.widgets.search')
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            @include($themePath . '.components.widgets.table')
        </div>
    </div>
    <div class="row">
        <div class="col-lg-3 order-lg-1 col-7">
            @include($themePath . '.components.widgets.counter')
        </div>
        <div class="col-lg-6 col-12 order-first order-lg-2 text-center">
            <div class="nodus-table-pagination d-inline-block" wire:loading.class="nodus-table-disabled">
                @if($show->pagination === true)
                    {{$results->links()}}
                @endif
            </div>
        </div>
        <div class="col-lg-3 col-5 order-last text-right">
            @include($themePath . '.components.widgets.page_length')
        </div>
    </div>
</div>
