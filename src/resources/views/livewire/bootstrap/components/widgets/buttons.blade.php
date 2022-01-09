@if(count($buttons) > 0)
    <td class="align-middle py-2">
        <div class="btn-group" role="group">
            @foreach($buttons as $button)
                @if(!$button->isAllowedToRender($item))
                    @continue
                @endif

                @if($button->isConfirmationButton())
                    <button wire:click="$emit('confirm:show', '{{$button->getRoute($item)}}', @js($button->getConfirmation()))"
                            class="trigger-btn {{$button->getClasses() ?? 'btn-sm btn-primary'}}"
                            data-toggle="modal">
                        @include($themePath . '.components.widgets.button_content')
                    </button>
                @else
                    <a target="{{$button->getTarget()}}"
                       class="btn {{$button->getClasses() ?? 'btn-sm btn-primary'}}"
                       href="{{$button->getRoute($item)}}">
                        @include($themePath . '.components.widgets.button_content')
                    </a>
                @endif
            @endforeach
        </div>
    </td>
@endif
