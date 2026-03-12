<?php

namespace Azuriom\Plugin\Draw\Models;

use Illuminate\Database\Eloquent\Model;
use Azuriom\Plugin\Draw\Models\DrawEntries;
use Azuriom\Models\User;
use Azuriom\Notifications\AlertNotification;
use Azuriom\Plugin\Draw\Models\DrawWinners;
use Illuminate\Support\Facades\Http;

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
        'is_open',
        'winners',
        'pined',
        'automatic_draw',
        'expires_at',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'is_open' => 'boolean',
        'pined' => 'boolean',
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

    public function getWinners()
    {
        return $this->winners()->with('user')->get();
    }

    public function close() 
    {
        // attribute "winners" is the number of winners; the relation method winners()
        // returns a collection of DrawWinners. avoid confusion by reading the raw
        // attribute value.
        $winnersCount = $this->getAttribute('winners');

        $entries = DrawEntries::where('draw_id', $this->id)->get();
        $total_entries = sizeof($entries);

        if ($total_entries < $winnersCount) {
            // not enough entries to draw
            return;
        }

        $shuffledEntries = collect($entries)->shuffle();
        $selectedWinners = [];

        foreach ($shuffledEntries as $entry) {
            if (!in_array($entry->user_id, $selectedWinners)) {
                $user = User::find($entry->user_id);
                if ($user) {
                    $selectedWinners[] = $entry->user_id;

                    if (sizeof($selectedWinners) >= $winnersCount) {
                        break;
                    }
                }
            }
        }

        if (sizeof($selectedWinners) < $winnersCount) {
            // somehow we couldn't pick enough unique winners
            return;
        }

        $notification_content = trans('draw::messages.notifications.won_draw');
        $notification_text = str_replace('{draw}', $this->name, $notification_content);

        foreach ($selectedWinners as $winner) {
            $user = User::find($winner);

            (new AlertNotification($notification_text))
                ->send($user);

            DrawWinners::insert([
                'user_id' => $winner,
                'draw_id' => $this->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($this->rewards && sizeof($this->rewards) > 0) {
                foreach ($this->rewards as $reward) {
                    $reward->dispatch($user);
                }
            }
        }

        Draw::find($this->id)->update([
            'closed' => true,
            'updated_at' => now(),
            'is_open' => false
        ]);

        $this->sendDiscordWebhook();
    }

    public function sendDiscordWebhook() 
    {
        $setting = setting('draw_discord_webhook', '{}');

        $settings = json_decode($setting, true);

        $webhook = trim($settings['discord_webhook'] ?? '');

        if($webhook === '') {
            return;
        }

        $fields = [];
        if (!empty($settings['fields_container']) && is_array($settings['fields_container'])) {
            foreach ($settings['fields_container'] as $f) {
                $fields[] = [
                    "name" => $this->translateString($f['name'] ?? ''),
                    "value" => $this->translateString($f['value'] ?? ''),
                    "inline" => true
                ];
            }
        }

        $color = 0;
        if (!empty($settings['embed_color'])) {
            $c = ltrim($settings['embed_color'], '#');
            if (ctype_xdigit($c)) {
                $color = hexdec($c);
            }
        }

        $payload = [
            "embeds" => [
                [
                    "title" => $this->translateString($settings['embed_title'] ?? ''),
                    "description" => $this->translateString($settings['embed_description'] ?? ''),
                    "color" => $color,
                    "fields" => $fields,

                    "footer" => [
                        "text" => $this->translateString($settings['embed_footer'] ?? '')
                    ],

                    "timestamp" => now()->toIso8601String()
                ]
            ]
        ];

        Http::post($webhook, $payload);
    }

    private function translateString($str) 
    {
        $str = str_replace('{draw_name}', $this->name, $str);

        $usernames = $this->getWinners()->pluck('user.name')->toArray();
        $joined = implode(', ', $usernames);

        $str = str_replace('{winners}', $joined, $str);

        if (strpos($str, '{winner}') !== false) {
            $str = str_replace('{winner}', $usernames[0] ?? '', $str);
        }

        return $str;
    }
}