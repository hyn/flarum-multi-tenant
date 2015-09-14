<?php namespace Hyn\MultiTenant;

use Flarum\Support\Extension as BaseExtension;
use Illuminate\Events\Dispatcher;
use Illuminate\Database\ConnectionInterface;

class Extension extends BaseExtension
{
    public function listen(Dispatcher $events)
    {

    }

    public function boot()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/multi-tenant.php', 'multi-tenant');

        $this->app->singleton('tenant', function($app) {
            return new Tenant\Environment($app);
        });

        app('tenant');

        $this->app->resolving(ConnectionInterface::class, function(ConnectionInterface $connection, $app)
        {
            Listeners\ConnectionResolving::resolves($connection, $app);
        });
    }
}
