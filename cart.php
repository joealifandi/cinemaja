<?php
session_start();
require 'config/config.php';

// Cek apakah user sudah login dan rolenya buyer
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'buyer') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil histori dari tabel transactions, gabungkan dengan tabel photos agar dapat judul dan file_path
$sql_beli = "SELECT transactions.*, photos.judul, photos.file_path 
             FROM transactions 
             JOIN photos ON transactions.photo_id = photos.id 
             WHERE transactions.buyer_id = '$user_id' 
             ORDER BY transactions.tanggal_transaksi DESC";
$result_pembelian = $conn->query($sql_beli);
?>

<?php include 'includes/header.php'; ?>

<main class="account-container" style="display: block; max-width: 1000px; margin: 40px auto; width: 100%;">
    <div class="account-content" style="padding: 30px; background: #fff; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
        <h2 style="margin-bottom: 25px; color: #2c3e50; border-bottom: 2px solid #eee; padding-bottom: 10px;">Keranjang & Riwayat Pembelian</h2>
        
        <table class="portfolio-table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Foto</th>
                    <th>Harga</th>
                    <th>Status</th>
                    <th>Download</th>
                </tr>
            </thead>
            <tbody>
                <?php if($result_pembelian && $result_pembelian->num_rows > 0): ?>
                    <?php while($row_beli = $result_pembelian->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo date('d M Y, H:i', strtotime($row_beli['tanggal_transaksi'])); ?></td>
                        <td><?php echo htmlspecialchars($row_beli['judul']); ?></td>
                        <td>Rp <?php echo number_format($row_beli['jumlah'], 0, ',', '.'); ?></td>
                        <td>
                            <?php if($row_beli['status'] == 'lunas'): ?>
                                <span class="status-active">Lunas</span>
                            <?php else: ?>
                                <span class="status-pending">Pending</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($row_beli['status'] == 'lunas'): ?>
                                <a href="<?php echo htmlspecialchars($row_beli['file_path']); ?>" download style="text-decoration: none;">
                                    <button class="Btn">
                                       <svg class="svgIcon" viewBox="0 0 384 512" height="1em" xmlns="http://www.w3.org/2000/svg"><path d="M169.4 470.6c12.5 12.5 32.8 12.5 45.3 0l160-160c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L224 370.8 224 64c0-17.7-14.3-32-32-32s-32 14.3-32 32l0 306.7L54.6 265.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l160 160z"></path></svg>
                                       <span class="icon2"></span>
                                       <span class="tooltip">Download</span>
                                    </button>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 20px;">Belum ada riwayat pembelian.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
