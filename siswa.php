<?php
session_start();
include 'config.php';
$no = 1; // Inisialisasi nomor urut

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = isset($_POST['nama']) ? $_POST['nama'] : '';
    $kelas = isset($_POST['kelas']) ? $_POST['kelas'] : '';
    $jurusan = isset($_POST['jurusan']) ? $_POST['jurusan'] : '';
}

// Upload foto
if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
    $foto = basename($_FILES['foto']['name']);
    $target_dir = "uploads/";
    $target_file = $target_dir . $foto;

    if (move_uploaded_file($_FILES['foto']['tmp_name'], $target_file)) {
        // Simpan ke database
        $stmt = $conn->prepare("INSERT INTO siswa (nama, kelas, jurusan, foto) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nama, $kelas, $jurusan, $foto);        
        $stmt->execute();
        $stmt->close();
    } else {
        echo "<script>alert('Gagal mengunggah foto.');</script>";
    }
} 

// Tambah Siswa
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_siswa'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $kelas = mysqli_real_escape_string($conn, $_POST['kelas']);
    $jurusan = mysqli_real_escape_string($conn, $_POST['jurusan']);

    $sql = "INSERT INTO siswa (nama, kelas, jurusan) VALUES ('$nama', '$kelas', '$jurusan')";
    $conn->query($sql);
}

// Hapus Siswa
if (isset($_GET['delete_siswa'])) {
    $id = intval($_GET['delete_siswa']);
    
    // 1. Hapus data siswa
    $sql = "DELETE FROM siswa WHERE id=$id";
    if ($conn->query($sql)) {
        // 2. Set ulang auto increment
        $sql_reset = "ALTER TABLE siswa DROP id";
        $conn->query($sql);
        
        $sql_add = "ALTER TABLE siswa ADD id INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST";
        $conn->query($sql);
        
        // 3. Update semua ID agar berurutan
        $sql_reorder = "SET @count = 0; 
                       UPDATE siswa SET id = @count:= @count + 1 
                       ORDER BY id;";
        $conn->multi_query($sql_reorder);
        
        header("Location: siswa.php");
        exit();
    }
}


$result = $conn->query("SELECT id, nama, kelas, jurusan, foto FROM siswa ORDER BY id ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Siswa</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #2D336B; /* Latar belakang utama */
            color: #fff; /* Warna teks utama menjadi putih agar kontras */
        }

        .sidebar {
            background-color: #7886C7; /* Warna sekunder untuk sidebar */
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
            color: #A9B5DF; /* Warna teks sekunder untuk judul */
        }

        .card {
            border: none;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            background-color: #A9B5DF; /* Latar belakang card lebih terang */
            margin-bottom: 20px;
        }

        .card-body {
            padding: 20px;
        }

        .form-label {
            font-weight: bold;
        }

        .btn-primary {
            background-color: #7886C7; /* Warna sekunder untuk tombol primary */
            border: none;
            transition: background-color 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #954BB3;
        }

        .table {
            background-color: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .table th {
            background-color: #7886C7; /* Warna sekunder untuk header tabel */
            color: white;
            border: none;
            padding: 12px;
            text-align: left;
        }

        .table td {
            border: none;
            padding: 12px;
            color: #333; /* Warna teks dalam tabel */
        }

        .table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .img-thumbnail {
            max-width: 100px;
            height: auto;
            border: none;
            border-radius: 5px;
        }

        .btn-danger {
            background-color: #dc3545;
            border: none;
        }

        .btn-danger:hover {
            background-color: #c82333;
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
            <h2 class="main-title">Data Siswa</h2>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Tambah Siswa</h5>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama Siswa</label>
                            <input type="text" class="form-control" id="nama" name="nama" placeholder="Nama Siswa" required>
                        </div>
                        <div class="mb-3">
                            <label for="kelas" class="form-label">Kelas</label>
                            <input type="text" class="form-control" id="kelas" name="kelas" placeholder="Kelas" required>
                        </div>
                        <div class="mb-3">
                            <label for="jurusan" class="form-label">Jurusan</label>
                            <input type="text" class="form-control" id="jurusan" name="jurusan" placeholder="Jurusan" required>
                        </div>
                        <div class="mb-3">
                            <label for="foto" class="form-label">Foto Siswa</label>
                            <input type="file" class="form-control" id="foto" name="foto" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Tambah Siswa</button>
                    </form>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama</th>
                            <th>Kelas</th>
                            <th>Jurusan</th>
                            <th>Foto</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                        <td><?= isset($row['id']) ? $row['id'] : 'N/A' ?></td>
                            <td><?= htmlspecialchars($row['nama']) ?></td>
                            <td><?= htmlspecialchars($row['kelas']) ?></td>
                            <td><?= htmlspecialchars($row['jurusan']) ?></td>
                            <td><img src="uploads/<?php echo $row['foto'] ?>" class="img-thumbnail"></td>
                            <td>
                                <!-- Edit Button -->
                                <a href="edit_siswa.php?id=<?= htmlspecialchars($row['id']) ?>" 
                                   class="btn btn-warning btn-sm me-1">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                
                                <!-- Delete Button -->
                                <form action="siswa.php" method="GET" style="display:inline;">
                                    <input type="hidden" name="delete_siswa" value="<?= htmlspecialchars($row['id']) ?>">
                                    <button type="submit" class="btn btn-danger btn-sm" 
                                            onclick="return confirm('Apakah Anda yakin ingin menghapus siswa ini?');">
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>