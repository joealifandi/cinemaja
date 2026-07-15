<?php
session_start();
require 'config/config.php';

// Pastikan yang mengakses adalah buyer yang sedang login
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'buyer') {
    header("Location: login.php");
    exit;
}

$buyer_id = $_SESSION['user_id'];
$error_msg = '';
$success_msg = '';

// 1. Tangkap ID Foto yang akan dibeli dari URL
if(!isset($_GET['photo_id'])) {
    header("Location: gallery.php");
    exit;
}

$photo_id = mysqli_real_escape_string($conn, $_GET['photo_id']);

// 2. Ambil detail foto dari database
$sql_photo = "SELECT photos.*, users.nama AS nama_fotografer 
              FROM photos 
              JOIN users ON photos.fotografer_id = users.id 
              WHERE photos.id = '$photo_id' AND photos.status = 'aktif'";
$result_photo = $conn->query($sql_photo);

if($result_photo->num_rows == 0) {
    die("Foto tidak ditemukan atau sudah tidak aktif.");
}

$photo = $result_photo->fetch_assoc();

// 3. Cek apakah buyer ini SUDAH PERNAH membeli foto ini sebelumnya
// (Agar tidak membeli foto yang sama 2x)
$sql_cek_beli = "SELECT id FROM transactions WHERE buyer_id = '$buyer_id' AND photo_id = '$photo_id' AND status = 'lunas'";
if($conn->query($sql_cek_beli)->num_rows > 0) {
    $error_msg = "Anda sudah pernah membeli foto ini. Silakan unduh di halaman Keranjang & Riwayat.";
}

// 4. PROSES PEMBAYARAN SIMULASI
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['konfirmasi_bayar'])) {
    
    // Jika belum pernah beli, proses transaksinya
    if($error_msg == '') {
        $harga = $photo['harga'];
        $fotografer_id = $photo['fotografer_id'];
        
        // POTONGAN ADMIN (5%)
        $potongan_admin = $harga * 0.05;
        $pendapatan_bersih = $harga - $potongan_admin;

        // A. Masukkan data ke tabel transactions (Tandai 'lunas')
        // Catat admin_fee untuk laporan
        
        // Pastikan kolom admin_fee ada di tabel transactions
        $check_admin_fee_col = $conn->query("SHOW COLUMNS FROM transactions LIKE 'admin_fee'");
        if($check_admin_fee_col->num_rows == 0) {
            $conn->query("ALTER TABLE transactions ADD COLUMN admin_fee DECIMAL(10,2) DEFAULT 0.00 AFTER jumlah");
        }

        $sql_transaksi = "INSERT INTO transactions (buyer_id, photo_id, jumlah, admin_fee, status) 
                          VALUES ('$buyer_id', '$photo_id', '$harga', '$potongan_admin', 'lunas')";
        
        if($conn->query($sql_transaksi) === TRUE) {
            
            // B. Tambahkan Saldo Fotografer (hanya pendapatan bersih 95%)
            
            // Cek apakah kolom saldo sudah ada di tabel users
            $check_saldo_col = $conn->query("SHOW COLUMNS FROM users LIKE 'saldo'");
            if($check_saldo_col->num_rows == 0) {
                $conn->query("ALTER TABLE users ADD COLUMN saldo DECIMAL(10,2) DEFAULT 0.00 AFTER bio");
            }
            
            // Update saldo fotografer HANYA dengan pendapatan bersih
            $sql_update_saldo = "UPDATE users SET saldo = saldo + $pendapatan_bersih WHERE id = '$fotografer_id'";
            $conn->query($sql_update_saldo);

            $success_msg = "Pembayaran Berhasil! Foto sekarang sudah bisa diunduh.";
        } else {
            $error_msg = "Terjadi kesalahan saat memproses transaksi.";
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<main class="dashboard-container" style="max-width: 800px; margin: 40px auto; padding: 20px;">
    
    <div style="background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
        <h2 style="text-align: center; margin-bottom: 30px; color: #2c3e50;">Checkout Pembelian</h2>

        <?php if($error_msg != ''): ?>
            <div class="error-msg" style="background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                <?php echo $error_msg; ?>
                <br><br>
                <a href="cart.php" class="btn btn-primary" style="text-decoration: none;">Ke Halaman Keranjang & Riwayat</a>
            </div>
        <?php elseif($success_msg != ''): ?>
            <div class="success-msg" style="background-color: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; text-align: center;">
                <h3 style="margin-bottom: 10px;">✅ <?php echo $success_msg; ?></h3>
                <p>Terima kasih telah membeli foto ini. Hak komersial sekarang menjadi milik Anda.</p>
                <br>
                <a href="cart.php" class="btn btn-success" style="text-decoration: none;">Pergi ke Pusat Unduhan</a>
            </div>
        <?php else: ?>

            <!-- TAMPILAN DETAIL PESANAN -->
            <div style="display: flex; gap: 20px; margin-bottom: 30px; border-bottom: 1px solid #eee; padding-bottom: 20px;">
                <div style="flex: 1;">
                    <img src="<?php echo htmlspecialchars($photo['file_path']); ?>" alt="Foto" style="width: 100%; border-radius: 8px;">
                </div>
                <div style="flex: 2;">
                    <h3 style="margin-bottom: 10px;"><?php echo htmlspecialchars($photo['judul']); ?></h3>
                    <p style="color: #7f8c8d; margin-bottom: 15px;">Fotografer: <strong><?php echo htmlspecialchars($photo['nama_fotografer']); ?></strong></p>
                    <p style="margin-bottom: 15px;"><?php echo htmlspecialchars($photo['deskripsi']); ?></p>
                    
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; display: inline-block;">
                        <p style="font-size: 14px; color: #666;">Total yang harus dibayar:</p>
                        <h2 style="color: #27ae60;">Rp <?php echo number_format($photo['harga'], 0, ',', '.'); ?></h2>
                    </div>
                </div>
            </div>

            <!-- FORM SIMULASI PEMBAYARAN -->
            <form action="checkout.php?photo_id=<?php echo $photo['id']; ?>" method="POST" style="text-align: center;">
                <p style="margin-bottom: 20px; font-size: 14px; color: #888;">
                    *Ini adalah mode simulasi. Klik tombol di bawah untuk pura-pura mengonfirmasi pembayaran berhasil melalui Payment Gateway.
                </p>
                
                <input type="hidden" name="konfirmasi_bayar" value="1">
                <div style="display: flex; justify-content: center; gap: 10px; align-items: center;">
                    <button type="submit" class="btn btn-success" style="padding: 15px 30px; font-size: 18px; border-radius: 5px;">
                        Bayar Sekarang (Simulasi)
                    </button>
                    
                    <a href="gallery.php" class="btn" style="background-color: #95a5a6; padding: 15px 30px; font-size: 18px; text-decoration: none; border-radius: 5px; display: inline-block;">
                        Batal
                    </a>
                </div>
            </form>

        <?php endif; ?>

    </div>
</main>

<?php include 'includes/footer.php'; ?>