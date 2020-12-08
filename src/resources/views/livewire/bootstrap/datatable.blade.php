<div class="nodus-table">
    <div class="row">
        <div class="col-6">
            @include('nodus.packages.livewire-datatables::livewire.' . config('livewire-datatables.theme') . '.components.widgets.scopes')
        </div>
        <div class="col-6">
            @include('nodus.packages.livewire-datatables::livewire.' . config('livewire-datatables.theme') . '.components.widgets.search')
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            @include('nodus.packages.livewire-datatables::livewire.' . config('livewire-datatables.theme') . '.components.widgets.table')
        </div>
    </div>
    <div class="row">
        <div class="col-lg-4 order-lg-1 col-7">
            @include('nodus.packages.livewire-datatables::livewire.' . config('livewire-datatables.theme') . '.components.widgets.counter')
        </div>
        <div class="col-lg-4 col-12 order-first order-lg-2 text-center">
            <div class="nodus-table-pagination d-inline-block">
                {{$results->links()}}
            </div>
        </div>
        <div class="col-lg-4 col-5 order-last text-right">
            @include('nodus.packages.livewire-datatables::livewire.' . config('livewire-datatables.theme') . '.components.widgets.page_length')
        </div>
    </div>
</div>
