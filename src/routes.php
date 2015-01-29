<?php

$uisControllers = 'UIS\Core\Controllers\Api\\';

Route::get('/api/core/xsrfRefresh', array('uses' => $uisControllers.'UtilController@xsrfRefresh'));

Route::get('/api/core/config', array('uses' => $uisControllers.'ConfigController@config'));
Route::get('/api/core/dictionary/mobile', array('uses' => $uisControllers.'ConfigController@dictionary'));

//

Route::post('/api/media/imageUploader/upload', array('uses' => $uisControllers.'ImageUploaderController@upload'));
Route::post('/api/media/fileUploader/upload', array('uses' => $uisControllers.'FileUploaderController@upload'));

