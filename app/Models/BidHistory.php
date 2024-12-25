<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BidHistory extends Model
{
    protected $table = 'bid_history';

    protected $primaryKey = 'bid_id';

    protected $fillable = [
        'aid',
        'player_id',
        'bidder_team_id',
        'bid_amount'
    ];

    public function auction()
    {
        return $this->belongsTo(Auction::class, 'aid', 'aid');
    }

    public function player()
    {
        return $this->belongsTo(Player::class, 'player_id', 'id');
    }

    public function bidderTeam()
    {
        return $this->belongsTo(Team::class, 'bidder_team_id', 'tid');
    }
}
