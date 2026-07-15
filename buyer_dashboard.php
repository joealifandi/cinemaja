<?php
session_start();
require 'config/config.php';

// Pastikan yang mengakses adalah buyer yang sedang login
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'buyer') {
    header("Location: login.php");
    exit;
}

// Ambil 6 foto terbaru yang statusnya aktif untuk ditampilkan di halaman depan
$sql_latest_photos = "SELECT * FROM photos WHERE status = 'aktif' ORDER BY created_at DESC LIMIT 6";
$res_latest_photos = $conn->query($sql_latest_photos);
?>

<?php include 'includes/header.php'; ?>

<main class="buyer-dashboard">
    <!-- Hero Banner (HEADER) -->
    <div class="hero-banner">
        <h1>Cinemajaa Gallery</h1>
        <p>Temukan Karya Fotografi Event & Pariwisata Terbaik di Indonesia</p>
    </div>

    <!-- Categories / Latest Photos Section -->
    <div class="category-section">
        <h2>Karya Terbaru</h2>
        
        <div class="category-grid">
            <?php if($res_latest_photos && $res_latest_photos->num_rows > 0): ?>
                <?php while($photo = $res_latest_photos->fetch_assoc()): ?>
                <div class="category-card">
                    <!-- Menampilkan foto asli dari database dengan proteksi klik kanan -->
                    <img src="<?php echo htmlspecialchars($photo['file_path']); ?>" alt="Foto" class="category-img" oncontextmenu="return false;" draggable="false" style="pointer-events: none; user-select: none;">
                    
                    <!-- Keterangan Judul dan Tempat -->
                    <div class="category-title"><?php echo htmlspecialchars($photo['judul']); ?></div>
                    <div class="category-location">
                        📍 <?php echo htmlspecialchars($photo['event_tag'] ? $photo['event_tag'] : 'Lokasi tidak spesifik'); ?>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="grid-column: 1 / -1; text-align: center; color: #666; padding: 40px; background: #fff; border-radius: 8px;">
                    Belum ada foto yang tersedia saat ini.
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Tombol lihat semua yang mengarah ke galeri -->
        <div style="text-align: center; margin-top: 40px;">
            <a href="gallery.php" class="btn btn-primary" style="padding: 12px 30px; text-decoration: none; display: inline-block;">Lihat Semua Koleksi di Gallery</a>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>