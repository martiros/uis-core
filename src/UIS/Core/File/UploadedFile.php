<?php

namespace UIS\Core\File;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile as SymphonyUploadedFile;
use Symfony\Component\HttpFoundation\File\File as SymphonyFile;
use JsonSerializable;

class UploadedFile implements JsonSerializable
{
    /**
     * @var \Symfony\Component\HttpFoundation\File\UploadedFile
     */
    protected $file = null;

    protected $id = null;

    protected $userId = null;

    protected $uploaderKey = null;

    protected $uploaderType = null;

    public function __construct(SymphonyUploadedFile $file)
    {
        $this->file = $file;
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
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    public function getUserId()
    {
        return $this->userId;
    }

    public function setUploaderKey($uploaderKey)
    {
        $this->uploaderKey = $uploaderKey;
    }

    public function getUploaderKey()
    {
        return $this->uploaderKey;
    }

    public function setUploaderType($uploaderType)
    {
        $this->uploaderType = $uploaderType;
    }

    public function getUploaderType()
    {
        return $this->uploaderType;
    }

    public function getPathname()
    {
        return $this->file->getPathname();
    }

    protected function getName($name)
    {
        $originalName = str_replace('\\', '/', $name);
        $pos = strrpos($originalName, '/');
        $originalName = false === $pos ? $originalName : substr($originalName, $pos + 1);

        return $originalName;
    }

    public function getSize()
    {
        return $this->file->getSize();
    }

    /**
     * @return string
     */
    public function getMimeType()
    {
        return $this->file->getMimeType();
    }

    public function getExtension()
    {
        return $this->file->getExtension();
    }

    /**
     * @return string
     */
    public function guessExtension()
    {
        return $this->file->guessExtension();
    }

    public function getClientOriginalName()
    {
        return $this->file->getClientOriginalName();
    }

    /**
     * @return string
     */
    public function getClientMimeType()
    {
        return $this->file->getClientMimeType();
    }

    /**
     * @return string
     */
    public function getClientSize()
    {
        return $this->file->getClientSize();
    }

    public function getError()
    {
        return $this->file->getError();
    }

    /**
     * @param string $directory
     * @param string $name
     * @return string Return file new destination
     */
    public function move($directory, $name = null)
    {
        $target = $this->file->move($directory, $name);

        return $target->getPathname();
    }

    public function save($directory, $name)
    {
        $target = $this->getTargetFile($directory, $name);

        if (!@rename($this->getPathname(), $target)) {
            $error = error_get_last();
            throw new FileException(sprintf('Could not move the file "%s" to "%s" (%s)', $this->getPathname(), $target, strip_tags($error['message'])));
        }

        @chmod($target, 0666 & ~umask());

        // @TODO: Remove file from database
        return $target;
    }

    protected function getTargetFile($directory, $name = null)
    {
        if (!is_dir($directory)) {
            if (false === @mkdir($directory, 0777, true)) {
                throw new FileException(sprintf('Unable to create the "%s" directory', $directory));
            }
        } elseif (!is_writable($directory)) {
            throw new FileException(sprintf('Unable to write in the "%s" directory', $directory));
        }

        $target = rtrim($directory, '/\\').DIRECTORY_SEPARATOR.(null === $name ? $this->getBasename() : $this->getName($name));

        return new SymphonyFile($target, false);
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
        ];
    }
}

class UIS_Uploader_File
{
    /**
     * Name of uploaded file.
     * @var string
     */
    private $name = '';

    /**
     * @var string
     */
    private $tmpName = '';

    /**
     * Get mime type of the file.
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get extension of uploaded file from name.
     * @return string
     */
    public function getExtension()
    {
        return strtolower(UIS_File::getFileExtension($this->name));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getSanitizedFileName()
    {
        return UIS_File::sanitizeFileName(UIS_File::getFileName($this->getName()));
    }

    /**
     * @return string
     */
    public function getTempName()
    {
        return $this->tmpName;
    }

    public function moveUploadedFile($filePath)
    {

        // FIXME: VALIDATE ERROR CODE !!!!!!!!!!!!!!!!!!!
        $moveResult = move_uploaded_file($this->getTempName(), $filePath);
//            if ( $moveResult == false ) {
//                throw new Media_FileUploader_Exception_CantStore( 'Can`t store image, File - '.( $moveToTempDirectory.DS.$fileTempName ) );
//            }
//
//            throw new Media_FileUploader_Exception_CantStore( 'Can`t store image, File - '.( $moveToTempDirectory.DS.$fileTempName ) );
//            throw new UIS_File_Exception_CantStore( 'Can`t store image, File - '.( $moveToTempDirectory.DS.$fileTempName ) );
    }

    /************************************************************************************************************/
    /************************************************************************************************************/
    /************************************************************************************************************/

    /**
     * Preset no errors.
     * @var int
     */
    private $errorCode = 0;

    public function __construct($fileData)
    {
        $this->errorCode = $fileData['error'];
        $this->name = $fileData['name'];
        $this->tmpName = $fileData['tmp_name'];
        $this->size = intval($fileData['size']);
    }

    /**
     * @return int
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * @return bool
     */
    public function hasErrors()
    {
        return $this->errorCode !== UPLOAD_ERR_OK;
    }

    public function validate()
    {
        if (!$this->hasErrors()) {
            return;
        }

        if ($this->errorCode === UPLOAD_ERR_INI_SIZE || $this->errorCode === UPLOAD_ERR_FORM_SIZE) {
            throw new UIS_Uploader_Exception_TooLargeFile();
        }

        if ($this->errorCode === UPLOAD_ERR_PARTIAL) {
            throw new UIS_Uploader_Exception_PartiallyUploaded();
        }

        if ($this->errorCode === UPLOAD_ERR_NO_FILE || $this->size == 0) {
            throw new UIS_Uploader_Exception_FileNotFound();
        }

        if ($this->errorCode === UPLOAD_ERR_NO_TMP_DIR) {
            throw new UIS_Uploader_Exception_TempDirNotFound(' Missing a temporary folder.');
        }

        if ($this->errorCode === UPLOAD_ERR_CANT_WRITE) {
            throw new UIS_Uploader_Exception_CantWrite('Failed to write file to disk.');
        }

        if ($this->errorCode === UPLOAD_ERR_EXTENSION) {
            throw new UIS_Uploader_Exception_UnableUpload('A PHP extension stopped the file upload. PHP does not provide a way to ascertain which extension caused the file upload to stop; examining the list of loaded extensions with phpinfo() may help.');
        }
        throw new UIS_Uploader_Exception_UnableUpload('Unable Upload, Error Code-'.$this->errorCode);
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }
}
