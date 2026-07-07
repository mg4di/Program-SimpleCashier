<?php
include 'koneksi.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$id = (int) $_GET['id'];
mysqli_query($koneksi, "DELETE FROM produk WHERE id = $id");
header("Location: produk.php");
exit;