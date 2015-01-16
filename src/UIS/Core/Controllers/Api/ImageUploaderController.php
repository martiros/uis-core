<?php namespace UIS\Core\Controllers\Api;

use UIS\Core\Controllers\BaseController;
use UIS\Core\Image\Uploader;

class ImageUploaderController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function upload()
    {
        $imageUploader = new Uploader();
        $uploadedFileId = $imageUploader->saveToTemp();

        return $this->api(
            'OK',
            [
                'file_id' => $uploadedFileId
            ]
        );
    }
}




use Request;

class ImageController extends BaseController
{

    public function upload()
    {
//        new \Symfony\Component\HttpFoundation\File\UploadedFile();
//        Request::file('image')->get
        uis_dump(Request::file('image')->getExtension());

        Request::file('image');

        return $this->api('OK');

        $request = $this->getRequest();
        $imgModuleName = $request->getParam('uploaderModule');

        //	@throws	Media_ImgUploader_Exception_ModuleNotFound
        $imgUploader = Media_ImgUploader::getInstance( $imgModuleName );
        $result = new stdClass();
        $valResult = new UIS_Mvf_Validator_Result();

        try {
            $tempImageData = $imgUploader->storeAsTemp('image');
            $result->status = 'OK';
            $result->tempFile = array(
                'img_path' => adm_path( '/media/imgUploader/show?imgId='.sc_url_param( $tempImageData['id'] ) ),
                'id' => $tempImageData['id'],
                'size' => $tempImageData['info']['size'],
                'size_str' => Core_Helper_File::showSize($tempImageData['info']['size']),
                'uploader_module' => $tempImageData['uploader_module'],
                'sanitized_file_name' => $tempImageData['info']['sanitized_file_name'],
                'extension' =>  $tempImageData['info']['extension'],
                'type' => UIS_File_MimeType::getTypeFromExtension($tempImageData['info']['extension']),
                'icon' => Core_Helper_File::iconFromExtension( $tempImageData['info']['extension'] )
            );

        } catch( UIS_Uploader_Exception_FileNotFound $e ){
            $valResult->addError('image', '{media.img_uploader.error.select_image}');
        } catch (Media_ImgUploader_Exception_InvalidWidth $ex) {
            $valResult->addError(
                'image',
                trans(
                    'media.img_uploader.error.width',
                    null,
                    array(
                        'width' => $ex->getWidth(),
                        'file_width' => $ex->getFileWidth()
                    )
                )
            );
        } catch (Media_ImgUploader_Exception_InvalidMinWidth $ex) {
            $valResult->addError(
                'image',
                trans(
                    'media.img_uploader.error.min_width',
                    null,
                    array(
                        'min_width' => $ex->getMinWidth(),
                        'file_width' => $ex->getFileWidth()
                    )
                )
            );
        } catch (Media_ImgUploader_Exception_InvalidMaxWidth $ex) {
            $valResult->addError(
                'image',
                trans(
                    'media.img_uploader.error.max_width',
                    null,
                    array(
                        'max_width' => $ex->getMaxWidth(),
                        'file_width' => $ex->getFileWidth()
                    )
                )
            );
        } catch (Media_ImgUploader_Exception_InvalidHeight $ex) {
            $valResult->addError(
                'image',
                trans(
                    'media.img_uploader.error.height',
                    null,
                    array(
                        'height' => $ex->getHeight(),
                        'file_height' => $ex->getFileHeight()
                    )
                )
            );
        } catch (Media_ImgUploader_Exception_InvalidMinHeight $ex) {
            $valResult->addError(
                'image',
                trans(
                    'media.img_uploader.error.min_height',
                    null,
                    array(
                        'min_height' => $ex->getMinHeight(),
                        'file_height' => $ex->getFileHeight()
                    )
                )
            );
        } catch (Media_ImgUploader_Exception_InvalidMaxHeight $ex) {
            $valResult->addError(
                'image',
                trans(
                    'media.img_uploader.error.max_height',
                    null,
                    array(
                        'max_height' => $ex->getMaxHeight(),
                        'file_height' => $ex->getFileHeight()
                    )
                )
            );
        }

            ////////////////////////////////////////////////////////////////////////////////////////////////////
            ////////////////////////////////////////////////////////////////////////////////////////////////////
            ////////////////////////////////////////////////////////////////////////////////////////////////////

        catch ( Media_ImgUploader_Exception_ImageNotFound $e ) {
            $valResult->addError( 'result', '{media.img_uploader.error.select_image}' );
        }
        catch ( Media_ImgUploader_Exception_CantStore $e ) {
            $valResult->addError( 'result', '{media.img_uploader.error.unavailable}' );
            $logger = UIS_Log_File::getLogger();
            $logger->logFull( 'img_uploader_unavailable', $e );
        }
        catch ( Media_ImgUploader_Exception_InvalidFileMaxSize $e ) {
            $valResult->addError( 'result', trans( 'media.img_uploader.error.invalid_max_size',null, array( $e->getMaxSize() ) ) );
        }
        catch ( Media_ImgUploader_Exception_InvalidExtension $e ) {
            $result->status 	=  'INVALID_DATA';
            $result->errors = array( 'result'	=>	trans( 'media.img_uploader.error.invalid_image_type', null, array( implode( ', ', $e->getAllowedExtensions() ) ) ) );
        }
        catch ( Media_ImgUploader_Exception_InvalidMinWidth $e ) {
            $result->status 	=  'INVALID_DATA';
            $result->errors = array( 'result'	=>	trans( 'media.img_uploader.error.invalid_min_width', null, array( $e->getMinWidth() ) ) );
        }
        catch ( Media_ImgUploader_Exception_InvalidMinHeight $e ) {
            $result->status 	=  'INVALID_DATA';
            $result->errors = array( 'result'	=>	trans( 'media.img_uploader.error.invalid_min_height', null, array( $e->getMinHeight() ) ) );
        }
        catch ( Media_ImgUploader_Exception_InvalidMaxWidth $e ) {
            $result->status 	=  'INVALID_DATA';
            $result->errors = array( 'result'	=>	trans( 'media.img_uploader.error.invalid_max_width', null, array( $e->getMaxWidth() ) ) );
        }
        catch ( Media_ImgUploader_Exception_InvalidMaxHeight $e ) {
            $result->status 	=  'INVALID_DATA';
            $result->errors = array( 'result'	=>	trans( 'media.img_uploader.error.invalid_max_height', null, array( $e->getMaxHeight() ) ) );
        }
        $result->status = $valResult->isValid() ? 'OK' : 'INVALID_DATA';
        $result->errors = $valResult->errors();
        $this->send($result);
    }
}

