<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User2::where('username', 'sarifin')->first();
if (!$user) {
    echo "User sarifin not found!\n";
    exit(1);
}
\Illuminate\Support\Facades\Auth::login($user);

$routes = [
    '/kadiv/keuangan/dashboard' => 'Dashboard Keuangan',
    '/kadiv/keuangan/rekap-divisi' => 'Rekap Divisi Laba/Rugi',
    '/kadiv/keuangan/alokasi' => 'Alokasi Anggaran',
    '/kadiv/keuangan/permintaan-dana' => 'Permintaan Dana',
    '/kadiv/keuangan/pemasukan' => 'Kas Masuk',
    '/kadiv/keuangan/pengeluaran' => 'Kas Keluar',
    '/kadiv/keuangan/laporan-laba-rugi' => 'Verifikasi Laporan Laba Rugi',
    '/kadiv/pengajuan-dana' => 'Kadiv Pengajuan Dana',
    '/kadiv/riwayat-pengajuan-dana' => 'Kadiv Riwayat Pengajuan Dana',
    '/kadiv/laporan-laba-rugi' => 'Kadiv Laporan Laba Rugi',
];

echo "Testing routes for Kadiv Finance (sarifin)...\n";
$allOk = true;

foreach ($routes as $uri => $name) {
    try {
        $req = \Illuminate\Http\Request::create($uri, 'GET');
        $res = $app->handle($req);
        $status = $res->getStatusCode();
        $target = $res->headers->get('Location') ?? '';
        echo "[$status] $name ($uri) $target\n";
        if ($status !== 200) {
            $allOk = false;
        }
    } catch (\Throwable $e) {
        echo "[ERR] $name ($uri): " . $e->getMessage() . "\n";
        $allOk = false;
    }
}

if ($allOk) {
    echo "\nAll finance & kadiv routes returned 200 OK!\n";
} else {
    echo "\nSome routes failed!\n";
}
