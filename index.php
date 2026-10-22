<?php
// SPK Pemilihan Bahan Pangan - demo AHP sederhana.
// Edit koneksi database sesuai konfigurasi Laragon Anda.
$host = '127.0.0.1';
$db   = 'spk_pangan_ahp';
$user = 'root';
$pass = ''; // Password default Laragon sering kosong; sesuaikan jika berbeda.
$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

$alternatif = [
    ['nama'=>'Telur ayam', 'protein'=>13, 'energi'=>155, 'harga'=>28000, 'skor'=>4],
    ['nama'=>'Ikan kembung', 'protein'=>19, 'energi'=>167, 'harga'=>35000, 'skor'=>5],
    ['nama'=>'Daging ayam', 'protein'=>27, 'energi'=>239, 'harga'=>38000, 'skor'=>4],
    ['nama'=>'Tempe', 'protein'=>20, 'energi'=>193, 'harga'=>16000, 'skor'=>4],
    ['nama'=>'Bayam', 'protein'=>3, 'energi'=>23, 'harga'=>10000, 'skor'=>3],
];
$hasil = [];
$error = '';
// Contoh bobot AHP dari matriks perbandingan berpasangan 4 kriteria:
// protein, energi, keterjangkauan harga, kemudahan pengolahan.
// Bobot contoh ini untuk demonstrasi; silakan ganti berdasarkan penilaian ahli.
$bobot = ['protein'=>0.40, 'energi'=>0.20, 'harga'=>0.25, 'olah'=>0.15];

foreach ($alternatif as $a) {
    // Normalisasi nilai ke rentang 0..1; harga adalah kriteria cost (lebih murah lebih baik).
    $protein = $a['protein'] / 27;
    $energi = $a['energi'] / 239;
    $harga = 10000 / $a['harga'];
    $olah = $a['skor'] / 5;
    $nilai = $protein*$bobot['protein'] + $energi*$bobot['energi'] +
             $harga*$bobot['harga'] + $olah*$bobot['olah'];
    $a['nilai'] = $nilai;
    $hasil[] = $a;
}
usort($hasil, fn($x,$y) => $y['nilai'] <=> $x['nilai']);

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    $pdo->exec("CREATE TABLE IF NOT EXISTS catatan (
        id INT AUTO_INCREMENT PRIMARY KEY,
        isi VARCHAR(255) NOT NULL,
        dibuat TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['isi'])) {
        $isi = trim($_POST['isi']);
        if ($isi !== '') {
            $stmt = $pdo->prepare("INSERT INTO catatan (isi) VALUES (?)");
            $stmt->execute([$isi]);
            header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
            exit;
        }
    }
    $catatan = $pdo->query("SELECT * FROM catatan ORDER BY id DESC LIMIT 10")->fetchAll();
} catch (Throwable $e) {
    $catatan = [];
    $error = 'Database belum tersambung. Buat database spk_pangan_ahp di phpMyAdmin, lalu periksa pengaturan koneksi di index.php. Detail: ' . $e->getMessage();
}
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>SPK Bahan Pangan Bergizi | AHP</title>
<style>
:root{--green:#176b4d;--mint:#eaf6ef;--ink:#19312a;--muted:#60736b;--line:#dce9e1}
*{box-sizing:border-box}body{margin:0;background:#f4f8f5;color:var(--ink);font:16px/1.5 Arial,sans-serif}
header{background:linear-gradient(120deg,#14583f,#21865f);color:white;padding:32px 20px}
.wrap{max-width:1100px;margin:auto;padding:0 18px}h1{margin:0 0 8px;font-size:clamp(25px,4vw,38px)}header p{margin:0;color:#e2f4e9}
main{padding:24px 0}.cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:14px;margin-bottom:22px}
.card,.panel{background:white;border:1px solid var(--line);border-radius:14px;padding:18px;box-shadow:0 4px 18px #163e2b08}
.kicker{color:var(--muted);font-size:13px;text-transform:uppercase;letter-spacing:.06em}.metric{font-size:25px;font-weight:700;margin-top:5px}
.panel{margin-bottom:20px}h2{margin:0 0 14px;font-size:21px}.note{background:#fff8e6;border:1px solid #f0dfa6;padding:12px 14px;border-radius:10px;margin-bottom:18px;color:#664d0b}
.alert{background:#fff0ef;border:1px solid #f3c3bd;padding:12px;border-radius:10px;margin-bottom:18px;color:#8a261c;overflow-wrap:anywhere}
table{width:100%;border-collapse:collapse}th,td{text-align:left;padding:11px;border-bottom:1px solid var(--line)}th{background:#f0f7f2;font-size:13px}
.rank{display:inline-flex;min-width:28px;height:28px;align-items:center;justify-content:center;border-radius:50%;background:var(--mint);color:var(--green);font-weight:bold}
.bar{height:8px;background:#e5eee8;border-radius:20px;overflow:hidden;margin-top:6px}.bar span{display:block;height:100%;background:#21865f}
form{display:flex;gap:8px;flex-wrap:wrap}input{flex:1;min-width:180px;padding:11px;border:1px solid #bdcec3;border-radius:8px;font-size:15px}button{border:0;background:var(--green);color:white;padding:11px 16px;border-radius:8px;font-weight:bold;cursor:pointer}
.small{font-size:13px;color:var(--muted)}footer{text-align:center;color:var(--muted);padding:20px}
@media(max-width:600px){.table-scroll{overflow-x:auto}table{min-width:620px}}
</style>
</head>
<body>
<header><div class="wrap"><h1>SPK Pemilihan Bahan Pangan Bergizi</h1><p>Sistem Pendukung Keputusan dengan pendekatan Analytical Hierarchy Process (AHP)</p></div></header>
<main class="wrap">
<?php if ($error): ?><div class="alert"><strong>Perhatian:</strong> <?=e($error)?></div><?php endif; ?>
<div class="note"><strong>Catatan demo:</strong> Data gizi, harga, dan bobot di bawah adalah contoh untuk belajar, bukan rekomendasi gizi resmi. Bobot saat ini ditetapkan sebagai contoh dan belum dihitung dari input perbandingan berpasangan pengguna.</div>
<div class="cards">
  <div class="card"><div class="kicker">Alternatif contoh</div><div class="metric"><?=count($alternatif)?></div></div>
  <div class="card"><div class="kicker">Metode</div><div class="metric">AHP</div></div>
  <div class="card"><div class="kicker">Kriteria</div><div class="metric">4</div></div>
</div>
<section class="panel">
<h2>Bobot kriteria (contoh)</h2>
<table><thead><tr><th>Kriteria</th><th>Bobot</th><th>Visualisasi</th></tr></thead><tbody>
<?php foreach (['Protein'=>'protein','Energi'=>'energi','Keterjangkauan harga'=>'harga','Kemudahan pengolahan'=>'olah'] as $label=>$key): ?>
<tr><td><?=e($label)?></td><td><?=number_format($bobot[$key]*100,1,',','.')?>%</td><td><div class="bar"><span style="width:<?=$bobot[$key]*100?>%"></span></div></td></tr>
<?php endforeach; ?>
</tbody></table>
</section>
<section class="panel">
<h2>Hasil peringkat bahan pangan</h2>
<div class="table-scroll"><table><thead><tr><th>Peringkat</th><th>Alternatif</th><th>Protein (g/100g)</th><th>Energi (kkal/100g)</th><th>Harga contoh (Rp/kg)</th><th>Skor olah</th><th>Nilai akhir</th></tr></thead><tbody>
<?php foreach ($hasil as $i=>$a): ?>
<tr><td><span class="rank"><?=$i+1?></span></td><td><strong><?=e($a['nama'])?></strong></td><td><?=$a['protein']?></td><td><?=$a['energi']?></td><td><?=number_format($a['harga'],0,',','.')?></td><td><?=$a['skor']?>/5</td><td><strong><?=number_format($a['nilai'],4,',','.')?></strong></td></tr>
<?php endforeach; ?>
</tbody></table></div>
<p class="small">Nilai akhir adalah demonstrasi penjumlahan berbobot setelah normalisasi sederhana. Untuk penelitian formal, tentukan skala dan matriks AHP yang valid, hitung eigenvector serta Consistency Ratio (CR), dan validasi data dengan sumber tepercaya.</p>
</section>
<section class="panel">
<h2>Catatan pengembangan</h2>
<p>Bagian ini membuktikan koneksi database MySQL Laragon. Tambahkan catatan singkat, lalu tekan Simpan.</p>
<form method="post"><input name="isi" maxlength="255" placeholder="Contoh: kriteria perlu divalidasi" required><button type="submit">Simpan catatan</button></form>
<?php if ($catatan): ?><ul><?php foreach ($catatan as $c): ?><li><?=e($c['isi'])?> <span class="small">(<?=e($c['dibuat'])?>)</span></li><?php endforeach; ?></ul><?php endif; ?>
</section>
</main><footer>Prototipe lokal untuk pembelajaran • SPK Bahan Pangan AHP</footer>
</body></html>
