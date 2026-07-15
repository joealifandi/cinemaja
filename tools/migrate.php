<?php
require '../config/config.php';

// 1. Ubah struktur ENUM pada tabel users untuk menambahkan role 'admin'
$sql_alter = "ALTER TABLE users MODIFY COLUMN role ENUM('fotografer', 'buyer', 'admin') NOT NULL";
$conn->query($sql_alter);

// 2. Buat akun Admin bawaan (jika belum ada)
$email_admin = 'admin@example.com';
$password_admin = password_hash('123', PASSWORD_DEFAULT);

$cek_admin = $conn->query("SELECT id FROM users WHERE email = '$email_admin'");
if($cek_admin->num_rows == 0) {
    $conn->query("INSERT INTO users (nama, email, password, role) VALUES ('Administrator', '$email_admin', '$password_admin', 'admin')");
    echo "Akun Admin berhasil dibuat! Email: admin@example.com | Password: 123\n";
} else {
    echo "Akun Admin sudah ada.\n";
}

// 3. Pastikan tabel withdrawals sudah sesuai
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
echo "Tabel withdrawals siap.\n";
?>