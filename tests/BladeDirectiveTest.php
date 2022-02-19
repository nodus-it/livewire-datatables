<?php

namespace Nodus\Packages\LivewireDatatables\Tests;

use Illuminate\View\Compilers\BladeCompiler;

class BladeDirectiveTest extends TestCase
{
    public function testLivewireDatatableStylesBladeDirective()
    {
        $compiler = $this->app->get(BladeCompiler::class);
        $this->assertStringContainsString('</style>', $compiler->compileString('@livewireDatatableStyles'));
    }
}
