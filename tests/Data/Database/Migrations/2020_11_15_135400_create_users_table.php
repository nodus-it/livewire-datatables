<?php

namespace Nodus\Packages\LivewireDatatables\Tests\Data\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create(
            'users',
            function (Blueprint $table) {
                $table->id();
                $table->timestamps();
                $table->softDeletes();
                $table->string('first_name', 64);
                $table->string('last_name', 16);
                $table->string('email');
                $table->boolean('admin');
            }
        );
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
};
