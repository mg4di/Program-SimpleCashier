<?php
include 'koneksi.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (isset($_POST['tambah_produk'])) {
    $nama = $_POST['nama_produk'];
    $harga = (int) $_POST['harga'];
    $stok = (int) $_POST['stok'];
    mysqli_query($koneksi, "INSERT INTO produk (nama_produk, harga, stok) VALUES ('$nama', '$harga', '$stok')");
    header("Location: produk.php");
    exit;
}

$produk_list = mysqli_query($koneksi, "SELECT * FROM produk");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Kelola Produk</title>
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
                    class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-emerald-700 rounded-xl flex items-center justify-center shadow-md">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-slate-800">Kelola Produk</h1>
                    <p class="text-xs text-slate-500">Manajemen stok barang</p>
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

    <main class="max-w-4xl mx-auto px-6 py-8 space-y-6">

        <div>
            <h2 class="text-2xl font-bold text-slate-800">Kelola Produk</h2>
            <p class="text-sm text-slate-500 mt-1">Tambah, edit, dan hapus produk yang tersedia</p>
        </div>

        <!-- Form Tambah Produk -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 bg-gradient-to-r from-emerald-50 to-transparent">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-800">Tambah Produk Baru</h3>
                        <p class="text-xs text-slate-500">Isi form untuk menambah produk</p>
                    </div>
                </div>
            </div>
            <form action="" method="POST" class="p-6">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <input type="text" name="nama_produk" placeholder="Nama Produk" required
                        class="px-4 py-2.5 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none transition text-sm">
                    <input type="number" name="harga" placeholder="Harga" min="0" required
                        class="px-4 py-2.5 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none transition text-sm">
                    <input type="number" name="stok" placeholder="Stok" min="0" required
                        class="px-4 py-2.5 bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent outline-none transition text-sm">
                </div>
                <button type="submit" name="tambah_produk"
                    class="mt-4 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition shadow-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Tambah Produk
                </button>
            </form>
        </div>

        <!-- Daftar Produk -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-800">Daftar Produk</h3>
                <p class="text-xs text-slate-500 mt-0.5">Semua produk yang terdaftar di sistem</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                No</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                Produk</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                Harga</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                Stok</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php $no = 1;
                        while ($row = mysqli_fetch_assoc($produk_list)): ?>
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-6 py-3 text-sm text-slate-500">#
                                    <?php echo $no++; ?>
                                </td>
                                <td class="px-6 py-3 text-sm font-medium text-slate-800">
                                    <?php echo htmlspecialchars($row['nama_produk']); ?>
                                </td>
                                <td class="px-6 py-3 text-sm text-slate-700">
                                    Rp
                                    <?php echo number_format($row['harga'], 0, ',', '.'); ?>
                                </td>
                                <td class="px-6 py-3">
                                    <span
                                        class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-full <?php echo $row['stok'] > 5 ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'; ?>">
                                        <?php echo $row['stok']; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-sm space-x-3">
                                    <a href="edit.php?id=<?php echo $row['id']; ?>"
                                        class="text-indigo-600 hover:text-indigo-800 font-medium">Edit</a>
                                    <a href="hapus.php?id=<?php echo $row['id']; ?>"
                                        onclick="return confirm('Hapus produk <?php echo htmlspecialchars($row['nama_produk'], ENT_QUOTES); ?>?')"
                                        class="text-rose-600 hover:text-rose-800 font-medium">Hapus</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</body>

</html>