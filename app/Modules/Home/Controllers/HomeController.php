<?php

namespace App\Modules\Home\Controllers;

use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    public function index(): string
    {
        return $this->view('index');
    }
}