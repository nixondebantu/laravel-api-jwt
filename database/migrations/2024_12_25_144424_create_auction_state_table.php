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
        Schema::create('auction_state', function (Blueprint $table) {
            $table->foreignId('aid')->primary()->constrained('auctions', 'aid')
                ->onDelete('cascade');
            $table->foreignId('current_player')->nullable()
                ->constrained('players', 'id')
                ->onDelete('set null');
            $table->integer('current_bid')->default(0);
            $table->foreignId('bidder_team_id')->nullable()
                ->constrained('teams', 'tid')
                ->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auction_state');
    }
};
