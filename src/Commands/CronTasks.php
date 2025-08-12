<?php

namespace Azuriom\Plugin\Draw\Commands;

use Illuminate\Console\Command;
use Azuriom\Models\Setting;
use Azuriom\Plugin\Draw\Models\Draw;

class CronTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'draw:cron_tasks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Differents cron tasks of draw plugin.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        if(!setting('draw.cron_activated', false)) {
            Setting::updateSettings([
                'draw.cron_activated' => true,
            ]);
            Setting::save();
        }

        $draws = Draw::where('closed', false)->get();
        
        foreach($draws as $draw) {         
            if($draw->automatic_draw && $draw->expires_at <= now()) {
                $draw->close();
            }
        }
    }
}