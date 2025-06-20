<?php
session_start();
include 'config.php';
$no = 1; // Inisialisasi nomor urut

// Fungsi untuk memeriksa apakah admin sudah login
function isAdminLoggedIn() {
    return isset($_SESSION['admin']);
}

// Fungsi untuk mengamankan input dari SQL Injection
function escapeInput($conn, $input) {
    return mysqli_real_escape_string($conn, trim($input));
}

// Fungsi untuk mendapatkan data siswa
function getSiswaData($conn) {
$sql = "SELECT * FROM siswa ORDER BY id ASC";
$result = $conn->query($sql);

    if (!$result) {
        error_log("Error fetching siswa data: " . $conn->error);
        return false;
    }
    return $result;
}

function getAbsensiData($conn) {
    $sql = "SELECT absensi.id, siswa.nama, absensi.status, absensi.tanggal 
            FROM absensi 
            JOIN siswa ON absensi.siswa_id = siswa.id
            ORDER BY absensi.id ASC"; // Menambahkan ORDER BY
    $result = $conn->query($sql);
    if (!$result) {
        error_log("Error fetching absensi data: " . $conn->error);
        return false;
    }
    return $result;
}

// Redirect jika bukan admin
if (!isAdminLoggedIn()) {
    header("Location: login.php");
    exit();
}

// Tambah Absensi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_absensi'])) {
    $siswa_id = escapeInput($conn, $_POST['siswa_id']);
    $status = escapeInput($conn, $_POST['status']);

    $sql = "INSERT INTO absensi (siswa_id, status, tanggal) VALUES ('$siswa_id', '$status', NOW())";
    if ($conn->query($sql) === TRUE) {
        header("Location: absensi.php?status=success&message=Absensi berhasil ditambahkan");
        exit();
    } else {
        error_log("Error adding absensi: " . $conn->error);
        header("Location: absensi.php?status=error&message=Gagal menambahkan absensi");
        exit();
    }
}

// Hapus Absensi
if (isset($_GET['delete_absensi'])) {
    $id = intval($_GET['delete_absensi']);
    
    // 1. Hapus data absensi
    $sql = "DELETE FROM absensi WHERE id=$id";
    if ($conn->query($sql)) {
        // 2. Set ulang auto increment
        $sql_reset = "ALTER TABLE absensi DROP id";
        $conn->query($sql_reset);
        
        $sql_add = "ALTER TABLE absensi ADD id INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST";
        $conn->query($sql_add);
        
        // 3. Update semua ID agar berurutan
        $sql_reorder = "SET @count = 0; 
                       UPDATE absensi SET id = @count:= @count + 1 
                       ORDER BY id;";
        $conn->multi_query($sql_reorder);
        
        header("Location: absensi.php");
        exit();
    }
}

// Update Absensi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_absensi'])) {
    $id = intval($_POST['id']);
    $id = escapeInput($conn, $id);
    $status = escapeInput($conn, $_POST['status']);

    $sql = "UPDATE absensi SET status='$status' WHERE id=$id";
    if ($conn->query($sql) === TRUE) {
        header("Location: absensi.php?status=success&message=Absensi berhasil diupdate");
         exit();
    } else {
        error_log("Error updating absensi: " . $conn->error);
        header("Location: absensi.php?status=error&message=Gagal mengupdate absensi");
        exit();
    }
}

// Ambil Data Siswa & Absensi
$siswa_result = getSiswaData($conn);
$absensi_result = getAbsensiData($conn);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi Siswa</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #A9B5DF; /* Warna paling terang: Abu-abu muda */
            color: #2D336B; /* Warna paling gelap: Biru tua */
        }

        .sidebar {
            background-color: #2D336B; /* Warna paling gelap: Biru tua */
            color: white;
            padding: 20px;
            width: 250px;
            height: 100vh;
            position: fixed;
        }

        .sidebar h4 {
            text-align: center;
            margin-bottom: 30px;
            font-weight: bold;
            color: #fff;
        }

        .sidebar .nav-link {
            color: #fff;
            padding: 10px 15px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
            display: block;
            text-align: left;
        }

        .sidebar .nav-link i {
            margin-right: 10px;
        }

        .sidebar .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .content {
            margin-left: 260px;
            width: calc(100% - 260px);
            padding: 30px;
        }

        .main-title {
            text-align: left;
            margin-bottom: 30px;
            font-size: 2rem;
            color: #2D336B; /* Warna paling gelap: Biru tua */
        }

        .card {
            border: none;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            background-color: #fff;
            margin-bottom: 20px;
        }

        .card-body {
            padding: 20px;
        }

        .form-label {
            font-weight: bold;
             color: #2D336B; /* Warna paling gelap: Biru tua */
        }

        .btn-primary {
            background-color: #7886C7; /* Warna tengah: Biru */
            border: none;
            transition: background-color 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #2D336B; /* Warna paling gelap: Biru tua */
        }

        .table {
            background-color: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .table th {
            background-color: #7886C7; /* Warna tengah: Biru */
            color: white;
            border: none;
            padding: 12px;
            text-align: left;
        }

        .table td {
            border: none;
            padding: 12px;
        }

        .table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .btn-warning {
            background-color: #ffc107;
            border: none;
        }

        .btn-warning:hover {
            background-color: #e0a800;
        }

        .btn-danger {
            background-color: #dc3545;
            border: none;
        }

        .btn-danger:hover {
            background-color: #c82333;
        }

        /* Alert Styling */
        .alert-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }

        .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }

        .edit-form {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <nav class="sidebar">
            <h4>Absensi Siswa</h4>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">
                        <i class="fas fa-tachometer-alt"></i>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="siswa.php">
                        <i class="fas fa-user-graduate"></i>
                        Data Siswa
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="absensi.php">
                        <i class="fas fa-clipboard-check"></i>
                        Absensi
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-danger" href="logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                </li>
            </ul>
        </nav>
        <div class="content">
            <h2 class="main-title">Absensi Siswa</h2>
             <?php
                if (isset($_GET['status']) && isset($_GET['message'])) {
                    $status = $_GET['status'];
                    $message = $_GET['message'];
                    echo '<div class="alert alert-' . ($status == 'success' ? 'success' : 'danger') . ' alert-dismissible fade show" role="alert">
                            ' . $message . '
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                          </div>';
                }
                ?>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Tambah Absensi</h5>
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-4">
                                <select name="siswa_id" class="form-control" required>
                                    <option value="">Pilih Siswa</option>
                                    <?php
                                        $siswa_result = $conn->query("SELECT * FROM siswa");
                                        if ($siswa_result) {
                                            while ($row = $siswa_result->fetch_assoc()) {
                                        ?>
                                            <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['nama']) ?></option>
                                        <?php 
                                            }
                                        }
                                        ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <select name="status" class="form-control" required>
                                    <option value="Hadir">Hadir</option>
                                    <option value="Sakit">Sakit</option>
                                    <option value="Terlambat">Terlambat</option>
                                    <option value="Alpha">Alpha</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" name="add_absensi" class="btn btn-primary w-100">Tambah Absensi</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered text-center">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama Siswa</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            //$absensi_result = $conn->query("SELECT absensi.id, siswa.nama, absensi.status, absensi.tanggal FROM absensi JOIN siswa ON absensi.siswa_id = siswa.id");
                            if ($absensi_result) {
                                while ($row = $absensi_result->fetch_assoc()) {
                        ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td><?= htmlspecialchars($row['nama']) ?></td>
                                <td><?= htmlspecialchars($row['status']) ?></td>
                                <td><?= $row['tanggal'] ?></td>
                                <td>
                                    <a href="absensi.php?edit_absensi=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                    <a href="absensi.php?delete_absensi=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus?')">Hapus</a>
                                </td>
                            </tr>
                        <?php 
                                }
                            }
                         ?>
                    </tbody>
                </table>
            </div>

            <!-- Form Edit Absensi -->
            <?php if (isset($_GET['edit_absensi'])) {
                $id = intval($_GET['edit_absensi']);
                $id = escapeInput($conn, $id);
                $edit_result = $conn->query("SELECT * FROM absensi WHERE id=$id");
                if ($edit_result) {
                    $edit_row = $edit_result->fetch_assoc();
            ?>
                <div class="card edit-form">
                    <div class="card-body">
                        <h5 class="card-title">Edit Absensi</h5>
                        <form method="POST" class="text-center">
                            <input type="hidden" name="id" value="<?= $edit_row['id'] ?>">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select name="status" id="status" class="form-control mx-auto" required>
                                    <option value="Hadir" <?= $edit_row['status'] == 'Hadir' ? 'selected' : '' ?>>Hadir</option>
                                    <option value="Sakit" <?= $edit_row['status'] == 'Sakit' ? 'selected' : '' ?>>Sakit</option>
                                    <option value="Terlambat" <?= $edit_row['status'] == 'Terlambat' ? 'selected' : '' ?>>Terlambat</option>
                                    <option value="Alpha" <?= $edit_row['status'] == 'Alpha' ? 'selected' : '' ?>>Alpha</option>
                                </select>
                            </div>
                            <button type="submit" name="update_absensi" class="btn btn-success">Update Absensi</button>
                        </form>
                    </div>
                </div>
            <?php 
                    } else {
                        echo "Data absensi tidak ditemukan.";
                    }
                } ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
