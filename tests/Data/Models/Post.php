<?php

namespace Nodus\Packages\LivewireDatatables\Tests\Data\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Nodus\Packages\LivewireDatatables\Tests\Data\database\Factories\PostFactory;

class Post extends Model
{
    use HasFactory;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function newFactory()
    {
        return PostFactory::new();
    }
}
