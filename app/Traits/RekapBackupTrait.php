<?php 

namespace App\Traits; 
use Illuminate\Support\Facades\DB; 
use App\Models\Perusahaan;
use App\Models\Departemen;
use App\Models\Inventori;
use App\Models\InventoriHistory;
use App\Models\RekapBackup;
use App\Models\Stok;

trait RekapBackupTrait 
{
    public function getDepartemenQuery($perusahaanId, $periode)
    {
        return DB::table('departemen')
            ->leftJoin('inventori', 'inventori.departemen_id', '=', 'departemen.id')
            ->leftJoin(DB::raw("
                (
                    SELECT ih.*
                    FROM inventori_history ih
                    JOIN (
                        SELECT inventori_id, MIN(effective_date) as last_date
                        FROM inventori_history
                        WHERE effective_date >= STR_TO_DATE('$periode','%Y-%m-%d')
                        GROUP BY inventori_id
                    ) latest
                    ON ih.inventori_id = latest.inventori_id
                    AND ih.effective_date = latest.last_date
                ) as snapshot
            "), 'snapshot.inventori_id', '=', 'inventori.id')
            ->leftJoin('rekap_backup', function ($join) use ($periode) {
                $join->on('rekap_backup.inventori_id', '=', 'inventori.id')
                    ->where('rekap_backup.periode', $periode);
            })
            ->where('departemen.perusahaan_id', $perusahaanId)
            ->where(function($q) use ($periode) {
                $periodeDate = DB::raw("STR_TO_DATE('$periode','%Y-%m-%d')");
                $q->where(function($sub) use ($periodeDate) {
                    $sub->where('inventori.status', 'active')
                        ->orWhere($periodeDate, '<', DB::raw('inventori.updated_at'));
                })
                ->where($periodeDate, '>=', DB::raw('inventori.created_at')); 
            })
            ->select(
                'departemen.id',
                'departemen.nama_departemen',
                DB::raw('MAX(inventori.id) as inventori_id'),
                DB::raw('COALESCE(SUM(rekap_backup.size_data), 0) AS size_data'),
                DB::raw('COALESCE(SUM(rekap_backup.size_email), 0) AS size_email'),
                DB::raw('COALESCE(SUM(rekap_backup.size_data + rekap_backup.size_email), 0) AS total_size'),
                DB::raw('COALESCE(SUM(rekap_backup.jumlah_cd700), 0) AS jumlah_cd700'),
                DB::raw('COALESCE(SUM(rekap_backup.jumlah_dvd47), 0) AS jumlah_dvd47'),
                DB::raw('COALESCE(SUM(rekap_backup.jumlah_dvd85), 0) AS jumlah_dvd85'),
                DB::raw('COALESCE(SUM(rekap_backup.jumlah_cd700 + rekap_backup.jumlah_dvd47 + rekap_backup.jumlah_dvd85), 0) AS total_cd_dvd'),
                DB::raw("MAX(CASE 
                            WHEN snapshot.id IS NOT NULL 
                                AND STR_TO_DATE('$periode','%Y-%m-%d') < snapshot.effective_date 
                            THEN snapshot.hostname 
                            ELSE inventori.hostname 
                        END) as hostname"),
                DB::raw("MAX(CASE 
                            WHEN snapshot.id IS NOT NULL 
                                AND STR_TO_DATE('$periode','%Y-%m-%d') < snapshot.effective_date 
                            THEN snapshot.username 
                            ELSE inventori.username 
                        END) as username"),
                DB::raw("MAX(CASE 
                            WHEN snapshot.id IS NOT NULL 
                                AND STR_TO_DATE('$periode','%Y-%m-%d') < snapshot.effective_date 
                            THEN snapshot.email 
                            ELSE inventori.email 
                        END) as email"),
                DB::raw("MAX(CASE 
                            WHEN snapshot.id IS NOT NULL 
                                AND STR_TO_DATE('$periode','%Y-%m-%d') < snapshot.effective_date 
                            THEN snapshot.kategori 
                            ELSE inventori.kategori 
                        END) as kategori"),
                DB::raw("MAX(CASE 
                            WHEN snapshot.id IS NOT NULL 
                                AND STR_TO_DATE('$periode','%Y-%m-%d') < snapshot.effective_date 
                            THEN snapshot.status 
                            ELSE inventori.status 
                        END) as status"),
                DB::raw("
                    CASE
                        WHEN COUNT(rekap_backup.id) = 0 THEN 'pending'
                        WHEN SUM(CASE WHEN rekap_backup.status = 'completed' THEN 1 ELSE 0 END) = COUNT(rekap_backup.id)
                            AND COALESCE(SUM(rekap_backup.size_data),0) > 0
                            AND (
                                (SUM(CASE WHEN inventori.email IS NOT NULL AND inventori.email <> '' THEN 1 ELSE 0 END) > 0
                                AND COALESCE(SUM(rekap_backup.size_email),0) > 0)
                                OR SUM(CASE WHEN inventori.email IS NOT NULL AND inventori.email <> '' THEN 1 ELSE 0 END) = 0
                            )
                        THEN 'completed'
                        ELSE 'partial'
                    END AS status_backup
                "),
                DB::raw("
                    CASE
                        WHEN COUNT(rekap_backup.id) = 0 THEN 'data belum di backup'
                        WHEN SUM(CASE WHEN rekap_backup.status = 'completed' THEN 1 ELSE 0 END) < COUNT(rekap_backup.id) 
                            THEN 'proses backup'
                        WHEN SUM(CASE WHEN rekap_backup.status = 'completed' THEN 1 ELSE 0 END) = COUNT(rekap_backup.id) 
                            AND COALESCE(SUM(rekap_backup.size_data),0) > 0
                            AND (
                                (SUM(CASE WHEN inventori.email IS NOT NULL AND inventori.email <> '' THEN 1 ELSE 0 END) > 0
                                AND COALESCE(SUM(rekap_backup.size_email),0) > 0)
                                OR SUM(CASE WHEN inventori.email IS NOT NULL AND inventori.email <> '' THEN 1 ELSE 0 END) = 0
                            )
                            AND (COALESCE(SUM(rekap_backup.jumlah_cd700),0) = 0 
                                AND COALESCE(SUM(rekap_backup.jumlah_dvd47),0) = 0 
                                AND COALESCE(SUM(rekap_backup.jumlah_dvd85),0) = 0)
                            THEN 'file di main folder aman'
                        WHEN SUM(CASE WHEN rekap_backup.status = 'completed' THEN 1 ELSE 0 END) = COUNT(rekap_backup.id) 
                            AND COALESCE(SUM(rekap_backup.size_data),0) > 0
                            AND (
                                (SUM(CASE WHEN inventori.email IS NOT NULL AND inventori.email <> '' THEN 1 ELSE 0 END) > 0
                                AND COALESCE(SUM(rekap_backup.size_email),0) > 0)
                                OR SUM(CASE WHEN inventori.email IS NOT NULL AND inventori.email <> '' THEN 1 ELSE 0 END) = 0
                            )
                            AND (COALESCE(SUM(rekap_backup.jumlah_cd700),0) > 0 
                                OR COALESCE(SUM(rekap_backup.jumlah_dvd47),0) > 0 
                                OR COALESCE(SUM(rekap_backup.jumlah_dvd85),0) > 0)
                            THEN 'file di cd aman'
                        ELSE 'proses backup'
                    END AS status_data
                ")
            )
            ->groupBy('departemen.id','departemen.nama_departemen')
            ->orderBy('departemen.nama_departemen');
    }

    public function getInventoriDetailQuery($departemenId, $periode)
    {
        return DB::table('inventori')
            ->leftJoin(DB::raw("
                (
                    SELECT ih.*
                    FROM inventori_history ih
                    JOIN (
                        SELECT inventori_id, MIN(effective_date) as last_date
                        FROM inventori_history
                        WHERE effective_date >= STR_TO_DATE('$periode','%Y-%m-%d')
                        GROUP BY inventori_id
                    ) latest
                    ON ih.inventori_id = latest.inventori_id
                    AND ih.effective_date = latest.last_date
                ) as snapshot
            "), 'snapshot.inventori_id', '=', 'inventori.id')
            ->leftJoin('rekap_backup', function ($join) use ($periode) {
                $join->on('rekap_backup.inventori_id', '=', 'inventori.id')
                    ->where('rekap_backup.periode', $periode);
            })
            ->where('inventori.departemen_id', $departemenId)
            ->where(function($q) use ($periode) {
                $periodeDate = DB::raw("STR_TO_DATE('$periode','%Y-%m-%d')");
                $q->where(function($sub) use ($periodeDate) {
                    $sub->where('inventori.status', 'active')
                        ->orWhere($periodeDate, '<', DB::raw('inventori.updated_at'));
                })
                ->where($periodeDate, '>=', DB::raw('inventori.created_at')); 
            })
            ->select(
                'inventori.id',
                DB::raw("CASE 
                    WHEN snapshot.id IS NOT NULL 
                        AND '$periode' < snapshot.effective_date 
                    THEN snapshot.hostname 
                    ELSE inventori.hostname 
                END as hostname"),
                DB::raw("CASE 
                    WHEN snapshot.id IS NOT NULL 
                        AND '$periode' < snapshot.effective_date 
                    THEN snapshot.username 
                    ELSE inventori.username 
                END as username"),
                DB::raw("CASE 
                    WHEN snapshot.id IS NOT NULL 
                        AND '$periode' < snapshot.effective_date 
                    THEN snapshot.email 
                    ELSE inventori.email 
                END as email"),
                DB::raw("CASE 
                    WHEN snapshot.id IS NOT NULL 
                        AND '$periode' < snapshot.effective_date 
                    THEN snapshot.kategori 
                    ELSE inventori.kategori 
                END as kategori"),
                DB::raw("CASE 
                    WHEN snapshot.id IS NOT NULL 
                        AND '$periode' < snapshot.effective_date 
                    THEN snapshot.status 
                    ELSE inventori.status 
                END as status"),
                DB::raw('COALESCE(SUM(rekap_backup.size_data), 0) AS size_data'),
                DB::raw('COALESCE(SUM(rekap_backup.size_email), 0) AS size_email'),
                DB::raw('COALESCE(SUM(rekap_backup.size_data + rekap_backup.size_email), 0) AS total_size')
            )
            ->groupBy('inventori.id','hostname','username','email','kategori', 'status')
            ->orderBy('hostname');
    }
}