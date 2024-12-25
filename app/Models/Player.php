<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Player extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'aid',
        'uid',
        'position',
        'category',
        'status',
        'tid',
    ];

    public function auction()
    {
        return $this->belongsTo(Auction::class, 'aid');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'uid');
    }

    public function team()
    {
        return $this->belongsTo(Team::class, 'tid');
    }
}
