<?php

namespace Nodus\Packages\LivewireDatatables\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * Confirm modal class
 *
 * @package Nodus\Packages\LivewireDatatables\Livewire
 */
class ConfirmModal extends Component
{
    /**
     * Visibility state of the modal
     *
     * @var bool
     */
    public bool $isOpen = false;

    /**
     * URL which will be called after confirmation
     *
     * @var string
     */
    public string $url = '';

    /**
     * Confirmation text
     *
     * @var string
     */
    public string $title = '';

    /**
     * Confirmation text
     *
     * @var string
     */
    public string $text = '';

    /**
     * Confirmation button label
     *
     * @var string
     */
    public string $confirmButton = '';

    /**
     * Cancel button label
     *
     * @var string
     */
    public string $cancelButton = '';

    /**
     * CSS styling context
     *
     * @var string
     */
    public string $context = 'danger';

    /**
     * Livewire event listeners
     *
     * @var string[]
     */
    protected $listeners = [
        'confirm:show'  => 'open',
        'confirm:close' => 'close',
    ];

    /**
     * Opens the confirmation modal
     *
     * @param string $url
     * @param array  $options
     *
     * @return void
     */
    public function open(string $url, array $options = []): void
    {
        if ($this->isOpen) {
            $this->close();
        }

        $prefix = 'nodus.packages.livewire-datatables::confirm_modal.';
        $this->isOpen = true;
        $this->url = $url;
        $this->title = $options[ 'title' ] ?? $prefix . 'title';
        $this->text = $options[ 'text' ] ?? $prefix . 'text';
        $this->confirmButton = $options[ 'confirm' ] ?? $prefix . 'button_confirm';
        $this->cancelButton = $options[ 'cancel' ] ?? $prefix . 'button_confirm';

        if (isset($options[ 'context' ])) {
            $this->context = $options[ 'context' ];
        }

        $this->emit('confirm:client-show');
    }

    /**
     * Closes the confirmation modal
     *
     * @return void
     */
    public function close(): void
    {
        $this->isOpen = false;

        $this->emit('confirm:client-close');
    }

    /**
     * Renders the modal
     *
     * @return View
     */
    public function render(): View
    {
        return view('nodus.packages.livewire-datatables::livewire.' . config('livewire-datatables.theme') . '.confirm_modal');
    }

    /**
     * Returns the CSS styles of this component
     *
     * @return string
     */
    public static function styles(): string
    {
        return <<<CSS
        /** Livewire confirm modal styles **/
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
            border-width: 3px;
            border-style: solid;
        }

        .modal-confirm .icon-box span {
            font-size: 46px;
            display: inline-block;
            margin-top: 2px;
        }
CSS;
    }
}
