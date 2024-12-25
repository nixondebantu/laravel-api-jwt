<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuctionState extends Model
{
    protected $table = 'auction_state';

    protected $primaryKey = 'aid';

    public $incrementing = false;

    protected $fillable = [
        'aid',
        'current_player',
        'current_bid',
        'bidder_team_id'
    ];

    public function auction()
    {
        return $this->belongsTo(Auction::class, 'aid', 'aid');
    }

    public function currentPlayer()
    {
        return $this->belongsTo(Player::class, 'current_player', 'id');
    }

    public function bidderTeam()
    {
        return $this->belongsTo(Team::class, 'bidder_team_id', 'tid');
    }
}
