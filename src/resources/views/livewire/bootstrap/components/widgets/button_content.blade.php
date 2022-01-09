@if($button->getRenderMode() == \Nodus\Packages\LivewireDatatables\Services\Button::RENDER_MODE_ICON)
    <i class="{{$button->getIcon()}}" title="{{$button->getLabel()}}"></i>
@elseif($button->getRenderMode() == \Nodus\Packages\LivewireDatatables\Services\Button::RENDER_MODE_ICON_LABEL)
    <i class="{{$button->getIcon()}}"></i> {{$button->getLabel()}}
@else
    {{$button->getLabel()}}
@endif