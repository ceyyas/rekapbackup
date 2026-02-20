<?php

namespace Database\Seeders;

use App\Models\Departemen;
use App\Models\Perusahaan;
use Illuminate\Database\Seeder;

class DepartemenSeeder extends Seeder
{
    public function run(): void
    {
        $mapping = [
            'Murni Cahaya Pratama' => [
                'Sales', 'RND', 'PE', 'CI', 'PPC', 'Produksi', 'QC',
                'Gudang', 'Utility', 'HRD', 'GA', 'Management Development',
                'Accounting Pabrik', 'TS', 'Care', 'K3',
                'Accounting Bintaro', 'Purchasing Bintaro', 'Marketing'
            ],

            'Mega Karya Mandiri' => [
                'Purchasing', 'Sales', 'Accounting Pabrik', 'QA', 'PPC',
                'Produksi', 'Management Development', 'RND Admin',
                'RND Desain', 'Gudang', 'Injection & Hardcoat',
                'HRD-GA', 'CI', 'Maintenance',
                'Accounting Bintaro', 'Purchasing Bintaro', 'Marketing'
            ],

            'Mekar Karya Pratama' => [
                'Accfin', 'Accfin-adm', 'Gudang', 'HRDGA', 'Painting',
                'Sales', 'Service & Sparepart', 'MKP Cibinong',
                'Accounting Bintaro', 'Purchasing Bintaro',
                'Service & Sparepart Bintaro', 'Kacab Bogor & Cibinong'
            ],

            'Putra Prima Grosia' => [
                'Accounting Pabrik', 'Gudang', 'Management Development',
                'Sales', 'Paint & Care',
                'Accounting Bintaro', 'Purchasing Bintaro', 'Marketing'
            ],

            'Prima Panca Murya' => [
                'Gudang', 'HRD-GA', 'Operasional', 'Accounting Pabrik',
                'Management Development', 'Marketing Holding',
                'Sales', 'Accounting Bintaro'
            ],
        ];

        foreach ($mapping as $namaPerusahaan => $departemens) {
            $perusahaan = Perusahaan::where('nama_perusahaan', $namaPerusahaan)->first();

            if (!$perusahaan) {
                continue;
            }

            foreach ($departemens as $dept) {
                Departemen::create([
                    'nama_departemen' => $dept,
                    'perusahaan_id' => $perusahaan->id,
                ]);
            }
        }
    }
}
