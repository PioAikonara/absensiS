<?php
// Ambil nama file yang sedang dibuka
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="w-64 bg-gradient-to-b from-pink-500 to-pink-300 text-white p-6">
    <h2 class="text-2xl font-bold mb-6">Dashboard Admin</h2>
    <ul>
        <li class="mb-4">
            <a href="index.php" class="block py-2 px-4 rounded-lg <?= ($current_page == 'index.php') ? 'bg-pink-600' : 'hover:bg-pink-500' ?>">Dashboard</a>
        </li>
        <li class="mb-4">
            <a href="siswa.php" class="block py-2 px-4 rounded-lg <?= ($current_page == 'siswa.php') ? 'bg-pink-600' : 'hover:bg-pink-500' ?>">Data Siswa</a>
        </li>
        <li class="mb-4">
            <a href="absensi.php" class="block py-2 px-4 rounded-lg <?= ($current_page == 'absensi.php') ? 'bg-pink-600' : 'hover:bg-pink-500' ?>">Absensi</a>
        </li>
        <li>
            <a href="logout.php" class="block py-2 px-4 rounded-lg bg-red-600 hover:bg-red-700 text-white">Logout</a>
        </li>
    </ul>
</div>

