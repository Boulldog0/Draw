<?php

namespace Azuriom\Plugin\Draw\Models;

use Illuminate\Database\Eloquent\Model;

class DrawRewardServer extends Model
{
    protected $table = 'draw_rewards_servers';

    protected $fillable = [
        'server_id',
        'reward_id',
    ];

    public $timestamps = false;
}