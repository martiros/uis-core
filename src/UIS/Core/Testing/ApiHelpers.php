<?php
namespace UIS\Core\Testing;

trait ApiHelpers
{
    protected $responseObject;

    /**
     * @param string $uri
     * @param string $method
     * @param array $params
     * @return static
     */
    public function apiRequest($uri, $method = 'GET', $params = [])
    {
        $response = $this->call($method, $uri, $params)->getContent();
        $this->assertJson($response, 'Invalid API JSON response.');
        $this->responseObject = json_decode($response);
        return $this;
    }

    public function apiDump()
    {
        dd($this->responseObject);
        return $this;
    }

    public function assertApiStatusOk()
    {
        $this->assertApiStatus('OK');
    }

    public function assertApiStatusNotAuth()
    {
        $this->assertApiStatus('NOT_AUTH');
    }

    public function assertApiStatusInvalidData()
    {
        $this->assertApiStatus('INVALID_DATA');
    }

    public function assertApiStatusNotFound()
    {
        $this->assertApiStatus('NOT_FOUND');
    }

    public function assertApiStatus($status)
    {
        $this->assertEquals($status, $this->responseObject->status, print_r((array)$this->responseObject, true));
    }
}
