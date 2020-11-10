<?php

namespace Nodus\Packages\LivewireDatatables;

use Illuminate\Support\ServiceProvider;

class LivewireDatatablesServiceProvider extends ServiceProvider
{
    private string $packageNamespace = 'nodus.packages.livewire-datatables';

    private string $resourcesPath = __DIR__ . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR;

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/livewire-datatables.php', 'livewire-datatables');
    }

    public function boot()
    {
        $this->loadTranslationsFrom($this->resourcesPath . 'lang', $this->packageNamespace);
        $this->loadViewsFrom($this->resourcesPath . 'views', $this->packageNamespace);
    }
}
