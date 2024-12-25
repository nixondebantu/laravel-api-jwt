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
        Schema::create('teams', function (Blueprint $table) {
            $table->id('tid');
            $table->string('name');
            $table->foreignId('aid')->constrained('auctions', 'aid')
                ->onDelete('cascade');
            $table->string('logo_url')->nullable();
            $table->foreignId('manager_id')->constrained('users', 'id')
                ->onDelete('cascade');
            $table->integer('cost')->default(0);
            $table->boolean('isAccepted')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teams');
    }
};
