<?php

namespace Azuriom\Plugin\Draw\Models;

use Illuminate\Database\Eloquent\Model;
use Azuriom\Models\User;

class DrawWinners extends Model
{
    protected $table = 'draw_winners';

    protected $fillable = [
        'draw_id',
        'user_id',
        'created_at',
        'updated_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}