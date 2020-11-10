<div class="nodus-table-content flex flex-col">
    <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
            <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                    @foreach($columns as $column)
                        <th wire:click="changeSort('{{$column->getIdentifier()}}')" class="px-6 py-3 bg-gray-50 text-left text-xs leading-4
                        font-medium text-gray-500 uppercase tracking-wider">
                            {{$column->getLabel()}}
                            @include('nodus.packages.livewire-datatables::livewire.' . config('livewire-datatables.theme') . '.components.widgets.sorting')
                        </th>
                    @endforeach
                    @if(count($buttons) > 0)
                        <th class="px-6 py-3 bg-gray-50 text-left text-xs leading-4
                        font-medium text-gray-500 uppercase tracking-wider">
                            @lang('nodus.packages.livewire-datatables::datatable.table.actions')
                        </th>
                    @endif
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($results as $result)
                        <tr class="bg-white">
                            @foreach($columns as $column)
                                @if($column->isHtmlEnabled())
                                    <td class="px-6 py-4 whitespace-no-wrap text-sm leading-5 text-cool-gray-900">{!! $column->getValues($result) !!}</td>
                                @else
                                    <td class="px-6 py-4 whitespace-no-wrap text-sm leading-5 text-cool-gray-900">{{ $column->getValues($result)}}</td>
                                @endif
                            @endforeach
                            @include('nodus.packages.livewire-datatables::livewire.' . config('livewire-datatables.theme') . '.components.widgets.buttons',['item'=>$result])
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
