<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;

class InfoSistemController extends Controller
{
    public function index()
    {
        return view('public.info-sistem');
    }
}
