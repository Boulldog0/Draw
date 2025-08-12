<?php

namespace Azuriom\Plugin\Draw\Providers;

use Azuriom\Extensions\Plugin\BasePluginServiceProvider;
use Azuriom\Models\Permission;
use Azuriom\Plugin\Draw\Commands\CronTasks;
use Illuminate\Console\Scheduling\Schedule;

class DrawServiceProvider extends BasePluginServiceProvider
{
    /**
     * The plugin's global HTTP middleware stack.
     */
    protected array $middleware = [
        // \Azuriom\Plugin\Draw\Middleware\ExampleMiddleware::class,
    ];

    /**
     * The plugin's route middleware groups.
     */
    protected array $middlewareGroups = [];

    /**
     * The plugin's route middleware.
     */
    protected array $routeMiddleware = [
        // 'example' => \Azuriom\Plugin\Draw\Middleware\ExampleRouteMiddleware::class,
    ];

    /**
     * The policy mappings for this plugin.
     *
     * @var array<string, string>
     */
    protected array $policies = [
        // User::class => UserPolicy::class,
    ];

    /**
     * Register any plugin services.
     */
    public function register(): void
    {
        // $this->registerMiddleware();

        //
    }

    /**
     * Bootstrap any plugin services.
     */
    public function boot(): void
    {
        // $this->registerPolicies();

        $this->loadViews();

        $this->loadTranslations();

        $this->loadMigrations();

        $this->registerRouteDescriptions();
        
        $this->registerSchedule();

        $this->registerAdminNavigation();

        $this->registerUserNavigation();

        $this->commands([
            CronTasks::class,
        ]);

        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            $this->schedule($schedule);
        });

        Permission::registerPermissions([
            'draw.manage' => 'draw::admin.permissions.manage',
            'draw.create_draw' => 'draw::admin.permissions.create',
        ]);
    }

    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('draw:cron_tasks')->everyMinute();
    }   

    /**
     * Returns the routes that should be able to be added to the navbar.
     *
     * @return array<string, string>
     */
    protected function routeDescriptions(): array
    {
        return [
            'draw.index' => trans('draw::messages.title'),
        ];
    }

    /**
     * Return the admin navigations routes to register in the dashboard.
     *
     * @return array<string, array<string, string>>
     */
    protected function adminNavigation(): array
    {
        return [
            'draw' => [
                'name' => trans('draw::messages.title'),
                'type' => 'dropdown',
                'icon' => 'bi bi-ticket-detailed',
                'route' => 'draw.admin.*',
                'items' => [
                    'draw.admin.index' => trans('draw::admin.nav.draws'),
                    'draw.admin.draws.add' => trans('draw::admin.nav.create'),
                    'draw.admin.rewards' => trans('draw::admin.nav.rewards'),
                ],
                'permission' => 'draw.manage'
            ],
        ];
    }

    /**
     * Return the user navigations routes to register in the user menu.
     *
     * @return array<string, array<string, string>>
     */
    protected function userNavigation(): array
    {
        return [
            //
        ];
    }
}
