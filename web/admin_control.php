<?php
session_start();

// ================= KONFIGURASI =================
$host = "localhost";
$user = "ghazi";  // GANTI USERNAME DB
$pass = "IgTxAu";    // GANTI PASSWORD DB
$db   = "ghazi_riwayat_iot";    // GANTI NAMA DB

$admin_pass = "12345678"; // PASSWORD LOGIN

// ================= KONEKSI DATABASE =================
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) { die("Koneksi Database Gagal: " . $conn->connect_error); }

// ================= LOGIKA LOGIN =================
if (isset($_POST['login'])) {
    if ($_POST['password'] == $admin_pass) {
        $_SESSION['loggedin'] = true;
    } else {
        $error_msg = "Password Salah!";
    }
}
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin_control.php");
    exit();
}

// JIKA BELUM LOGIN
if (!isset($_SESSION['loggedin'])) {
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login Developer</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { background: #121212; color: white; font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .box { background: #1e1e1e; padding: 30px; border-radius: 10px; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.5); width: 300px; }
        input { padding: 10px; width: 90%; margin: 10px 0; border-radius: 5px; border: none; }
        button { padding: 10px 20px; background: #00bcd4; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; width: 100%; }
    </style>
</head>
<body>
    <div class="box">
        <h2>🔒 RESTRICTED AREA</h2>
        <?php if(isset($error_msg)) echo "<p style='color:red'>$error_msg</p>"; ?>
        <form method="post">
            <input type="password" name="password" placeholder="Masukkan Password Admin" required>
            <button type="submit" name="login">MASUK</button>
        </form>
    </div>
</body>
</html>
<?php
    exit();
}

// ================= LOGIKA HAPUS DATA =================
if (isset($_GET['hapus_id']) && isset($_GET['tabel'])) {
    $id = intval($_GET['hapus_id']);
    $tabel = ($_GET['tabel'] == 'log') ? 'riwayat_alat' : 'log_debug';
    $conn->query("DELETE FROM $tabel WHERE id=$id");
    header("Location: admin_control.php");
}

if (isset($_GET['clear_all'])) {
    $tabel = ($_GET['clear_all'] == 'log') ? 'riwayat_alat' : 'log_debug';
    $conn->query("TRUNCATE TABLE $tabel");
    header("Location: admin_control.php");
}

// ================= AMBIL DATA =================
$logs   = $conn->query("SELECT * FROM riwayat_alat ORDER BY id DESC LIMIT 100"); // Limit dinaikkan biar enak scrollnya
$debugs = $conn->query("SELECT * FROM log_debug ORDER BY id DESC LIMIT 100");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Developer Control Room</title>
    <meta http-equiv="refresh" content="30"> 
    <style>
        :root { --bg: #121212; --panel: #1e1e1e; --text: #e0e0e0; --accent: #00bcd4; --danger: #ff4444; }
        
        * { box-sizing: border-box; }
        
        body { 
            font-family: 'Segoe UI', monospace; 
            background: var(--bg); 
            color: var(--text); 
            margin: 0; 
            padding: 10px; 
            height: 100vh; /* Full Height Layar */
            display: flex;
            flex-direction: column;
            overflow: hidden; /* Hilangkan scroll body */
        }
        
        /* HEADER COMPACT */
        .header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            padding: 0 10px 10px 10px; 
            border-bottom: 2px solid #333; 
            flex-shrink: 0; /* Header gak boleh mengecil */
        }
        h1 { margin: 0; color: var(--accent); font-size: 20px; }
        .btn-logout { background: #333; color: white; text-decoration: none; padding: 5px 10px; border-radius: 5px; font-size: 11px; }

        /* GRID LAYOUT (SIDE BY SIDE) */
        .dashboard-container {
            display: grid;
            grid-template-columns: 1fr 1fr; /* Dua kolom sama besar */
            gap: 15px;
            flex-grow: 1; /* Isi sisa ruang ke bawah */
            overflow: hidden; /* Mencegah overflow container */
            margin-top: 10px;
        }

        .section { 
            background: var(--panel); 
            border-radius: 10px; 
            box-shadow: 0 4px 10px rgba(0,0,0,0.3); 
            display: flex;
            flex-direction: column;
            overflow: hidden; /* Penting buat scroll internal */
        }

        .sec-head { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            padding: 15px;
            background: #252525;
            border-bottom: 1px solid #333;
        }
        h2 { margin: 0; font-size: 16px; border-left: 4px solid var(--accent); padding-left: 10px; }

        /* SCROLLABLE TABLE AREA */
        .table-scroll {
            overflow-y: auto; /* Scroll vertikal aktif disini */
            flex-grow: 1;
            padding: 0;
        }

        table { width: 100%; border-collapse: collapse; font-size: 12px; }
        
        /* STICKY HEADER */
        thead th { 
            position: sticky; 
            top: 0; 
            background: #2c2c2c; 
            z-index: 10;
            text-align: left; 
            padding: 10px; 
            color: #aaa; 
            border-bottom: 2px solid #444;
        }
        
        td { padding: 8px 10px; border-bottom: 1px solid #333; vertical-align: middle; }
        tr:hover { background: #2a2a2a; }

        /* UI Elements */
        .badge { padding: 3px 6px; border-radius: 3px; font-weight: bold; font-size: 10px; }
        .b-on { background: rgba(46, 204, 113, 0.2); color: #2ecc71; }
        .b-off { background: rgba(231, 76, 60, 0.2); color: #e74c3c; }
        .b-err { background: #ff4444; color: black; }
        .b-warn { background: #ffbb33; color: black; }
        .b-info { background: #33b5e5; color: black; }

        .btn-del { color: #666; text-decoration: none; font-size: 14px; }
        .btn-del:hover { color: var(--danger); }
        .btn-clear { background: var(--danger); color: white; text-decoration: none; padding: 4px 8px; border-radius: 4px; font-size: 10px; font-weight: bold; }

        /* RESPONSIVE UNTUK HP (Balik jadi atas bawah kalau layar kecil) */
        @media (max-width: 768px) {
            body { overflow: auto; height: auto; } /* Aktifkan scroll body di HP */
            .dashboard-container { grid-template-columns: 1fr; height: auto; }
            .section { height: 500px; /* Batasi tinggi tabel di HP */ }
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>🛠️ CONTROL ROOM</h1>
        <div>
            <span style="font-size:11px; color:#666; margin-right:10px;"><?= date("H:i:s") ?></span>
            <a href="admin_control.php" style="color:var(--accent); text-decoration:none; margin-right:15px; font-size:12px;">🔄 Refresh</a>
            <a href="?logout=true" class="btn-logout">Logout</a>
        </div>
    </div>

    <div class="dashboard-container">
        
        <div class="section">
            <div class="sec-head">
                <h2 style="border-color: #ff4444;">🚨 DEBUG LOG</h2>
                <a href="?clear_all=debug" class="btn-clear" onclick="return confirm('Hapus SEMUA log debug?')">BERSIHKAN</a>
            </div>
            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th width="40">ID</th>
                            <th width="130">Waktu</th>
                            <th width="70">Tipe</th>
                            <th>Pesan</th>
                            <th width="30"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($debugs->num_rows > 0): ?>
                            <?php while($row = $debugs->fetch_assoc()): ?>
                            <tr>
                                <td>#<?= $row['id'] ?></td>
                                <td style="color:#888"><?= date('H:i:s', strtotime($row['waktu'])) ?> <small><?= date('d/m', strtotime($row['waktu'])) ?></small></td>
                                <td>
                                    <?php 
                                        $cls = 'b-info';
                                        if($row['tipe'] == 'ERROR' || $row['tipe'] == 'FATAL') $cls = 'b-err';
                                        if($row['tipe'] == 'WARNING') $cls = 'b-warn';
                                    ?>
                                    <span class="badge <?= $cls ?>"><?= $row['tipe'] ?></span>
                                </td>
                                <td style="font-family: monospace; color:#eee; font-size:11px;"><?= $row['pesan'] ?></td>
                                <td style="text-align: center;">
                                    <a href="?hapus_id=<?= $row['id'] ?>&tabel=debug" class="btn-del">✖</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" style="text-align:center; padding:20px; opacity:0.5;">Belum ada log error.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="section">
            <div class="sec-head">
                <h2 style="border-color: #2ecc71;">📜 RIWAYAT AKTIVITAS</h2>
                <a href="?clear_all=log" class="btn-clear" onclick="return confirm('Hapus SEMUA riwayat?')">BERSIHKAN</a>
            </div>
            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th width="40">ID</th>
                            <th width="130">Waktu</th>
                            <th width="70">Status</th>
                            <th>Sumber</th>
                            <th width="30"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($logs->num_rows > 0): ?>
                            <?php while($row = $logs->fetch_assoc()): ?>
                            <tr>
                                <td>#<?= $row['id'] ?></td>
                                <td style="color:#888"><?= date('H:i:s', strtotime($row['waktu'])) ?> <small><?= date('d/m', strtotime($row['waktu'])) ?></small></td>
                                <td>
                                    <span class="badge <?= ($row['status']=='ON') ? 'b-on' : 'b-off' ?>">
                                        <?= $row['status'] ?>
                                    </span>
                                </td>
                                <td><?= $row['sumber'] ?></td>
                                <td style="text-align: center;">
                                    <a href="?hapus_id=<?= $row['id'] ?>&tabel=log" class="btn-del">✖</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" style="text-align:center; padding:20px; opacity:0.5;">Belum ada riwayat.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</body>
</html>