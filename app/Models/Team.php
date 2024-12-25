<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    protected $primaryKey = 'tid';

    protected $fillable = [
        'name',
        'aid',
        'logo_url',
        'manager_id',
        'cost',
        'isAccepted'
    ];

    protected $casts = [
        'isAccepted' => 'boolean',
    ];

    public function auction()
    {
        return $this->belongsTo(Auction::class, 'aid', 'aid');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id', 'id');
    }
}
