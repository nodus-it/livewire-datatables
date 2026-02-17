<?php

namespace Nodus\Packages\LivewireDatatables;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Nodus\Packages\LivewireDatatables\Livewire\ConfirmModal;
use Nodus\Packages\LivewireCore\Services\SupportsComponentAssets;
use Nodus\Packages\LivewireDatatables\Livewire\DataTable;

class LivewireDatatablesServiceProvider extends ServiceProvider
{
    use SupportsComponentAssets;

    private string $packageNamespace = 'nodus.packages.livewire-datatables';

    private string $resourcesPath = __DIR__ . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR;

    private array $styledComponents = [
        DataTable::class,
        ConfirmModal::class,
    ];

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config/livewire-datatables.php', 'livewire-datatables');
    }

    public function boot()
    {
        $this->loadTranslationsFrom($this->resourcesPath . 'lang', $this->packageNamespace);
        $this->loadViewsFrom($this->resourcesPath . 'views', $this->packageNamespace);

        $this->registerComponents();
        $this->registerVendorPublishes();
        $this->registerBladeDirectives();
    }

    private function registerComponents()
    {
        Livewire::component('livewire-datatables.confirm-modal', ConfirmModal::class);
    }

    private function registerVendorPublishes()
    {
        $this->publishes([__DIR__ . '/config/livewire-datatables.php' => config_path('livewire-datatables.php')], 'livewire-datatables:config');
        $this->publishes([__DIR__ . '/resources/views' => resource_path('views/vendor/' . $this->packageNamespace)], 'livewire-datatables:views');
    }

    private function registerBladeDirectives()
    {
        Blade::directive('livewireDatatableStyles', function () {
            $stylesPhp = var_export($this->styles(), true);

            return "<?php echo '<style '"
                   . " . app('Nodus\\\\Packages\\\\LivewireCore\\\\Services\\\\CspNonce')->toHtml()"
                   . " . \">\\n\" . {$stylesPhp} . \"\\n</style>\"; ?>";
        });
    }
}
