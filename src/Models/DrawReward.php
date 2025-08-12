<?php

namespace Azuriom\Plugin\Draw\Models;

use Illuminate\Database\Eloquent\Model;
use Azuriom\Models\User;

class DrawReward extends Model
{
    protected $table = 'draw_rewards';

    protected $fillable = [
        'name',
        'money',
        'need_online',
        'commands',
    ];

    protected $casts = [
        'commands' => 'array',
        'need_online' => 'boolean',
    ];

    public function servers()
    {
        return $this->belongsToMany(\Azuriom\Models\Server::class, 'draw_rewards_servers', 'reward_id', 'server_id');
    }
    
    public function dispatch(User $user): void
    {
        if($this->money > 0) {
            $user->addMoney($this->money);
        }

        $commands = $this->commands ?? [];

        if(empty($commands)) {
            return;
        }

        $parsedCommands = array_map(function ($command) use ($user) {
            return str_replace('{player}', $user->name, $command);
        }, $commands);

        foreach($this->servers as $server) {
            $server->bridge()->sendCommands($parsedCommands, $user, $this->need_online);
        }
    }
}
