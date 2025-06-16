<?php
require 'koneksi.php'; // Menghubungkan ke database
header('Content-Type: application/json'); // Set header agar output berupa JSON

$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : ''; // Ambil parameter pencarian (keyword) dari URL, jika ada

if ($keyword !== '') {
    // Jika ada keyword, cari dokter berdasarkan nama atau spesialisasi yang mengandung keyword (case-insensitive)
    $stmt = $koneksi->prepare(
        "SELECT nama, spesialisasi FROM dokter WHERE nama LIKE CONCAT('%', ?, '%') OR spesialisasi LIKE CONCAT('%', ?, '%') ORDER BY nama ASC LIMIT 15"
    );
    $stmt->bind_param('ss', $keyword, $keyword); // Bind parameter keyword ke query
} else {
    // Jika tidak ada keyword, ambil 15 dokter pertama urut nama
    $stmt = $koneksi->prepare(
        "SELECT nama, spesialisasi FROM dokter ORDER BY nama ASC LIMIT 15"
    );
}
$stmt->execute(); // Eksekusi query
$result = $stmt->get_result(); // Ambil hasil query

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row; // Masukkan setiap baris hasil ke array $data
}
echo json_encode($data); // Keluarkan hasil dalam format JSON
