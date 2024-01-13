<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition(): array
  {
    $faker = \Faker\Factory::create();
    $faker->addProvider(new \Bezhanov\Faker\Provider\Commerce($faker));
    return [
      'name' => $faker->unique()->category,
    ];
  }
}
