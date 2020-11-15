<?php

namespace Nodus\Packages\LivewireDatatables\Tests\data\database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Nodus\Packages\LivewireDatatables\Tests\data\models\Post;
use Nodus\Packages\LivewireDatatables\Tests\data\models\User;

class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition()
    {
        return [
            'id'      => $this->faker->numberBetween(1, 99999),
            'title'   => $this->faker->sentence,
            'text'    => $this->faker->text,
            'user_id' => User::factory(),
        ];
    }
}
