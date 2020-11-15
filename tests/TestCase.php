<?php

namespace Nodus\Packages\LivewireDatatables\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Livewire\LivewireServiceProvider;
use Nodus\Packages\LivewireDatatables\LivewireDatatablesServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/data/database/migrations');
        $this->withFactories(__DIR__ . '/data/database/factories');

        /**
         * Fake Routes
         */
        Route::get(
            'user/{id}',
            function ($id) {
                return 'user.detais:' . $id;
            }
        )->name('users.details');
    }

    protected function getPackageProviders($app)
    {
        return [
            LivewireServiceProvider::class,
            LivewireDatatablesServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        $app[ 'config' ]->set('app.key', 'AckfSECXIvnK5r28GVIWUAxmbBSjTsmF');
        $app[ 'config' ]->set('app.locale', 'ab'); // Fake for getting translation strings
        $app[ 'config' ]->set('app.fallback_locale', 'ab'); // Fake for getting translation strings
        $app[ 'config' ]->set('database.default', 'sqlite');
        $app[ 'config' ]->set(
            'database.connections.sqlite',
            [
                'driver'   => 'sqlite',
                'database' => ':memory:',
                'prefix'   => '',
            ]
        );
    }
}
