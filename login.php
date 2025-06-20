<?php
include 'config.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    // Gunakan prepared statement untuk keamanan
    $stmt = $conn->prepare("SELECT * FROM admin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        if ($password === $row['password']) { // Bandingkan password secara langsung
            session_start();
            $_SESSION['admin'] = $username;
            header("Location: index.php");
            exit();
        } else {
            $error = "Login gagal. Periksa kembali username dan password.";
        }
    } else {
        $error = "Login gagal. Periksa kembali username dan password.";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="d-flex justify-content-center align-items-center vh-100">
    <div class="card p-4" style="width: 300px;">
        <h2 class="text-center">Login Admin</h2>
        <?php if (isset($error)) : ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <input type="text" name="username" class="form-control" placeholder="Username" required>
            </div>
            <div class="mb-3 position-relative">
                <input type="password" name="password" id="password" class="form-control" placeholder="Password" required>
                <!-- Checkbox untuk Show Password -->
                <div class="form-check mt-2">
                    <input type="checkbox" id="showPassword" class="form-check-input">
                    <label for="showPassword" class="form-check-label">Show Password</label>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
    </div>
    <!-- JavaScript untuk Show Password -->
    <script>
        // Ambil elemen input password dan checkbox
        const passwordInput = document.getElementById('password');
        const showPasswordCheckbox = document.getElementById('showPassword');

        // Tambahkan event listener untuk checkbox
        showPasswordCheckbox.addEventListener('change', function () {
            // Jika checkbox dicentang, ubah tipe input menjadi 'text'
            if (this.checked) {
                passwordInput.type = 'text';
            } else {
                // Jika tidak dicentang, kembalikan tipe input menjadi 'password'
                passwordInput.type = 'password';
            }
        });
    </script>
</body>
</html>