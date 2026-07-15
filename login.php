<?php
// session_start() digunakan untuk memulai atau melanjutkan sesi login
session_start();

// Memanggil file koneksi database
require 'config/config.php';

// Jika sudah login, redirect sesuai perannya
if(isset($_SESSION['user_id'])) {
    if($_SESSION['role'] == 'fotografer'){
        header("Location: dashboard.php");
        exit;
    } else if($_SESSION['role'] == 'buyer') {
        header("Location: gallery.php");
        exit;
    } else if($_SESSION['role'] == 'admin') {
        header("Location: admin.php");
        exit;
    }
}

$error = '';

// Jika tombol login ditekan
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    
    // 1. Ambil data dari form dan bersihkan
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    // 2. Cari user di database berdasarkan email saja
    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = $conn->query($sql);

    // Jika user ditemukan (ada 1 baris data)
    if($result->num_rows == 1) {
        // Ambil data user tersebut ke dalam bentuk array (baris)
        $user = $result->fetch_assoc();
        
        // 3. Cocokkan password yang diinput dengan password acak di database
        if(password_verify($password, $user['password'])) {
            
            // 4. Jika password benar, buat SESI LOGIN
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nama'] = $user['nama'];
            $_SESSION['role'] = $user['role'];

            // 5. Arahkan ke halaman yang sesuai
            if($user['role'] == 'fotografer') {
                header("Location: dashboard.php");
            } else if($user['role'] == 'admin') {
                header("Location: admin.php");
            } else {
                header("Location: buyer_dashboard.php");
            }
            exit;
        } else {
            $error = 'Password salah!';
        }
    } else {
        $error = 'Email tidak ditemukan!';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Cinemajaa</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="auth-wrapper">
        <!-- Left: Visual Showcase (Dribbble/SaaS Style) -->
        <div class="auth-visual" style="background-color: #1444c0; display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden;">
            <!-- Decorative Elements -->
            <div style="position: absolute; top: -10%; left: -10%; width: 50%; height: 50%; background: radial-gradient(circle, rgba(235,168,52,0.15) 0%, rgba(24, 64, 167, 0) 70%); border-radius: 50%; filter: blur(40px);"></div>
            <div style="position: absolute; bottom: -10%; right: -10%; width: 50%; height: 50%; background: radial-gradient(circle, rgba(235,168,52,0.1) 0%, rgba(11,17,32,0) 70%); border-radius: 50%; filter: blur(40px);"></div>
            
            <img src="mentah_foto/CINEMAJA2.png" alt="Cinemajaa Logo" style="max-width: 85%; height: auto; position: relative; z-index: 2; filter: drop-shadow(0 10px 20px rgba(0,0,0,0.3));">
        </div>

        <!-- Right: Authentication Form -->
        <div class="auth-form-container">
            <div class="auth-form-inner">
                <div class="brand-logo">CINEMAJA</div>
                
                <div class="auth-header">
                    <h2>Hi Abangkuhh</h2>
                    <p>Welcome back! Please enter your details.</p>
                </div>
                
                <?php if($error != ''): ?>
                    <div class="error-msg"><?php echo $error; ?></div>
                <?php endif; ?>

                <form action="login.php" method="POST">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" placeholder="Enter your email" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div style="position: relative;">
                            <input type="password" id="password" name="password" placeholder="Enter your password" required style="width: 100%; padding-right: 40px;">
                            <span onclick="togglePasswordVisibility('password', 'toggleIcon')" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;">
                                <svg id="toggleIcon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </span>
                        </div>
                    </div>
                    
                    <div class="form-options">
                        <label class="remember-me">
                            <input type="checkbox" name="remember"> Remember me
                        </label>
                        <a href="#" class="forgot-password" onclick="alert('Silakan hubungi administrator untuk mereset kata sandi Anda.')">Forgot Password?</a>
                    </div>
                    
                    <button type="submit" class="btn-auth">Sign In</button>
                </form>

                <div class="auth-divider">Or continue with</div>

                <button class="btn-social" onclick="alert('Login Google sedang disimulasikan!')">
                    <svg width="18" height="18" viewBox="0 0 18 18">
                        <path d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844c-.209 1.125-.843 2.078-1.796 2.717v2.258h2.908c1.702-1.567 2.684-3.874 2.684-6.615z" fill="#4285F4"/>
                        <path d="M9 18c2.43 0 4.467-.806 5.956-2.184l-2.908-2.258c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332A8.997 8.997 0 0 0 9 18z" fill="#34A853"/>
                        <path d="M3.964 10.707a5.416 5.416 0 0 1-.282-1.707c0-.593.102-1.17.282-1.707V4.961H.957A8.996 8.996 0 0 0 0 9c0 1.452.348 2.827.957 4.039l3.007-2.332z" fill="#FBBC05"/>
                        <path d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.844 11.426 0 9 0A8.997 8.997 0 0 0 .957 4.961l3.007 2.332C4.672 5.164 6.656 3.58 9 3.58z" fill="#EA4335"/>
                    </svg>
                    Google
                </button>

                <p class="auth-switch">Don't have an account? <a href="register.php">Create Account</a></p>
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