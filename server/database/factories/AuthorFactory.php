<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Hidehalo\Nanoid\Client;

class AuthorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $client = new Client();
        return [
            'author_id' => $client->generateId($size = 7, $mode = Client::MODE_DYNAMIC),
            'name' => $this->faker->name(),
            'portrait' => $this->faker->image(storage_path(''), 640, 480, null, true),
            'description' => $this->faker->sentence($nbWords = 6, $variableNbWords = true),
        ];
    }
}
