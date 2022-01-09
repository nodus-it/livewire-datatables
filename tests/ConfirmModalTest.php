<?php

namespace Nodus\Packages\LivewireDatatables\Tests;

use Illuminate\Support\Facades\Lang;
use Livewire\Livewire;
use Nodus\Packages\LivewireDatatables\Livewire\ConfirmModal;

class ConfirmModalTest extends TestCase
{
    public function testBasic()
    {
        Livewire::test(ConfirmModal::class)
            ->assertSet('isOpen', false)
            ->assertSeeHtml('<div class="modal-dialog modal-confirm">');
    }

    public function testOpen()
    {
        Livewire::test(ConfirmModal::class)
            ->emit('confirm:show', 'delete/url', ['context' => 'warning'])
            ->assertSet('url', 'delete/url')
            ->assertSet('context', 'warning')
            ->assertSet('isOpen', true);
    }

    public function testAutoClose()
    {
        Livewire::test(ConfirmModal::class)
            ->emit('confirm:show', 'delete/url/1', ['context' => 'warning'])
            ->assertSet('url', 'delete/url/1')
            ->assertSet('isOpen', true)
            ->emit('confirm:show', 'delete/url/2', ['context' => 'warning'])
            ->assertEmitted('confirm:client-close')
            ->assertSet('url', 'delete/url/2')
            ->assertSet('isOpen', true);
    }

    public function testClose() {
        Livewire::test(ConfirmModal::class)
            ->emit('confirm:show', 'delete/url/1', ['context' => 'warning'])
            ->assertSet('url', 'delete/url/1')
            ->assertSet('isOpen', true)
            ->emit('confirm:close')
            ->assertEmitted('confirm:client-close')
            ->assertSet('isOpen', false);
    }

    public function testStyles()
    {
        $styles = ConfirmModal::styles();

        $this->assertIsString($styles);
        $this->assertStringContainsString('.modal-confirm', $styles);
    }

    public function testTranslations()
    {
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
                $this->assertTrue(
                    Lang::has($translationString),
                    'missing translation string "' . $translationString . '" for "' . $language . '"'
                );
            }
        }
    }
}
