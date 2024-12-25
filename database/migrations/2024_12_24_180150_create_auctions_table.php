<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuctionsTable extends Migration
{
    public function up()
    {
        Schema::create('auctions', function (Blueprint $table) {
            $table->id('aid');
            $table->foreignId('hostid')->constrained('users');
            $table->string('title');
            $table->text('description');
            $table->dateTime('auction_date');
            $table->integer('bid_starting_price');
            $table->integer('team_balance');
            $table->integer('min_bid_increase_amount');
            $table->integer('min_player_amount');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('auctions');
    }
}
