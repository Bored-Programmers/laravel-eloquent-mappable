<?php declare(strict_types=1);

namespace BoredProgrammers\LaravelEloquentMappable\Tests;

use Closure;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{

    public function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/../workbench/database/migrations');
        $this->artisan('migrate', ['--database' => 'laravel_eloquent_mappable'])->run();
    }

    protected function setUpDatabaseRequirements(Closure $callback): void
    {
    }


    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'laravel_eloquent_mappable');
        $app['config']->set('database.connections.laravel_eloquent_mappable', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

}
