<?php namespace Hyn\MultiTenant\Tenant;

use Illuminate\Database\Connectors\ConnectionFactory;
use PDO;

use Flarum\Core\Application;
use Hyn\MultiTenant\Infuse\Url;


class Environment {

    /**
     * @var Application
     */
    protected $app;

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var string
     */
    public $hostname;

    /**
     * @var string
     */
    public $tenant;

    public function __construct(Application $app) {

        $this->app = $app;

        $this->tenant = $this->identifyTenant();

        if($this->tenant) {
            $this->configureEnvironment();
        }

    }

    /**
     * Identifies the current tenant hostname
     * @return null|string
     */
    protected function identifyTenant()
    {
        if(php_sapi_name() != 'cli') {
            $host = array_get($_SERVER, 'HTTP_HOST');
            $uri = array_get($_SERVER, 'REQUEST_URI');
            foreach($this->app['config']->get('multi-tenant', []) as $tenant => $settings) {
                foreach(array_get($settings, 'hostnames', []) as $hostname) {
                    if($hostname == $host || substr_compare($hostname, sprintf("%s%s", $host, $uri), 0, strlen($hostname), true) === 0) {
                        $this->hostname = $hostname;
                        return $tenant;
                    }
                }
            }
        }
        return null;
    }

    protected function configureEnvironment() {

        $this->config = $this->app['config']->get("multi-tenant.{$this->tenant}");

        // set base url
        Url::set($this->hostname);

        // set database
        $this->app->singleton('flarum.db', function () {
            $factory = new ConnectionFactory($this->app);
            $connection = $factory->make(array_merge($this->app->make('flarum.config')['database'], array_get($this->config, 'database', [])));
            $connection->setEventDispatcher($this->app->make('Illuminate\Contracts\Events\Dispatcher'));
            $connection->setFetchMode(PDO::FETCH_CLASS);
            return $connection;
        });
    }
}