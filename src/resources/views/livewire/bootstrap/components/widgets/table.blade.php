<div class="nodus-table-content my-2">
    <table class="table table-striped ">
        <thead>
            <tr>
            @foreach($columns as $column)
                <th class="border-top-0 {{$column->getClasses()}}" role="button" wire:click="changeSort('{{$column->getId()}}')">
                    {{$column->getLabel()}}
                    @include($themePath . '.components.widgets.sorting')
                </th>
            @endforeach
            @if(count($buttons) > 0)
                <th class="border-top-0" role="button">
                    @lang('nodus.packages.livewire-datatables::datatable.table.actions')
                </th>
            @endif
            </tr>
        </thead>
        <tbody>
        @foreach($results as $result)
            <tr>
                @foreach($columns as $column)
                    @if($column->isHtmlEnabled())
                        <td class="align-middle {{$column->getClasses()}}">{!! $column->getValues($result) !!}</td>
                    @else
                        <td class="align-middle {{$column->getClasses()}}">{{ $column->getValues($result)}}</td>
                    @endif
                @endforeach
                @include($themePath . '.components.widgets.buttons', ['item' => $result])
            </tr>
        @endforeach
        @if($results->count() === 0)
            <tr>
                <td colspan="{{ count($columns) + (count($buttons) > 0) }}">
                    @lang('nodus.packages.livewire-datatables::datatable.table.empty')
                </td>
            </tr>
        @endif
        </tbody>
    </table>
</div>
