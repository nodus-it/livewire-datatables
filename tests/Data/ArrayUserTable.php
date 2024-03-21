<?php

namespace Nodus\Packages\LivewireDatatables\Tests\Data;

use Nodus\Packages\LivewireDatatables\Livewire\ArrayDataTable;
use Nodus\Packages\LivewireDatatables\Livewire\DataTable;

class ArrayUserTable extends ArrayDataTable
{
    protected function columns()
    {
        $this->addColumn('first_name')->setSearchKeys('first_name');
        $this->addColumn('last_name');
        $this->addColumn('email');
        //$this->addColumn('latestPost.title');
        $this->addColumn(
            function ($user) {
                return $user['first_name'] . '-extension';
            }
        );
        $this->addColumn('admin')->setDataTypeBool();
    }

    protected function scopes()
    {
        //$this->addSimpleScope('admins');
    }

    protected function buttons()
    {
        //$this->addButton('details-button', 'users.show', ['id' => ':id']);
    }
}
