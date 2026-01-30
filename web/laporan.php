<?php
// ================= 1. KONEKSI DATABASE =================
$host = "localhost";
$user = "ghazi";  // GANTI DENGAN USERNAME DB KAMU
$pass = "IgTxAu";    // GANTI DENGAN PASSWORD DB KAMU
$db   = "ghazi_riwayat_iot";    // GANTI DENGAN NAMA DB KAMU

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) { die("Koneksi gagal: " . $conn->connect_error); }

// ================= 2. AMBIL DATA UNTUK GRAFIK =================
// Kita hitung berapa kali alat menyala (ON) setiap harinya
$sql_chart = "SELECT DATE(waktu) as tanggal, COUNT(*) as jumlah 
              FROM riwayat_alat 
              WHERE status='ON' 
              GROUP BY DATE(waktu) 
              ORDER BY tanggal ASC LIMIT 7"; // Ambil 7 hari terakhir

$res_chart = $conn->query($sql_chart);

$label_tgl = [];
$data_jml  = [];

while($row = $res_chart->fetch_assoc()) {
    $label_tgl[] = $row['tanggal']; // Sumbu X (Tanggal)
    $data_jml[]  = $row['jumlah'];  // Sumbu Y (Jumlah Nyala)
}

// Ubah format array PHP ke JSON biar bisa dibaca JavaScript
$json_tgl = json_encode($label_tgl);
$json_jml = json_encode($data_jml);

// ================= 3. AMBIL DATA UNTUK TABEL =================
$sql_tabel = "SELECT * FROM riwayat_alat ORDER BY id DESC LIMIT 50";
$res_tabel = $conn->query($sql_tabel);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Aktivitas Alat</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root { --bg: #121212; --panel: #1e1e1e; --text: #e0e0e0; --accent: #00bcd4; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); color: var(--text); padding: 20px; margin: 0; }
        .container { max-width: 800px; margin: auto; }
        
        h1, h2 { text-align: center; color: var(--accent); }
        .card { background: var(--panel); padding: 20px; border-radius: 15px; margin-bottom: 20px; box-shadow: 0 4px 10px rgba(0,0,0,0.3); }
        
        /* Styling Tabel */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #333; }
        th { background: #252525; color: #aaa; }
        tr:hover { background: #2a2a2a; }
        
        .badge { padding: 5px 10px; border-radius: 5px; font-weight: bold; font-size: 12px; }
        .on { background: rgba(46, 204, 113, 0.2); color: #2ecc71; }
        .off { background: rgba(231, 76, 60, 0.2); color: #e74c3c; }
    </style>
</head>
<body>

<div class="container">
    <h1>📊 LAPORAN SMART SAWAH</h1>

    <div class="card">
        <h2>Frekuensi Penggunaan (7 Hari Terakhir)</h2>
        <canvas id="myChart"></canvas> 
    </div>

    <div class="card">
        <h2>Rincian Riwayat</h2>
        <table>
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Status</th>
                    <th>Sumber Perintah</th>
                </tr>
            </thead>
            <tbody>
                <?php if($res_tabel->num_rows > 0): ?>
                    <?php while($row = $res_tabel->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['waktu'] ?></td>
                        <td>
                            <span class="badge <?= ($row['status'] == 'ON') ? 'on' : 'off' ?>">
                                <?= $row['status'] ?>
                            </span>
                        </td>
                        <td><?= $row['sumber'] ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="3" style="text-align:center;">Belum ada data.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    // Ambil data dari PHP tadi
    const dataTanggal = <?php echo $json_tgl; ?>;
    const dataJumlah  = <?php echo $json_jml; ?>;

    const ctx = document.getElementById('myChart').getContext('2d');
    const myChart = new Chart(ctx, {
        type: 'bar', // Bisa diganti 'line' jika ingin grafik garis
        data: {
            labels: dataTanggal, // Sumbu X (Tanggal)
            datasets: [{
                label: 'Jumlah Alat Dinyalakan',
                data: dataJumlah, // Sumbu Y (Jumlah)
                backgroundColor: 'rgba(0, 188, 212, 0.5)', // Warna Batang
                borderColor: 'rgba(0, 188, 212, 1)',       // Warna Garis Tepi
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { color: '#aaa' },
                    grid: { color: '#333' }
                },
                x: {
                    ticks: { color: '#aaa' },
                    grid: { display: false }
                }
            },
            plugins: {
                legend: { labels: { color: '#fff' } }
            }
        }
    });
</script>

</body>
</html>