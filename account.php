<?php
session_start();
require 'config/config.php';

// Cek apakah user sudah login, jika belum arahkan ke login
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// --- AMBIL DATA RIWAYAT UNTUK BUYER ---
$result_pembelian = null;
if($_SESSION['role'] == 'buyer') {
    // Ambil histori dari tabel transactions, gabungkan dengan tabel photos agar dapat judul dan file_path
    $sql_beli = "SELECT transactions.*, photos.judul, photos.file_path 
                 FROM transactions 
                 JOIN photos ON transactions.photo_id = photos.id 
                 WHERE transactions.buyer_id = '$user_id' 
                 ORDER BY transactions.tanggal_transaksi DESC";
    $result_pembelian = $conn->query($sql_beli);
}
// --- AMBIL DATA RIWAYAT UNTUK FOTOGRAFER ---
$saldo_tersedia = 0;
if($_SESSION['role'] == 'fotografer') {
    $sql_saldo = "SELECT saldo FROM users WHERE id = '$user_id'";
    $res_saldo = $conn->query($sql_saldo);
    if($res_saldo && $res_saldo->num_rows > 0) {
        $saldo_tersedia = $res_saldo->fetch_assoc()['saldo'];
    }
}

// PROSES PENGAJUAN WITHDRAWAL OLEH FOTOGRAFER
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajukan_wd']) && $_SESSION['role'] == 'fotografer') {
    $jumlah_tarik = (float)$_POST['jumlah_tarik'];
    $metode_tarik = mysqli_real_escape_string($conn, $_POST['metode_tarik']);
    
    if($jumlah_tarik > 0 && $jumlah_tarik <= $saldo_tersedia) {
        $conn->query("INSERT INTO withdrawals (fotografer_id, jumlah, metode, status) VALUES ('$user_id', '$jumlah_tarik', '$metode_tarik', 'pending')");
        echo "<script>alert('Pengajuan penarikan dana berhasil dikirim ke Admin. Saldo Anda akan terpotong setelah Admin mentransfer ke rekening Anda.'); window.location.href='account.php#riwayat-tarik';</script>";
    } else {
        echo "<script>alert('Gagal! Saldo tidak mencukupi atau jumlah tidak valid.'); window.location.href='account.php#riwayat-tarik';</script>";
    }
}

// Ambil histori withdrawal fotografer
$res_history_wd = null;
if($_SESSION['role'] == 'fotografer') {
    $res_history_wd = $conn->query("SELECT * FROM withdrawals WHERE fotografer_id = '$user_id' ORDER BY tanggal_request DESC");
}
?>

<?php include 'includes/header.php'; ?>

<main class="account-container">
    <div class="account-sidebar">
        <div class="profile-summary">
            <div class="profile-avatar">
                <!-- Inisial nama sebagai avatar sementara -->
                <?php echo substr($_SESSION['nama'], 0, 1); ?>
            </div>
            <h3><?php echo htmlspecialchars($_SESSION['nama']); ?></h3>
            <p class="role-badge"><?php echo ucfirst($_SESSION['role']); ?></p>
        </div>
        <ul class="account-menu">
            <li><a href="#profil" class="active">Profil Saya</a></li>
            <li><a href="#pengaturan">Pengaturan</a></li>
            <?php if($_SESSION['role'] == 'fotografer'): ?>
                <li><a href="#riwayat-tarik">Riwayat Penarikan Dana</a></li>
            <?php elseif($_SESSION['role'] == 'admin'): ?>
                <li><a href="#laporan-finansial">Laporan Finansial Sistem</a></li>
            <?php endif; ?>
        </ul>
    </div>

    <div class="account-content">
        <!-- Bagian Profil -->
        <section id="profil" class="account-section">
            <h2>Profil Saya</h2>
            <form action="#" method="POST" class="form-account"> <!-- Formulir untuk mengubah profil -->
                <div class="form-group">
                    <label for="nama">Nama Lengkap</label>
                    <input type="text" id="nama" name="nama" value="<?php echo htmlspecialchars($_SESSION['nama']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo ($_SESSION['role'] == 'fotografer') ? 'foto@example.com' : (($_SESSION['role'] == 'admin') ? 'admin@example.com' : 'buyer@example.com'); ?>" required>
                </div>
                <?php if($_SESSION['role'] == 'fotografer'): ?>
                <div class="form-group">
                    <label for="bio">Bio / Tentang Saya</label>
                    <textarea id="bio" name="bio" rows="4">Fotografer yang fokus pada keindahan alam dan budaya Indonesia.</textarea>
                </div>
                <?php endif; ?>
                
                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px;">
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    <!-- Tombol Logout Khusus di Tab Profil -->
                    <a href="auth/logout.php" class="btn btn-delete" style="text-decoration: none; padding: 10px 20px;">Keluar dari Akun (Logout)</a>
                </div>
            </form>
        </section>

        <!-- Bagian Pengaturan -->
        <section id="pengaturan" class="account-section" style="display: none; margin-top: 40px;">
            <h2>Pengaturan Akun</h2>
            <form action="#" method="POST" class="form-account">
                <h3>Ubah Password</h3>
                <div class="form-group">
                    <label for="password_lama">Password Lama</label>
                    <input type="password" id="password_lama" name="password_lama">
                </div>
                <div class="form-group">
                    <label for="password_baru">Password Baru</label>
                    <input type="password" id="password_baru" name="password_baru">
                </div>
                <div class="form-group">
                    <label for="konfirmasi_password">Konfirmasi Password Baru</label>
                    <input type="password" id="konfirmasi_password" name="konfirmasi_password">
                </div>
                
                <h3 style="margin-top: 30px;">Preferensi Notifikasi</h3>
                <div class="form-checkbox">
                    <input type="checkbox" id="notif_email" name="notif_email" checked>
                    <label for="notif_email">Terima email promosi dan pembaruan</label>
                </div>
                <?php if($_SESSION['role'] == 'fotografer'): ?>
                <div class="form-checkbox">
                    <input type="checkbox" id="notif_sales" name="notif_sales" checked>
                    <label for="notif_sales">Beri tahu saya saat ada foto yang terjual</label>
                </div>
                <?php endif; ?>

                <button type="submit" class="btn btn-primary" style="margin-top: 20px;">Simpan Pengaturan</button>
            </form>
        </section>

        <!-- Bagian Riwayat (Dinamis berdasarkan Role) -->
        <?php if($_SESSION['role'] == 'fotografer'): ?>
        <section id="riwayat-tarik" class="account-section" style="display: none; margin-top: 40px;">
            <h2>Riwayat Penarikan Dana</h2>
            
            <div class="balance-info" style="margin-bottom: 20px; padding: 20px; background: #e8f4f8; border-radius: 8px;">
                <p>Saldo Tersedia: <strong style="color: #27ae60; font-size: 1.2rem;">Rp <?php echo number_format($saldo_tersedia, 0, ',', '.'); ?></strong></p>
                
                <!-- Formulir Tarik Dana -->
                <form action="account.php" method="POST" style="margin-top: 15px; border-top: 1px solid #ccc; padding-top: 15px;">
                    <div class="form-group">
                        <label for="jumlah_tarik">Jumlah yang Ingin Ditarik (Rp)</label>
                        <input type="number" id="jumlah_tarik" name="jumlah_tarik" max="<?php echo $saldo_tersedia; ?>" min="10000" placeholder="Min. Rp 10.000" required>
                    </div>
                    <div class="form-group">
                        <label for="metode_tarik">Metode Transfer & Nomor Rekening / E-Wallet</label>
                        <input type="text" id="metode_tarik" name="metode_tarik" placeholder="Cth: Bank BCA - 123456789 a.n Budi" required>
                    </div>
                    <input type="hidden" name="ajukan_wd" value="1">
                    <button type="submit" class="btn btn-primary" style="margin-top: 10px; width: auto; padding: 10px 20px;">Ajukan Penarikan Sekarang</button>
                </form>
            </div>

            <table class="portfolio-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Jumlah</th>
                        <th>Metode</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($res_history_wd && $res_history_wd->num_rows > 0): ?>
                        <?php while($wd = $res_history_wd->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo date('d M Y, H:i', strtotime($wd['tanggal_request'])); ?></td>
                            <td>Rp <?php echo number_format($wd['jumlah'], 0, ',', '.'); ?></td>
                            <td><?php echo htmlspecialchars($wd['metode']); ?></td>
                            <td>
                                <?php if($wd['status'] == 'selesai'): ?>
                                    <span class="status-active">Selesai</span>
                                <?php elseif($wd['status'] == 'batal'): ?>
                                    <span class="status-pending" style="color: #e74c3c;">Ditolak/Batal</span>
                                <?php else: ?>
                                    <span class="status-pending">Menunggu Transfer</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 20px;">Belum ada riwayat penarikan dana.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
        <?php elseif($_SESSION['role'] == 'admin'): ?>
        <section id="laporan-finansial" class="account-section" style="display: none; margin-top: 40px;">
            <h2>Laporan Finansial Sistem (Escrow)</h2>
            <p style="margin-bottom: 20px; color: #666;">Ringkasan keuangan dari seluruh transaksi marketplace.</p>
            
            <?php
            // Hitung total pendapatan bersih sistem (Total Admin Fee 5%)
            $check_admin_fee = $conn->query("SHOW COLUMNS FROM transactions LIKE 'admin_fee'");
            $total_admin_fee = 0;
            if($check_admin_fee->num_rows > 0) {
                $admin_fee_query = $conn->query("SELECT SUM(admin_fee) as total_fee FROM transactions WHERE status = 'lunas'");
                $total_admin_fee = $admin_fee_query->fetch_assoc()['total_fee'] ?? 0;
            }

            // Hitung Total Saldo Semua Fotografer
            $total_saldo_query = $conn->query("SELECT SUM(saldo) as total_saldo_fotografer FROM users WHERE role = 'fotografer'");
            $total_saldo_fotografer = $total_saldo_query->fetch_assoc()['total_saldo_fotografer'] ?? 0;

            // Hitung Kas Sistem
            $total_income_query = $conn->query("SELECT SUM(jumlah) as total FROM transactions WHERE status = 'lunas'");
            $total_income = $total_income_query->fetch_assoc()['total'] ?? 0;
            
            $uang_keluar_query = $conn->query("SELECT SUM(jumlah) as total_keluar FROM withdrawals WHERE status = 'selesai'");
            $total_uang_keluar = $uang_keluar_query->fetch_assoc()['total_keluar'] ?? 0;
            
            $kas_sistem = $total_income - $total_uang_keluar;
            ?>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
                <div style="background: #e8f8f5; border-left: 4px solid #1abc9c; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                    <h3 style="font-size: 14px; color: #7f8c8d; margin-bottom: 10px;">Pendapatan Bersih (5%)</h3>
                    <p style="font-size: 24px; font-weight: bold; color: #16a085; margin: 0;">Rp <?php echo number_format($total_admin_fee, 0, ',', '.'); ?></p>
                </div>
                <div style="background: #eaf2f8; border-left: 4px solid #3498db; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                    <h3 style="font-size: 14px; color: #7f8c8d; margin-bottom: 10px;">Kas Sistem Saat Ini</h3>
                    <p style="font-size: 24px; font-weight: bold; color: #2980b9; margin: 0;">Rp <?php echo number_format($kas_sistem, 0, ',', '.'); ?></p>
                </div>
                <div style="background: #f4ecf7; border-left: 4px solid #9b59b6; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                    <h3 style="font-size: 14px; color: #7f8c8d; margin-bottom: 10px;">Total Saldo Kontributor</h3>
                    <p style="font-size: 24px; font-weight: bold; color: #8e44ad; margin: 0;">Rp <?php echo number_format($total_saldo_fotografer, 0, ',', '.'); ?></p>
                </div>
            </div>
            
            <p style="font-size: 14px; color: #888; background: #f8f9fa; padding: 15px; border-radius: 5px;">
                <strong>Keterangan:</strong><br>
                - <strong>Pendapatan Bersih</strong> adalah keuntungan sistem (developer) dari potongan 5% tiap penjualan.<br>
                - <strong>Kas Sistem Saat Ini</strong> adalah total uang fisik yang sekarang berada di rekening perantara (Escrow) admin.<br>
                - <strong>Total Saldo Kontributor</strong> adalah jumlah uang milik seluruh fotografer yang belum mereka tarik ke rekening pribadi.
            </p>
        </section>
        <?php endif; ?>

    </div>
</main>

<script>
// Script sederhana untuk navigasi tab di halaman account
document.addEventListener('DOMContentLoaded', function() {
    const menuLinks = document.querySelectorAll('.account-menu a');
    const sections = document.querySelectorAll('.account-section');

    menuLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Hapus class active dari semua menu
            menuLinks.forEach(l => l.classList.remove('active'));
            // Tambahkan class active ke menu yang diklik
            this.classList.add('active');

            // Sembunyikan semua section
            sections.forEach(sec => sec.style.display = 'none');

            // Tampilkan section yang sesuai dengan href target
            const targetId = this.getAttribute('href').substring(1);
            document.getElementById(targetId).style.display = 'block';
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>