<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::disableForeignKeyConstraints();

    Schema::create('posts', function (Blueprint $table) {
      $table->id();
      $table->string('title', 100);
      $table->string('slug', 150);
      $table->string('thumbnail', 255);
      $table->boolean('is_published')->default(false);
      $table->text('content');
      $table->json('tags')->nullable();
      $table->foreignId('user_id')->constrained()->cascadeOnDelete()->unsigned();
      $table->foreignId('category_id')->nullable()->constrained()->unsigned();
      $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
      $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'))->onUpdate('current_timestamp');
    });

    Schema::enableForeignKeyConstraints();
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('posts');
  }
};
