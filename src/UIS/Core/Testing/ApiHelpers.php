<?php namespace UIS\Core\Testing;

use GuzzleHttp\Client as HttpClient;

trait ApiHelpers
{
    /**
     * @var HttpClient
     */
    protected $client = null;

    public function apiRequest($url, $method = 'get', $params = [])
    {
        $response = $this->getClient()->$method(
            $url,
            $this->makeParams($method, $params)
        );

        $this->assertJson($response->getBody() . '', 'Invalid API JSON response.');
        return $response->json();
    }

    protected function makeParams($method, $params = [])
    {
        if ($method != 'get') {
            $params['_token'] = $this->getXsrfToken();
        }
        return [
            'cookies' => true,
            'body' => $params
        ];
    }

    protected function getClient()
    {
        if ($this->client === null) {
            $this->client = new HttpClient();
        }
        return $this->client;
    }

    public function getXsrfToken()
    {
        $result = $this->apiRequest(url('/api/core/xsrfRefresh'));
        return $result['data']['token'];
    }

    public function assertApiValidResponse($response)
    {
        $this->assertJson($response->getBody() . '', 'Invalid API JSON response.');
    }

    public function assertApiOK($result)
    {
        $this->assertEquals($result['status'], 'OK', 'Invalid status, must be "OK" ');
    }

    public function assertApiInvalidData($result)
    {
        $this->assertEquals('INVALID_DATA', $result['status'], 'Invalid status, must be "INVALID_DATA" ');
    }
}
