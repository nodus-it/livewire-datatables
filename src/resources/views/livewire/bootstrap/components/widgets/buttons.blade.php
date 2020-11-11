@if(count($buttons) > 0)
    <td class="py-2">
        @foreach($buttons as $button)
            <a target="{{$button->getTarget()}}" class="btn btn-sm btn-primary" href="{{$button->getRoute($item)}}">
                @if($button->getRenderMode() == \Nodus\Packages\LivewireDatatables\Services\Button::RENDER_MODE_ICON)
                    <i class="{{$button->getIcon()}}" title="{{$button->getLabel()}}"></i>
                @elseif($button->getRenderMode() == \Nodus\Packages\LivewireDatatables\Services\Button::RENDER_MODE_ICON_LABEL)
                    <i class="{{$button->getIcon()}}"></i> {{$button->getLabel()}}
                @else
                    {{$button->getLabel()}}
                @endif
            </a>
        @endforeach
    </td>
@endif
