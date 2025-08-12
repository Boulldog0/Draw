<?php

namespace Azuriom\Plugin\Draw\Models;

use Illuminate\Database\Eloquent\Model;

class DrawWinners extends Model
{
    protected $table = 'draw_winners';

    protected $fillable = [
        'draw_id',
        'user_id',
        'created_at',
        'updated_at'
    ];
}