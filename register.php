<?php
// session_start() digunakan untuk memulai sesi. Sesi ini seperti "memori sementara" 
// yang mengingat siapa yang sedang login saat pindah-pindah halaman.
session_start();

// Memanggil file config.php agar halaman ini terhubung ke database
require 'config/config.php';

// Cek jika pengguna sudah login, langsung arahkan ke halamannya, tidak perlu daftar lagi
if(isset($_SESSION['user_id'])) {
    if($_SESSION['role'] == 'fotografer'){
        header("Location: dashboard.php");
        exit;
    } else if($_SESSION['role'] == 'buyer') {
        header("Location: buyer_dashboard.php");
        exit;
    } else if($_SESSION['role'] == 'admin') {
        header("Location: admin.php");
        exit;
    }
}

$error = '';
$success = '';

// Mengecek apakah form sudah di-submit (tombol Daftar ditekan)
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    
    // 1. Mengambil data yang diisi di form
    // mysqli_real_escape_string digunakan untuk membersihkan inputan agar aman dari serangan hacker (SQL Injection)
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $role = mysqli_real_escape_string($conn, $_POST['role']);

    // 2. Mengecek apakah email tersebut sudah pernah didaftarkan sebelumnya
    $cek_email = "SELECT id FROM users WHERE email = '$email'";
    $hasil_cek = $conn->query($cek_email);

    if($hasil_cek->num_rows > 0) {
        // Jika hasil pencarian > 0, berarti email sudah ada di database
        $error = 'Email ini sudah terdaftar. Silakan gunakan email lain atau Login.';
    } else {
        // 3. Mengacak (Hash) Password
        // Kita tidak boleh menyimpan password asli di database. Kita ubah menjadi kode acak.
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // 4. Menyimpan data ke dalam tabel users di database
        $sql_insert = "INSERT INTO users (nama, email, password, role) VALUES ('$nama', '$email', '$hashed_password', '$role')";
        
        if($conn->query($sql_insert) === TRUE) {
            $success = 'Pendaftaran berhasil! Silakan masuk menggunakan akun baru Anda.';
        } else {
            $error = 'Terjadi kesalahan sistem: ' . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Cinemajaa</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="auth-wrapper">
        <!-- Left: Visual Showcase (Dribbble/SaaS Style) -->
        <div class="auth-visual" style="background-color: #1444c0; display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden;">
            <!-- Decorative Elements -->
            <div style="position: absolute; top: -10%; left: -10%; width: 50%; height: 50%; background: radial-gradient(circle, rgba(235,168,52,0.15) 0%, rgba(11,17,32,0) 70%); border-radius: 50%; filter: blur(40px);"></div>
            <div style="position: absolute; bottom: -10%; right: -10%; width: 50%; height: 50%; background: radial-gradient(circle, rgba(235,168,52,0.1) 0%, rgba(11,17,32,0) 70%); border-radius: 50%; filter: blur(40px);"></div>
            
            <img src="mentah_foto/CINEMAJA2.png" alt="Cinemajaa Logo" style="max-width: 85%; height: auto; position: relative; z-index: 2; filter: drop-shadow(0 10px 20px rgba(0,0,0,0.3));">
        </div>

        <!-- Right: Authentication Form -->
        <div class="auth-form-container">
            <div class="auth-form-inner">
                <div class="brand-logo">CINEMAJA</div>
                
                <div class="auth-header">
                    <h2>Join Cinemaja</h2>
                    <p>Mulai perjalanan kreatif Anda bersama kami hari ini.</p>
                </div>
                
                <?php if($error != ''): ?>
                    <div class="error-msg"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if($success != ''): ?>
                    <div class="success-msg" style="background-color: #D1FAE5; color: #065F46; padding: 12px 16px; margin-bottom: 20px; border-radius: 10px; border: 1px solid #A7F3D0; font-weight: 500; font-size: 14px;"><?php echo $success; ?></div>
                <?php endif; ?>

                <form action="register.php" method="POST">
                    <div class="form-group">
                        <label for="nama">Full Name</label>
                        <input type="text" id="nama" name="nama" placeholder="Enter your full name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" placeholder="Enter your email" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div style="position: relative;">
                            <input type="password" id="password" name="password" placeholder="Create a strong password" required style="width: 100%; padding-right: 40px;">
                            <span onclick="togglePasswordVisibility('password', 'toggleIcon')" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;">
                                <svg id="toggleIcon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="role">Bergabung Sebagai</label>
                        <select id="role" name="role" required style="cursor: pointer;">
                            <option value="buyer">Pembeli (Cari Foto)</option>
                            <option value="fotografer">Fotografer (Jual Foto)</option>
                        </select>
                    </div>
                    
                    <div class="form-options">
                        <label class="remember-me">
                            <input type="checkbox" name="terms" required> I agree to Terms & Privacy Policy
                        </label>
                    </div>
                    
                    <button type="submit" class="btn-auth">Create Account</button>
                </form>

                <p class="auth-switch">Already have an account? <a href="login.php">Sign In</a></p>
            </div>
        </div>
    </div>
    <script>
        function togglePasswordVisibility(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const toggleIcon = document.getElementById(iconId);
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
            } else {
                passwordInput.type = 'password';
                toggleIcon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
            }
        }
    </script>
</body>
</html>