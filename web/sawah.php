<?php
// File: simpan.php (Upload ke hosting)

$host = "localhost"; // Di hosting biasanya tetap 'localhost'
$user = "ghazi"; // GANTI INI
$pass = "IgTxAu"; // GANTI INI
$db   = "ghazi_riwayat_iot";     // GANTI INI

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi gagal");
}

if (isset($_GET['status']) && isset($_GET['sumber'])) {
    $status = $_GET['status'];
    $sumber = $_GET['sumber'];

    $sql = "INSERT INTO riwayat_alat (status, sumber) VALUES ('$status', '$sumber')";
    
    if ($conn->query($sql) === TRUE) {
        echo "OK";
    } else {
        echo "Error: " . $conn->error;
    }
}
$conn->close();
?>