<?php
// config.php
$host = "localhost";
$user = "root";
$pass = ""; // Default XAMPP biasanya kosong
$db   = "marketplace_foto";

// Membuat koneksi ke MySQL Server (tanpa memilih database dulu)
$conn_server = new mysqli($host, $user, $pass);

if ($conn_server->connect_error) {
    die("Koneksi gagal: " . $conn_server->connect_error);
}

// Skrip otomatis membuat database jika belum ada
$sql_create_db = "CREATE DATABASE IF NOT EXISTS $db";
if ($conn_server->query($sql_create_db) === TRUE) {
    // Database berhasil dibuat atau sudah ada
} else {
    die("Error membuat database: " . $conn_server->error);
}

$conn_server->close();

// Sekarang konek langsung ke database yang baru saja dibuat
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi ke database gagal: " . $conn->connect_error);
}

// --- Membuat Tabel-Tabel Otomatis ---

// Tabel Users
$sql_users = "CREATE TABLE IF NOT EXISTS users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('fotografer', 'buyer') NOT NULL,
    bio TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($sql_users);

// Tabel Photos
$sql_photos = "CREATE TABLE IF NOT EXISTS photos (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    fotografer_id INT(11) NOT NULL,
    judul VARCHAR(255) NOT NULL,
    deskripsi TEXT,
    event_tag VARCHAR(100),
    harga DECIMAL(10,2) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    status ENUM('pending', 'aktif', 'ditolak') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (fotografer_id) REFERENCES users(id) ON DELETE CASCADE
)";
$conn->query($sql_photos);

// Tabel Transactions
$sql_transactions = "CREATE TABLE IF NOT EXISTS transactions (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    buyer_id INT(11) NOT NULL,
    photo_id INT(11) NOT NULL,
    jumlah DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'lunas', 'batal') DEFAULT 'pending',
    tanggal_transaksi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (buyer_id) REFERENCES users(id),
    FOREIGN KEY (photo_id) REFERENCES photos(id)
)";
$conn->query($sql_transactions);

// (Opsional) Tabel Withdrawals untuk fotografer
$sql_withdrawals = "CREATE TABLE IF NOT EXISTS withdrawals (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    fotografer_id INT(11) NOT NULL,
    jumlah DECIMAL(10,2) NOT NULL,
    metode VARCHAR(50),
    status ENUM('pending', 'selesai', 'batal') DEFAULT 'pending',
    tanggal_request TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (fotografer_id) REFERENCES users(id) ON DELETE CASCADE
)";
$conn->query($sql_withdrawals);

?>