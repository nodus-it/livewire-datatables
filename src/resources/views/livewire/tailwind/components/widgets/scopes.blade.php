@if(count($simpleScopes) > 0)
    <div class="nodus-table-simple-scopes col-span-6 sm:col-span-3">
        <select wire:model="simpleScope" class="mt-1 block form-select w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:shadow-outline-blue focus:border-blue-300 transition duration-150 ease-in-out sm:text-sm sm:leading-5">
            <option value="">@lang('nodus.packages.livewire-datatables::datatable.scopes.no_filter')</option>
            @foreach($simpleScopes as $scope)
                <option value="{{$scope->getIdentifier()}}">{{$scope->getLabel()}}</option>
            @endforeach
        </select>
    </div>
@endif
