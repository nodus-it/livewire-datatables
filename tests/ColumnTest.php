<?php

namespace Nodus\Packages\LivewireDatatables\Tests;

use Carbon\Carbon;
use Nodus\Packages\LivewireDatatables\Services\Column;
use Nodus\Packages\LivewireDatatables\Tests\data\models\Post;
use Nodus\Packages\LivewireDatatables\Tests\data\models\User;

class ColumnTest extends TestCase
{
    public function testDefault()
    {
        $user = new User();
        $user->name = 'Bastian';
        $column = new Column('name', 'label');
        $this->assertEquals('label', $column->getId());
        $this->assertEquals('label', $column->getLabel());
        $this->assertEquals('Bastian', $column->getValues($user));
        $this->assertEquals(['name'], $column->getSearchKeys());
        $this->assertEquals(['name'], $column->getSortKeys());
    }

    public function testValueNotFound()
    {
        $user = new User();
        $user->name = 'Bastian';
        $column = new Column('email', 'label');
        $this->assertEquals('', $column->getValues($user));
    }

    public function testMethodValue()
    {
        $model = new User();
        $column = new Column('methodCall', 'label');
        $this->assertEquals('methodCallResult', $column->getValues($model));
    }

    public function testRelationMethod()
    {
        $post = Post::factory()->create();
        $column = new Column('user.first_name', 'label');
        $this->assertEquals($post->user->first_name, $column->getValues($post));
    }

    public function testMultipleValues()
    {
        $user = new User();
        $user->first_name = 'Bastian';
        $user->last_name = 'Schur';
        $column = new Column(['first_name', 'last_name'], 'label');
        $this->assertEquals('Bastian Schur', $column->getValues($user));
    }

    public function testClosureValue()
    {
        $user = new User();
        $user->first_name = 'Bastian';
        $column = new Column(
            function ($user) {
                return $user->first_name . '-extension';
            },
            'label'
        );
        $this->assertEquals('Bastian-extension', $column->getValues($user));
    }

    public function testChangeSortKey()
    {
        $column = new Column('name', 'label');
        $this->assertInstanceOf(Column::class, $column->setSortKeys(['fist_name', 'last_name']));
        $this->assertEquals(['fist_name', 'last_name'], $column->getSortKeys());
    }

    public function testChangeSerachKey()
    {
        $column = new Column('name', 'label');
        $this->assertInstanceOf(Column::class, $column->setSearchKeys(['fist_name', 'last_name']));
        $this->assertEquals(['fist_name', 'last_name'], $column->getSearchKeys());
    }

    public function testEnablHtml()
    {
        $column = new Column('name', 'label');
        $this->assertInstanceOf(Column::class, $column->enableHtml());
        $this->assertTrue($column->isHtmlEnabled());
        $this->assertInstanceOf(Column::class, $column->enableHtml(false));
        $this->assertFalse($column->isHtmlEnabled());
    }

    public function testBreakpoint()
    {
        $column = new Column('name', 'label');
        $this->assertEmpty($column->getClasses());
        $this->assertInstanceOf(Column::class, $column->setBreakpoint(500));
        $this->assertEquals('breakpoint-500', $column->getClasses());
    }

    public function testDataTypeDate()
    {
        $user = new User();
        $user->name = '2020-10-01';
        $column = new Column('name', 'label');
        $this->assertInstanceOf(Column::class, $column->setDataTypeDate());
        Carbon::setLocale('en');
        $this->assertEquals('10/01/2020', $column->getValues($user));
        Carbon::setLocale('de');
        $this->assertEquals('01.10.2020', $column->getValues($user));
        $user->name = null;
        $this->assertEquals('-', $column->getValues($user));
    }

    public function testDataTypeDateTime()
    {
        $user = new User();
        $user->name = '2020-10-01 15:07:23';
        $column = new Column('name', 'label');
        $this->assertInstanceOf(Column::class, $column->setDataTypeDateTime());
        Carbon::setLocale('en');
        $this->assertEquals('10/01/2020 3:07:23 PM', $column->getValues($user));
        Carbon::setLocale('de');
        $this->assertEquals('01.10.2020 15:07:23', $column->getValues($user));
        $user->name = null;
        $this->assertEquals('-', $column->getValues($user));
    }

    public function testDataTypeTime()
    {
        $user = new User();
        $user->name = '15:07:23';
        $column = new Column('name', 'label');
        $this->assertInstanceOf(Column::class, $column->setDataTypeTime());
        Carbon::setLocale('en');
        $this->assertEquals('3:07:23 PM', $column->getValues($user));
        Carbon::setLocale('de');
        $this->assertEquals('15:07:23', $column->getValues($user));
        $user->name = null;
        $this->assertEquals('-', $column->getValues($user));
    }

    public function testDataTypeBool()
    {
        $user = new User();
        $user->admin = true;
        $column = new Column('admin', 'label');
        $this->assertInstanceOf(Column::class, $column->setDataTypeBool());
        $this->assertStringContainsString('<span class="text-success">✓</span>', $column->getValues($user));
        $user->admin = false;
        $this->assertStringContainsString('<span class="text-danger">✕</span>', $column->getValues($user));
    }

    public function testCustomDataType()
    {
        Column::addCustomDataType(
            'upper',
            function ($var) {
                return strtoupper($var);
            }
        );

        $user = new User();
        $user->name = 'username';
        $column = new Column('name', 'label');
        $this->assertInstanceOf(Column::class, $column->setDataTypeUpper());
        $this->assertEquals('USERNAME', $column->getValues($user));
        $column->setDataTypeNotAvailable();
    }
}
