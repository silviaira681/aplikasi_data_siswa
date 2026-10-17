<?php
session_start();
date_default_timezone_set('Asia/Jakarta');

$file = __DIR__ . '/data_siswa.json';

if (!file_exists($file)) {
    file_put_contents($file, json_encode([], JSON_PRETTY_PRINT));
}

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function bacaData($file) {
    $data = json_decode(file_get_contents($file), true);
    return is_array($data) ? $data : [];
}

function simpanData($file, $data) {
    file_put_contents(
        $file,
        json_encode(array_values($data), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        LOCK_EX
    );
}

function pindah($url) {
    header("Location: $url");
    exit;
}

$pesanLogin = '';
$jenisLogin = 'error';

/* PROSES LOGIN */
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && ($_POST['aksi'] ?? '') === 'login') {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === 'admin' && $password === 'admin123') {
        session_regenerate_id(true);
        $_SESSION['login'] = true;
        $_SESSION['username'] = 'admin';

        pindah('index.php');
    } else {
        $pesanLogin = 'Username atau password salah!';
    }
}

/* PROSES LOGOUT */
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && ($_POST['aksi'] ?? '') === 'logout') {

    $_SESSION = [];
    session_destroy();

    pindah('index.php');
}

/* TAMPILKAN LOGIN JIKA BELUM MASUK */
if (empty($_SESSION['login'])) {
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - DataSiswa</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: linear-gradient(135deg, #063b91, #0878ed, #60a5fa);
        }

        .login-container {
            width: 100%;
            max-width: 420px;
            background: white;
            border-radius: 20px;
            padding: 38px;
            box-shadow: 0 20px 55px rgba(0, 20, 70, .25);
        }

        .logo {
            width: 65px;
            height: 65px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 18px;
            background: #0756c9;
            color: white;
            border-radius: 18px;
            font-size: 32px;
            font-weight: bold;
        }

        h1 {
            color: #172554;
            font-size: 25px;
            text-align: center;
            margin-bottom: 8px;
        }

        .subtitle {
            text-align: center;
            color: #64748b;
            font-size: 13px;
            line-height: 1.6;
            margin-bottom: 28px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #334155;
            font-weight: 600;
            font-size: 13px;
        }

        input {
            width: 100%;
            padding: 13px 14px;
            margin-bottom: 19px;
            border: 1px solid #dbe3ef;
            border-radius: 10px;
            font-size: 14px;
            outline: none;
        }

        input:focus {
            border-color: #0878ed;
            box-shadow: 0 0 0 3px #dbeafe;
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 10px;
            background: #0756c9;
            color: white;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            transition: .2s;
        }

        .btn-login:hover {
            background: #0645a5;
        }

        .alert {
            background: #fee2e2;
            color: #b91c1c;
            padding: 12px;
            border-radius: 9px;
            font-size: 13px;
            margin-bottom: 18px;
        }

        .account-info {
            background: #eff6ff;
            border: 1px solid #dbeafe;
            color: #1e40af;
            border-radius: 10px;
            padding: 14px;
            margin-top: 22px;
            font-size: 12px;
            line-height: 1.8;
        }

        .footer {
            text-align: center;
            color: #94a3b8;
            font-size: 11px;
            margin-top: 25px;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="logo">S</div>

    <h1>Selamat Datang</h1>
    <p class="subtitle">
        Silakan login untuk masuk ke<br>
        Sistem Informasi Data Siswa
    </p>

    <?php if ($pesanLogin !== ''): ?>
        <div class="alert"><?= e($pesanLogin) ?></div>
    <?php endif; ?>

    <form method="POST" action="index.php">
        <input type="hidden" name="aksi" value="login">

        <label for="username">Username</label>
        <input
            type="text"
            id="username"
            name="username"
            placeholder="Masukkan username"
            autocomplete="username"
            required
            autofocus
        >

        <label for="password">Password</label>
        <input
            type="password"
            id="password"
            name="password"
            placeholder="Masukkan password"
            autocomplete="current-password"
            required
        >

        <button type="submit" class="btn-login">Login →</button>
    </form>

    <div class="account-info">
        <strong>Informasi akun demo</strong><br>
        Username: admin<br>
        Password: admin123
    </div>

    <div class="footer">
        © <?= date('Y') ?> DataSiswa
    </div>
</div>

</body>
</html>
<?php
    exit;
}

/* BAGIAN DASHBOARD HANYA DAPAT DIAKSES SETELAH LOGIN */

$dataSiswa = bacaData($file);
$pesan = '';
$jenisPesan = 'success';

/* TAMBAH DATA SISWA */
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && ($_POST['aksi'] ?? '') === 'tambah') {

    $nama = trim($_POST['nama'] ?? '');
    $nis = trim($_POST['nis'] ?? '');
    $kelas = trim($_POST['kelas'] ?? '');
    $jurusan = trim($_POST['jurusan'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');

    if ($nama === '' || $nis === '' || $kelas === ''
        || $jurusan === '' || $alamat === '') {

        $pesan = 'Semua kolom wajib diisi!';
        $jenisPesan = 'error';

    } else {
        $nisAda = false;

        foreach ($dataSiswa as $siswa) {
            if (($siswa['nis'] ?? '') === $nis) {
                $nisAda = true;
                break;
            }
        }

        if ($nisAda) {
            $pesan = 'NIS sudah terdaftar!';
            $jenisPesan = 'error';
        } else {
            $dataSiswa[] = [
                'id' => bin2hex(random_bytes(8)),
                'nama' => $nama,
                'nis' => $nis,
                'kelas' => $kelas,
                'jurusan' => $jurusan,
                'alamat' => $alamat,
                'tanggal' => date('d-m-Y H:i')
            ];

            simpanData($file, $dataSiswa);
            pindah('index.php?halaman=data&status=tambah');
        }
    }
}

/* HAPUS DATA SISWA */
if (isset($_GET['hapus'])) {
    $idHapus = (string)$_GET['hapus'];

    $dataSiswa = array_filter($dataSiswa, function ($siswa) use ($idHapus) {
        return (string)($siswa['id'] ?? '') !== $idHapus;
    });

    simpanData($file, $dataSiswa);
    pindah('index.php?halaman=data&status=hapus');
}

if (($_GET['status'] ?? '') === 'tambah') {
    $pesan = 'Data siswa berhasil ditambahkan!';
}

if (($_GET['status'] ?? '') === 'hapus') {
    $pesan = 'Data siswa berhasil dihapus!';
}

$halaman = $_GET['halaman'] ?? 'dashboard';

$halamanDiizinkan = [
    'dashboard', 'data', 'tambah', 'detail', 'laporan'
];

if (!in_array($halaman, $halamanDiizinkan, true)) {
    $halaman = 'dashboard';
}

$totalSiswa = count($dataSiswa);
$totalKelas = count(array_unique(array_filter(array_column($dataSiswa, 'kelas'))));
$totalJurusan = count(array_unique(array_filter(array_column($dataSiswa, 'jurusan'))));

$cari = trim($_GET['cari'] ?? '');
$dataTampil = $dataSiswa;

if ($cari !== '') {
    $dataTampil = array_filter($dataSiswa, function ($siswa) use ($cari) {
        return stripos($siswa['nama'] ?? '', $cari) !== false
            || stripos($siswa['nis'] ?? '', $cari) !== false
            || stripos($siswa['kelas'] ?? '', $cari) !== false
            || stripos($siswa['jurusan'] ?? '', $cari) !== false;
    });
}

$detailSiswa = null;

if ($halaman === 'detail' && isset($_GET['id'])) {
    foreach ($dataSiswa as $siswa) {
        if ((string)($siswa['id'] ?? '') === (string)$_GET['id']) {
            $detailSiswa = $siswa;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard DataSiswa</title>

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f1f5fb;
            color: #1e293b;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 245px;
            height: 100vh;
            background: linear-gradient(180deg, #0756c9, #073b91);
            color: white;
            padding: 25px 16px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 5px 8px 25px;
            border-bottom: 1px solid #ffffff35;
        }

        .brand-icon {
            width: 44px;
            height: 44px;
            display: grid;
            place-items: center;
            border-radius: 12px;
            background: white;
            color: #0756c9;
            font-size: 24px;
            font-weight: bold;
        }

        .brand h2 { font-size: 18px; }
        .brand p { font-size: 11px; color: #dbeafe; margin-top: 4px; }

        .menu-title {
            color: #bfdbfe;
            font-size: 11px;
            font-weight: bold;
            margin: 28px 12px 12px;
        }

        .menu a {
            display: block;
            padding: 13px 14px;
            margin: 6px 0;
            border-radius: 10px;
            color: #eaf2ff;
            text-decoration: none;
            font-size: 14px;
        }

        .menu a:hover, .menu a.active {
            background: white;
            color: #0756c9;
            font-weight: bold;
        }

        .sidebar-bottom {
            position: absolute;
            bottom: 20px;
            left: 20px;
            right: 20px;
            padding: 14px;
            background: #ffffff20;
            border-radius: 12px;
            font-size: 12px;
            line-height: 1.7;
        }

        .main { margin-left: 245px; min-height: 100vh; }

        .topbar {
            background: white;
            min-height: 78px;
            padding: 18px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e5eaf2;
            gap: 15px;
        }

        .topbar h2 { font-size: 21px; color: #172554; }
        .topbar p { color: #64748b; font-size: 12px; margin-top: 5px; }

        .profile {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .avatar {
            width: 40px;
            height: 40px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            background: #dbeafe;
            color: #0756c9;
            font-weight: bold;
        }

        .profile strong { display: block; font-size: 13px; }
        .profile small { color: #64748b; }

        .btn-logout {
            background: #fee2e2;
            color: #b91c1c;
            border: none;
            border-radius: 8px;
            padding: 10px 12px;
            font-weight: bold;
            cursor: pointer;
        }

        .btn-logout:hover { background: #fecaca; }

        .content { padding: 28px; max-width: 1500px; margin: auto; }

        .welcome {
            background: linear-gradient(120deg, #0756c9, #2786f7);
            border-radius: 18px;
            padding: 30px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            box-shadow: 0 10px 25px #0756c926;
        }

        .welcome h1 { font-size: 26px; margin-bottom: 10px; }
        .welcome p { color: #e5efff; line-height: 1.7; font-size: 14px; }
        .welcome-icon { font-size: 60px; }

        .stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: white;
            border: 1px solid #e5eaf2;
            padding: 22px;
            border-radius: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .stat-card p { color: #64748b; font-size: 13px; margin-bottom: 10px; }
        .stat-card h2 { color: #172554; font-size: 30px; }

        .stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            font-size: 24px;
        }

        .blue { background: #dbeafe; color: #0756c9; }
        .cyan { background: #cffafe; color: #087e9b; }
        .purple { background: #ede9fe; color: #6d28d9; }

        .section {
            background: white;
            border: 1px solid #e5eaf2;
            border-radius: 15px;
            padding: 23px;
            margin-bottom: 24px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        .section-header h3 { font-size: 17px; color: #172554; }
        .section-header p { font-size: 12px; color: #64748b; margin-top: 5px; }

        .btn {
            display: inline-block;
            border: none;
            background: #0756c9;
            color: white;
            text-decoration: none;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 13px;
            cursor: pointer;
            font-weight: 600;
        }

        .btn:hover { background: #0645a5; }
        .btn-light { background: #eff6ff; color: #0756c9; }
        .btn-light:hover { background: #dbeafe; }
        .btn-danger { background: #fee2e2; color: #b91c1c; }
        .btn-danger:hover { background: #fecaca; }

        .table-wrap { width: 100%; overflow-x: auto; }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            min-width: 600px;
        }

        th {
            text-align: left;
            padding: 13px 11px;
            background: #eff6ff;
            color: #17478f;
        }

        td {
            padding: 13px 11px;
            border-bottom: 1px solid #edf0f5;
            color: #475569;
        }

        tr:hover td { background: #f8fbff; }

        .badge {
            display: inline-block;
            padding: 6px 10px;
            background: #dbeafe;
            color: #0756c9;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
        }

        input, select, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #dbe3ef;
            border-radius: 9px;
            font: inherit;
            font-size: 14px;
            outline: none;
            background: white;
        }

        input:focus, select:focus, textarea:focus {
            border-color: #2786f7;
            box-shadow: 0 0 0 3px #dbeafe;
        }

        .search-form { display: flex; gap: 8px; margin-bottom: 20px; }
        .search-form input { max-width: 380px; }

        .form-grid, .detail-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 13px;
            font-weight: 600;
        }

        .form-group.full { grid-column: 1 / -1; }
        textarea { min-height: 100px; resize: vertical; }

        .form-actions { display: flex; gap: 10px; margin-top: 22px; }

        .alert {
            padding: 14px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 13px;
        }

        .alert.success { background: #dcfce7; color: #166534; }
        .alert.error { background: #fee2e2; color: #991b1b; }

        .empty { text-align: center; padding: 35px 15px; color: #64748b; }
        .empty div { font-size: 38px; margin-bottom: 10px; }

        .detail-item {
            padding: 16px;
            background: #f8fafc;
            border-radius: 10px;
            overflow-wrap: anywhere;
        }

        .detail-item small { display: block; color: #64748b; margin-bottom: 8px; }
        .detail-item strong { font-size: 14px; }

        .report-box {
            padding: 20px;
            border-radius: 12px;
            background: #eff6ff;
            border-left: 4px solid #0756c9;
            margin-bottom: 18px;
            line-height: 1.8;
        }

        .report-box h4 { color: #0756c9; margin-bottom: 8px; }

        .footer {
            text-align: center;
            color: #94a3b8;
            font-size: 12px;
            padding: 10px 0 20px;
        }

        @media (max-width: 900px) {
            .sidebar { width: 205px; }
            .main { margin-left: 205px; }
            .content { padding: 18px; }
            .stats { grid-template-columns: 1fr; }
        }

        @media (max-width: 650px) {
            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
                padding: 15px;
            }

            .menu-title, .sidebar-bottom { display: none; }

            .menu {
                display: flex;
                gap: 5px;
                overflow-x: auto;
                margin-top: 12px;
            }

            .menu a { flex-shrink: 0; margin: 0; padding: 10px; font-size: 12px; }
            .main { margin-left: 0; }
            .topbar { padding: 15px; }
            .topbar h2 { font-size: 17px; }
            .profile { display: none; }
            .content { padding: 14px; }
            .welcome { padding: 22px; }
            .welcome h1 { font-size: 22px; }
            .welcome-icon { display: none; }
            .section { padding: 16px; }
            .form-grid, .detail-grid { grid-template-columns: 1fr; }
            .form-group.full { grid-column: auto; }
            .section-header { align-items: flex-start; flex-direction: column; }
        }

        @media print {
            .sidebar, .topbar, .btn, .footer, .search-form { display: none !important; }
            .main { margin-left: 0; }
            .content { padding: 0; }
            body { background: white; }
            .section { border: none; }
        }
    </style>
</head>

<body>

<aside class="sidebar">
    <div class="brand">
        <div class="brand-icon">S</div>
        <div>
            <h2>DataSiswa</h2>
            <p>Sistem Informasi Siswa</p>
        </div>
    </div>

    <div class="menu-title">MENU UTAMA</div>

    <nav class="menu">
        <a class="<?= $halaman === 'dashboard' ? 'active' : '' ?>" href="?halaman=dashboard">▦ &nbsp; Dashboard</a>
        <a class="<?= $halaman === 'data' ? 'active' : '' ?>" href="?halaman=data">♙ &nbsp; Data Siswa</a>
        <a class="<?= $halaman === 'tambah' ? 'active' : '' ?>" href="?halaman=tambah">＋ &nbsp; Tambah Siswa</a>
        <a class="<?= $halaman === 'laporan' ? 'active' : '' ?>" href="?halaman=laporan">▤ &nbsp; Laporan</a>
    </nav>

    <div class="sidebar-bottom">
        <strong>📘 Sistem Data Siswa</strong>
        <p style="margin-top:7px">Kelola data siswa dengan mudah dan teratur.</p>
    </div>
</aside>

<main class="main">
    <header class="topbar">
        <div>
            <h2>
                <?php
                $judul = [
                    'dashboard' => 'Dashboard',
                    'data' => 'Data Siswa',
                    'tambah' => 'Tambah Siswa',
                    'detail' => 'Detail Siswa',
                    'laporan' => 'Laporan Data Siswa'
                ];
                echo e($judul[$halaman]);
                ?>
            </h2>
            <p>Beranda / <?= e($judul[$halaman]) ?></p>
        </div>

        <div style="display:flex;align-items:center;gap:15px">
            <div class="profile">
                <div class="avatar">♙</div>
                <div>
                    <strong><?= e($_SESSION['username'] ?? 'admin') ?></strong>
                    <small>Administrator</small>
                </div>
            </div>

            <form method="POST" action="index.php"
                  onsubmit="return confirm('Apakah Anda yakin ingin logout?')">
                <input type="hidden" name="aksi" value="logout">
                <button class="btn-logout" type="submit">↪ Logout</button>
            </form>
        </div>
    </header>

    <div class="content">

        <?php if ($pesan !== ''): ?>
            <div class="alert <?= e($jenisPesan) ?>"><?= e($pesan) ?></div>
        <?php endif; ?>

        <?php if ($halaman === 'dashboard'): ?>

            <section class="welcome">
                <div>
                    <h1>Selamat Datang, Admin! 👋</h1>
                    <p>
                        Selamat datang di Dashboard Data Siswa.<br>
                        Kelola informasi siswa secara mudah, cepat, dan teratur.
                    </p>
                    <div style="margin-top:20px">
                        <a href="?halaman=tambah" class="btn"
                           style="background:white;color:#0756c9">+ Tambah Siswa</a>
                    </div>
                </div>
                <div class="welcome-icon">🎓</div>
            </section>

            <section class="stats">
                <div class="stat-card">
                    <div><p>Total Siswa</p><h2><?= $totalSiswa ?></h2></div>
                    <div class="stat-icon blue">♙</div>
                </div>
                <div class="stat-card">
                    <div><p>Total Kelas</p><h2><?= $totalKelas ?></h2></div>
                    <div class="stat-icon cyan">▦</div>
                </div>
                <div class="stat-card">
                    <div><p>Total Jurusan</p><h2><?= $totalJurusan ?></h2></div>
                    <div class="stat-icon purple">▤</div>
                </div>
            </section>

            <section class="section">
                <div class="section-header">
                    <div>
                        <h3>Data Siswa Terbaru</h3>
                        <p>Daftar lima siswa terakhir yang ditambahkan.</p>
                    </div>
                    <a href="?halaman=data" class="btn btn-light">Lihat Semua →</a>
                </div>

                <?php
                $dataTerbaru = array_slice(array_reverse($dataSiswa), 0, 5);
                ?>

                <?php if (count($dataTerbaru) > 0): ?>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr><th>No.</th><th>Nama</th><th>NIS</th><th>Kelas</th><th>Jurusan</th><th>Aksi</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($dataTerbaru as $i => $siswa): ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td><strong><?= e($siswa['nama'] ?? '') ?></strong></td>
                                        <td><?= e($siswa['nis'] ?? '') ?></td>
                                        <td><?= e($siswa['kelas'] ?? '') ?></td>
                                        <td><span class="badge"><?= e($siswa['jurusan'] ?? '') ?></span></td>
                                        <td><a class="btn btn-light" href="?halaman=detail&id=<?= urlencode((string)$siswa['id']) ?>">Detail</a></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty">
                        <div>📚</div>
                        <strong>Belum ada data siswa</strong>
                        <p style="margin-top:8px">Silakan tambahkan data siswa terlebih dahulu.</p>
                    </div>
                <?php endif; ?>
            </section>

        <?php elseif ($halaman === 'data'): ?>

            <section class="section">
                <div class="section-header">
                    <div>
                        <h3>Semua Data Siswa</h3>
                        <p>Total <?= $totalSiswa ?> siswa terdaftar.</p>
                    </div>
                    <a href="?halaman=tambah" class="btn">+ Tambah Siswa</a>
                </div>

                <form method="GET" class="search-form">
                    <input type="hidden" name="halaman" value="data">
                    <input type="text" name="cari"
                           placeholder="Cari nama, NIS, kelas, jurusan..."
                           value="<?= e($cari) ?>">
                    <button type="submit" class="btn">Cari</button>
                    <?php if ($cari !== ''): ?>
                        <a href="?halaman=data" class="btn btn-light">Reset</a>
                    <?php endif; ?>
                </form>

                <?php if (count($dataTampil) > 0): ?>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr><th>No.</th><th>Nama</th><th>NIS</th><th>Kelas</th><th>Jurusan</th><th>Aksi</th></tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($dataTampil as $siswa): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <td><strong><?= e($siswa['nama'] ?? '') ?></strong></td>
                                        <td><?= e($siswa['nis'] ?? '') ?></td>
                                        <td><?= e($siswa['kelas'] ?? '') ?></td>
                                        <td><span class="badge"><?= e($siswa['jurusan'] ?? '') ?></span></td>
                                        <td style="white-space:nowrap">
                                            <a class="btn btn-light" href="?halaman=detail&id=<?= urlencode((string)$siswa['id']) ?>">Detail</a>
                                            <a class="btn btn-danger"
                                               href="?hapus=<?= urlencode((string)$siswa['id']) ?>"
                                               onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty"><div>🔎</div><strong>Data tidak ditemukan</strong></div>
                <?php endif; ?>
            </section>

        <?php elseif ($halaman === 'tambah'): ?>

            <section class="section">
                <div class="section-header">
                    <div>
                        <h3>Form Tambah Siswa</h3>
                        <p>Isi seluruh kolom dengan benar.</p>
                    </div>
                </div>

                <form method="POST" action="?halaman=tambah">
                    <input type="hidden" name="aksi" value="tambah">

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nama">Nama Lengkap *</label>
                            <input id="nama" type="text" name="nama" required>
                        </div>

                        <div class="form-group">
                            <label for="nis">NIS *</label>
                            <input id="nis" type="text" name="nis" required>
                        </div>

                        <div class="form-group">
                            <label for="kelas">Kelas *</label>
                            <select id="kelas" name="kelas" required>
                                <option value="">-- Pilih Kelas --</option>
                                <option value="X">X</option>
                                <option value="XI">XI</option>
                                <option value="XII">XII</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="jurusan">Jurusan *</label>
                            <select id="jurusan" name="jurusan" required>
                                <option value="">-- Pilih Jurusan --</option>
                                <option value="RPL">RPL</option>
                                <option value="TKJ">TKJ</option>
                                <option value="DKV">DKV</option>
                                <option value="AKL">AKL</option>
                                <option value="MPLB">MPLB</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>

                        <div class="form-group full">
                            <label for="alamat">Alamat *</label>
                            <textarea id="alamat" name="alamat" required></textarea>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn">Simpan Data</button>
                        <a href="?halaman=data" class="btn btn-light">Batal</a>
                    </div>
                </form>
            </section>

        <?php elseif ($halaman === 'detail'): ?>

            <section class="section">
                <div class="section-header">
                    <div><h3>Detail Informasi Siswa</h3><p>Informasi lengkap siswa.</p></div>
                    <a href="?halaman=data" class="btn btn-light">← Kembali</a>
                </div>

                <?php if ($detailSiswa): ?>
                    <div class="detail-grid">
                        <div class="detail-item"><small>Nama Lengkap</small><strong><?= e($detailSiswa['nama'] ?? '') ?></strong></div>
                        <div class="detail-item"><small>NIS</small><strong><?= e($detailSiswa['nis'] ?? '') ?></strong></div>
                        <div class="detail-item"><small>Kelas</small><strong><?= e($detailSiswa['kelas'] ?? '') ?></strong></div>
                        <div class="detail-item"><small>Jurusan</small><strong><?= e($detailSiswa['jurusan'] ?? '') ?></strong></div>
                        <div class="detail-item"><small>Alamat</small><strong><?= nl2br(e($detailSiswa['alamat'] ?? '')) ?></strong></div>
                        <div class="detail-item"><small>Tanggal Ditambahkan</small><strong><?= e($detailSiswa['tanggal'] ?? '-') ?></strong></div>
                    </div>
                <?php else: ?>
                    <div class="empty"><div>⚠️</div><strong>Data siswa tidak ditemukan.</strong></div>
                <?php endif; ?>
            </section>

        <?php elseif ($halaman === 'laporan'): ?>

            <section class="section">
                <div class="section-header">
                    <div><h3>Laporan Data Siswa</h3><p>Ringkasan data yang tersimpan.</p></div>
                    <button class="btn" onclick="window.print()">Cetak Laporan</button>
                </div>

                <div class="report-box">
                    <h4>Ringkasan Data</h4>
                    <p>Total siswa: <strong><?= $totalSiswa ?></strong></p>
                    <p>Total kelas: <strong><?= $totalKelas ?></strong></p>
                    <p>Total jurusan: <strong><?= $totalJurusan ?></strong></p>
                    <p>Tanggal laporan: <strong><?= date('d-m-Y') ?></strong></p>
                </div>

                <?php if ($totalSiswa > 0): ?>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr><th>No.</th><th>Nama</th><th>NIS</th><th>Kelas</th><th>Jurusan</th><th>Alamat</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($dataSiswa as $i => $siswa): ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td><?= e($siswa['nama'] ?? '') ?></td>
                                        <td><?= e($siswa['nis'] ?? '') ?></td>
                                        <td><?= e($siswa['kelas'] ?? '') ?></td>
                                        <td><?= e($siswa['jurusan'] ?? '') ?></td>
                                        <td><?= e($siswa['alamat'] ?? '') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty"><div>📄</div><strong>Belum ada data untuk dilaporkan.</strong></div>
                <?php endif; ?>
            </section>

        <?php endif; ?>

        <div class="footer">
            © <?= date('Y') ?> DataSiswa — Sistem Informasi Data Siswa
        </div>
    </div>
</main>

</body>
</html>
