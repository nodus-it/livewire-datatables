<?php

namespace Nodus\Packages\LivewireDatatables\Tests;

use Nodus\Packages\LivewireDatatables\Services\SimpleScope;
use Nodus\Packages\LivewireDatatables\Tests\data\models\User;

class SimpleScopeTest extends TestCase
{
    public function testDefault()
    {
        $scope = new SimpleScope('scope', 'my-scope');
        $this->assertEquals('my-scope', $scope->getId());
        $this->assertEquals('my-scope', $scope->getLabel());
    }

    public function testScopeBuilder()
    {
        User::factory()->count(3)->create(['admin' => false]);
        User::factory()->create(['admin' => true]);
        $scope = new SimpleScope('admins', 'my-scope');
        $builder = $scope->addScope(User::query());
        $this->assertEquals(1, $builder->count());
    }

    public function testScopeNotExists()
    {
        $scope = new SimpleScope('noExistingScope', 'my-scope');
        $this->expectException(\Exception::class);
        $scope->addScope(User::query());
    }
}
