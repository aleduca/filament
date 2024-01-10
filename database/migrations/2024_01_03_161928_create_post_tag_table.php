<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::disableForeignKeyConstraints();

    Schema::create('post_tag', function (Blueprint $table) {
      $table->foreignId('post_id')->constrained()->cascadeOnDelete()->unsigned();
      $table->foreignId('tag_id')->constrained()->cascadeOnDelete()->unsigned();
    });

    Schema::enableForeignKeyConstraints();
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('post_tag');
  }
};
