<div class="nodus-table-search">
    <input wire:model.debounce.300ms="search"
           class="form-control form-control-sm ml-auto"
           placeholder="@lang('nodus.packages.livewire-datatables::datatable.search.placeholder')"
           @if(config('livewire-datatables.csp_nonce') === null) style="max-width: 300px;" @endif
           type="text">
</div>