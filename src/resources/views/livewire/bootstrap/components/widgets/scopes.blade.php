@if(count($simpleScopes) > 0 && $show->scopes)
    <div class="nodus-table-simple-scopes">
        <select wire:model="simpleScope" class="custom-select custom-select-sm">
            <option value="">
                @lang('nodus.packages.livewire-datatables::datatable.scopes.no_filter')
            </option>
            @foreach($simpleScopes as $scope)
                <option value="{{$scope->getId()}}">
                    {{$scope->getLabel()}}
                </option>
            @endforeach
        </select>
    </div>
@endif
