<?php

namespace App\Controllers;

class Akun extends BaseController
{
    public function index()
    {
        return view('akun/index', ['title' => 'Akun Saya — SINDAK']);
    }
}
