<?php namespace UIS\Core\File;

use UIS\Core\File\Exceptions\FileNotFoundException;
use Auth, DB;

class Uploader
{
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
            return null;
        }
        $fileData = unserialize($fileData->file_data);

        $tempFile = new UploadedTempFile($fileData);

        /** @var \UIS\Core\File\UploadedFile $file */
        $file = self::$filesList[$id] = $tempFile;
        if ($checkUser && $file->getUserId() !== Auth::user()->id) {
            if ($findOrFail) {
                throw new FileNotFoundException();
            }
            return null;
        }
        return $file;
    }
}


