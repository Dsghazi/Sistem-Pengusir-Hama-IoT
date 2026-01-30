<?php
header("Access-Control-Allow-Origin: *");
// GANTI DENGAN KREDENSIAL DATABASE ANDA
$host = "localhost"; 
$user = "ghazi"; 
$pass = "IgTxAu"; 
$db   = "ghazi_riwayat_iot";     

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) { die("Koneksi gagal"); }

if (isset($_GET['tipe']) && isset($_GET['pesan'])) {
    $tipe  = $_GET['tipe'];
    $pesan = $conn->real_escape_string($_GET['pesan']);
    $sql = "INSERT INTO log_debug (tipe, pesan) VALUES ('$tipe', '$pesan')";
    $conn->query($sql);
}
$conn->close();
?>