<?php namespace UIS\Core\File;

use DB;

class Uploader
{
    /**
     * @var array
     */
    protected static $filesList = [];

    /**
     * @param int $id
     * @return \UIS\Core\File\UploadedFile
     */
    public static function getTempFile($id)
    {
        if (isset(self::$filesList[$id])) {
            return self::$filesList[$id];
        }

        $fileData = DB::table('uploaded_files')->find($id);
//        if () {
//
//        }
        uis_dump($files);

        /*
                [id] => 7
                [file_path] => 3865/7.jpeg
                [file_object] => O:26:"UIS\Core\File\UploadedFile":2:{s:7:"*file";O:50:"Symfony\Component\HttpFoundation\File\UploadedFile":8:{s:56:"Symfony\Component\HttpFoundation\File\UploadedFiletest";b:0;s:64:"Symfony\Component\HttpFoundation\File\UploadedFileoriginalName";s:50:"sea-high-resolution-sailboat-sunset-for-342485.jpg";s:60:"Symfony\Component\HttpFoundation\File\UploadedFilemimeType";s:24:"application/octet-stream";s:56:"Symfony\Component\HttpFoundation\File\UploadedFilesize";i:342485;s:57:"Symfony\Component\HttpFoundation\File\UploadedFileerror";i:0;s:21:"SplFileInfofileName";s:52:"/var/www/ncv/storage/app/uploaded_images/3865/7.jpeg";s:22:"SplFileInfofileClass";s:13:"SplFileObject";s:22:"SplFileInfoinfoClass";s:11:"SplFileInfo";}s:5:"*id";i:7;}
            [uploader_key] => default
            [uploader_type] => image
                [uploaded_by_id] => 14
                [created_at] => 2015-01-12 13:05:04

        */
        /********************************************************************************************/
        /********************************************************************************************/
        /********************************************************************************************/

        $id = DB::table('uploaded_files')->insertGetId([
            'file_object' => '',
            'created_at' => new Carbon(),
            'uploader_key' => $this->getUploaderKey(),
            'uploader_type' => $this->getUploaderType(),
            'uploaded_by_id' => Auth::user()->id
        ]);
        $uploadedFile = $uploadedFile->move($moveToTempDirectory, $id . '.' . $extension);
        $uploadedFile->setId($id);

        DB::table('uploaded_files')
            ->where('id', $id)
            ->update([
                'file_object' => serialize($uploadedFile),
                'file_path' => $tempSubDirFolder . '/' .  $id . '.' . $extension
            ]);

        /**********************************************************************************************/
        /**********************************************************************************************/
        /**********************************************************************************************/

        $tempImageData = self::getDAO()->getTempImageById($id);
        if (empty($tempImageData)) {
            return null;
        }
        self::$imagesList[$id] = new Media_ImgUploader_TempImage($tempImageData);
        return self::$imagesList[$id];

    }
}


