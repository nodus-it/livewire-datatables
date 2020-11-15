<?php


namespace Nodus\Packages\LivewireDatatables\Tests\data\models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Nodus\Packages\LivewireDatatables\Tests\data\database\factories\PostFactory;

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
