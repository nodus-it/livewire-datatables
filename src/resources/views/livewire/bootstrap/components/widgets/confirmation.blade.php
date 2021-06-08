<div>
    {{-- TODO refactor confirmation modal handling --}}
    <style @if(is_callable(config('livewire-datatables.csp_nonce'))) nonce="{{ config('livewire-datatables.csp_nonce')() }}" @endif>
        .modal-confirm {
            color: var(--gray);
            width: 400px;
        }

        .modal-confirm .modal-content {
            padding: 20px;
            border-radius: 5px;
            border: none;
            text-align: center;
            font-size: 14px;
        }

        .modal-confirm .modal-header {
            border-bottom: none;
            position: relative;
        }

        .modal-confirm h4 {
            text-align: center;
            font-size: 26px;
            margin: 30px 0 -10px;
        }

        .modal-confirm .close {
            position: absolute;
            top: -5px;
            right: -2px;
        }

        .modal-confirm .modal-footer {
            border: none;
            padding-top: 0;
        }

        .modal-confirm .icon-box {
            width: 80px;
            height: 80px;
            margin: 0 auto;
            border-radius: 50%;
            text-align: center;
            border: 3px solid var(--danger);
        }

        .modal-confirm .icon-box span {
            color: var(--danger);
            font-size: 46px;
            display: inline-block;
            margin-top: 2px;
        }
    </style>

    <div id="{{$modal_name}}" class="modal fade">
        <div class="modal-dialog modal-confirm">
            <div class="modal-content">
                <div class="modal-header flex-column">
                    <div class="icon-box">
                        <span>✕</span>
                    </div>
                    <h4 class="modal-title w-100">{{$confirmation['title'] ?? trans('nodus.packages.livewire-datatables::datatable.confirm.title')}}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>
                <div class="modal-body">
                    <p>{{$confirmation['message']}}</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{$confirmation['abort']}}</button>
                    <a href="{{$url}}">
                        <button type="button" class="btn btn-danger">{{$confirmation['confirm']}}</button>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
