<?php

use Core\Http\Request;
use Router\Route;

Route::get('/hello/{user}', function (Request $request) {
    return 'Hello,  with ID: !' . $request->route('user');
})->whereAlpha('user');

Route::pathNotFound(function () {
    return '404 Not Found';
});