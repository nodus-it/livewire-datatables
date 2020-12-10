@if(count($buttons) > 0)
    <td class="py-2">
        <div class="btn-group" role="group">
            @foreach($buttons as $button)
                @if(!empty($button->getConfirmation()))
                    <a href="#{{\Illuminate\Support\Str::slug($button->getLabel().'-'.$item->id)}}" class="trigger-btn {{$button->getClasses() ?? 'btn-sm
                    btn-primary'}}"
                       data-toggle="modal">
                        @else
                            <a target="{{$button->getTarget()}}" class="btn {{$button->getClasses() ?? 'btn-sm btn-primary'}}"
                               href="{{$button->getRoute($item)}}">
                                @endif
                                @if($button->getRenderMode() == \Nodus\Packages\LivewireDatatables\Services\Button::RENDER_MODE_ICON)
                                    <i class="{{$button->getIcon()}}" title="{{$button->getLabel()}}"></i>
                                @elseif($button->getRenderMode() == \Nodus\Packages\LivewireDatatables\Services\Button::RENDER_MODE_ICON_LABEL)
                                    <i class="{{$button->getIcon()}}"></i> {{$button->getLabel()}}
                                @else
                                    {{$button->getLabel()}}
                                @endif
                            </a>
                    @endforeach
        </div>
    </td>
    @foreach($buttons as $button)
        @if(!empty($button->getConfirmation()))
            @include('nodus.packages.livewire-datatables::livewire.' . config('livewire-datatables.theme') .
    '.components.widgets.confirmation',['confirmation'=>$button->getConfirmation(),'url'=>$button->getRoute($item),
    'modal_name'=>\Illuminate\Support\Str::slug($button->getLabel().'-'.$item->id)])
        @endif
    @endforeach
@endif
