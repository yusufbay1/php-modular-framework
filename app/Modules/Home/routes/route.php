<?php

use App\Modules\Home\Controllers\HomeController;
use Router\Route;

Route::get('/', [HomeController::class, 'index']);