@if($show->pageLength === true)
<div class="nodus-table-pagination-change">
    <select wire:model.live="paginate" class="custom-select custom-select-sm">
        <option>10</option>
        <option>25</option>
        <option>50</option>
        <option>100</option>
    </select>
</div>
@endif