<?php

namespace Nodus\Packages\LivewireDatatables\Tests;

use Nodus\Packages\LivewireDatatables\LivewireComponent;

class LivewireTraitTest extends TestCase
{
    public function testLivewireDefault()
    {
        $mock = $this->getMockBuilder('Nodus\Packages\LivewireDatatables\Livewire')->getMockForTrait();
        $this->assertInstanceOf(LivewireComponent::class, $mock->livewire('component-name', ['parameter1' => 'a']));
    }

    public function testLivewireWithDefaultLayout()
    {
        $mock = $this->getMockBuilder('Nodus\Packages\LivewireDatatables\Livewire')->getMockForTrait();
        $mock->defaultLayout = 'my-layout';
        $livewire = $mock->livewire('component-name', ['parameter1' => 'a']);
        $this->assertInstanceOf(LivewireComponent::class, $livewire);
        $this->assertEquals('my-layout', $livewire->render()->getData()[ 'livewire__layout' ]);
    }

    public function testLivewireWithDefaultSection()
    {
        $mock = $this->getMockBuilder('Nodus\Packages\LivewireDatatables\Livewire')->getMockForTrait();
        $mock->defaultSection = 'my-section';
        $livewire = $mock->livewire('component-name', ['parameter1' => 'a']);
        $this->assertInstanceOf(LivewireComponent::class, $livewire);
        $this->assertEquals('my-section', $livewire->render()->getData()[ 'livewire__section' ]);
    }
}
