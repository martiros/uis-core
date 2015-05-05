<?php
namespace UIS\Core\Testing;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Filesystem\Filesystem;

trait ApiHelpers
{
    protected $responseObject;

    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    protected $fs;

    /**
     * @param string $uri
     * @param string $method
     * @param array $params
     * @param array $cookies
     * @param array $files
     * @param array $server
     * @param null $content
     * @return static
     */
    public function apiRequest($uri, $method = 'GET', $params = [], $cookies = [], $files = [], $server = [], $content = null)
    {
        $response = $this->call($method, $uri, $params, $cookies, $files, $server, $content)->getContent();
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

    protected function uploadFile($filePath, $fileName = null, $error = 0)
    {
        $file = new File($filePath);
        $fileName = $fileName ?: $file->getFilename();

        $tempFilePath = storage_path('/app/temp_file_for_test_upload');
        $this->getFilesystem()->copy($file->getRealPath(), $tempFilePath, true);

        $file = new UploadedFile($tempFilePath, $fileName, $file->getMimeType(), $file->getSize(), $error, true) ;
        $this->apiRequest(
            '/api/media/fileUploader/upload',
            'POST',
            [ '_token' => $this->token() ],
            [],
            [ 'file' => $file ]
        );
        $this->assertApiStatus('OK');
        return $this->responseObject->data->file_id;
    }

    public function getFilesystem()
    {
        if ($this->fs === null) {
            $this->fs = new Filesystem();
        }
        return $this->fs;
    }
}
