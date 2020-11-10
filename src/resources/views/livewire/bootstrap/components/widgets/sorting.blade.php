<span class="@if($sort !== $column->getId()) invisible @endif">
    {{-- Todo better default icons --}}
    @if($sortDirection === 'ASC')
        ↓
    @else
        ↑
    @endif
</span>