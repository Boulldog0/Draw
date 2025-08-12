<?php

namespace Azuriom\Plugin\Draw\Models;

use Illuminate\Database\Eloquent\Model;

class DrawRewardLink extends Model
{
    protected $table = 'draw_rewards_link';

    protected $fillable = [
        'draw_id',
        'reward_id',
    ];

    public $timestamps = false;
}