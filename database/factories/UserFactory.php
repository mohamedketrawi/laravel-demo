<?php

namespace Database\Factories;

use App\Models\Image;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'username' => $this->faker->unique()->name,
            'karma_score' => $this->faker->numberBetween(1, 100000),
            'image_id' => function () {
                return Image::create([
                    'url' => 'https://picsum.photos/200/300?random=' . $this->faker->numberBetween(0, 1000),
                ])->id;
            },
        ];
    }
}
