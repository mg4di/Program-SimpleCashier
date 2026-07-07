<?php
include 'koneksi.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$uid = (int) $_SESSION['user_id'];
$nama_kasir_login = $_SESSION['nama_lengkap'];

if (isset($_GET['hapus_nota'])) {
    $hapus_id = (int) $_GET['hapus_nota'];
    // Pastikan nota ini milik user yang sedang login
    $cek = mysqli_fetch_row(mysqli_query($koneksi, "SELECT id FROM penjualan WHERE id = $hapus_id AND user_id = $uid"));
    if ($cek) {
        // Kembalikan stok semua produk dalam nota ini sebelum dihapus
        $detail = mysqli_query($koneksi, "SELECT produk_id, jumlah FROM detail_penjualan WHERE penjualan_id = $hapus_id");
        while ($item = mysqli_fetch_assoc($detail)) {
            $pid = (int) $item['produk_id'];
            $jml = (int) $item['jumlah'];
            mysqli_query($koneksi, "UPDATE produk SET stok = stok + $jml WHERE id = $pid");
        }
        // Hapus nota (detail_penjualan ikut terhapus via ON DELETE CASCADE)
        mysqli_query($koneksi, "DELETE FROM penjualan WHERE id = $hapus_id");
    }
    header("Location: riwayat.php");
    exit;
}

if (isset($_GET['hapus_semua'])) {
    mysqli_query($koneksi, "DELETE FROM penjualan");
    header("Location: riwayat.php");
    exit;
}

$sql = "SELECT penjualan.id AS nota_id, penjualan.user_id AS penjualan_user_id, penjualan.tanggal,
               penjualan.total_bayar, user.nama_lengkap AS nama_kasir,
               produk.nama_produk, detail_penjualan.jumlah, detail_penjualan.subtotal
        FROM detail_penjualan
        JOIN penjualan ON detail_penjualan.penjualan_id = penjualan.id
        JOIN produk ON detail_penjualan.produk_id = produk.id
        JOIN user ON penjualan.user_id = user.id
        ORDER BY penjualan.tanggal DESC, penjualan.id DESC";
$query = mysqli_query($koneksi, $sql);

// Kelompokkan hasil per nota_id
$notas = [];
while ($row = mysqli_fetch_assoc($query)) {
    $nid = $row['nota_id'];
    if (!isset($notas[$nid])) {
        $notas[$nid] = [
            'nota_id' => $nid,
            'penjualan_user_id' => $row['penjualan_user_id'],
            'tanggal' => $row['tanggal'],
            'nama_kasir' => $row['nama_kasir'],
            'total_bayar' => $row['total_bayar'],
            'items' => [],
        ];
    }
    $notas[$nid]['items'][] = [
        'nama_produk' => $row['nama_produk'],
        'jumlah' => $row['jumlah'],
        'subtotal' => $row['subtotal'],
    ];
}

// Total semua subtotal (semua kasir)
$row_total_semua = mysqli_fetch_row(mysqli_query($koneksi, "SELECT IFNULL(SUM(subtotal), 0) FROM detail_penjualan"));
$total_semua = $row_total_semua[0];

// Total subtotal milik user yang sedang login
$row_total_saya = mysqli_fetch_row(mysqli_query(
    $koneksi,
    "SELECT IFNULL(SUM(detail_penjualan.subtotal), 0)
     FROM detail_penjualan
     JOIN penjualan ON detail_penjualan.penjualan_id = penjualan.id
     WHERE penjualan.user_id = $uid"
));
$total_saya = $row_total_saya[0];
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Riwayat Penjualan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/inter@5.0.0/index.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="min-h-screen bg-slate-50">

    <nav class="bg-white border-b border-slate-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div
                    class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-indigo-700 rounded-xl flex items-center justify-center shadow-md">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-slate-800">Riwayat Penjualan</h1>
                    <p class="text-xs text-slate-500">Catatan transaksi</p>
                </div>
            </div>
            <a href="index.php"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 rounded-lg transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Dashboard
            </a>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-6 py-8">

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Riwayat Transaksi</h2>
                <p class="text-sm text-slate-500 mt-1">Semua catatan penjualan yang pernah dilakukan</p>
            </div>
            <a href="riwayat.php?hapus_semua=1"
                onclick="return confirm('Yakin ingin menghapus semua riwayat transaksi? Tindakan ini akan MENGHAPUS SEMUA RIWAYAT TRANSAKSI!')"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-sm font-semibold rounded-lg transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Hapus Semua Riwayat
            </a>
        </div>

        <!-- Kartu Ringkasan Total -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex items-center gap-4">
                <div
                    class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Total Semua Transaksi</p>
                    <p class="text-2xl font-bold text-emerald-700 mt-0.5">
                        Rp
                        <?php echo number_format($total_semua, 0, ',', '.'); ?>
                    </p>
                    <p class="text-xs text-slate-400 mt-0.5">Dari seluruh kasir</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-indigo-100 shadow-sm p-6 flex items-center gap-4">
                <div
                    class="w-12 h-12 bg-indigo-100 text-indigo-600 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Total Transaksi Saya</p>
                    <p class="text-2xl font-bold text-indigo-700 mt-0.5">
                        Rp
                        <?php echo number_format($total_saya, 0, ',', '.'); ?>
                    </p>
                    <p class="text-xs text-slate-400 mt-0.5">
                        <?php echo htmlspecialchars($nama_kasir_login); ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                Nota</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                Tanggal</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                Kasir</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                Detail Produk</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                Total</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php $no = 1;
                        foreach ($notas as $nota): ?>
                            <tr class="hover:bg-slate-50 transition align-top">
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex px-2.5 py-1 bg-indigo-50 text-indigo-700 text-xs font-bold rounded-md">
                                        #<?php echo $no++; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600 whitespace-nowrap">
                                    <?php echo $nota['tanggal']; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="w-8 h-8 bg-indigo-100 text-indigo-700 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0">
                                            <?php echo strtoupper(substr($nota['nama_kasir'], 0, 1)); ?>
                                        </div>
                                        <span class="text-sm text-slate-800 font-medium whitespace-nowrap">
                                            <?php echo htmlspecialchars($nota['nama_kasir']); ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="space-y-1">
                                        <?php foreach ($nota['items'] as $item): ?>
                                            <div class="flex items-center justify-between gap-4 text-sm">
                                                <span class="text-slate-800 font-medium">
                                                    <?php echo htmlspecialchars($item['nama_produk']); ?>
                                                </span>
                                                <span class="text-slate-500 whitespace-nowrap">
                                                    <?php echo $item['jumlah']; ?> &times;
                                                    <span class="text-emerald-700 font-semibold">
                                                        Rp
                                                        <?php echo number_format($item['subtotal'] / $item['jumlah'], 0, ',', '.'); ?>
                                                    </span>
                                                    = <span class="font-semibold text-slate-700">Rp
                                                        <?php echo number_format($item['subtotal'], 0, ',', '.'); ?></span>
                                                </span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm font-bold text-emerald-700 whitespace-nowrap">
                                    Rp <?php echo number_format($nota['total_bayar'], 0, ',', '.'); ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if ($nota['penjualan_user_id'] == $uid): ?>
                                        <a href="riwayat.php?hapus_nota=<?php echo $nota['nota_id']; ?>"
                                            onclick="return confirm('Hapus nota #<?php echo $no - 1; ?>?')"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-semibold rounded-md transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            Hapus
                                        </a>
                                    <?php else: ?>
                                        <span class="text-xs text-slate-400 italic">Bukan milik Anda</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>

</html>