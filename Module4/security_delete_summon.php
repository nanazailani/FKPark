<?php
// Aktifkan error reporting supaya senang nampak error masa development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include config.php untuk sambung ke database
include '../config.php';
// Start session untuk access maklumat login user
session_start();
// Disable cache supaya browser tak simpan page lama
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// Check sama ada SummonID dihantar melalui URL
// Kalau tak ada, hentikan proses
if (!isset($_GET['id'])) {
    die("ERROR: Missing Summon ID.");
}
// Ambil SummonID dari URL
$id = $_GET['id'];

// SQL query untuk padam saman berdasarkan SummonID
$query = "DELETE FROM Summon WHERE SummonID = '$id'";

if (mysqli_query($conn, $query)) {
    // Jika berjaya padam, redirect balik ke senarai saman
    header("Location: security_summon_list.php?deleted=1");
    exit;
} else {
    // Jika gagal, paparkan error dari database
    die("ERROR deleting summon: " . mysqli_error($conn));
}
