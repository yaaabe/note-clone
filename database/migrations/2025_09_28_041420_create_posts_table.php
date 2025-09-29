<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up(): void
{
    Schema::create('posts', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->string('title');
        $table->string('slug')->unique(); // とりあえず全体でユニーク
        $table->longText('body');
        $table->boolean('is_published')->default(false);
        $table->timestamp('published_at')->nullable();
        $table->timestamps();

        $table->index(['is_published', 'published_at']);
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
