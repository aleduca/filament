<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
  /**
   * Seed the application's database.
   */
  public function run(): void
  {

    $users = \App\Models\User::factory(30)->create();
    \App\Models\Tag::factory(30)->create();
    $categories = \App\Models\Category::factory(30)->create();
    $posts = \App\Models\Post::factory(30)
      ->recycle($users)
      ->recycle($categories)
      ->create();
    $comments = \App\Models\Comment::factory(30)->recycle($users)->recycle($posts)->create();
    \App\Models\Reply::factory(30)->recycle($users)->recycle($comments)->create();

    // \App\Models\User::factory()->create([
    //     'name' => 'Test User',
    //     'email' => 'test@example.com',
    // ]);
  }
}
