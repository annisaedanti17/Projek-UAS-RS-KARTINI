<?php
session_start(); // Memulai sesi user
require 'koneksi.php'; // Koneksi ke database

// Cek jika belum login, redirect ke login.php
if (empty($_SESSION['email'])) {
    header('Location: login.php'); // Redirect jika belum login
    exit;
}

$dokter_id = isset($_GET['dokter_id']) ? (int)$_GET['dokter_id'] : 0; // Ambil dokter_id dari URL
$hari = isset($_GET['hari']) ? $_GET['hari'] : ''; // Ambil hari dari URL
$jam = isset($_GET['jam']) ? $_GET['jam'] : ''; // Ambil jam dari URL
$nama_dokter = '';

// Ambil nama dokter dari database berdasarkan ID
if ($dokter_id) {
    $stmt_dokter = $koneksi->prepare("SELECT nama FROM dokter WHERE id = ?"); // Query nama dokter
    $stmt_dokter->bind_param("i", $dokter_id); // Bind dokter_id
    $stmt_dokter->execute(); // Eksekusi query
    $stmt_dokter->bind_result($nama_dokter); // Ambil hasil ke $nama_dokter
    $stmt_dokter->fetch();
    $stmt_dokter->close();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Jika form disubmit
    $nik = $_POST['nik']; // Ambil NIK dari form


    $nama_lengkap   = $_POST['nama_lengkap']; // Ambil nama lengkap
    $jenis_kelamin  = $_POST['jenis_kelamin']; // Ambil jenis kelamin
    $tempat_lahir   = $_POST['tempat_lahir']; // Ambil tempat lahir
    $tanggal_lahir  = $_POST['tanggal_lahir']; // Ambil tanggal lahir
    $telepon        = $_POST['telepon']; // Ambil telepon
    $email = $_SESSION['email']; // Ambil email dari session

    $dokter_id      = $_POST['dokter_id']; // Ambil dokter_id dari form
    $hari           = $_POST['hari']; // Ambil hari dari form
    $jam            = $_POST['jam']; // Ambil jam dari form

    // Ambil nama dokter dari ID
    $nama_dokter = '';
    if ($dokter_id) {
        $stmt_dokter = $koneksi->prepare("SELECT nama FROM dokter WHERE id = ?"); // Query nama dokter
        $stmt_dokter->bind_param("i", $dokter_id);
        $stmt_dokter->execute();
        $stmt_dokter->bind_result($nama_dokter);
        $stmt_dokter->fetch();
        $stmt_dokter->close();
    }

    // Pisahkan jam mulai dan jam selesai
    $jam_parts = explode(' - ', $jam); // Pisahkan jam mulai dan selesai
    $jam_mulai = $jam_parts[0] ?? '';
    $jam_selesai = $jam_parts[1] ?? '';

    // Cek apakah user sudah pernah membuat janji dengan dokter, hari, dan jam yang sama sebelumnya
    $stmt = $koneksi->prepare("SELECT COUNT(*) as cek FROM pasien WHERE email = ? AND nama_dokter = ? AND hari_janji = ? AND jam_mulai = ?");
    $stmt->bind_param("ssss", $email, $nama_dokter, $hari, $jam_mulai);
    $stmt->execute();
    $cek = $stmt->get_result()->fetch_assoc();
    if ($cek['cek'] > 0) {
        echo "<script>alert('Anda sudah pernah membuat janji dengan dokter dan jam ini pada hari yang sama. Silakan pilih jadwal lain.'); history.back();</script>";
        exit;
    }

    // Cek jumlah janji untuk dokter, hari yang sama (kuota maksimal 10)
    $stmt = $koneksi->prepare("SELECT COUNT(*) as total FROM pasien WHERE nama_dokter = ? AND hari_janji = ?");
    $stmt->bind_param("ss", $nama_dokter, $hari);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $jumlah_pasien = $result['total'];

    $kuota_maksimal = 10; // Batas maksimal pasien per jadwal

    if ($jumlah_pasien >= $kuota_maksimal) {
        echo "<script>alert('Kuota penuh untuk jadwal ini. Silakan pilih jadwal lain.'); history.back();</script>";
        exit;
    }

    // Ambil nomor antrian terakhir
    $stmt = $koneksi->prepare("SELECT MAX(nomor_antrian) as terakhir FROM pasien WHERE nama_dokter = ? AND hari_janji = ?");
    $stmt->bind_param("ss", $nama_dokter, $hari);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $nomor_antrian = ($row['terakhir'] ?? 0) + 1; // Nomor antrian berikutnya

    // Query INSERT lengkap, pastikan field sesuai tabel pasien
    $stmt = $koneksi->prepare("INSERT INTO pasien (nik, nama_lengkap, jenis_kelamin, tempat_lahir, tanggal_lahir, telepon, email, dokter_id, nama_dokter, hari_janji, jam_mulai, jam_selesai, nomor_antrian) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param(
        "ssssssssssssi",
        $nik,
        $nama_lengkap,
        $jenis_kelamin,
        $tempat_lahir,
        $tanggal_lahir,
        $telepon,
        $email,
        $dokter_id,
        $nama_dokter,
        $hari,
        $jam_mulai,
        $jam_selesai,
        $nomor_antrian
    );

    if ($stmt->execute()) {
        echo "<script>alert('Data pasien dan janji berhasil disimpan'); window.location='home.php';</script>";
    } else {
        echo "<script>alert('Gagal menyimpan data: {$stmt->error}');</script>";
    }
    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Form Data Pasien</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="form_pasien.css">
    <link rel="stylesheet" href="home.css">
</head>
<body>
    <!-- Navbar Start -->
    <header>
      <div class="logo">
        <a href="home.php">
          <img src="Asset/Logo-Image 1.png" alt="Logo RS Kartini" />
        </a>
      </div>
      <nav>
        <a href="poli.php">Poliklinik</a>
        <a href="fasilitas.php">Fasilitas</a>
        <a href="artikel.php">Artikel</a>
        <a href="profil.php">Tentang Kami</a>
        <?php if (isset($_SESSION['nama'])): ?>
            <a href="riwayat_pelayanan.php">Riwayat Pelayanan</a>
            <span style="margin-right: 10px;">Halo, <?= htmlspecialchars($_SESSION['nama']) ?></span>
            <a href="logout.php"><button class="btn-daftar">Logout</button></a>
        <?php else: ?>
            <a href="register.php"><button class="btn-daftar">Daftar</button></a>
        <?php endif; ?>
      </nav>
    </header>
    <!-- Navbar End -->

    <main class="form-pasien-container">
      <h2 class="form-title">Data Pribadi</h2>
      <form method="POST" action="" class="form-pasien">
        <?php if ($dokter_id && $hari && $jam): ?>
  <div class="form-info">
    <p style="padding: 10px; background: #e7f3ff; border-left: 5px solid #2586d0;">
      Anda membuat janji dengan dokter <strong><?= htmlspecialchars($nama_dokter) ?></strong> pada 
      <strong><?= htmlspecialchars($hari) ?></strong> pukul <strong><?= htmlspecialchars($jam) ?></strong>.
    </p>
  </div>
<?php endif; ?>
        <input type="hidden" name="dokter_id" value="<?= $dokter_id ?>">
        <input type="hidden" name="hari" value="<?= htmlspecialchars($hari) ?>">
        <input type="hidden" name="jam" value="<?= htmlspecialchars($jam) ?>">

          <div class="form-group">
                <label for="nik">Nomor Induk Kependudukan (NIK) *</label>
                <input type="text" id="nik" name="nik" maxlength="16" placeholder="16 Digit NIK" required onblur="cekNIK()">
                <small id="nik-error" style="color:red;"></small>
            </div>

          <div class="form-group">
              <label for="jenis_kelamin">Jenis Kelamin *</label>
              <select id="jenis_kelamin" name="jenis_kelamin" required>
                  <option value="" disabled selected>Pilih Jenis Kelamin</option>
                  <option value="Laki-laki">Laki-laki</option>
                  <option value="Perempuan">Perempuan</option>
              </select>
          </div>
          <div class="form-group">
              <label for="nama_lengkap">Nama Lengkap *</label>
              <input type="text" id="nama_lengkap" name="nama_lengkap" placeholder="Nama Lengkap" required>
          </div>
          <div class="form-group">
              <label for="tanggal_lahir">Tanggal Lahir *</label>
              <input type="date" id="tanggal_lahir" name="tanggal_lahir" placeholder="yyyy-mm-dd" required>
          </div>
          <div class="form-group">
              <label for="tempat_lahir">Tempat Lahir *</label>
              <input type="text" id="tempat_lahir" name="tempat_lahir" placeholder="Tempat Lahir" required>
          </div>
          <div class="form-group">
              <label for="telepon">Telepon Seluler *</label>
              <input type="text" id="telepon" name="telepon" placeholder="08xx" required>
          </div>
          <?php if (!empty($_SESSION['email'])): ?>
                <div class="form-group-full">
                    <label>Email Anda:</label>
                    <p><?= htmlspecialchars($_SESSION['email']) ?></p>
                </div>
            <?php endif; ?>

          <div class="form-group-full" style="text-align:right;">
              <button type="submit" class="btn-submit">Submit</button>
          </div>
      </form>
    </main>

 
 <!-- Footer -->
<footer class="footer">
  <div class="footer-container">

    <!-- Google Maps API -->
    <div class="footer-map">
      <div id="googleMap" style="width: 100%; height: 250px;"></div>
    </div>

    <!-- Footer Info -->
    <div class="footer-info">
      <p>
        Jalan Ciledug Raya No. 94-96, Cipulir, Kebayoran Lama,<br />
        RT.13/RW.6, Cipulir, Kby. Lama, Kota Jakarta Selatan,<br />
        Daerah Khusus Ibukota Jakarta 12230
      </p>
      <div class="footer-social">
        <a href="https://www.facebook.com/kartini.hospital.79/" target="_blank">
          <img src="Asset/Logo-03.png" alt="Facebook" />
        </a>
        <a href="https://www.instagram.com/kartini.hospital?igsh=dDBsaGFnYm8xZ255" target="_blank">
          <img src="Asset/Logo-02.png" alt="Instagram" />
        </a>
      </div>
    </div>

  </div>

  <div class="footer-bottom">
    <p>All Rights Reserved ©2025 Kelompok Annisa Eka Danti, Desna Romarta Tambun, Fitria Andriana Sari</p>
  </div>
</footer>

<!-- Script JS -->

<script async defer
  src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBruozd2y6BfdCpnCy0JpyMeh8sv66Ksvc&callback=initialize">
</script>

    <script>
function cekNIK() {
    const nik = document.getElementById('nik').value;
    const errorText = document.getElementById('nik-error');
    // Tambahkan email session ke parameter
    const email = "<?= isset($_SESSION['email']) ? $_SESSION['email'] : '' ?>";

    fetch('cek_nik.php?nik=' + nik + '&email=' + encodeURIComponent(email))
        .then(response => response.json())
        .then(data => {
            if (!data.valid) {
                errorText.textContent = "NIK yang anda masukan salah";
                document.querySelector('button[type="submit"]').disabled = true;
            } else {
                errorText.textContent = "";
                document.querySelector('button[type="submit"]').disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            errorText.textContent = "Terjadi kesalahan saat mengecek NIK.";
            document.querySelector('button[type="submit"]').disabled = true;
        });
}
</script>


    <script src="script.js"></script>
  </body>
</html>

<!-- Catatan: cekNIK() akan mengecek NIK ke seluruh database pasien melalui cek_nik.php
Pastikan cek_nik.php melakukan pengecekan tanpa filter email/session -->