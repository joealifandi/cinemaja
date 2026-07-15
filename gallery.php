<?php
session_start();
require 'config/config.php'; // Hubungkan halaman ini ke database

// 1. FITUR PENCARIAN
// Mengecek apakah ada kata kunci yang diketik user di kolom pencarian
$keyword = isset($_GET['keyword']) ? mysqli_real_escape_string($conn, $_GET['keyword']) : '';

// 2. MENGAMBIL DATA DARI DATABASE
// Kita butuh data foto sekaligus nama fotografernya.
// Jadi kita menggunakan JOIN untuk menggabungkan tabel 'photos' dan tabel 'users'.
// Kita hanya mengambil foto yang statusnya 'aktif'.
$sql = "SELECT photos.*, users.nama AS nama_fotografer 
        FROM photos 
        JOIN users ON photos.fotografer_id = users.id 
        WHERE photos.status = 'aktif'";

// Jika user mengetik pencarian, tambahkan aturan pencarian (LIKE)
if ($keyword != '') {
    // Cari judul atau event_tag yang mirip/mengandung kata kunci tersebut
    $sql .= " AND (photos.judul LIKE '%$keyword%' OR photos.event_tag LIKE '%$keyword%')";
}

// Urutkan foto dari yang paling baru diunggah
$sql .= " ORDER BY photos.created_at DESC";

// Jalankan perintah SQL di atas
$result = $conn->query($sql);
?>

<?php include 'includes/header.php'; ?>

<main>
    <div class="gallery-header">
        <h2>Gallery photo</h2>
        <p>Temukan dan beli foto berkualitas tinggi dari berbagai event dan lokasi wisata Indonesia.</p>
    </div>

    <!-- FORM PENCARIAN -->
    <div class="search-filter-bar">
        <form action="gallery.php" method="GET" class="search-form">
            <input type="text" name="keyword" placeholder="Cari nama event atau lokasi..." value="<?php echo htmlspecialchars($keyword); ?>">
            <button type="submit" class="btn btn-primary">Cari Foto</button>
            <?php if($keyword != ''): ?>
                <!-- Tombol Reset akan mengembalikan ke halaman gallery.php tanpa pencarian -->
                <a href="gallery.php" class="btn" style="background-color: #95a5a6; text-decoration: none; padding: 12px 25px; display: inline-block;">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- TEMPAT MENAMPILKAN FOTO -->
    <div class="photo-grid">

        <?php 
        if($result->num_rows > 0): 
            while($row = $result->fetch_assoc()): 
        ?>
            <!-- KOTAK FOTO (NEW UIVERSE CARD) -->
            <div class="card-uiverse" oncontextmenu="return false;">
                <!-- Tagging Lokasi/Event -->
                <?php if(!empty($row['event_tag'])): ?>
                    <p class="tag"><?php echo htmlspecialchars($row['event_tag']); ?></p>
                <?php endif; ?>

                <div class="wrapper">
                    <!-- Bagian Gambar dengan Watermark -->
                    <div class="card-image" style="background-image: url('<?php echo htmlspecialchars($row['file_path']); ?>');" oncontextmenu="return false;" draggable="false">
                        <div class="watermark" style="user-select: none;">CINEMAJAA</div>
                    </div>
                    
                    <div class="content">
                        <p class="title" title="<?php echo htmlspecialchars($row['judul']); ?>">
                            <?php echo htmlspecialchars($row['judul']); ?>
                        </p>
                        <p class="author">oleh <?php echo htmlspecialchars($row['nama_fotografer']); ?></p>
                        <p class="title price">Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></p>
                    </div>
                    
                    <!-- Tombol Aksi Sesuai Role -->
                    <div style="display: flex; gap: 5px; width: 100%;">
                        <button class="card-btn" style="background-color: #f1c40f; color: #333; flex: 1;" onclick="openDetailModal('<?php echo htmlspecialchars($row['file_path']); ?>', '<?php echo htmlspecialchars(addslashes($row['judul'])); ?>', '<?php echo htmlspecialchars(addslashes($row['nama_fotografer'])); ?>', '<?php echo number_format($row['harga'], 0, ',', '.'); ?>', '<?php echo htmlspecialchars(addslashes($row['deskripsi'])); ?>', '<?php echo htmlspecialchars(addslashes($row['event_tag'])); ?>')">DETAIL</button>
                        
                        <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'buyer'): ?>
                            <a href="checkout.php?photo_id=<?php echo $row['id']; ?>" style="flex: 1; text-decoration: none;">
                                <button class="card-btn" style="width: 100%;">BELI</button>
                            </a>
                        <?php elseif(!isset($_SESSION['user_id'])): ?>
                            <a href="login.php" style="flex: 1; text-decoration: none;">
                                <button class="card-btn" style="width: 100%; font-size: 0.7rem;">LOGIN</button>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php 
            endwhile; 
        else: 
        ?>
            <!-- Jika database kosong atau pencarian tidak ditemukan -->
            <div style="grid-column: 1 / -1; text-align: center; padding: 50px; background: #fff; border-radius: 8px;">
                <h3 style="color: #7f8c8d;">Tidak ada foto yang ditemukan.</h3>
            </div>
        <?php endif; ?>

    </div>
</main>

<!-- Modal Detail Foto -->
<div id="detailPhotoModal" class="modal-overlay">
    <div class="modal-content" style="max-width: 800px; padding: 20px; display: flex; flex-direction: column; gap: 20px;">
        <span class="modal-close" onclick="closeDetailModal()">&times;</span>
        
        <div style="display: flex; flex-wrap: wrap; gap: 20px;">
            <!-- Gambar Sebelah Kiri -->
            <div style="flex: 1; min-width: 300px; position: relative; overflow: hidden; border-radius: 8px; user-select: none;">
                <img id="detail-img" src="" alt="Detail Foto" oncontextmenu="return false;" draggable="false" style="width: 100%; display: block; pointer-events: none; user-select: none; -webkit-user-drag: none;">
                <!-- Watermark Layer Menutupi Seluruh Gambar -->
                <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; pointer-events: none; background-image: repeating-linear-gradient(45deg, transparent, transparent 40px, rgba(255,255,255,0.2) 40px, rgba(255,255,255,0.2) 80px); z-index: 10;">
                    <div class="watermark-center" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-30deg); font-size: 3rem; font-weight: bold; color: rgba(255, 255, 255, 0.6); letter-spacing: 3px; text-shadow: 1px 1px 3px rgba(0,0,0,0.5); white-space: nowrap; pointer-events: none; z-index: 10; user-select: none;">CINEMAJAA</div>
                </div>
            </div>
            
            <!-- Detail Informasi Sebelah Kanan -->
            <div style="flex: 1; min-width: 300px; display: flex; flex-direction: column; justify-content: flex-start;">
                <h2 id="detail-title" style="color: #2c3e50; margin-bottom: 5px;">Judul Foto</h2>
                <p style="color: #7f8c8d; font-size: 0.9rem; margin-bottom: 15px;">oleh <strong id="detail-author">Fotografer</strong></p>
                
                <span id="detail-tag" style="background: #e8f4f8; color: #2980b9; padding: 5px 10px; border-radius: 4px; font-size: 0.8rem; display: inline-block; margin-bottom: 15px; width: fit-content;">Event Tag</span>
                
                <h3 style="color: #27ae60; font-size: 1.5rem; margin-bottom: 15px;">Rp <span id="detail-price">0</span></h3>
                
                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 4px solid #3498db; margin-bottom: 20px;">
                    <h4 style="font-size: 1rem; color: #333; margin-bottom: 5px;">Deskripsi:</h4>
                    <p id="detail-desc" style="font-size: 0.95rem; color: #555; line-height: 1.5;">Deskripsi foto akan muncul di sini.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Fungsi untuk membuka modal detail foto dan mengisi data
function openDetailModal(imagePath, title, author, price, desc, tag) {
    document.getElementById('detail-img').src = imagePath;
    document.getElementById('detail-title').innerText = title;
    document.getElementById('detail-author').innerText = author;
    document.getElementById('detail-price').innerText = price;
    document.getElementById('detail-desc').innerText = desc || 'Tidak ada deskripsi.';
    
    const tagElement = document.getElementById('detail-tag');
    if (tag && tag.trim() !== '') {
        tagElement.innerText = '📍 ' + tag;
        tagElement.style.display = 'inline-block';
    } else {
        tagElement.style.display = 'none';
    }
    
    document.getElementById('detailPhotoModal').style.display = 'flex';
}

function closeDetailModal() {
    document.getElementById('detailPhotoModal').style.display = 'none';
}

// Menutup modal jika user mengklik area abu-abu di luar kotak putih
window.addEventListener('click', function(event) {
    var modal = document.getElementById('detailPhotoModal');
    if (event.target == modal) {
        closeDetailModal();
    }
});
</script>

<?php include 'includes/footer.php'; ?>