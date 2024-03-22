@if($show->search === true)
<div class="nodus-table-search">
    <input wire:model.live.debounce.300ms="search"
           class="form-control form-control-sm ml-auto"
           placeholder="@lang('nodus.packages.livewire-datatables::datatable.search.placeholder')"
           type="text">
</div>
@endif