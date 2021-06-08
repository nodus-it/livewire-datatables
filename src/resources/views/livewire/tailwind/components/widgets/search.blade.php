<div class="nodus-table-search">
    <input wire:model.debounce.300ms="search"
           class="mt-1 form-input block w-full transition duration-150 ease-in-out sm:text-sm sm:leading-5"
           placeholder="@lang('nodus.packages.livewire-datatables::datatable.search.placeholder')"
           @if(config('livewire-datatables.csp_nonce') === null) style="max-width: 300px;" @endif
           type="text">
</div>
