<?php

class ServerTest extends TestCase
{
    /**
     * Test is server hide X-Powered-By http header.
     * @return void
     */
    public function testHeaderXPoweredBy()
    {
        $result = Unirest::get(url('/api/core/config'));
        $this->assertFalse(isset($result->headers['X-Powered-By']), 'X-Powered-By not hided from http headers.');
    }

    public function testRestApplicationType()
    {
        $result = Unirest::get(url('/api/core/config'));
        $this->assertTrue(isset($result->headers['Content-Type']) && $result->headers['Content-Type'] === 'application/json');
    }
}
