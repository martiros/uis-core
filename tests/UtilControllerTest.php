<?php

use UIS\Core\Testing\ApiHelpers;

class UtilControllerTest extends TestCase
{
    use ApiHelpers;

    public function testXsrfRefresh()
    {
        $result = $this->apiRequest(url('/api/core/xsrfRefresh'));
        $this->assertApiOK($result);
    }
}
