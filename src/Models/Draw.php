<?php

namespace Azuriom\Plugin\Draw\Models;

use Illuminate\Database\Eloquent\Model;
use Azuriom\Plugin\Draw\Models\DrawEntries;
use Azuriom\Models\User;
use Azuriom\Notifications\AlertNotification;
use Azuriom\Plugin\Draw\Models\DrawWinners;

class Draw extends Model
{
    protected $table = 'draws';

    protected $fillable = [
        'name',
        'description',
        'submitter',
        'max_entries',
        'max_entries_per_player',
        'price',
        'closed',
        'winners',
        'pined',
        'automatic_draw',
        'expires_at',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function rewards()
    {
        return $this->belongsToMany(\Azuriom\Plugin\Draw\Models\DrawReward::class, 'draw_rewards_link', 'draw_id', 'reward_id');
    }

    public function winners()
    {
        return $this->hasMany(DrawWinners::class, 'draw_id');
    }

    public function entries()
    {
        return $this->hasMany(DrawEntries::class, 'draw_id');
    }

    public function close() 
    {
        $winners = $this->winners;
        $entries = DrawEntries::where('draw_id', $this->id)->get();
        $total_entries = sizeof($entries);

        if($total_entries < $winners) {
            return;
        }

        $shuffledEntries = collect($entries)->shuffle();
        $selectedWinners = [];

        foreach($shuffledEntries as $entry) {
            if(!in_array($entry->user_id, $selectedWinners)) {
                $user = User::find($entry->user_id);
                if($user) {
                    $selectedWinners[] = $entry->user_id;
                
                    if(sizeof($selectedWinners) >= $winners) {
                        break;
                    }
                }
            }
        }

        if(sizeof($selectedWinners) < $winners) {
            return;
        }

        $notification_content = trans('draw::messages.notifications.won_draw');
        $notification_text = str_replace('{draw}', $this->name, $notification_content);

        foreach($selectedWinners as $winner) {
            $user = User::find($winner);

            (new AlertNotification($notification_text))
                ->send($user);

            DrawWinners::insert([
                'user_id' => $winner,
                'draw_id' => $this->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if($this->rewards && sizeof($this->rewards) > 0) {
                foreach($this->rewards as $reward) {
                    $reward->dispatch($user);
                }
            }
        }

        Draw::find($this->id)->update([
            'closed' => true,
            'updated_at' => now(),
            'is_open' => false
        ]);
    }
}