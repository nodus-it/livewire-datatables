<?php

namespace Nodus\Packages\LivewireDatatables\Tests;

use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Livewire\LivewireServiceProvider;
use Nodus\Packages\LivewireDatatables\LivewireDatatablesServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    use RefreshDatabase;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadFactoriesUsing(
            $this->app,
            __DIR__ . '/Data/Database/Factories'
        );

        /**
         * Fake Routes
         */
        Route::get('user/{id}', fn ($id) => 'users.show: ' . $id)
            ->name('users.show');
        Route::get('post/{id}', fn ($id) => 'posts.show: ' . $id)
            ->name('posts.show');
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/Data/Database/Migrations');
    }

    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            LivewireDatatablesServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        $app['config']->set('app.key', 'AckfSECXIvnK5r28GVIWUAxmbBSjTsmF');
        $app['config']->set('app.locale', 'ab'); // Fake for getting translation strings
        $app['config']->set('app.fallback_locale', 'ab'); // Fake for getting translation strings
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }
}
