<?php


$uisControllers = 'UIS\Core\Controllers\Api\\';

Route::get('/api/core/config', array('uses' => $uisControllers.'ConfigController@config'));
Route::get('/api/core/dictionary/mobile', array('uses' => $uisControllers.'ConfigController@dictionary'));
