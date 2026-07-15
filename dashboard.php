<?php
session_start();
require 'config/config.php';

// Cek apakah user sudah login
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'fotografer') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$success_msg = '';
$error_msg = '';

// PROSES UPLOAD FOTO KE DATABASE DAN FOLDER
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file_foto'])) {
    $judul = mysqli_real_escape_string($conn, $_POST['judul']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $event_tag = mysqli_real_escape_string($conn, $_POST['event_tag']);
    $harga = $_POST['harga'];

    // Informasi File
    $nama_file = $_FILES['file_foto']['name'];
    $ukuran_file = $_FILES['file_foto']['size'];
    $tmp_file = $_FILES['file_foto']['tmp_name'];
    $error_file = $_FILES['file_foto']['error'];

    // Ekstensi yang diizinkan
    $ekstensi_diizinkan = ['jpg', 'jpeg', 'png'];
    $ekstensi_file = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));

    if($error_file === 0) {
        if(in_array($ekstensi_file, $ekstensi_diizinkan)) {
            if($ukuran_file <= 10000000) { // Max 10MB
                // Buat nama file unik agar tidak bentrok
                $nama_file_baru = uniqid('foto_', true) . '.' . $ekstensi_file;
                $path_upload = 'uploads/photos/' . $nama_file_baru;

                // Pindahkan file dari folder sementara ke folder kita
                if(move_uploaded_file($tmp_file, $path_upload)) {
                    
                    // Simpan data ke Database (Status langsung ke 'pending' bukan 'aktif')
                    $sql_insert = "INSERT INTO photos (fotografer_id, judul, deskripsi, event_tag, harga, file_path, status) 
                                   VALUES ('$user_id', '$judul', '$deskripsi', '$event_tag', '$harga', '$path_upload', 'pending')";
                    
                    if($conn->query($sql_insert) === TRUE) {
                        $success_msg = "Foto berhasil diunggah!";
                    } else {
                        $error_msg = "Gagal menyimpan ke database: " . $conn->error;
                    }
                } else {
                    $error_msg = "Gagal memindahkan file ke folder uploads.";
                }
            } else {
                $error_msg = "Ukuran file terlalu besar! Maksimal 10MB.";
            }
        } else {
            $error_msg = "Ekstensi file tidak diizinkan! Hanya JPG, JPEG, dan PNG.";
        }
    } else {
        $error_msg = "Terjadi kesalahan saat mengunggah file.";
    }
}

// AMBIL DATA PORTOFOLIO DARI DATABASE UNTUK DITAMPILKAN (Kecuali yang sudah dihapus/soft delete)
$sql_portofolio = "SELECT * FROM photos WHERE fotografer_id = '$user_id' AND status != 'dihapus' ORDER BY created_at DESC";
$result_portofolio = $conn->query($sql_portofolio);

// Hitung Statistik (Hanya hitung foto yang tidak dihapus)
$sql_stats = "SELECT COUNT(id) as total_foto FROM photos WHERE fotografer_id = '$user_id' AND status != 'dihapus'";
$res_stats = $conn->query($sql_stats);
$total_foto = $res_stats->fetch_assoc()['total_foto'];

// Hitung Foto Terjual
$sql_terjual = "SELECT COUNT(transactions.id) as foto_terjual 
                FROM transactions 
                JOIN photos ON transactions.photo_id = photos.id 
                WHERE photos.fotografer_id = '$user_id' AND transactions.status = 'lunas'";
$res_terjual = $conn->query($sql_terjual);
$foto_terjual = $res_terjual->fetch_assoc()['foto_terjual'];

// Ambil Saldo Fotografer
$sql_saldo = "SELECT saldo FROM users WHERE id = '$user_id'";
$res_saldo = $conn->query($sql_saldo);
// Jika kolom saldo belum dibuat, defaultkan 0
$saldo = ($res_saldo && $res_saldo->num_rows > 0) ? $res_saldo->fetch_assoc()['saldo'] : 0;

?>

<?php include 'includes/header.php'; ?>

<main class="dashboard-container">
    <div class="dashboard-header">
        <h2>Selamat datang, <?php echo htmlspecialchars($_SESSION['nama']); ?>!</h2>
        <p>Kelola portofolio foto dan lihat statistik penjualan Anda di sini.</p>
    </div>

    <!-- Tampilkan Notifikasi -->
    <?php if($success_msg != ''): ?>
        <div class="success-msg" style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px; border-radius: 4px;"><?php echo $success_msg; ?></div>
    <?php endif; ?>
    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'hapus_sukses'): ?>
        <div class="success-msg" style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px; border-radius: 4px;">Foto berhasil dihapus!</div>
    <?php endif; ?>
    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'edit_sukses'): ?>
        <div class="success-msg" style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px; border-radius: 4px;">Perubahan foto berhasil disimpan!</div>
    <?php endif; ?>
    <?php if($error_msg != ''): ?>
        <div class="error-msg"><?php echo $error_msg; ?></div>
    <?php endif; ?>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Foto</h3>
            <p class="stat-value"><?php echo $total_foto; ?></p>
        </div>
        <div class="stat-card">
            <h3>Foto Terjual</h3>
            <p class="stat-value"><?php echo $foto_terjual; ?></p>
        </div>
        <div class="stat-card">
            <h3>Saldo</h3>
            <p class="stat-value">Rp <?php echo number_format($saldo, 0, ',', '.'); ?></p>
        </div>
    </div>

    <div class="dashboard-actions">
        <button class="btn btn-primary" onclick="openModal()">+ Unggah Foto Baru</button>
    </div>

    <!-- Modal Popup Upload Foto -->
    <div id="uploadModal" class="modal-overlay">
        <div class="modal-content">
            <span class="modal-close" onclick="closeModal()">&times;</span>
            <h3>Formulir Unggah Foto</h3>
            <!-- WAJIB ADA enctype="multipart/form-data" AGAR BISA UPLOAD FILE -->
            <form action="dashboard.php" method="POST" enctype="multipart/form-data" class="form-upload">
                <div class="form-group">
                    <label for="judul">Judul Foto</label>
                    <input type="text" id="judul" name="judul" required>
                </div>
                <div class="form-group">
                    <label for="deskripsi">Deskripsi</label>
                    <textarea id="deskripsi" name="deskripsi" rows="3" required></textarea>
                </div>
                <div class="form-group">
                    <label for="event_tag">Event / Lokasi (Tagging)</label>
                    <input type="text" id="event_tag" name="event_tag" placeholder="Cth: Candi Borobudur, Prambanan Jazz">
                </div>
                <div class="form-group">
                    <label for="harga">Harga (Rp)</label>
                    <input type="number" id="harga" name="harga" required>
                </div>
                <div class="form-group">
                    <label for="file_foto">Pilih Foto (Max 10MB, JPG/PNG)</label>
                    <input type="file" id="file_foto" name="file_foto" accept="image/jpeg, image/png" required>
                </div>
                <button type="submit" class="btn btn-success">Upload Foto</button>
            </form>
        </div>
    </div>

    <div class="portfolio-section">
        <h3>Portofolio Terbaru</h3>
        <table class="portfolio-table">
            <thead>
                <tr>
                    <th>Foto</th>
                    <th>Judul</th>
                    <th>Harga</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if($result_portofolio->num_rows > 0): ?>
                    <?php while($row = $result_portofolio->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <!-- Menampilkan foto yang sebenarnya dan menonaktifkan klik kanan -->
                            <img src="<?php echo htmlspecialchars($row['file_path']); ?>" alt="Foto" oncontextmenu="return false;" draggable="false" style="width: 80px; height: 60px; object-fit: cover; border-radius: 4px; pointer-events: none; user-select: none;">
                        </td>
                        <td><?php echo htmlspecialchars($row['judul']); ?></td>
                        <td>Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></td>
                        <td>
                            <?php if($row['status'] == 'aktif'): ?>
                                <span class="status-active">Aktif</span>
                            <?php else: ?>
                                <span class="status-pending"><?php echo ucfirst($row['status']); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <!-- Tombol Edit diarahkan ke edit_foto.php dengan mengirimkan ID foto -->
                            <a href="edit_foto.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-edit" style="text-decoration: none;">Edit</a> 
                            
                            <!-- Tombol Hapus diarahkan ke hapus_foto.php dengan mengirimkan ID foto (id=...) -->
                            <a href="actions/hapus_foto.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-delete" style="text-decoration: none;" onclick="return confirm('Apakah Anda yakin ingin menghapus foto ini? File dan data akan hilang permanen.')">Hapus</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 20px;">Belum ada foto di portofolio Anda.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<script>
// Fungsi untuk membuka modal popup
function openModal() {
    document.getElementById("uploadModal").style.display = "flex";
}

// Fungsi untuk menutup modal popup
function closeModal() {
    document.getElementById("uploadModal").style.display = "none";
}

// Menutup modal jika user mengklik area abu-abu di luar kotak putih
window.onclick = function(event) {
    var modal = document.getElementById("uploadModal");
    if (event.target == modal) {
        modal.style.display = "none";
    }
}
</script>

<?php include 'includes/footer.php'; ?>