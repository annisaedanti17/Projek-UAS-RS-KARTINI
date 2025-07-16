<?php
require 'koneksi.php';
session_start();
if (!isset($_SESSION['email'])) {
    header('Location: login.php');
    exit;
}

// Menggunakan prepared statement untuk keamanan
$stmt = $koneksi->prepare("SELECT * FROM pasien WHERE email = ?");
$stmt->bind_param("s", $_SESSION['email']);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Pelayanan</title>
    <link rel="stylesheet" href="home.css" />
    <style>
        body {
             background: #f8fafc;
             font-family: 'Segoe UI', 'Poppins', Arial, sans-serif;
             margin: 0;
            padding: 0;
        }
        .riwayat-container {
             max-width: 650px;
             margin: 48px auto 32px auto;
             border-radius: 16px;
             box-shadow: 0 6px 32px rgba(44,120,220,0.10);
             padding: 36px 32px 28px 32px;
        }
        .riwayat-title {
            text-align: center;
            color: #f47b20;
            margin-bottom: 24px;
            font-size: 1.5em;
            font-weight: bold;
        }
        .riwayat-card {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 24px;
            padding: 24px 20px 18px 20px;
            position: relative;
        }
        .riwayat-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }
        .riwayat-faskes {
            font-weight: bold;
            font-size: 1.1em;
            color: #222;
        }
        .riwayat-date {
            font-size: 0.95em;
            color: #2586d0; 
            margin-left: 10px;
        }
        .riwayat-rating {
            color: #FFD600;
            font-size: 1.1em;
            margin-left: 8px;
        }
        .riwayat-section {
            margin-bottom: 10px;
        }
        .riwayat-label {
            font-weight: bold;
            color: #2586d0;
            margin-right: 6px;
        }
        .riwayat-value {
            color: #222;
        }
        .riwayat-actions {
            margin-top: 12px;
            text-align: right;
        }
        .riwayat-actions a {
            color: #2586d0;
            text-decoration: none;
            margin-left: 12px;
            font-size: 0.95em;
        }
        .btn-buat-janji {
            display: inline-block;
            background: #f47b20;
            color: #fff;
            padding: 10px 28px;
            border-radius: 24px;
            font-weight: bold;
            font-size: 1em;
            text-decoration: none;
            box-shadow: 0 2px 8px rgba(37,134,208,0.10);
            transition: background 0.2s, box-shadow 0.2s;
            border: none;
            outline: none;
        }
        .btn-buat-janji:hover {
            background: #00c6a7;
            box-shadow: 0 4px 16px rgba(37,134,208,0.18);
            color: #fff;
        }
        @media (max-width: 600px) {
            .riwayat-container { max-width: 100%; }
            .riwayat-card { padding: 16px 8px; }
        }
    </style>
</head>
<body>
    <!-- Header & Navigation -->
    <header>
      <div class="logo">
        <a href="home.php"
          ><img src="Asset/Logo-Image 1.png" alt="Logo RS Kartini"
        /></a>
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
    <!-- End Header & Navigation -->

    <div class="riwayat-container">
        <div class="riwayat-title">Riwayat Pelayanan</div>
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $created_at = isset($row['created_at']) ? htmlspecialchars(date('d-m-Y', strtotime($row['created_at']))) : '';

                ?>
                <div class="riwayat-card">
                    <div class="riwayat-header">
                        <div>
                            <div class="riwayat-faskes"><?= htmlspecialchars($row['nama_dokter']) ?></div>
                            <div class="riwayat-date"><?= htmlspecialchars($row['hari_janji']) ?> <?= htmlspecialchars($row['jam_mulai']) ?> - <?= htmlspecialchars($row['jam_selesai']) ?></div>
                        </div>
                        <?php if ($created_at): ?>
                            <div class="riwayat-date" style="font-size:0.95em;color:#2586d0; min-width:110px; text-align:right;">
                                 <?= $created_at ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="riwayat-section">
                        <span class="riwayat-label">Nama Pasien:</span>
                        <span class="riwayat-value"><?= htmlspecialchars($row['nama_lengkap']) ?></span>
                    </div>
                    <div class="riwayat-section">
                        <span class="riwayat-label">Nomor Antrian:</span>
                        <span class="riwayat-value"><?= htmlspecialchars($row['nomor_antrian']) ?></span>
                    </div>
                    <div class="riwayat-section">
                        <a href="caridokter.php" class="btn-buat-janji">Buat Janji Baru</a>
                    </div>
                    <div class="riwayat-actions">
                        <a href="edit_form.php?id=<?= urlencode($row['id']) ?>">Edit Jadwal</a>
                        <a href="delate_form.php?id=<?= urlencode($row['id']) ?>" onclick="return confirm('Yakin ingin menghapus janji ini?')">Hapus</a>
                    </div>
                </div>
                <?php
            }
        } else {
            echo "<div class='riwayat-card'><div class='riwayat-section'>Tidak ada riwayat pelayanan ditemukan.</div></div>";
        }
        $stmt->close();
        ?>
    </div>

    <!-- Footer ------>
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
    <!-- End Footer -->

    <script async defer
      src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBruozd2y6BfdCpnCy0JpyMeh8sv66Ksvc&callback=initialize">
    </script>
    <script src="script.js"></script>
    <script>
    </script>
</body>
</html>
