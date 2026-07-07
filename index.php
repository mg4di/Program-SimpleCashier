<?php
include 'koneksi.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$user_id_sekarang = $_SESSION['user_id'];
$nama_kasir_sekarang = $_SESSION['nama_lengkap'];

if (isset($_POST['bayar'])) {
    $produk_ids = $_POST['produk_id'];   // array
    $jumlah_arr = $_POST['jumlah'];      // array

    $total_bayar = 0;
    $valid_items = [];
    $errors = [];

    // Validasi semua item terlebih dahulu
    foreach ($produk_ids as $i => $pid) {
        $pid = (int) $pid;
        $jml = (int) $jumlah_arr[$i];
        if ($pid <= 0 || $jml <= 0)
            continue;

        $res = mysqli_query($koneksi, "SELECT * FROM produk WHERE id = $pid");
        $p = mysqli_fetch_assoc($res);

        if (!$p) {
            $errors[] = "Produk ID $pid tidak ditemukan.";
            continue;
        }
        if ($p['stok'] < $jml) {
            $errors[] = "Stok <strong>" . htmlspecialchars($p['nama_produk']) . "</strong> tidak mencukupi (stok: {$p['stok']}, diminta: $jml).";
            continue;
        }

        $subtotal = $p['harga'] * $jml;
        $total_bayar += $subtotal;
        $valid_items[] = ['produk' => $p, 'jumlah' => $jml, 'subtotal' => $subtotal];
    }

    if (!empty($errors)) {
        $pesan_error = implode('<br>', $errors);
    } elseif (!empty($valid_items)) {
        // Buat satu nota penjualan
        mysqli_query($koneksi, "INSERT INTO penjualan (user_id, total_bayar) VALUES ('$user_id_sekarang', '$total_bayar')");
        $penjualan_id = mysqli_insert_id($koneksi);

        // Simpan semua item ke detail_penjualan & kurangi stok
        foreach ($valid_items as $item) {
            $pid = (int) $item['produk']['id'];
            $jml = (int) $item['jumlah'];
            $subtotal = (int) $item['subtotal'];
            $stok_baru = (int) $item['produk']['stok'] - $jml;

            mysqli_query($koneksi, "INSERT INTO detail_penjualan (penjualan_id, produk_id, jumlah, subtotal) VALUES ('$penjualan_id', '$pid', '$jml', '$subtotal')");
            mysqli_query($koneksi, "UPDATE produk SET stok = '$stok_baru' WHERE id = $pid");
        }

        echo "<script>alert('Transaksi Berhasil! Total: Rp " . number_format($total_bayar, 0, ',', '.') . "'); window.location='index.php';</script>";
        exit;
    }
}

$produk_master = mysqli_query($koneksi, "SELECT * FROM produk");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Sistem Kasir Utama</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/inter@5.0.0/index.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="min-h-screen bg-slate-50">

    <!-- Top Navigation -->
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
                    <h1 class="text-lg font-bold text-slate-800">Sistem Kasir</h1>
                    <p class="text-xs text-slate-500">Dashboard Utama</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <div class="text-right hidden sm:block">
                    <p class="text-xs text-slate-500">Login sebagai</p>
                    <p class="text-sm font-semibold text-slate-800">
                        <?php echo htmlspecialchars($nama_kasir_sekarang); ?>
                    </p>
                </div>
                <div
                    class="w-10 h-10 bg-indigo-100 text-indigo-700 rounded-full flex items-center justify-center font-bold">
                    <?php echo strtoupper(substr($nama_kasir_sekarang, 0, 1)); ?>
                </div>
                <a href="logout.php"
                    class="px-4 py-2 text-sm font-medium text-rose-600 hover:bg-rose-50 rounded-lg transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Keluar
                </a>
            </div>
        </div>
    </nav>

    <!-- Quick Nav -->
    <div class="max-w-7xl mx-auto px-6 py-4">
        <div class="flex flex-wrap gap-3">
            <a href="riwayat.php"
                class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 rounded-lg hover:border-indigo-400 hover:text-indigo-600 text-sm font-medium text-slate-700 transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Riwayat Transaksi
            </a>
            <a href="user.php"
                class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 rounded-lg hover:border-indigo-400 hover:text-indigo-600 text-sm font-medium text-slate-700 transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                User / Kasir
            </a>
            <a href="produk.php"
                class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 rounded-lg hover:border-emerald-400 hover:text-emerald-600 text-sm font-medium text-slate-700 transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                Kelola Produk
            </a>
        </div>
    </div>

    <main class="max-w-4xl mx-auto px-6 pb-12">

        <!-- Transaksi Kasir -->
        <section class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 bg-gradient-to-r from-indigo-50 to-transparent">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-indigo-100 text-indigo-600 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Input Transaksi</h2>
                        <p class="text-xs text-slate-500">Proses penjualan barang</p>
                    </div>
                </div>
            </div>

            <?php if (!empty($pesan_error)): ?>
                <div class="mx-6 mt-4 p-3 bg-rose-50 border border-rose-200 text-rose-700 text-sm rounded-lg">
                    <?php echo $pesan_error; ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="p-6 space-y-4">

                <!-- Daftar produk data untuk JS -->
                <script>
                    const daftarProduk = <?php
                    $tmp = mysqli_query($koneksi, "SELECT * FROM produk");
                    $arr = [];
                    while ($r = mysqli_fetch_assoc($tmp))
                        $arr[] = $r;
                    echo json_encode($arr);
                    ?>;
                </script>

                <!-- Keranjang item -->
                <div id="keranjang" class="space-y-3">
                    <!-- Baris item pertama -->
                    <div class="keranjang-item grid grid-cols-[1fr_100px_36px] gap-2 items-end">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Produk</label>
                            <select name="produk_id[]" required onchange="updateHarga(this)"
                                class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition text-sm">
                                <option value="">-- Pilih --</option>
                                <?php
                                $produk_pilihan = mysqli_query($koneksi, "SELECT * FROM produk");
                                while ($row = mysqli_fetch_assoc($produk_pilihan)): ?>
                                    <option value="<?php echo $row['id']; ?>" data-harga="<?php echo $row['harga']; ?>"
                                        data-stok="<?php echo $row['stok']; ?>">
                                        <?php echo htmlspecialchars($row['nama_produk']); ?>
                                        (Stok: <?php echo $row['stok']; ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Jumlah</label>
                            <input type="number" name="jumlah[]" min="1" value="1" required onchange="hitungTotal()"
                                class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition text-sm">
                        </div>
                        <button type="button" onclick="hapusBaris(this)"
                            class="h-[38px] w-9 flex items-center justify-center text-slate-400 hover:text-rose-500 hover:bg-rose-50 rounded-lg transition border border-slate-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Tombol tambah baris -->
                <button type="button" onclick="tambahBaris()"
                    class="w-full py-2 border-2 border-dashed border-slate-200 hover:border-indigo-400 hover:text-indigo-600 text-slate-400 text-sm font-medium rounded-lg transition flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Tambah Produk Lain
                </button>

                <!-- Ringkasan total -->
                <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-slate-600">Estimasi Total</span>
                        <span id="estimasi-total" class="text-lg font-bold text-indigo-700">Rp 0</span>
                    </div>
                </div>

                <button type="submit" name="bayar"
                    class="w-full py-3 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white font-semibold rounded-lg shadow-md shadow-indigo-500/20 transition-all hover:shadow-lg flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Proses Bayar
                </button>
            </form>
        </section>
    </main>
    <script>
        // Ambil template baris dari baris pertama
        function tambahBaris() {
            const keranjang = document.getElementById('keranjang');
            const template = keranjang.querySelector('.keranjang-item');
            const clone = template.cloneNode(true);

            // Reset nilai clone
            clone.querySelector('select').value = '';
            clone.querySelector('input[type=number]').value = 1;

            keranjang.appendChild(clone);
            hitungTotal();
        }

        function hapusBaris(btn) {
            const keranjang = document.getElementById('keranjang');
            const items = keranjang.querySelectorAll('.keranjang-item');
            if (items.length <= 1) return; // minimal 1 baris
            btn.closest('.keranjang-item').remove();
            hitungTotal();
        }

        function updateHarga(select) {
            hitungTotal();
        }

        function hitungTotal() {
            const items = document.querySelectorAll('.keranjang-item');
            let total = 0;
            items.forEach(item => {
                const sel = item.querySelector('select');
                const qty = parseInt(item.querySelector('input[type=number]').value) || 0;
                if (sel.value) {
                    const opt = sel.options[sel.selectedIndex];
                    const harga = parseInt(opt.dataset.harga) || 0;
                    total += harga * qty;
                }
            });
            document.getElementById('estimasi-total').textContent =
                'Rp ' + total.toLocaleString('id-ID');
        }
    </script>
</body>

</html>