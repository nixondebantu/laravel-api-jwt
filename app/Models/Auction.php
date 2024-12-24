<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Auction extends Model
{
    protected $primaryKey = 'aid';

    protected $fillable = [
        'title',
        'description',
        'auction_date',
        'bid_starting_price',
        'team_balance',
        'min_bid_increase_amount'
    ];

    public function host()
    {
        return $this->belongsTo(User::class, 'hostid');
    }
}
