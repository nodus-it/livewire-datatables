<?php

use Illuminate\Support\Facades\Lang;
use Nodus\Packages\LivewireDatatables\Livewire\ConfirmModal;

use function Pest\Livewire\livewire;
use function PHPUnit\Framework\assertIsString;
use function PHPUnit\Framework\assertStringContainsString;
use function PHPUnit\Framework\assertTrue;

it('renders successfully', function () {
    livewire(ConfirmModal::class)
        ->assertSet('isOpen', false)
        ->assertSeeHtml('<div class="modal-dialog modal-confirm">');
});

it('can open', function () {
    livewire(ConfirmModal::class)
        ->dispatch('confirm:show', 'delete/url', ['context' => 'warning'])
        ->assertSet('url', 'delete/url')
        ->assertSet('context', 'warning')
        ->assertSet('isOpen', true);
});

it('can close automatically', function () {
    livewire(ConfirmModal::class)
        ->dispatch('confirm:show', 'delete/url/1', ['context' => 'warning'])
        ->assertSet('url', 'delete/url/1')
        ->assertSet('isOpen', true)
        ->dispatch('confirm:show', 'delete/url/2', ['context' => 'warning'])
        ->assertDispatched('confirm:client-close')
        ->assertSet('url', 'delete/url/2')
        ->assertSet('isOpen', true);
});

it('can close', function () {
    livewire(ConfirmModal::class)
        ->dispatch('confirm:show', 'delete/url/1', ['context' => 'warning'])
        ->assertSet('url', 'delete/url/1')
        ->assertSet('isOpen', true)
        ->dispatch('confirm:close')
        ->assertDispatched('confirm:client-close')
        ->assertSet('isOpen', false);
});

it('has custom styles', function () {
    $styles = ConfirmModal::styles();

    assertIsString($styles);
    assertStringContainsString('.modal-confirm', $styles);
});

it('has translations defined for the supported languages', function () {
    $languages = ['de', 'en'];

    $translationStrings = [
        'nodus.packages.livewire-datatables::confirm_modal.button_confirm',
        'nodus.packages.livewire-datatables::confirm_modal.button_cancel',
        'nodus.packages.livewire-datatables::confirm_modal.title',
        'nodus.packages.livewire-datatables::confirm_modal.text',
    ];

    foreach ($languages as $language) {
        Lang::setLocale($language);
        foreach ($translationStrings as $translationString) {
            assertTrue(
                Lang::has($translationString),
                'missing translation string "' . $translationString . '" for "' . $language . '"'
            );
        }
    }
});
