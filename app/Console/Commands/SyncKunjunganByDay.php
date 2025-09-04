<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncKunjunganByDay extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-kunjungan-by-day';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    
    protected $tables = [
        'rs_pendaftaran.kunjungan' => [
            'target'  => 'p_kunjungan',
            'columns' => ['NOMOR','NOPEN', 'MASUK', 'RUANGAN', 'STATUS', 'REF']
        ],
    ];

    public function handle()
    {
        $this->info("=== Mulai Sync Data ke DB Staging (Truncate per Day) ===");

        foreach ($this->tables as $source => $config) {
            [$connection, $table] = explode('.', $source);
            $this->syncTable($connection, $table, $config['target'], $config['columns']);
        }

        $this->info("=== Sync selesai ===");
    }

    private function syncTable($sourceConn, $sourceTable, $targetTable, $columns)
{
    $this->info("Sync {$sourceConn}.{$sourceTable} → localreportsimrs.{$targetTable}");

    $today = \Carbon\Carbon::today()->toDateString();
    // $today = date('2025-09-02');

    // Ambil data dari operasional untuk hari ini
    $data = DB::connection($sourceConn)
        ->table($sourceTable)
        ->select($columns)
        ->whereDate('MASUK', $today)
        ->get();

    // Hapus data di staging untuk hari ini
    DB::table($targetTable)->whereDate('MASUK', $today)->delete();

    // Insert ulang
    foreach ($data as $row) {
        DB::table($targetTable)->insert((array) $row);
    }

    $this->info("  ✅ {$targetTable} selesai sync untuk {$today}");
}

}
