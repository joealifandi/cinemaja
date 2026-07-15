<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cinemajaa</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
</head>
<body>
    <header class="main-header">
        <div class="header-content">
            <h1 class="logo-text">Cinemajaa</h1>
            <nav>
                <div class="button-container">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <?php
                            $dash_link = 'buyer_dashboard.php';
                            if($_SESSION['role'] == 'fotografer') {
                                $dash_link = 'dashboard.php';
                            } elseif($_SESSION['role'] == 'admin') {
                                $dash_link = 'admin.php';
                            }
                        ?>
                        <!-- Dashboard Button -->
                        <a href="<?php echo $dash_link; ?>">
                            <button class="nav-button">
                                <svg
                                  class="icon"
                                  stroke="currentColor"
                                  fill="currentColor"
                                  stroke-width="0"
                                  viewBox="0 0 1024 1024"
                                  height="1em"
                                  width="1em"
                                  xmlns="http://www.w3.org/2000/svg"
                                >
                                  <path d="M946.5 505L560.1 118.8l-25.9-25.9a31.5 31.5 0 0 0-44.4 0L77.5 505a63.9 63.9 0 0 0-18.8 46c.4 35.2 29.7 63.3 64.9 63.3h42.5V940h691.8V614.3h43.4c17.1 0 33.2-6.7 45.3-18.8a63.6 63.6 0 0 0 18.7-45.3c0-17-6.7-33.1-18.8-45.2zM568 868H456V664h112v204zm217.9-325.7V868H632V640c0-22.1-17.9-40-40-40H432c-22.1 0-40 17.9-40 40v228H238.1V542.3h-96l370-369.7 23.1 23.1L882 542.3h-96.1z"></path>
                                </svg>
                            </button>
                        </a>
                        <!-- Gallery Button -->
                        <a href="gallery.php">
                            <button class="nav-button">
                                <svg
                                  class="icon"
                                  stroke="currentColor"
                                  fill="none"
                                  stroke-width="2"
                                  viewBox="0 0 24 24"
                                  aria-hidden="true"
                                  height="1em"
                                  width="1em"
                                  xmlns="http://www.w3.org/2000/svg"
                                >
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </button>
                        </a>
                        <!-- Account Button -->
                        <a href="account.php">
                            <button class="nav-button">
                                <svg
                                  class="icon"
                                  stroke="currentColor"
                                  fill="currentColor"
                                  stroke-width="0"
                                  viewBox="0 0 24 24"
                                  height="1em"
                                  width="1em"
                                  xmlns="http://www.w3.org/2000/svg"
                                >
                                  <path d="M12 2.5a5.5 5.5 0 0 1 3.096 10.047 9.005 9.005 0 0 1 5.9 8.181.75.75 0 1 1-1.499.044 7.5 7.5 0 0 0-14.993 0 .75.75 0 0 1-1.5-.045 9.005 9.005 0 0 1 5.9-8.18A5.5 5.5 0 0 1 12 2.5ZM8 8a4 4 0 1 0 8 0 4 4 0 0 0-8 0Z"></path>
                                </svg>
                            </button>
                        </a>
                        <!-- Cart Icon (Only for Buyer) -->
                        <?php if($_SESSION['role'] == 'buyer'): ?>
                        <a href="cart.php"> <!-- Mengarah ke keranjang (cart.php) -->
                            <button class="nav-button">
                                <svg
                                  class="icon"
                                  stroke="currentColor"
                                  fill="none"
                                  stroke-width="2"
                                  viewBox="0 0 24 24"
                                  stroke-linecap="round"
                                  stroke-linejoin="round"
                                  height="1em"
                                  width="1em"
                                  xmlns="http://www.w3.org/2000/svg"
                                >
                                  <circle cx="9" cy="21" r="1"></circle>
                                  <circle cx="20" cy="21" r="1"></circle>
                                  <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                                </svg>
                            </button>
                        </a>
                        <?php endif; ?>

                        <!-- Change Account (Switch Icon) untuk semua role -->
                        <a href="javascript:void(0);" onclick="openSwitchModal()">
                            <button class="nav-button">
                                <svg 
                                  class="icon" 
                                  stroke="currentColor" 
                                  fill="none" 
                                  stroke-width="2" 
                                  viewBox="0 0 24 24" 
                                  stroke-linecap="round" 
                                  stroke-linejoin="round" 
                                  height="1em" 
                                  width="1em" 
                                  xmlns="http://www.w3.org/2000/svg"
                                >
                                  <path d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                </svg>
                            </button>
                        </a>
                    <?php else: ?>
                        <!-- If Not Logged In -->
                        <a href="gallery.php">
                            <button class="nav-button">
                                <svg class="icon" stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </button>
                        </a>
                        <a href="login.php" style="color: white; text-decoration: none; font-weight: bold; padding: 0 10px;">Login</a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </header>

    <!-- Modal Popup Ganti Akun (Quick Switch) -->
    <div id="switchAccountModal" class="modal-overlay">
        <div class="modal-content" style="max-width: 400px; text-align: center;">
            <span class="modal-close" onclick="closeSwitchModal()">&times;</span>
            <h3 style="margin-bottom: 15px;">Ganti Akun Cepat</h3>
            <p style="font-size: 14px; color: #7f8c8d; margin-bottom: 20px;">Pilih akun yang sudah terdaftar di database untuk langsung masuk:</p>
            
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <?php
                // Ambil daftar user dari database untuk switch cepat
                // (Ini aman karena dijalankan secara internal local)
                $host_sw = "localhost"; $user_sw = "root"; $pass_sw = ""; $db_sw = "marketplace_foto";
                $conn_sw = new mysqli($host_sw, $user_sw, $pass_sw, $db_sw);
                if (!$conn_sw->connect_error) {
                    $res_users_sw = $conn_sw->query("SELECT id, nama, role, email FROM users ORDER BY role ASC");
                    if($res_users_sw && $res_users_sw->num_rows > 0) {
                        while($u_sw = $res_users_sw->fetch_assoc()) {
                            // Tandai akun yang saat ini sedang aktif login
                            $isActive = ($_SESSION['user_id'] == $u_sw['id']) ? 'border: 2px solid #2ecc71;' : '';
                            $badgeColor = ($u_sw['role'] == 'admin') ? '#e74c3c' : (($u_sw['role'] == 'fotografer') ? '#3498db' : '#2ecc71');
                            
                            echo '
                            <form action="auth/switch_account.php" method="POST" style="margin: 0; width: 100%;">
                                <input type="hidden" name="user_id" value="'.$u_sw['id'].'">
                                <button type="submit" style="background: #f8f9fa; color: #333; display: flex; justify-content: space-between; align-items: center; width: 100%; border: 1px solid #ccc; border-radius: 6px; padding: 10px 15px; text-align: left; cursor: pointer; transition: all 0.2s; box-sizing: border-box; font-family: inherit; '.$isActive.'" onmouseover="this.style.background=\'#e2e8f0\'" onmouseout="this.style.background=\'#f8f9fa\'">
                                    <div style="flex: 1; text-align: left;">
                                        <strong style="display:block; font-size: 14px; margin-bottom: 2px;">'.htmlspecialchars($u_sw['nama']).'</strong>
                                        <span style="font-size: 11px; color: #7f8c8d;">'.htmlspecialchars($u_sw['email']).'</span>
                                    </div>
                                    <span style="background: '.$badgeColor.'; color: white; font-size: 11px; padding: 4px 10px; border-radius: 12px; font-weight: bold; text-transform: uppercase; white-space: nowrap;">
                                        '.$u_sw['role'].'
                                    </span>
                                </button>
                            </form>
                            ';
                        }
                    }
                    $conn_sw->close();
                }
                ?>
            </div>
        </div>
    </div>

    <script>
    function openSwitchModal() {
        document.getElementById("switchAccountModal").style.display = "flex";
    }
    function closeSwitchModal() {
        document.getElementById("switchAccountModal").style.display = "none";
    }
    // Tutup jika klik luar modal
    window.addEventListener('click', function(event) {
        var modal = document.getElementById("switchAccountModal");
        if (event.target == modal) {
            modal.style.display = "none";
        }
    });
    </script>