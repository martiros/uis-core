<?php

namespace UIS\Core\File;

use UIS\Core\File\Exceptions\FileNotFoundException;
use Auth;
use DB;
use Illuminate\Support\Facades\Request;
use UIS\Core\File\Exceptions\UnableCreateDirException;
use Carbon\Carbon;
use UIS\Core\Image\Exceptions\ImageNotFoundException;
use UIS\Core\Image\Exceptions\InvalidFileMaxSizeException;
use UIS\Core\Image\Exceptions\InvalidImageExtensionException;

class Uploader
{
    const TYPE = 'file';

    protected $options = [
        'file_max_size' => 5,
        'extensions' => ['jpg', 'jpeg', 'png', 'pdf', 'zip', 'doc', 'docx', 'txt'],
    ];

    public function getUploaderKey()
    {
        // @TODO: Fixme
        return 'default';
    }

    public function getUploaderType()
    {
        return self::TYPE;
    }

    /**
     * @return float
     */
    public function getFileMaxSize()
    {
        return $this->options['file_max_size'];
    }

    /**
     * @return array
     */
    public function getAllowedExtensions()
    {
        return $this->options['extensions'];
    }

    /**
     * @var array
     */
    protected static $filesList = [];

    /**
     * @param int $id
     * @param bool $checkUser
     * @param bool $findOrFail
     * @return \UIS\Core\File\UploadedFile
     * @throws \UIS\Core\File\Exceptions\FileNotFoundException
     */
    public static function getTempFile($id, $findOrFail = true, $checkUser = true)
    {
        if (isset(self::$filesList[$id])) {
            return self::$filesList[$id];
        }

        $fileData = DB::table('uploaded_files')->find($id);
        if (empty($fileData)) {
            if ($findOrFail) {
                throw new FileNotFoundException();
            }

            return;
        }
        $fileData = unserialize($fileData->file_data);

        $tempFile = new UploadedTempFile($fileData);

        /** @var \UIS\Core\File\UploadedFile $file */
        $file = self::$filesList[$id] = $tempFile;
        if ($checkUser && $file->getUserId() !== Auth::user()->id) {
            if ($findOrFail) {
                throw new FileNotFoundException();
            }

            return;
        }

        return $file;
    }

    /**
     * @param string $imageKey
     * @return UploadedFile
     * @throws FileNotFoundException
     */
    public function getUploadedFile($imageKey)
    {
        $file = Request::file($imageKey);
        if ($file == null) {
            $e = new FileNotFoundException();
            $e->setErrorKey($imageKey);
            throw $e;
        }

        return new UploadedFile($file);
    }

    /**
     * @return string
     * @throws UnableCreateDirException
     */
    protected function getStoragePath()
    {
        $path = storage_path('app');
        $imagesStoragePath = $path.'/'.'uploaded_files';
        if (!file_exists($imagesStoragePath) && !mkdir($imagesStoragePath, 0777)) {
            throw new UnableCreateDirException('Unable create dir-'.$imagesStoragePath);
        }

        return $imagesStoragePath;
    }

    protected function createTempSubDir()
    {
        $tempDirSubFolder = rand(1, 7000);
        $tempDirSub = $this->getStoragePath().'/'.$tempDirSubFolder;
        if (!file_exists($tempDirSub) && !mkdir($tempDirSub, 0777)) {
            throw new UnableCreateDirException('Unable create dir-'.$tempDirSub);
        }

        return $tempDirSubFolder;
    }

    protected function validateFileSize(UploadedFile $file)
    {
        $fileMaxSize = $this->getFileMaxSize();
        if ($fileMaxSize === false) {
            return;
        }
        $fileMaxSize *= 1048576;
        $size = $file->getSize();
        if ($fileMaxSize < $size) {
            $e = new InvalidFileMaxSizeException('Invalid file size -'.$size);
            $e->setMaxSize($fileMaxSize);
            $e->setFileSize($size);
            throw $e;
        }
    }

    /**
     * @param string $image
     * @return int
     * @throws Exception
     * @throws ImageNotFoundException
     * @throws \Exception
     */
    public function saveToTemp($image = 'file')
    {
        // FIXME: Check $_FILES['file']['error'] codes !!!

        $uploadedFile = $this->getUploadedFile($image);
        $tempSubDirFolder = $this->createTempSubDir();
        $moveToTempDirectory = $this->getStoragePath().'/'.$tempSubDirFolder;

        // @throws InvalidFileMaxSizeException
        $this->validateFileSize($uploadedFile);

        // @throws Media_ImgUploader_Exception_InvalidExtension, @throws Media_ImgUploader_Exception_InvalidImage
        $extension = $this->getFileExtension($uploadedFile);

        $userId = Auth::user()->id;
        $id = DB::table('uploaded_files')->insertGetId([
            'file_data' => '',
            'created_at' => new Carbon(),
            'uploader_key' => $this->getUploaderKey(),
            'uploader_type' => $this->getUploaderType(),
            'uploaded_by_id' => $userId,
        ]);

        $fileData = [
            'id' => $id,
            'client_original_name' => $uploadedFile->getClientOriginalName(),
            'client_size' => $uploadedFile->getClientSize(),
            'client_type' => $uploadedFile->getClientMimeType(),
            'created_at' => new Carbon(),
            'uploader_key' => $this->getUploaderKey(),
            'uploader_type' => $this->getUploaderType(),
            'uploaded_by_id' => $userId,
        ];
        $fileData['file_path'] = $uploadedFile->move($moveToTempDirectory, $id.'.'.$extension);

        DB::table('uploaded_files')
            ->where('id', $id)
            ->update([
                'file_data' => serialize($fileData),
                'file_path' => $tempSubDirFolder.'/'.$id.'.'.$extension,
            ]);

        return $id;
    }

    protected function getFileExtension(UploadedFile $file)
    {
        $extension = $file->guessExtension();
        if (!$extension || !in_array($extension, $this->getAllowedExtensions())) {
            $e = new InvalidImageExtensionException();
            $e->setAllowedExtensions($this->getAllowedExtensions());
            throw $e;
        }

        return $extension;
    }
}
