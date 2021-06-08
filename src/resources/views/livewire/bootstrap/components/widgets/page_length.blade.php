<div class="nodus-table-pagination-change">
    <select wire:model="paginate" class="custom-select custom-select-sm"
            @if(config('livewire-datatables.csp_nonce') === null) style="max-width: 100px;" @endif>
        <option>10</option>
        <option>25</option>
        <option>50</option>
        <option>100</option>
    </select>
</div>