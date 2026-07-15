<?php
session_start();
require '../config/config.php';

// Pastikan yang mengakses adalah fotografer yang sedang login
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'fotografer') {
    die("Akses ditolak.");
}

// Cek apakah ada parameter 'id' (ID foto yang akan dihapus) di URL
if(isset($_GET['id'])) {
    // Bersihkan ID agar aman
    $photo_id = mysqli_real_escape_string($conn, $_GET['id']);
    $user_id = $_SESSION['user_id'];

    // 1. Ambil data foto dari database (untuk mendapatkan path/lokasi file fisiknya)
    // Syaratnya: ID foto harus cocok, dan fotografer_id harus cocok dengan yang sedang login
    $sql_get_photo = "SELECT file_path FROM photos WHERE id = '$photo_id' AND fotografer_id = '$user_id'";
    $result = $conn->query($sql_get_photo);

    if($result->num_rows == 1) {
        $photo = $result->fetch_assoc();
        $file_path = $photo['file_path'];

        // 2. KITA TIDAK MENGHAPUS FILE FISIK & DATA (Soft Delete)
        // Karena kita menerapkan solusi "Soft Delete", kita membiarkan file fisik tetap ada
        // agar pembeli yang sudah pernah membeli foto ini tetap bisa mengunduhnya.

        // 3. Ubah status foto menjadi 'dihapus' (bukan DELETE FROM)
        $sql_delete = "UPDATE photos SET status = 'dihapus' WHERE id = '$photo_id' AND fotografer_id = '$user_id'";
        if($conn->query($sql_delete) === TRUE) {
            // Jika berhasil, redirect (kembalikan) ke dashboard dengan pesan sukses
            header("Location: ../dashboard.php?msg=hapus_sukses");
            exit;
        } else {
            echo "Error menghapus data dari database: " . $conn->error;
        }
    } else {
        echo "Foto tidak ditemukan atau Anda tidak memiliki izin untuk menghapusnya.";
    }
} else {
    echo "ID Foto tidak valid.";
}
?>