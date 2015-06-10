<?php

namespace UIS\Core\File;

class SubmittedFile
{
    const FILE_TYPE_NEW = 'new';
    const FILE_TYPE_EMPTY = 'empty';
    const FILE_TYPE_SAME = 'same';

    /**
     * @var string
     */
    protected $fileType = null;

    /**
     * @var UploadedTempFile
     */
    protected $tempFile = null;

    /**
     * @return bool
     */
    public function isNew()
    {
        return $this->fileType === self::FILE_TYPE_NEW;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return $this->fileType === self::FILE_TYPE_EMPTY;
    }

    /**
     * @return bool
     */
    public function isSame()
    {
        return $this->fileType === self::FILE_TYPE_SAME;
    }

    /**
     * @param string $fileType
     */
    public function setFileType($fileType)
    {
        $this->fileType = $fileType;
    }

    /**
     * @param UploadedTempFile $tempFile
     */
    public function setTempFile(UploadedTempFile $tempFile)
    {
        $this->tempFile = $tempFile;
    }

    /**
     * @return UploadedTempFile
     */
    public function getTempFile()
    {
        return $this->tempFile;
    }
}
