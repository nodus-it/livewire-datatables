<?php

use function PHPUnit\Framework\assertStringContainsString;

it('can resolve the blade directive', function () {
    $compiler = $this->app->get(Illuminate\View\Compilers\BladeCompiler::class);
    assertStringContainsString('</style>', $compiler->compileString('@livewireDatatableStyles'));
});
