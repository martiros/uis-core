<?php namespace UIS\Core\File;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;
use DB;

class UploadedTempFile extends File
{
    protected $data = [
        'client_original_name' => '',
        'client_size' => 0,
        'client_type' => null,
        'created_at' => null,
        'uploader_key' => null,
        'uploader_type' => null,
        'uploaded_by_id' => null
    ];

    public function __construct(array $data)
    {
        $this->data = $data;
        parent::__construct($data['file_path']);
    }

    /**
     * @return bool
     */
    public function isImage()
    {
        return in_array(
            $this->getMimeType(),
            ['image/gif', 'image/jpeg', 'image/pjpeg', 'image/png']
        );
    }

    public function getId()
    {
        return $this->data['id'];
    }

    public function getUserId()
    {
        return $this->data['uploaded_by_id'];
    }

    public function getUploaderKey()
    {
        return $this->data['uploader_key'];
    }

    public function getUploaderType()
    {
        return $this->data['uploader_type'];
    }

    public function getClientOriginalName()
    {
        return $this->data['client_original_name'];
    }

    /**
     * @return string
     */
    public function getClientMimeType()
    {
        return $this->data['client_type'];
    }

    /**
     * @return string
     */
    public function getClientSize()
    {
        return $this->data['client_size'];
    }

    public function save($directory, $name)
    {
        $target = $this->getTargetFile($directory, $name);

        if (!@rename($this->getPathname(), $target)) {
            $error = error_get_last();
            throw new FileException(sprintf('Could not move the file "%s" to "%s" (%s)', $this->getPathname(), $target, strip_tags($error['message'])));
        }

        @chmod($target, 0666 & ~umask());

        DB::table('uploaded_files')->where('id', $this->getId())->delete();

        return $target;
    }
}
