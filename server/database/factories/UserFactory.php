<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Hidehalo\Nanoid\Client;

class UserFactory extends Factory
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
            'user_id' => $client->generateId($size = 7, $mode = Client::MODE_DYNAMIC),
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'address' => $this->faker->address(),
            'password' => bcrypt($plain_text = "password"),
        ];
    }
}
