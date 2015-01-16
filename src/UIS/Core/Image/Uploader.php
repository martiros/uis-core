<?php namespace UIS\Core\Image;

use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Request;
use UIS\Core\File\Exceptions\UnableCreateDirException;
use UIS\Core\File\UploadedFile;
use UIS\Core\File\Uploader as FileUploader;
use UIS\Core\File\Exceptions\FileNotFoundException;
use UIS\Core\Image\Exceptions\ImageNotFoundException;
use UIS\Core\Image\Exceptions\InvalidFileMaxSizeException;
use UIS\Core\Image\Exceptions\InvalidImageException;
use UIS\Core\Image\Exceptions\InvalidImageExtensionException;

class Uploader extends FileUploader
{
    const TYPE = 'image';

    protected $options = [
        'file_max_size' => 5,
        'extensions' => ['jpg', 'jpeg', 'png']
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
     * @param string $imageKey
     * @return UploadedFile
     * @throws ImageNotFoundException
     */
    public function getUploadedImage($imageKey)
    {
        $file = Request::file($imageKey);
        if ($file == null) {
            $e = new ImageNotFoundException();
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
        $imagesStoragePath = $path . '/' .'uploaded_images';
        if (!file_exists($imagesStoragePath) && !mkdir($imagesStoragePath, 0777)) {
            throw new UnableCreateDirException('Unable create dir-' . $imagesStoragePath);
        }
        return $imagesStoragePath;
    }

    private function createTempSubDir()
    {
        $tempDirSubFolder = rand(1, 7000);
        $tempDirSub = $this->getStoragePath() . '/' . $tempDirSubFolder;
        if (!file_exists($tempDirSub) && !mkdir($tempDirSub, 0777)) {
            throw new UnableCreateDirException('Unable create dir-' . $tempDirSub);
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
        if ( $fileMaxSize < $size ) {
            $e = new InvalidFileMaxSizeException('Invalid file size -' . $size);
            $e->setMaxSize($fileMaxSize);
            $e->setFileSize($size);
            throw $e;
        }
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
     * @param int $id
     * @param bool $checkUser
     * @param bool $findOrFail
     * @return \UIS\Core\File\UploadedFile
     * @throws \UIS\Core\File\Exceptions\FileNotFoundException
     */
    public static function getTempImage($id, $findOrFail = true, $checkUser = true)
    {
        $file= self::getTempFile($id, $findOrFail, $checkUser);
        if (empty($file)) {
            return null;
        }
        if ($file->getUploaderType() !== self::TYPE) {
            if ($findOrFail) {
                throw new FileNotFoundException();
            }
            return null;
        }
        return $file;
    }

    /******************************************************************************************************************/
    /******************************************************************************************************************/
    /******************************************************************************************************************/

    /**
     * @param string $image
     * @return int
     * @throws Exception
     * @throws ImageNotFoundException
     * @throws \Exception
     */
    public function saveToTemp($image = 'image')
    {
        // FIXME: Check $_FILES['file']['error'] codes !!!

        $uploadedFile = $this->getUploadedImage($image);
        $tempSubDirFolder = $this->createTempSubDir();
        $moveToTempDirectory = $this->getStoragePath() . '/' . $tempSubDirFolder;

        // @throws InvalidFileMaxSizeException
        $this->validateFileSize($uploadedFile);

        // @throws Media_ImgUploader_Exception_InvalidExtension, @throws Media_ImgUploader_Exception_InvalidImage
        $extension = $this->getFileExtension( $uploadedFile );

        $userId = Auth::user()->id;
        $id = DB::table('uploaded_files')->insertGetId([
            'file_data' => '',
            'created_at' => new Carbon(),
            'uploader_key' => $this->getUploaderKey(),
            'uploader_type' => $this->getUploaderType(),
            'uploaded_by_id' => $userId
        ]);

        $fileData = [
            'id' => $id,
            'client_original_name' => $uploadedFile->getClientOriginalName(),
            'client_size' => $uploadedFile->getClientSize(),
            'client_type' => $uploadedFile->getClientMimeType(),
            'created_at' => new Carbon(),
            'uploader_key' => $this->getUploaderKey(),
            'uploader_type' => $this->getUploaderType(),
            'uploaded_by_id' => $userId
        ];
        $fileData['file_path'] = $uploadedFile->move($moveToTempDirectory, $id . '.' . $extension);

        DB::table('uploaded_files')
            ->where('id', $id)
            ->update([
                'file_data' => serialize($fileData),
                'file_path' => $tempSubDirFolder . '/' .  $id . '.' . $extension
            ]);
        return $id;
    }


    protected function getFileExtension(UploadedFile $file)
    {
        if( !$file->isImage() ){
            throw new InvalidImageException("Invalid file type-".$file->getType());
        }

        $extension = $file->guessExtension();
        if (!$extension || !in_array($extension, $this->getAllowedExtensions())) {
            $e = new InvalidImageExtensionException();
            $e->setAllowedExtensions($this->getAllowedExtensions());
            throw $e;
        }
        return $extension;
    }
}



class Media_ImgUploader
{

    /**
     * @var array
     */
    private static $imgUploaderList = array();

    /**
     * @var array
     */
    private static $imagesList = array();

    /**
     * @var string
     */
    private $uploaderModuleName = null;

    /**
     * @param int $id
     * @return Media_ImgUploader
     * @throws Media_ImgUploader_Exception_ModuleNotFound
     */
    public static function getInstanceByTempImage($id){
        $tempImage = self::getTempImage($id);
        if(empty($tempImage)){
            throw new Media_ImgUploader_Exception_ModuleNotFound();
        }
        return self::getInstance($tempImage->data('uploader_module'));
    }

    /**********************************************************************************************************/
    /**********************************************************************************************************/
    /**********************************************************************************************************/

    /**
     * @return string
     */
    public function getTempDirectory(){
        return $this->options['temp_dir'];
    }



    /*
        ************************************************************************************************************
        ************************************************************************************************************
        ************************************************************************************************************
        ************************************************************************************************************
        ************************************************************************************************************
    */

    /*************************************************************************/
    /*************************************************************************/
    /*************************************************************************/

    /**
     *	@return boolean
     */
    public function moveFromTemp ( $imgId, $destination ){

        if ( self::isValidImgId( $imgId )  ) {
            $tempDir  =  $this->getTempDirectory();
            $tempDir  =  $tempDir.DS.$imgId;
            $result   =  rename(	$tempDir , $destination	);
            if ( $result == true  ) {
                $this->removeTempInfo ( $imgId );// FIXME
                return true;
            }
        }
        return false;

    }


    public function copyTempImage( $storeTempData ) {

        $extension = UIS_File::getFileExtension( $storeTempData['img_tmp_path'] );
        $fileTempName = UIS_File::getRandomFileName ( $storeTempData['dir_path'] , $extension );

        $copyResult = copy (   $this->getTempDirectory().DS.$storeTempData['img_tmp_path']  , $storeTempData['dir_path'].DS.$fileTempName  );
        if( $copyResult == false ){
            return false;
        }
        $storeTempData['save_utime']      =  time();
        $storeTempData['img_tmp_path']    =  $storeTempData['temp_dir_name'].'/'.$fileTempName;

        $infoFileName = $storeTempData['dir_path'].DS.$fileTempName.'.txt';
        $data = serialize ( $storeTempData );
        file_put_contents ( $infoFileName , $data  );

        return $storeTempData;

    }

    /***************************************************************************************/
    /***************************************************************************************/
    /***************************************************************************************/

    /**
     * @param string $id
     * @return boolean
     */
    public static function isValidImgId( $id ){
        return !empty( self::getTempImage($id) );
    }

    /**
     * @return array
     */
    public static function getAllModules(){

        $imageUploaderModules = array();
        $modules = UIS_Module_Loader::getInstance()->getModuleNames();
        foreach( $modules AS $moduleName ){

            $moduleImagesConfig = Core_Config::conf( $moduleName.'.images' );
            if( !is_array($moduleImagesConfig) ){
                continue;
            }

            foreach( $moduleImagesConfig AS $imageUploaderModule => $imageUploaderOptions ){
                $imageUploaderModules[] = $moduleName.'.'.$imageUploaderModule;
            }

        }
        return $imageUploaderModules;

    }

    public static function removeAllOldTempImages(){
        $olderThan = time() - Core_Config::conf('media.img_uploader.clean_after');
        $olderThan = date('Y-m-d H:i:s',$olderThan);
        $tempImagesList = self::getDAO()->getAllTempImagesByDate($olderThan);
        foreach( $tempImagesList AS $temImageData ){
            try{
                Media_ImgUploader::getInstance( $temImageData['uploader_module'] )->removeTempImage( $temImageData );
            }
            catch(Exception $e){
            }
        }
    }

    public function removeTempImage($tempImageData){
        $filePath = $this->getTempDirectory().DS.$tempImageData['file_path'];
        if( is_file($filePath) ){
            unlink( $filePath );
        }
        self::getDAO()->removeTempImage($tempImageData['id']);
    }
}

