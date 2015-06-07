<?php
namespace UIS\Core\Testing;

use Laracasts\Integrated\Extensions\Laravel as IntegrationTest;
use Faker\Factory as Faker;
use BadMethodCallException;
use Mockery;
use Mockery\MockInterface;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
abstract class TestCase extends IntegrationTest
{
    protected $faker;

    protected $mailerMock = null;

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
//        dd($cookies);
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
            $this->app['mailer']->setSwiftMailer($this->mailerMock);
        }
        return $this->mailerMock;
    }
}
