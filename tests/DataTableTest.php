<?php

namespace Nodus\Packages\LivewireDatatables\Tests;

use Illuminate\Support\Facades\Lang;
use Livewire\Livewire;
use Nodus\Packages\LivewireDatatables\Tests\data\models\User;
use Nodus\Packages\LivewireDatatables\Tests\data\UserTable;

class DataTableTest extends TestCase
{
    public function testBasic()
    {
        Livewire::test(UserTable::class, ['builder' => User::query()])
            ->assertSee('nodus.packages.livewire-datatables::datatable.pagination.count')
            ->assertsee('nodus.packages.livewire-datatables::datatable.scopes.no_filter')
            ->set('simpleScope', 'users.scopes.simple.admins')
            ->assertSee('nodus.packages.livewire-datatables::datatable.table.empty');
    }

    public function testScope()
    {
        User::factory()->count(10)->create();
        Livewire::test(UserTable::class, ['builder' => User::query()])
            ->set('simpleScope', 'users.scopes.simple.admins')
            ->assertDontSee(User::where('admin', 0)->first()->email)
            ->assertSee(User::where('admin', 1)->first()->email);
    }

    public function testSearch()
    {
        User::factory()->count(10)->create();
        $randomUserForSearch = User::query()->inRandomOrder()->first();
        $randomOtherUser = User::where('id', '!=', $randomUserForSearch->id)->inRandomOrder()->first();
        Livewire::test(UserTable::class, ['builder' => User::query()->withTrashed()])
            ->set('search', $randomUserForSearch->email)
            ->assertDontSee($randomOtherUser->email)
            ->assertSee($randomUserForSearch->email);
    }

    public function testSort()
    {
        User::factory()->count(10)->create();
        $firstIdUser = User::orderBy('id', 'ASC')->first();
        $lastUser = User::orderBy('email', 'DESC')->first();
        $firstUser = User::orderBy('email', 'ASC')->first();
        Livewire::test(UserTable::class, ['builder' => User::query()])
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
    }

    public function testTranslations()
    {
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
                $this->assertTrue(
                    Lang::has($translationString),
                    'missing translation string "' . $translationString . '" for "' . $language . '"'
                );
            }
        }
    }

    public function testSessionRead()
    {
        Livewire::test(UserTable::class, ['builder' => User::query()])
            ->set('search', 'meine suche');
        Livewire::test(UserTable::class, ['builder' => User::query()])
            ->assertViewHas('search', 'meine suche');
    }
}
