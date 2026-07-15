<?php
session_start();
require 'config/config.php';

// Pastikan yang mengakses adalah fotografer yang sedang login
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'fotografer') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$error_msg = '';

// 1. Tangkap ID foto dari URL
if(!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit;
}
$photo_id = mysqli_real_escape_string($conn, $_GET['id']);


// 3. PROSES SIMPAN PERUBAHAN (Jika tombol 'Simpan Perubahan' ditekan)
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul = mysqli_real_escape_string($conn, $_POST['judul']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $event_tag = mysqli_real_escape_string($conn, $_POST['event_tag']);
    $harga = $_POST['harga'];

    // Cek apakah user juga mengunggah file foto baru
    if(isset($_FILES['file_foto']) && $_FILES['file_foto']['error'] === 0) {
        $nama_file = $_FILES['file_foto']['name'];
        $ukuran_file = $_FILES['file_foto']['size'];
        $tmp_file = $_FILES['file_foto']['tmp_name'];
        $ekstensi_diizinkan = ['jpg', 'jpeg', 'png'];
        $ekstensi_file = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));

        if(in_array($ekstensi_file, $ekstensi_diizinkan) && $ukuran_file <= 10000000) {
            $nama_file_baru = uniqid('foto_', true) . '.' . $ekstensi_file;
            $path_upload = 'uploads/photos/' . $nama_file_baru;

            if(move_uploaded_file($tmp_file, $path_upload)) {
                // Ambil path foto lama untuk dihapus fisik file-nya
                $sql_get_old = "SELECT file_path FROM photos WHERE id = '$photo_id'";
                $old_path = $conn->query($sql_get_old)->fetch_assoc()['file_path'];
                if(file_exists($old_path)) {
                    unlink($old_path); // Hapus foto lama
                }

                // Update database dengan gambar baru
                $sql_update = "UPDATE photos SET judul='$judul', deskripsi='$deskripsi', event_tag='$event_tag', harga='$harga', file_path='$path_upload' WHERE id='$photo_id' AND fotografer_id='$user_id'";
                $conn->query($sql_update);
                header("Location: dashboard.php?msg=edit_sukses");
                exit;
            }
        } else {
            $error_msg = "Format foto tidak didukung atau ukuran terlalu besar! (Maks 10MB)";
        }
    } else {
        // Jika tidak ada foto baru yang diunggah, cukup update teksnya saja
        $sql_update = "UPDATE photos SET judul='$judul', deskripsi='$deskripsi', event_tag='$event_tag', harga='$harga' WHERE id='$photo_id' AND fotografer_id='$user_id'";
        if($conn->query($sql_update) === TRUE) {
            header("Location: dashboard.php?msg=edit_sukses");
            exit;
        } else {
            $error_msg = "Terjadi kesalahan database: " . $conn->error;
        }
    }
}


// 2. TAMPILKAN DATA LAMA KE DALAM FORM
// Ambil data foto dari database berdasarkan ID
$sql = "SELECT * FROM photos WHERE id = '$photo_id' AND fotografer_id = '$user_id'";
$result = $conn->query($sql);

// Jika foto tidak ditemukan (mungkin ID salah atau milik orang lain)
if($result->num_rows == 0) {
    die("Foto tidak ditemukan atau Anda tidak memiliki akses.");
}

$photo = $result->fetch_assoc();
?>

<?php include 'includes/header.php'; ?>

<main class="dashboard-container">
    <div class="upload-section" style="max-width: 600px; margin: 0 auto;">
        <h2 style="margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 10px;">Edit Foto</h2>
        
        <?php if($error_msg != ''): ?>
            <div class="error-msg"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <form action="edit_foto.php?id=<?php echo $photo['id']; ?>" method="POST" enctype="multipart/form-data" class="form-upload">
            
            <div style="text-align: center; margin-bottom: 20px;">
                <p style="font-size: 14px; color: #666; margin-bottom: 5px;">Foto Saat Ini:</p>
                <img src="<?php echo htmlspecialchars($photo['file_path']); ?>" alt="Current Photo" style="width: 200px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.2);">
            </div>

            <div class="form-group">
                <label for="judul">Judul Foto</label>
                <input type="text" id="judul" name="judul" value="<?php echo htmlspecialchars($photo['judul']); ?>" required>
            </div>
            <div class="form-group">
                <label for="deskripsi">Deskripsi</label>
                <textarea id="deskripsi" name="deskripsi" rows="3" required><?php echo htmlspecialchars($photo['deskripsi']); ?></textarea>
            </div>
            <div class="form-group">
                <label for="event_tag">Event / Lokasi (Tagging)</label>
                <input type="text" id="event_tag" name="event_tag" value="<?php echo htmlspecialchars($photo['event_tag']); ?>">
            </div>
            <div class="form-group">
                <label for="harga">Harga (Rp)</label>
                <input type="number" id="harga" name="harga" value="<?php echo htmlspecialchars($photo['harga']); ?>" required>
            </div>
            <div class="form-group">
                <label for="file_foto">Ganti Foto (Opsional - Biarkan kosong jika tidak ingin ganti)</label>
                <input type="file" id="file_foto" name="file_foto" accept="image/jpeg, image/png">
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 20px;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">Simpan Perubahan</button>
                <a href="dashboard.php" class="btn" style="background-color: #95a5a6; text-align: center; flex: 1; text-decoration: none;">Batal</a>
            </div>
        </form>
    </div>
</main>

<?php include 'includes/footer.php'; ?>