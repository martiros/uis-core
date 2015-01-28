<?php

use UIS\Core\Testing\TestCase;

class UtilControllerTest extends TestCase
{
    use ApiHelpers;

    public function testXsrfRefresh()
    {
        $result = $this->apiRequest(url('/api/core/xsrfRefresh'));
        $this->assertApiOK($result);
    }
}
