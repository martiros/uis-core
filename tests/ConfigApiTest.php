<?php

class ConfigApiTest extends TestCase
{
    public function testConfig()
    {
        $response = $this->call('GET', '/api/core/config');
        $this->assertTrue(!$response->isOk());
        $this->assertJson($response->getContent());
    }

}
