<?php


namespace Nodus\Packages\LivewireDatatables\Tests\data;


use Nodus\Packages\LivewireDatatables\Livewire\DataTable;

class UserTable extends DataTable
{

    protected function columns()
    {
        $this->addColumn('first_name');
        $this->addColumn('last_name');
        $this->addColumn('email');
        $this->addColumn('admin')->setDataTypeBool();
    }

    protected function scopes()
    {
        $this->addSimpleScope('admins');
    }

    protected function buttons()
    {
        $this->addButton('details-button', 'users.details', ['id' => ':id']);
    }
}
