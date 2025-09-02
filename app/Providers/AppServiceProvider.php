<?php

namespace App\Providers;

use Illuminate\Console\Events\ScheduledTaskStarting;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(ScheduledTaskStarting::class, function ($event) {
        echo "[".now()."] Processing scheduled command: {$event->task->command}".PHP_EOL;
        });

        Event::listen(ScheduledTaskFinished::class, function ($event) {
            echo "[".now()."] Command \"{$event->task->command}\" completed successfully.".PHP_EOL;
        });
    }
}
