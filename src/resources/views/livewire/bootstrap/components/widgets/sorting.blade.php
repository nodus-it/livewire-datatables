<span class="@if($sort !== $column->getId()) invisible @endif">
    @if($sortDirection === 'ASC')
        ↓
    @else
        ↑
    @endif
</span>