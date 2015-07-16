<?php

namespace UIS\Core\Testing;

use Illuminate\Foundation\Testing\TestCase as IlluminateTestCase;
use Faker\Factory as Faker;
use BadMethodCallException;
use Mockery;
use Mockery\MockInterface;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

abstract class TestCase extends IlluminateTestCase
{
    protected $faker;

    protected $mailerMock = null;

    public function __construct()
    {
        $this->faker = Faker::create();
    }

    public function tearDown()
    {
        Mockery::close();
    }

    public function getUserForAuth()
    {
        throw new BadMethodCallException('getUserForAuth method not implemented.');
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

    public function seeCookie($name, $value = null, $path = '/', $domain = null)
    {
        $cookies = $this->response->headers->getCookies(ResponseHeaderBag::COOKIES_ARRAY);
        $this->assertTrue(isset($cookies[$domain][$path][$name]));

        if ($value !== null) {
            $encrypter = $this->app->make('\Illuminate\Contracts\Encryption\Encrypter');
            $cookie = $cookies[$domain][$path][$name];
            $this->assertEquals($encrypter->decrypt($value), $cookie->getValue());
        }
    }

    public function notSeeCookie($name, $value = null, $path = '/', $domain = null)
    {
        $cookies = $this->response->headers->getCookies(ResponseHeaderBag::COOKIES_ARRAY);
        $this->assertTrue(!isset($cookies[$domain][$path][$name]));
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

    /**
     * @return \Mockery\MockInterface
     */
    public function mockEmail()
    {
        if ($this->mailerMock === null) {
            $this->mailerMock = Mockery::mock('Swift_Mailer');

            $swiftTransport = Mockery::mock('Swift_Transport');
            $swiftTransport->shouldReceive('stop');

            $this->mailerMock->shouldReceive('getTransport')->andReturn($swiftTransport);
            $this->app['mailer']->setSwiftMailer($this->mailerMock);
        }

        return $this->mailerMock;
    }

    /**
     * Get the form from the page with the given submit button text.
     *
     * @param  string|null  $buttonText
     * @return \Symfony\Component\DomCrawler\Form
     * @throws \InvalidArgumentException
     */
    protected function getForm($buttonText = null)
    {
        try {
            if ($buttonText) {
                if (strpos($buttonText, '#') === 0) {
                    return $this->crawler->filter($buttonText)->form();
                } else {
                    return $this->crawler->selectButton($buttonText)->form();
                }
            }

            return $this->crawler->filter('form')->form();
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException(
                "Could not find a form that has submit button [{$buttonText}]."
            );
        }
    }

    /**
     * Assert that a given table empty.
     *
     * @param  string  $table
     * @param  string  $connection
     * @return $this
     */
    protected function seeEmptyTable($table, $connection = null)
    {
        $database = $this->app->make('db');

        $connection = $connection ?: $database->getDefaultConnection();

        $count = $database->connection($connection)->table($table)->count();

        $this->assertEquals(0, $count, sprintf(
            'Table [%s] not empty.', $table
        ));

        return $this;
    }


    /**
     * Assert that a given table not empty.
     *
     * @param  string  $table
     * @param  string  $connection
     * @return $this
     */
    protected function seeNotEmptyTable($table, $connection = null)
    {
        $database = $this->app->make('db');

        $connection = $connection ?: $database->getDefaultConnection();

        $count = $database->connection($connection)->table($table)->count();

        $this->assertNotEquals(0, $count, sprintf(
            'Table [%s] empty.', $table
        ));

        return $this;
    }
}
