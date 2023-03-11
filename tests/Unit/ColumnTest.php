<?php

use Carbon\Carbon;
use Nodus\Packages\LivewireDatatables\Services\Column;
use Nodus\Packages\LivewireDatatables\Tests\Data\Models\Post;
use Nodus\Packages\LivewireDatatables\Tests\Data\Models\User;

use function PHPUnit\Framework\assertEmpty;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertInstanceOf;
use function PHPUnit\Framework\assertStringContainsString;
use function PHPUnit\Framework\assertTrue;

it('can be initialized', function () {
    $user = new User();
    $user->name = 'Bastian';
    $column = new Column('name', 'label');
    assertEquals('label', $column->getId());
    assertEquals('label', $column->getLabel());
    assertEquals('Bastian', $column->getValues($user));
    assertEquals(['name'], $column->getSearchKeys());
    assertEquals(['name'], $column->getSortKeys());
});

it('has a fallback for not found values', function () {
    $user = new User();
    $user->name = 'Bastian';
    $column = new Column('email', 'label');
    assertEquals('', $column->getValues($user));
    $column = new Column('unknownRelation.value', 'label2');
    assertEquals('', $column->getValues($user));
});

it('works for methods', function () {
    $model = new User();
    $column = new Column('methodCall', 'label');
    assertEquals('methodCallResult', $column->getValues($model));
    $column = new Column('post.x', '');
    assertEmpty($column->getValues($model));
});

it('works with relations', function () {
    $post = Post::factory()->create();
    $column = new Column('user.first_name', 'label');
    assertEquals($post->user->first_name, $column->getValues($post));
});

it('works with multiple values', function () {
    $user = new User();
    $user->first_name = 'Bastian';
    $user->last_name = 'Schur';
    $column = new Column(['first_name', 'last_name'], 'label');
    assertEquals('Bastian Schur', $column->getValues($user));
});

it('works with closures', function () {
    $user = new User();
    $user->first_name = 'Bastian';
    $column = new Column(fn($user) => $user->first_name . '-extension', 'label');
    assertEquals('Bastian-extension', $column->getValues($user));
});

it('can have custom sort keys', function () {
    $column = new Column('name', 'label');
    assertInstanceOf(Column::class, $column->setSortKeys(['fist_name', 'last_name']));
    assertEquals(['fist_name', 'last_name'], $column->getSortKeys());
});

it('can have custom search keys', function () {
    $column = new Column('name', 'label');
    assertInstanceOf(Column::class, $column->setSearchKeys(['fist_name', 'last_name']));
    assertEquals(['fist_name', 'last_name'], $column->getSearchKeys());
});

it('can enable HTML rendering', function () {
    $column = new Column('name', 'label');
    assertInstanceOf(Column::class, $column->enableHtml());
    assertTrue($column->isHtmlEnabled());
    assertInstanceOf(Column::class, $column->enableHtml(false));
    assertFalse($column->isHtmlEnabled());
});

it('supports responsive breakpoints', function () {
    $column = new Column('name', 'label');
    assertEmpty($column->getClasses());
    assertInstanceOf(Column::class, $column->setBreakpoint(500));
    assertEquals('breakpoint-500', $column->getClasses());
});

it('supports the date datatype', function () {
    $user = new User();
    $user->name = '2020-10-01';
    $column = new Column('name', 'label');
    assertInstanceOf(Column::class, $column->setDataTypeDate());
    Carbon::setLocale('en');
    assertEquals('10/01/2020', $column->getValues($user));
    Carbon::setLocale('de');
    assertEquals('01.10.2020', $column->getValues($user));
    $user->name = null;
    assertEquals('-', $column->getValues($user));
});

it('supports the datetime datatype', function () {
    $user = new User();
    $user->name = '2020-10-01 15:07:23';
    $column = new Column('name', 'label');
    assertInstanceOf(Column::class, $column->setDataTypeDateTime());
    Carbon::setLocale('en');
    assertEquals('10/01/2020 3:07:23 PM', $column->getValues($user));
    Carbon::setLocale('de');
    assertEquals('01.10.2020 15:07:23', $column->getValues($user));
    $user->name = null;
    assertEquals('-', $column->getValues($user));
});

it('supports the time datatype', function () {
    $user = new User();
    $user->name = '15:07:23';
    $column = new Column('name', 'label');
    assertInstanceOf(Column::class, $column->setDataTypeTime());
    Carbon::setLocale('en');
    assertEquals('3:07:23 PM', $column->getValues($user));
    Carbon::setLocale('de');
    assertEquals('15:07:23', $column->getValues($user));
    $user->name = null;
    assertEquals('-', $column->getValues($user));
});

it('supports the bool datatype', function () {
    $user = new User();
    $user->admin = true;
    $column = new Column('admin', 'label');
    assertInstanceOf(Column::class, $column->setDataTypeBool());
    assertStringContainsString('<span class="text-success">✓</span>', $column->getValues($user));
    $user->admin = false;
    assertStringContainsString('<span class="text-danger">✕</span>', $column->getValues($user));
});

it('supports custom datatypes', function () {
    Column::addCustomDataType('upper', fn($var) => strtoupper($var));

    $user = new User();
    $user->name = 'username';

    $column = new Column('name', 'label');
    assertInstanceOf(Column::class, $column->setDataTypeUpper());
    assertEquals('USERNAME', $column->getValues($user));
});

it('throws an exception for invalid custom datatypes', function () {
    $column = new Column('name', 'label');
    $column->setDataTypeNotAvailable();
})->throws(Exception::class, 'Custom datatype "notavailable" not found!');
