<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncStaging extends Command
{
    protected $signature = 'app:sync-staging';
    protected $description = 'Sinkronisasi tabel dari DB operasional ke DB staging dengan prefix dan kolom tertentu';

    /**
     * Mapping tabel & kolom yang diambil
     * format: 'connection.table' => ['target' => 'nama_tabel_staging', 'columns' => ['kolom1', 'kolom2']]
     */
    protected $tables = [
        // aplikasi
        // 'rs_aplikasi.instansi' => [
        //     'target'  => 'a_instansi',
        //     'columns' => ['PPK']
        // ],

        // // master
        // 'rs_master.ikatan_kerja_sama' => [
        //     'target'  => 'm_ikatan_kerja_sama',
        //     'columns' => ['ID']
        // ],
        // 'rs_master.kartu_asuransi_pasien' => [
        //     'target'  => 'm_kartu_asuransi_pasien',
        //     'columns' => ['NORM', 'JENIS']
        // ],
        // 'rs_master.pasien' => [
        //     'target'  => 'm_pasien',
        //     'columns' => ['NORM', 'TANGGAL', 'JENIS_KELAMIN']
        // ],
        // 'rs_master.ppk' => [
        //     'target'  => 'm_ppk',
        //     'columns' => ['ID', 'NAMA', 'ALAMAT']
        // ],
        // 'rs_master.referensi' => [
        //     'target'  => 'm_referensi',
        //     'columns' => ['ID', 'DESKRIPSI', 'JENIS']
        // ],
        // 'rs_master.ruangan' => [
        //     'target'  => 'm_ruangan',
        //     'columns' => ['ID', 'JENIS', 'JENIS_KUNJUNGAN']
        // ],
        // 'rs_master.tanggal' => [
        //     'target'  => 'm_tanggal',
        //     'columns' => ['TANGGAL', 'NAMAHARI']
        // ],

        // pendaftaran
        'rs_pendaftaran.kunjungan' => [
            'target'  => 'p_kunjungan',
            'columns' => ['NOMOR','NOPEN', 'MASUK', 'RUANGAN', 'STATUS', 'REF']
        ],
        // 'rs_pendaftaran.pendaftaran' => [
        //     'target'  => 'p_pendaftaran',
        //     'columns' => ['NOMOR', 'NORM', 'STATUS', 'RUJUKAN', 'TANGGAL']
        // ],
        // 'rs_pendaftaran.penjamin' => [
        //     'target'  => 'p_penjamin',
        //     'columns' => ['NOPEN', 'JENIS']
        // ],
        // 'rs_pendaftaran.surat_rujukan_pasien' => [
        //     'target'  => 'p_surat_rujukan_pasien',
        //     'columns' => ['ID', 'STATUS', 'PPK']
        // ],
        // 'rs_pendaftaran.tujuan_pasien' => [
        //     'target'  => 'p_tujuan_pasien',
        //     'columns' => ['NOPEN', 'RUANGAN']
        // ],
    ];

    public function handle()
    {
        $this->info("=== Mulai Sync Data ke DB Staging ===");

        foreach ($this->tables as $source => $config) {
            [$connection, $table] = explode('.', $source);
            $this->syncTable($connection, $table, $config['target'], $config['columns']);
        }

        $this->info("=== Sync selesai ===");
    }

    private function syncTable($sourceConn, $sourceTable, $targetTable, $columns)
    {
        $this->info("Sync {$sourceConn}.{$sourceTable} → localreportsimrs.{$targetTable}");

        // Ambil data hanya kolom yang dibutuhkan
        $data = DB::connection($sourceConn)
            ->table($sourceTable)
            ->select($columns)
            ->get();

        // Kosongkan tabel staging
        DB::table($targetTable)->truncate();

        // Insert data baru
        foreach ($data as $row) {
            DB::table($targetTable)->insert((array) $row);
        }

        $this->info("  ✅ {$targetTable} selesai");
    }
}
