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
        Schema::create('bid_history', function (Blueprint $table) {
            $table->id('bid_id');
            $table->foreignId('aid')->constrained('auctions', 'aid')
                ->onDelete('cascade');
            $table->foreignId('player_id')->constrained('players', 'id')
                ->onDelete('cascade');
            $table->foreignId('bidder_team_id')->constrained('teams', 'tid')
                ->onDelete('cascade');
            $table->integer('bid_amount');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bid_history');
    }
};
