<?php

use Nodus\Packages\LivewireDatatables\Services\SimpleScope;
use Nodus\Packages\LivewireDatatables\Tests\Data\Models\User;

use function PHPUnit\Framework\assertEquals;

it('can be initialized', function () {
    $scope = new SimpleScope('scope', 'my-scope');
    assertEquals('my-scope', $scope->getId());
    assertEquals('my-scope', $scope->getLabel());
});

it('applies the scope to the builder', function () {
    User::factory()->count(3)->create(['admin' => false]);
    User::factory()->create(['admin' => true]);
    $scope = new SimpleScope('admins', 'my-scope');
    $builder = $scope->addScope(User::query());
    assertEquals(1, $builder->count());
});

it('throws an exception if the scope does not exist', function () {
    $scope = new SimpleScope('noExistingScope', 'my-scope');
    $scope->addScope(User::query());
})->throws(Exception::class);
