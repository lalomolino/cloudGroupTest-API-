<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class JobTitleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'code' => $this->faker->randomNumber(5),
            'importance' => $this->faker->realText(rand(10,20)),
            'boss' => $this->faker->numberBetween(0, 1)
        ];
    }
}
