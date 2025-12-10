<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../config.php';
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_GET['id'])) {
    die("ERROR: Missing Summon ID.");
}

$id = $_GET['id'];

$query = "DELETE FROM Summon WHERE SummonID = '$id'";

if (mysqli_query($conn, $query)) {
    header("Location: security_summon_list.php?deleted=1");
    exit;
} else {
    die("ERROR deleting summon: " . mysqli_error($conn));
}
