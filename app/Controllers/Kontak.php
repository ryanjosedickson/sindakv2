<?php

namespace App\Controllers;

class Kontak extends BaseController
{
    public function index()
    {
        return view('kontak/index', ['title' => 'Kontak — SINDAK']);
    }
}
