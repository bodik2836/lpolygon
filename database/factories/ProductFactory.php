<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word() . ' ' . 'product',
            'description' => $this->faker->sentence(20),
            'price' => $this->faker->randomFloat(2, 1, 99999),
            'image_url' => $this->faker->imageUrl,
        ];
    }
}
