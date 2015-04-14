<?php
namespace UIS\Core\Sphinx;

use InvalidArgumentException;

class SphinxManager// implements ConnectionResolverInterface
{
    /**
     * The application instance.
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * The custom connection resolvers.
     *
     * @var array
     */
    protected $extensions = array();

    /**
     * The active connection instances.
     *
     * @var array
     */
    protected $connections = [];

    /**
     * Create a new sphinx manager instance.
     *
     * @param  \Illuminate\Foundation\Application  $app
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Get the sphinx connection instance.
     *
     * @param  string  $name
     * @return \Foolz\SphinxQL\Drivers\ConnectionInterface
     */
    public function connection($name = null)
    {
        $name = $name ?: $this->getDefaultConnection();
        if (!isset($this->connections[$name])) {
            $this->connections[$name] = $this->makeConnection($name);
        }
        return $this->connections[$name];
    }

    /**
     * Get the default connection name.
     *
     * @return string
     */
    public function getDefaultConnection()
    {
        return $this->app['config']['sphinx.default'];
    }

    /**
     * Make the sphinx connection instance.
     *
     * @param  string  $name
     * @return \Foolz\SphinxQL\Drivers\ConnectionInterface
     */
    protected function makeConnection($name)
    {
        $config = $this->getConfig($name);
        if (isset($this->extensions[$name]))
        {
            return call_user_func($this->extensions[$name], $config, $name);
        }
        dd($config);
    }

    /**
     * Get the configuration for a connection.
     *
     * @param  string  $name
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    protected function getConfig($name)
    {
        $name = $name ?: $this->getDefaultConnection();

        // To get the sphinx connection configuration, we will just pull each of the
        // connection configurations and get the configurations for the given name.
        // If the configuration doesn't exist, we'll throw an exception and bail.
        $connections = $this->app['config']['sphinx.connections'];

        if (is_null($config = array_get($connections, $name)))
        {
            throw new InvalidArgumentException("Sphinx [$name] not configured.");
        }

        return $config;
    }

    /**
     * Set the default connection name.
     *
     * @param  string  $name
     * @return void
     */
    public function setDefaultConnection($name)
    {
        $this->app['config']['sphinx.default'] = $name;
    }

    /**
     * Register an extension connection resolver.
     *
     * @param  string    $name
     * @param  callable  $resolver
     * @return void
     */
    public function extend($name, callable $resolver)
    {
        $this->extensions[$name] = $resolver;
    }

    /**
     * Return all of the created connections.
     *
     * @return array
     */
    public function getConnections()
    {
        return $this->connections;
    }

    /***************************************************************************************/
    /***************************************************************************************/
    /***************************************************************************************/












    /**
     * Dynamically pass methods to the default connection.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return call_user_func_array(array($this->connection(), $method), $parameters);
    }

}
