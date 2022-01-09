<div id="confirm_modal" class="modal fade">
    <div class="modal-dialog modal-confirm">
        <div class="modal-content">
            <div class="modal-header flex-column">
                <div class="icon-box border-{{$context}}">
                    <span class="text-{{$context}}">✕</span>
                </div>
                <h4 class="modal-title w-100">
                    @lang($title)
                </h4>
                <button type="button" class="close" wire:click="$emitSelf('confirm:close')" aria-hidden="true">&times;</button>
            </div>
            <div class="modal-body">
                <p>@lang($text)</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" wire:click="$emitSelf('confirm:close')">
                    @lang($cancelButton)
                </button>
                <a href="{{$url}}" class="btn btn-{{$context}}">
                    @lang($confirmButton)
                </a>
            </div>
        </div>
    </div>
</div>

@push(config('livewire-core.blade_stacks.scripts'))
    <script @nonce>
        Livewire.on('confirm:client-show', function () {
            $('#confirm_modal').modal('show')
        });
        Livewire.on('confirm:client-close', function () {
            $('#confirm_modal').modal('hide')
        });
    </script>
@endpush