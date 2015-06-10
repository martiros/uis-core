<?php

$uisControllers = 'UIS\Core\Controllers\Api\\';

Route::get('/api/core/xsrfRefresh', ['uses' => $uisControllers.'UtilController@xsrfRefresh']);

Route::get('/api/core/config', ['uses' => $uisControllers.'ConfigController@config']);
Route::get('/api/core/dictionary/mobile', ['uses' => $uisControllers.'ConfigController@dictionary']);

//

Route::post('/api/media/imageUploader/upload', ['uses' => $uisControllers.'ImageUploaderController@upload']);
Route::post('/api/media/fileUploader/upload', ['uses' => $uisControllers.'FileUploaderController@upload']);
