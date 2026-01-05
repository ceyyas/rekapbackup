<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LaptopController extends Controller
{
    public function index()
    {
        return view('laptop.index');
    }
}
