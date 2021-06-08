@if(count($simpleScopes) > 0)
    <div class="nodus-table-simple-scopes">
        <select wire:model="simpleScope" class="custom-select custom-select-sm"
                @if(config('livewire-datatables.csp_nonce') === null) style="max-width: 300px;" @endif>
            <option value="">@lang('nodus.packages.livewire-datatables::datatable.scopes.no_filter')</option>
            @foreach($simpleScopes as $scope)
                <option value="{{$scope->getId()}}">{{$scope->getLabel()}}</option>
            @endforeach
        </select>
    </div>
@endif
