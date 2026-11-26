<?php
require_once 'functions.php';
batasiAksesPelanggan();

$id_transaksi = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id_transaksi <= 0) {
    http_response_code(400);
    exit('Nomor transaksi tidak valid.');
}

$sql = "
    SELECT t.*, p.nama_pelanggan
    FROM tbl_transaksi t
    LEFT JOIN tbl_pelanggan p ON p.id_pelanggan = t.id_pelanggan
    WHERE t.id_transaksi = $id_transaksi
    LIMIT 1
";
$q = mysqli_query($conn, $sql);
if (!$q || mysqli_num_rows($q) === 0) {
    http_response_code(404);
    exit('Transaksi tidak ditemukan.');
}
$trx = mysqli_fetch_assoc($q);

if (($trx['metode_pembayaran'] ?? '') !== 'QRIS DANA MANUAL') {
    http_response_code(403);
    exit('Transaksi ini bukan pembayaran QRIS DANA manual.');
}

$nominal = (float) ($trx['total_belanja'] ?? 0);
$ref = $trx['ref_pembayaran'] ?? ('QRIS-MANUAL-' . $id_transaksi);
$status = $trx['status_pembayaran'] ?? 'Pending';
function rupiahQrisManual($angka) {
    return 'Rp ' . number_format((float)$angka, 0, ',', '.');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran QRIS DANA Manual - TokoKita</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-100 text-slate-800">
    <main class="mx-auto max-w-3xl px-4 py-8">
        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-xl">
            <div class="bg-gradient-to-r from-sky-700 to-blue-600 px-6 py-6 text-white">
                <p class="text-xs font-bold uppercase tracking-widest text-sky-100">TokoKita • Pembayaran Manual</p>
                <h1 class="mt-1 text-2xl font-black">QRIS DANA</h1>
                <p class="mt-1 text-sm text-sky-100">Scan kode QR di bawah menggunakan aplikasi pembayaran yang mendukung QRIS.</p>
            </div>

            <div class="space-y-5 p-5 sm:p-7">
                <div class="rounded-2xl border border-sky-200 bg-sky-50 p-4 text-center">
                    <p class="text-xs font-bold uppercase tracking-wide text-sky-800">Total yang harus dibayar</p>
                    <p class="mt-1 text-3xl font-black text-slate-900"><?= htmlspecialchars(rupiahQrisManual($nominal)) ?></p>
                    <p class="mt-2 text-xs text-slate-600">Nominal ini diambil langsung dari total transaksi kasir.</p>
                </div>

                <div class="mx-auto max-w-md rounded-2xl border border-slate-200 bg-white p-3 text-center shadow-sm">
                    <img src="uploads/pembayaran/qris.jpg"
                         alt="QRIS YALAAA"
                         class="mx-auto block h-auto w-full max-w-sm object-contain"
                         onerror="this.style.display='none';document.getElementById('qrisError').classList.remove('hidden');">
                    <div id="qrisError" class="hidden rounded-xl border-2 border-dashed border-amber-300 bg-amber-50 p-6 text-sm font-semibold text-amber-800">
                        Foto QRIS tidak ditemukan. Pastikan file berada di uploads/pembayaran/qris.jpg.
                    </div>
                    <p class="mt-3 text-sm font-black text-slate-800">YALAAA</p>
                    <p class="text-xs text-slate-500">QRIS pembayaran toko</p>
                </div>

                <div class="grid gap-3 rounded-2xl bg-slate-50 p-4 text-sm sm:grid-cols-2">
                    <div>
                        <p class="text-xs text-slate-500">Nomor transaksi</p>
                        <p class="font-bold">#TRX-<?= (int)$id_transaksi ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500">Referensi pembayaran</p>
                        <p class="break-all font-mono text-xs font-bold"><?= htmlspecialchars($ref) ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500">Pelanggan</p>
                        <p class="font-bold"><?= htmlspecialchars($trx['nama_pelanggan'] ?? '-') ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500">Status</p>
                        <p class="font-bold text-amber-700"><?= htmlspecialchars($status) ?></p>
                    </div>
                </div>

                <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                    <p class="font-black">Petunjuk pembayaran</p>
                    <ol class="mt-2 list-decimal space-y-1 pl-5 text-xs leading-5">
                        <li>Buka aplikasi DANA atau aplikasi pembayaran yang mendukung QRIS.</li>
                        <li>Pilih menu Scan/QRIS, lalu pindai gambar QR di atas.</li>
                        <li>Masukkan nominal tepat <strong><?= htmlspecialchars(rupiahQrisManual($nominal)) ?></strong> jika aplikasi meminta nominal.</li>
                        <li>Selesaikan pembayaran dan tunjukkan bukti pembayaran kepada kasir.</li>
                        <li>Status transaksi tetap Pending sampai kasir/admin memeriksa pembayaran.</li>
                    </ol>
                </div>

                <div class="flex flex-col gap-2 sm:flex-row">
                    <button onclick="window.print()" class="flex-1 rounded-xl bg-slate-800 px-4 py-3 text-sm font-bold text-white hover:bg-slate-700">Cetak Halaman</button>
                    <a href="kasir_pos_qris_manual.php" class="flex-1 rounded-xl border border-slate-300 px-4 py-3 text-center text-sm font-bold text-slate-700 hover:bg-slate-50">Kembali ke Kasir POS</a>
                </div>
                <p class="text-center text-[11px] leading-5 text-slate-500">
                    Catatan: QR pada gambar adalah QRIS statis. Nominal transaksi ditampilkan otomatis oleh TokoKita, tetapi nominal tidak tertanam otomatis di dalam QR.
                </p>
            </div>
        </section>
    </main>
</body>
</html>
