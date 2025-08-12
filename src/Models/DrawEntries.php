<?php

namespace Azuriom\Plugin\Draw\Models;

use Illuminate\Database\Eloquent\Model;

class DrawEntries extends Model
{
    protected $table = 'draw_entries';

    protected $fillable = [
        'draw_id',
        'user_id',
        'created_at',
        'updated_at'
    ];
}