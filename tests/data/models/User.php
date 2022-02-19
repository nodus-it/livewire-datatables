<?php

namespace Nodus\Packages\LivewireDatatables\Tests\data\models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Nodus\Packages\LivewireDatatables\Tests\data\database\factories\UserFactory;

class User extends Model
{
    use HasFactory;
    use SoftDeletes;

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function latestPost()
    {
        return $this->hasOne(Post::class)->latest();
    }

    public function methodCall()
    {
        return 'methodCallResult';
    }

    public function scopeAdmins(Builder $builder)
    {
        return $builder->where('admin', 1);
    }

    protected static function newFactory()
    {
        return UserFactory::new();
    }
}
