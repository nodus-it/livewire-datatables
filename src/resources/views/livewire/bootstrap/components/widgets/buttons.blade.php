@if(count($buttons) > 0)
    <td class="py-2">
        @foreach($buttons as $button)
            <a target="{{$button->getTarget()}}" class="btn btn-sm btn-primary" href="{{$button->getRoute($item)}}">
                {{$button->getLabel()}}
            </a>
        @endforeach
    </td>
@endif
