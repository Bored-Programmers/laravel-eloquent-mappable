<?php declare(strict_types=1);

namespace BoredProgrammers\LaravelEloquentMappable\Providers;

use BoredProgrammers\LaravelEloquentMappable\Commands\GenerateMappableColumnsCommand;
use Illuminate\Support\ServiceProvider;

class LaravelEloquentMappableServiceProvider extends ServiceProvider
{

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateMappableColumnsCommand::class,
            ]);
        }
    }

}
