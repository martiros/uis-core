<?php

use UIS\Core\Testing\TestCase;

class UtilControllerTest extends TestCase
{
    public function testXsrfRefresh()
    {
        $response = $this->call('GET', 'api/core/xsrfRefresh');
        $content = $response->getContent();

        $this->assertJson($content);

        $data = json_decode($content);
        $this->assertInternalType('string', $data->data->token);
    }
}
