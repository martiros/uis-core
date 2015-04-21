<?php
namespace UIS\Core\Testing;

use Laracasts\Integrated\Extensions\Laravel as IntegrationTest;
use Faker\Factory as Faker;
use BadMethodCallException;
use Mockery;
use Mockery\MockInterface;

abstract class TestCase extends IntegrationTest
{
    protected $faker;

    public function __construct()
    {
        $this->faker = Faker::create();
    }

    public function getUserForAuth()
    {
        throw new BadMethodCallException("getUserForAuth method not implemented.");
    }

    public function login()
    {
        $this->be($this->getUserForAuth());
        return $this;
    }

    public function token()
    {
        $this->startSession();
        return csrf_token();
    }

    /**
     * @return \Mockery\MockInterface
     */
    public function mock($class)
    {
        $obj = $this->app->make($class);
        if (empty($obj) || !($obj instanceof MockInterface)) {
            $obj = Mockery::mock($class);
            $this->app->instance($class, $obj);
        }
        return $obj;
    }
}
