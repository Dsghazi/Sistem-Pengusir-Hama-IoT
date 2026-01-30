<?php
// File: admin.php
$host = "localhost";
$user = "ghazi"; // GANTI
$pass = "IgTxAu"; // GANTI
$db   = "ghazi_riwayat_iot";     // GANTI

$conn = new mysqli($host, $user, $pass, $db);

// Ambil Data Riwayat Penggunaan (Limit 20 terakhir)
$sql_history = "SELECT * FROM riwayat_alat ORDER BY id DESC LIMIT 20";
$res_history = $conn->query($sql_history);

// Ambil Data Debug/Error (Limit 20 terakhir)
$sql_debug = "SELECT * FROM log_debug ORDER BY id DESC LIMIT 20";
$res_debug = $conn->query($sql_debug);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Developer Dashboard</title>
    <style>
        body { font-family: 'Segoe UI', monospace; background: #1a1a1a; color: #ddd; padding: 20px; }
        h1 { color: #00bcd4; text-align: center; }
        h2 { border-bottom: 2px solid #333; padding-bottom: 10px; margin-top: 30px; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; background: #252525; border-radius: 8px; overflow: hidden; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #333; }
        th { background: #333; color: #00bcd4; }
        tr:hover { background: #2a2a2a; }
        
        .badge { padding: 5px 10px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .bg-on { background: #1b5e20; color: #a5d6a7; }
        .bg-off { background: #b71c1c; color: #ef9a9a; }
        
        .bg-error { background: #ff5f5f; color: black; }
        .bg-warning { background: #ffbd2e; color: black; }
        .bg-info { background: #2196f3; color: white; }

        .container { max-width: 1000px; margin: auto; }
        .refresh-btn { display: block; width: 100px; margin: 20px auto; padding: 10px; background: #00bcd4; color: black; text-align: center; text-decoration: none; border-radius: 5px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🛠️ CONTROL ROOM</h1>
        <a href="admin.php" class="refresh-btn">REFRESH</a>

        <h2>🚨 Log Kesehatan & Debug</h2>
        <table>
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Tipe</th>
                    <th>Pesan Sistem</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $res_debug->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['waktu'] ?></td>
                    <td>
                        <?php 
                        $cls = 'bg-info';
                        if($row['tipe'] == 'ERROR') $cls = 'bg-error';
                        if($row['tipe'] == 'WARNING') $cls = 'bg-warning';
                        ?>
                        <span class="badge <?= $cls ?>"><?= $row['tipe'] ?></span>
                    </td>
                    <td style="font-family: monospace; color: #fff;"><?= $row['pesan'] ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <h2>📜 Riwayat Aktivitas Alat</h2>
        <table>
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Status</th>
                    <th>Sumber Perintah</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $res_history->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['waktu'] ?></td>
                    <td>
                        <span class="badge <?= ($row['status'] == 'ON') ? 'bg-on' : 'bg-off' ?>">
                            <?= $row['status'] ?>
                        </span>
                    </td>
                    <td><?= $row['sumber'] ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>