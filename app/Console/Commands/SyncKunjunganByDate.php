<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncStaging extends Command
{
    protected $signature = 'app:sync-kunjungan-by-date';
    protected $description = 'Sinkronisasi tabel dari DB operasional ke DB staging dengan metode append-only berdasarkan waktu MASUK';

    protected $tables = [
        'rs_pendaftaran.kunjungan' => [
            'target'  => 'p_kunjungan',
            'columns' => ['NOMOR','NOPEN', 'MASUK', 'RUANGAN', 'STATUS', 'REF']
        ],
    ];

    public function handle()
    {
        $this->info("=== Mulai Sync Data ke DB Staging (append only) ===");

        foreach ($this->tables as $source => $config) {
            [$connection, $table] = explode('.', $source);
            $this->syncTable($connection, $table, $config['target'], $config['columns']);
        }

        $this->info("=== Sync selesai ===");
    }

    private function syncTable($sourceConn, $sourceTable, $targetTable, $columns)
    {
        $this->info("Sync {$sourceConn}.{$sourceTable} → localreportsimrs.{$targetTable}");

        // Cari waktu MASUK terakhir di staging
        $lastMasuk = DB::table($targetTable)->max('MASUK');

        $this->info("  ⏳ MASUK terakhir di staging: " . ($lastMasuk ?? 'BELUM ADA'));

        // Ambil data baru yang MASUK > $lastMasuk
        $query = DB::connection($sourceConn)
            ->table($sourceTable)
            ->select($columns);

        if ($lastMasuk) {
            $query->where('MASUK', '>', $lastMasuk);
        }

        $data = $query->orderBy('MASUK')->get();

        if ($data->isEmpty()) {
            $this->info("  ✅ Tidak ada data baru");
            return;
        }

        // Insert batch (lebih efisien daripada loop insert per baris)
        DB::table($targetTable)->insert($data->map(fn($row) => (array) $row)->toArray());

        $this->info("  ✅ Berhasil insert " . count($data) . " baris baru ke {$targetTable}");
    }
}
