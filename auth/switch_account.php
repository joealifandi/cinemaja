<?php
session_start();
require '../config/config.php';

// Jika ada permintaan pindah akun
if(isset($_POST['user_id'])) {
    $user_id = (int)$_POST['user_id'];
    
    // Cari user di database
    $sql = "SELECT * FROM users WHERE id = $user_id";
    $result = $conn->query($sql);
    
    if($result && $result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        // Buat ulang Sesi Login tanpa password (Khusus untuk fitur Quick Switch)
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['role'] = $user['role'];
        
        // Arahkan ke halaman sesuai Role
        if($user['role'] == 'admin') {
            header("Location: ../admin.php");
        } else if($user['role'] == 'fotografer') {
            header("Location: ../dashboard.php");
        } else {
            header("Location: ../buyer_dashboard.php");
        }
        exit;
    }
}

// Jika gagal/tidak valid, tendang ke login
header("Location: logout.php");
exit;
?>