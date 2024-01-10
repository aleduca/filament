<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PostFactory extends Factory
{
  /**
   * The name of the factory's corresponding model.
   *
   * @var string
   */
  protected $model = Post::class;

  /**
   * Define the model's default state.
   */
  public function definition(): array
  {
    return [
      'title' => $this->faker->sentence(4),
      'slug' => $this->faker->slug(),
      'thumbnail' => $this->faker->imageUrl(640, 480),
      'is_published' => rand(0, 1),
      'content' => $this->faker->paragraphs(3, true),
      'user_id' => User::factory(),
      'category_id' => Category::factory(),
      'created_at' => $this->faker->dateTime(),
      'updated_at' => $this->faker->dateTime(),
    ];
  }
}
