<?php
session_start();
require 'config/config.php';

// Pastikan yang mengakses HANYA ADMIN
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

$msg = '';

// --- PROSES MODERASI FOTO ---
if(isset($_GET['action']) && isset($_GET['photo_id'])) {
    $action = $_GET['action'];
    $photo_id = (int)$_GET['photo_id'];

    if($action == 'approve') {
        $conn->query("UPDATE photos SET status = 'aktif' WHERE id = $photo_id");
        $msg = "Foto berhasil disetujui dan diterbitkan ke Gallery.";
    } elseif($action == 'reject') {
        $conn->query("UPDATE photos SET status = 'ditolak' WHERE id = $photo_id");
        $msg = "Foto ditolak.";
    }
    header("Location: admin.php?msg=" . urlencode($msg));
    exit;
}

// --- PROSES WITHDRAWAL (PENARIKAN DANA) ---
if(isset($_GET['action']) && isset($_GET['wd_id'])) {
    $action = $_GET['action'];
    $wd_id = (int)$_GET['wd_id'];

    if($action == 'approve_wd') {
        // Ambil data penarikan
        $wd_data = $conn->query("SELECT * FROM withdrawals WHERE id = $wd_id")->fetch_assoc();
        $fotografer_id = $wd_data['fotografer_id'];
        $jumlah = $wd_data['jumlah'];

        // Kurangi saldo fotografer
        $conn->query("UPDATE users SET saldo = saldo - $jumlah WHERE id = $fotografer_id");
        
        // Ubah status withdraw menjadi selesai
        $conn->query("UPDATE withdrawals SET status = 'selesai' WHERE id = $wd_id");
        $msg = "Penarikan dana berhasil diproses (Saldo terpotong).";
    } elseif($action == 'reject_wd') {
        $conn->query("UPDATE withdrawals SET status = 'batal' WHERE id = $wd_id");
        $msg = "Penarikan dana dibatalkan.";
    }
    header("Location: admin.php?msg=" . urlencode($msg));
    exit;
}

// --- AMBIL DATA UNTUK ADMIN PANEL ---
// 1. Foto yang butuh moderasi (status = pending)
$sql_pending_photos = "SELECT photos.*, users.nama as nama_fotografer FROM photos JOIN users ON photos.fotografer_id = users.id WHERE photos.status = 'pending' ORDER BY created_at ASC";
$res_pending_photos = $conn->query($sql_pending_photos);

// 2. Semua Transaksi (Untuk melihat arus uang pihak ketiga)
$sql_transactions = "SELECT t.*, p.judul, p.harga, u_buyer.nama as nama_buyer, u_foto.nama as nama_fotografer 
                     FROM transactions t
                     JOIN photos p ON t.photo_id = p.id
                     JOIN users u_buyer ON t.buyer_id = u_buyer.id
                     JOIN users u_foto ON p.fotografer_id = u_foto.id
                     ORDER BY t.tanggal_transaksi DESC LIMIT 10";
$res_transactions = $conn->query($sql_transactions);

// 3. Permintaan Penarikan Dana (Withdrawals) yang pending
$sql_pending_wd = "SELECT w.*, u.nama as nama_fotografer, u.saldo as saldo_saat_ini 
                   FROM withdrawals w 
                   JOIN users u ON w.fotografer_id = u.id 
                   WHERE w.status = 'pending' ORDER BY w.tanggal_request ASC";
$res_pending_wd = $conn->query($sql_pending_wd);

// Hitung total uang berputar (Total Lunas)
$total_income_query = $conn->query("SELECT SUM(jumlah) as total FROM transactions WHERE status = 'lunas'");
$total_income = $total_income_query->fetch_assoc()['total'] ?? 0;

// Pastikan kolom admin_fee ada agar query tidak error jika dipanggil pertama kali
$check_admin_fee = $conn->query("SHOW COLUMNS FROM transactions LIKE 'admin_fee'");
$total_admin_fee = 0;
if($check_admin_fee->num_rows > 0) {
    // Hitung total pendapatan bersih sistem (Total Admin Fee 5%)
    $admin_fee_query = $conn->query("SELECT SUM(admin_fee) as total_fee FROM transactions WHERE status = 'lunas'");
    $total_admin_fee = $admin_fee_query->fetch_assoc()['total_fee'] ?? 0;
}
// Hitung Total Saldo Semua Fotografer
$total_saldo_query = $conn->query("SELECT SUM(saldo) as total_saldo_fotografer FROM users WHERE role = 'fotografer'");
$total_saldo_fotografer = $total_saldo_query->fetch_assoc()['total_saldo_fotografer'] ?? 0;

// Kas Sistem (Uang yang sedang dipegang oleh pihak ketiga / admin saat ini)
// Uang masuk total dikurangi uang yang sudah ditarik dan dikurangi pendapatan admin
$uang_keluar_query = $conn->query("SELECT SUM(jumlah) as total_keluar FROM withdrawals WHERE status = 'selesai'");
$total_uang_keluar = $uang_keluar_query->fetch_assoc()['total_keluar'] ?? 0;
$kas_sistem = $total_income - $total_uang_keluar;

?>

<?php include 'includes/header.php'; ?>

<main class="dashboard-container">
    <div class="dashboard-header">
        <h2 style="color: #e74c3c;">Panel Administrator (Sistem Escrow / Pihak Ketiga)</h2>
        <p>Moderasi foto dari kontributor dan kelola penarikan dana agar transaksi aman.</p>
    </div>

    <?php if(isset($_GET['msg'])): ?>
        <div class="success-msg" style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px; border-radius: 4px;">
            <?php echo htmlspecialchars($_GET['msg']); ?>
        </div>
    <?php endif; ?>

    <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
        <div class="stat-card" style="background-color: #fcf3cf; border-left: 4px solid #f1c40f;">
            <h3>Foto Pending</h3>
            <p class="stat-value"><?php echo $res_pending_photos->num_rows; ?></p>
        </div>
        <div class="stat-card" style="background-color: #d4efdf; border-left: 4px solid #27ae60;">
            <h3>Total Uang Berputar</h3>
            <p class="stat-value">Rp <?php echo number_format($total_income, 0, ',', '.'); ?></p>
        </div>
        <div class="stat-card" style="background-color: #fadbd8; border-left: 4px solid #e74c3c;">
            <h3>Permintaan Withdraw</h3>
            <p class="stat-value"><?php echo $res_pending_wd->num_rows; ?></p>
        </div>
    </div>

    <!-- TABEL 1: MODERASI FOTO -->
    <div class="portfolio-section" style="margin-bottom: 40px;">
        <h3>Moderasi Foto Masuk</h3>
        <table class="portfolio-table">
            <thead>
                <tr>
                    <th>Foto</th>
                    <th>Detail Foto</th>
                    <th>Fotografer</th>
                    <th>Harga</th>
                    <th>Aksi Admin</th>
                </tr>
            </thead>
            <tbody>
                <?php if($res_pending_photos->num_rows > 0): ?>
                    <?php while($row = $res_pending_photos->fetch_assoc()): ?>
                    <tr>
                        <td><img src="<?php echo htmlspecialchars($row['file_path']); ?>" oncontextmenu="return false;" draggable="false" style="width: 100px; border-radius: 4px; pointer-events: none; user-select: none;"></td>
                        <td>
                            <strong><?php echo htmlspecialchars($row['judul']); ?></strong><br>
                            <span style="font-size: 0.8rem; color: #7f8c8d;"><?php echo htmlspecialchars($row['event_tag']); ?></span>
                        </td>
                        <td><?php echo htmlspecialchars($row['nama_fotografer']); ?></td>
                        <td>Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></td>
                        <td>
                            <a href="admin.php?action=approve&photo_id=<?php echo $row['id']; ?>" class="btn btn-sm btn-success" style="text-decoration: none;" onclick="return confirm('Setujui foto ini? Foto akan muncul di Gallery.')">Terima</a>
                            <a href="admin.php?action=reject&photo_id=<?php echo $row['id']; ?>" class="btn btn-sm btn-delete" style="text-decoration: none;" onclick="return confirm('Tolak foto ini?')">Tolak</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" style="text-align: center; padding: 20px;">Tidak ada foto yang butuh moderasi saat ini.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- TABEL 2: PERMINTAAN WITHDRAWAL -->
    <div class="portfolio-section" style="margin-bottom: 40px;">
        <h3>Permintaan Penarikan Dana (Withdrawals)</h3>
        <p style="font-size: 0.9rem; color: #666; margin-bottom: 10px;">Sebagai admin, Anda harus mentransfer uang ini ke rekening fotografer, lalu klik tombol "Selesai Ditransfer".</p>
        <table class="portfolio-table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Fotografer</th>
                    <th>Metode/Rekening</th>
                    <th>Jumlah Tarik</th>
                    <th>Saldo Sisa (Sblm Tarik)</th>
                    <th>Aksi Admin</th>
                </tr>
            </thead>
            <tbody>
                <?php if($res_pending_wd->num_rows > 0): ?>
                    <?php while($wd = $res_pending_wd->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo date('d M Y, H:i', strtotime($wd['tanggal_request'])); ?></td>
                        <td><?php echo htmlspecialchars($wd['nama_fotografer']); ?></td>
                        <td><?php echo htmlspecialchars($wd['metode']); ?></td>
                        <td style="color: #e74c3c; font-weight: bold;">Rp <?php echo number_format($wd['jumlah'], 0, ',', '.'); ?></td>
                        <td>Rp <?php echo number_format($wd['saldo_saat_ini'], 0, ',', '.'); ?></td>
                        <td>
                            <div style="display: flex; gap: 8px;">
                                <?php if($wd['jumlah'] <= $wd['saldo_saat_ini']): ?>
                                    <a href="admin.php?action=approve_wd&wd_id=<?php echo $wd['id']; ?>" class="btn btn-sm btn-primary" style="text-decoration: none;" onclick="return confirm('PENTING: Pastikan Anda sudah mentransfer uang ke rekening fotografer. Lanjutkan memotong saldo mereka dari sistem?')">Sudah Ditransfer</a>
                                <?php else: ?>
                                    <span style="color: red; font-size: 0.8rem; display: flex; align-items: center;">Saldo Tidak Cukup!</span>
                                <?php endif; ?>
                                <a href="admin.php?action=reject_wd&wd_id=<?php echo $wd['id']; ?>" class="btn btn-sm btn-delete" style="text-decoration: none;">Tolak</a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="text-align: center; padding: 20px;">Tidak ada permintaan penarikan dana.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- TABEL 3: LOG TRANSAKSI PIHAK KETIGA -->
    <div class="portfolio-section">
        <h3>Histori Transaksi (Uang Masuk dari Buyer)</h3>
        <table class="portfolio-table">
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Pembeli</th>
                    <th>Membeli Foto</th>
                    <th>Milik Fotografer</th>
                    <th>Jumlah Masuk</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if($res_transactions->num_rows > 0): ?>
                    <?php while($t = $res_transactions->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo date('d M, H:i', strtotime($t['tanggal_transaksi'])); ?></td>
                        <td><?php echo htmlspecialchars($t['nama_buyer']); ?></td>
                        <td><?php echo htmlspecialchars($t['judul']); ?></td>
                        <td><?php echo htmlspecialchars($t['nama_fotografer']); ?></td>
                        <td style="color: #27ae60; font-weight: bold;">+ Rp <?php echo number_format($t['jumlah'], 0, ',', '.'); ?></td>
                        <td><span class="status-active"><?php echo strtoupper($t['status']); ?></span></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="text-align: center; padding: 20px;">Belum ada transaksi.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</main>

<?php include 'includes/footer.php'; ?>