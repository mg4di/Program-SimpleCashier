<?php
include 'koneksi.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$users = mysqli_query($koneksi, "SELECT * FROM user");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Daftar Kasir</title>
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
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-slate-800">Daftar Kasir</h1>
                    <p class="text-xs text-slate-500">Akun kasir terdaftar</p>
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

    <main class="max-w-4xl mx-auto px-6 py-8">

        <div class="mb-6">
            <h2 class="text-2xl font-bold text-slate-800">Daftar User / Kasir</h2>
            <p class="text-sm text-slate-500 mt-1">Semua akun kasir yang terdaftar di sistem</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 flex items-center gap-3">
                <div class="w-10 h-10 bg-indigo-100 text-indigo-600 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-800">Daftar Kasir</h3>
                    <p class="text-xs text-slate-500">Akun yang terdaftar di sistem</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                No</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                Nama Kasir</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                Username</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">
                                Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php $no = 1;
                        while ($row = mysqli_fetch_assoc($users)): ?>
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-6 py-4 text-sm text-slate-500">#<?php echo $no++; ?></td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-9 h-9 bg-gradient-to-br from-indigo-400 to-purple-500 text-white rounded-full flex items-center justify-center text-sm font-bold">
                                            <?php echo strtoupper(substr($row['nama_lengkap'], 0, 1)); ?>
                                        </div>
                                        <span class="text-sm font-medium text-slate-800">
                                            <?php echo htmlspecialchars($row['nama_lengkap']); ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">
                                    <?php echo htmlspecialchars($row['username']); ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if ($row['id'] == $_SESSION['user_id']): ?>
                                        <span
                                            class="inline-flex items-center gap-1 px-3 py-1.5 bg-emerald-50 text-emerald-700 text-xs font-semibold rounded-md">
                                            <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></span>
                                            Akun Aktif
                                        </span>
                                    <?php else: ?>
                                        <span
                                            class="inline-flex items-center gap-1 px-3 py-1.5 bg-slate-100 text-slate-500 text-xs font-medium rounded-md">
                                            <span class="w-1.5 h-1.5 bg-slate-400 rounded-full"></span>
                                            Tidak Aktif
                                        </span>
                                    <?php endif; ?>
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