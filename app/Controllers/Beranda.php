<?php

namespace App\Controllers;

class Beranda extends BaseController
{
    public function index()
    {
        $pegawaiModel = new \App\Models\PegawaiModel();

        return view('beranda/index', [
            'title'        => 'Beranda — SINDAK',
            'totalPusat'   => $pegawaiModel->where('kategori', 'pusat')->countAllResults(),
            'totalDaerah'  => $pegawaiModel->where('kategori', 'daerah')->countAllResults(),
        ]);
    }
}
