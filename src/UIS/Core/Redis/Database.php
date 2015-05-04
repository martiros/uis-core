<?php
namespace UIS\Core\Redis;

use Doctrine\Instantiator\Exception\InvalidArgumentException;
use Redis as RedisClient;
use Illuminate\Contracts\Redis\Database as DatabaseContract;

class Database implements DatabaseContract
{
    /**
     * The host address of the database.
     *
     * @var array
     */
    protected $clients;

    /**
     * Create a new Redis connection instance.
     *
     * @param  array $servers
     */
    public function __construct(array $servers = [])
    {
        if (isset($servers['cluster']) && $servers['cluster']) {
            throw new InvalidArgumentException("Redis cluster not implemented.");
        } else {
            $this->clients = $this->createSingleClients($servers);
        }
    }

    /**
     * Create an array of single connection clients.
     *
     * @param  array $servers
     * @return array
     */
    protected function createSingleClients(array $servers)
    {
        $clients = [];
        $servers = array_except($servers, ['cluster']);
        foreach ($servers as $key => $server) {
            $clients[$key] = new RedisClient();
            $clients[$key]->connect($server['host'], $server['port']);
        }
        return $clients;
    }

    /**
     * Get a specific Redis connection instance.
     *
     * @param  string $name
     * @return RedisClient
     */
    public function connection($name = 'default')
    {
        return $this->clients[$name ?: 'default'];
    }

    /**
     * Run a command against the Redis database.
     *
     * @param  string $method
     * @param  array $parameters
     * @return mixed
     */
    public function command($method, array $parameters = array())
    {
        return call_user_func_array(array($this->clients['default'], $method), $parameters);
    }

    /**
     * Dynamically make a Redis command.
     *
     * @param  string $method
     * @param  array $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->command($method, $parameters);
    }

}
