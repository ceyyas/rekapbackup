<?php

namespace Database\Seeders;

use App\Models\Perusahaan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PerusahaanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $perusahaan = Perusahaan::insert([
            ['nama_perusahaan' => 'Murni Cahaya Pratama'],
            ['nama_perusahaan' => 'Mega Karya Mandiri'],
            ['nama_perusahaan' => 'Mekar Karya Pratama'],
            ['nama_perusahaan' => 'Putra Prima Grosia'],
            ['nama_perusahaan' => 'Prima Panca Murya'],
        ]);

    }
}
