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
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aid')->constrained('auctions', 'aid')->onDelete('cascade');
            $table->foreignId('uid')->constrained('users', 'id')->onDelete('cascade');
            $table->string('position');
            $table->string('category')->default('Unknown');
            $table->string('status')->default('Queued');
            $table->foreignId('tid')->nullable()->constrained('teams', 'tid')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('players');
    }
};
