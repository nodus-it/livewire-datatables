<?php

namespace Nodus\Packages\LivewireDatatables\Tests;

use Illuminate\Support\Facades\Lang;
use Nodus\Packages\LivewireDatatables\Tests\Data\Models\User;
use Nodus\Packages\LivewireDatatables\Tests\Data\UserTable;

use function Pest\Livewire\livewire;
use function PHPUnit\Framework\assertTrue;


it('can be rendered', function () {
    livewire(UserTable::class, ['builder' => User::query()])
        ->assertSee('nodus.packages.livewire-datatables::datatable.pagination.count')
        ->assertsee('nodus.packages.livewire-datatables::datatable.scopes.no_filter')
        ->set('simpleScope', 'users.scopes.simple.admins')
        ->assertSee('nodus.packages.livewire-datatables::datatable.table.empty');
});

it('can filter data by simple scopes', function () {
    User::factory()->count(10)->create();
    livewire(UserTable::class, ['builder' => User::query()])
        ->set('simpleScope', 'users.scopes.simple.admins')
        ->assertDontSee(User::where('admin', 0)->first()->email)
        ->assertSee(User::where('admin', 1)->first()->email);
});

it('can search by a string', function () {
    User::factory()->count(10)->create();
    $randomUserForSearch = User::query()->inRandomOrder()->first();
    $randomOtherUser = User::where('id', '!=', $randomUserForSearch->id)->inRandomOrder()->first();
    livewire(UserTable::class, ['builder' => User::query()->withTrashed()])
        ->set('search', $randomUserForSearch->email)
        ->assertDontSee($randomOtherUser->email)
        ->assertSee($randomUserForSearch->email);
});

it('can sort by columns', function () {
    User::factory()->count(10)->create();
    $firstIdUser = User::orderBy('id', 'ASC')->first();
    $lastUser = User::orderBy('email', 'DESC')->first();
    $firstUser = User::orderBy('email', 'ASC')->first();
    livewire(UserTable::class, ['builder' => User::query()])
        ->set('paginate', 1)
        ->assertSee($firstIdUser->email)
        ->call('changeSort', 'users.fields.email')
        ->assertDontSee($lastUser->email)
        ->assertSee($firstUser->email)
        ->call('changeSort', 'users.fields.email')
        ->assertSee($lastUser->email)
        ->assertDontSee($firstUser->email)
        ->call('changeSort', 'email')
        ->assertDontSee($lastUser->email)
        ->assertSee($firstUser->email);
});

it('has translations defined for the supported languages', function () {
    $languages = ['de', 'en'];

    $translationStrings = [
        'nodus.packages.livewire-datatables::datatable.table.actions',
        'nodus.packages.livewire-datatables::datatable.table.empty',
        'nodus.packages.livewire-datatables::datatable.search.placeholder',
        'nodus.packages.livewire-datatables::datatable.pagination.count',
        'nodus.packages.livewire-datatables::datatable.scopes.no_filter',
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

it('saves the search string in the session', function () {
    livewire(UserTable::class, ['builder' => User::query()])
        ->set('search', 'meine suche');
    livewire(UserTable::class, ['builder' => User::query()])
        ->assertViewHas('search', 'meine suche');
});
