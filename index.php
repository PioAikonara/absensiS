<?php
session_start();
include 'config.php';

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

// --- DASHBOARD ---
$total_siswa = 0;
$hadir = 0;
$sakit = 0;
$izin = 0;
$alpha = 0;

$result = $conn->query("SELECT COUNT(*) AS total FROM siswa");
if ($result) {
    $total_siswa = $result->fetch_assoc()['total'];
}

$statuses = ['Hadir', 'Sakit', 'Izin', 'Alpha'];
$absensi_counts = [];

foreach ($statuses as $status) {
    $query = $conn->query("SELECT COUNT(*) AS total FROM absensi WHERE status='$status'");
    $absensi_counts[$status] = $query ? $query->fetch_assoc()['total'] : 0;
}

$hadir = $absensi_counts['Hadir'];
$sakit = $absensi_counts['Sakit'];
$izin = $absensi_counts['Izin'];
$alpha = $absensi_counts['Alpha'];

// --- ABSENSI ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_absensi'])) {
    $siswa_id = mysqli_real_escape_string($conn, $_POST['siswa_id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    $sql = "INSERT INTO absensi (siswa_id, status, tanggal) VALUES ('$siswa_id', '$status', NOW())";
    $conn->query($sql);
    header("Location: absensi.php");
    exit();
}

if (isset($_GET['delete_absensi'])) {
    $id = intval($_GET['delete_absensi']);
    $sql = "DELETE FROM absensi WHERE id=$id";
    $conn->query($sql);
    header("Location: absensi.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_absensi'])) {
    $id = intval($_POST['id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    $sql = "UPDATE absensi SET status='$status' WHERE id=$id";
    $conn->query($sql);
    header("Location: absensi.php");
    exit();
}

$siswa_result = $conn->query("SELECT * FROM siswa");
$absensi_result = $conn->query("SELECT absensi.id, siswa.nama, absensi.status, absensi.tanggal FROM absensi JOIN siswa ON absensi.siswa_id = siswa.id");
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
    text-align: center; /* Pusatkan konten */
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

.edit-form {
    margin-top: 20px;
}

/* Tombol Tambah Siswa dan Choose File */
.btn-tambah, .custom-file-upload {
    background-color: #7886C7; /* Warna tengah: Biru */
    color: white;
    border: none;
    padding: 12px 20px;
    font-size: 16px;
    border-radius: 5px;
    cursor: pointer;
    transition: background 0.3s ease-in-out, transform 0.2s;
}

.btn-tambah:hover, .custom-file-upload:hover {
    background-color: #2D336B; /* Warna paling gelap: Biru tua */
    transform: scale(1.05);
}

.btn-tambah:disabled {
    background-color: #cccccc;
    cursor: not-allowed;
}

input[type="file"] {
    display: none; /* Sembunyikan input file default */
}
/* Add to your existing <style> section */
.program-keahlian .status-item {
    padding: 15px;
    margin-bottom: 10px;
    background-color: rgba(120, 134, 199, 0.1);
    border-radius: 8px;
    transition: transform 0.2s;
}

.program-keahlian .status-item:hover {
    transform: translateX(5px);
    background-color: rgba(120, 134, 199, 0.2);
}

.program-keahlian i {
    color: #7886C7;
}

.program-keahlian span {
    font-weight: 500;
}

.school-info-container {
    text-align: justify;
}

.card-title {
    border-bottom: 2px solid rgba(120, 134, 199, 0.2);
    padding-bottom: 10px;
}

/* Add these styles to your existing CSS */
.info-container {
    display: flex;
    justify-content: center;
    align-items: center;
    margin: 30px 0;
}

.info-item {
    text-align: center;
    padding: 15px;
}

.attendance-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 30px;
    margin: 20px 0;
}

.attendance-item {
    text-align: center;
    padding: 15px;
    background-color: rgba(120, 134, 199, 0.1);
    border-radius: 10px;
}

.attendance-item p {
    margin: 5px 0;
    font-weight: 500;
}

.attendance-item h3 {
    margin: 0;
    font-weight: bold;
    color: #2D336B;
}

.card {
    height: 100%;
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-5px);
}

.text-success { color: #28a745 !important; }
.text-warning { color: #ffc107 !important; }
.text-info { color: #17a2b8 !important; }
.text-danger { color: #dc3545 !important; }

.btn-primary {
    margin-top: auto;
    width: 50%;
    margin-left: auto;
    margin-right: auto;
}

/* Add to your existing */
 style> section
.status-container {
    display: flex;
    flex-direction: column;
    gap: 15px;
    padding: 10px;
}

.status-item {
    display: flex;
    align-items: center;
    padding: 30px;
    background-color: rgba(120, 134, 199, 0.1);
    border-radius: 10px;
    transition: transform 0.2s;
}

.status-item:hover {
    transform: translateX(5px);
    background-color: rgba(120, 134, 199, 0.2);
}

.status-item i {
    width: 24px;
    text-align: center;
}

.status-item span {
    font-size: 0.9rem;
    color: #2D336B;
}

/* Update existing column widths */
.col-md-4 {
    padding: 30px;
}

/* Add to your existing <style> section */
.school-info-container {
    padding: 15px;
    text-align: left;
}

.mission-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.mission-list li {
    margin-bottom: 15px;
    padding-left: 25px;
    position: relative;
}

.mission-list li:before {
    content: "•";
    color: #7886C7;
    font-weight: bold;
    position: absolute;
    left: 0;
}

.card-text {
    line-height: 1.6;
    font-size: 1.1rem;
    color: #2D336B;
    font-style: italic;
}

/* Update existing card styles */
.card {
    height: 100%;
    transition: transform 0.2s;
    margin-bottom: 20px;
}

.card:hover {
    transform: translateY(-5px);
}

.card-title i {
    color: #7886C7;
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
                        Dasbor
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
                        Keluar
                    </a>
                </li>
            </ul>
        </nav>
        <div class="content">
            <?php
            $current_page = basename($_SERVER['PHP_SELF']);
            if ($current_page == 'index.php' || $current_page == 'absensi.php') { ?>
                <h2 class="main-title text-center">Selamat Datang di Sistem Absensi Siswa</h2>

<div class="row mb-4">
    <!-- Existing Data Siswa Card -->
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title mb-3">Data Siswa</h5>
                <div class="info-container">
                    <div class="info-item">
                        <i class="fas fa-users fa-2x mb-2 text-primary"></i>
                        <p class="card-text">Total Siswa</p>
                        <h3 class="mb-0"><strong><?php echo $total_siswa; ?></strong></h3>
                    </div>
                </div>
                <a href="siswa.php" class="btn btn-primary mt-3">Lihat Data</a>
            </div>
        </div>
    </div>
    
    <!-- Existing Absensi Card -->
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title mb-3">Absensi Hari Ini</h5>
                <div class="attendance-grid">
                    <div class="attendance-item">
                        <i class="fas fa-check fa-2x mb-2 text-success"></i>
                        <p>Hadir</p>
                        <h3><?php echo $hadir; ?></h3>
                    </div>
                    <div class="attendance-item">
                        <i class="fas fa-head-side-cough fa-2x mb-2 text-warning"></i>
                        <p>Sakit</p>
                        <h3><?php echo $sakit; ?></h3>
                    </div>
                    <div class="attendance-item">
                        <i class="fas fa-envelope fa-2x mb-2 text-info"></i>
                        <p>Izin</p>
                        <h3><?php echo $izin; ?></h3>
                    </div>
                    <div class="attendance-item">
                        <i class="fas fa-times fa-2x mb-2 text-danger"></i>
                        <p>Alpha</p>
                        <h3><?php echo $alpha; ?></h3>
                    </div>
                </div>
                <a href="absensi.php" class="btn btn-primary mt-3">Lihat Absensi</a>
            </div>
        </div>
    </div>

    <!-- New Keterangan Card -->
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title mb-3">Keterangan Status</h5>
                <div class="status-container">
                    <div class="status-item">
                        <i class="fas fa-check-circle fa-lg text-success"></i>
                        <span class="ms-2">Hadir: Siswa mengikuti pembelajaran</span>
                    </div>
                    <div class="status-item">
                        <i class="fas fa-hospital fa-lg text-warning"></i>
                        <span class="ms-2">Sakit: Siswa tidak hadir karena sakit</span>
                    </div>
                    <div class="status-item">
                        <i class="fas fa-envelope fa-lg text-info"></i>
                        <span class="ms-2">Izin: Siswa tidak hadir dengan izin</span>
                    </div>
                    <div class="status-item">
                        <i class="fas fa-times-circle fa-lg text-danger"></i>
                        <span class="ms-2">Alpha: Siswa tidak hadir tanpa keterangan</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title mb-3">
                    <i class="fas fa-school fa-lg text-primary me-2"></i>
                    Tentang SMKN 40
                </h5>
                <div class="school-info-container">
                    <p class="card-text">
                        SMKN 40 Jakarta adalah salah satu Sekolah Menengah Kejuruan (SMK) unggulan di Jakarta yang 
                        berfokus pada pendidikan vokasi dan pengembangan keterampilan sesuai dengan kebutuhan industri. 
                        Sekolah ini menyediakan berbagai program keahlian yang bertujuan untuk membekali siswa dengan 
                        kompetensi yang relevan di dunia kerja maupun dunia usaha.
                    </p>
                    // Replace the program-keahlian div content with:

<div class="program-keahlian mt-4" >
    <h6 class="text-primary mb-3">Program Keahlian:</h6>
    <div class="row">
        <a href="rpl.php"><div class="col-md-6">
            <div class="status-item">
                <i class="fas fa-laptop-code fa-lg text-primary"></i>
                <span class="ms-2">Rekayasa Perangkat Lunak</span>
            </div>
        </div></a>
        <div class="col-md-6">
            <div class="status-item">
                <i class="fas fa-tasks fa-lg text-primary"></i>
                <span class="ms-2">Manajemen Perkantoran</span>
            </div>
        </div>
        <div class="col-md-6">
            <div class="status-item">
                <i class="fas fa-store fa-lg text-primary"></i>
                <span class="ms-2">Bisnis Retail</span>
            </div>
        </div>
        <div class="col-md-6">
            <div class="status-item">
                <i class="fas fa-calculator fa-lg text-primary"></i>
                <span class="ms-2">Akuntansi</span>
            </div>
        </div>
        <div class="col-md-6">
            <div class="status-item">
                <i class="fas fa-paint-brush fa-lg text-primary"></i>
                <span class="ms-2">Desain Komunikasi Visual</span>
            </div>
        </div>
    </div>
</div> 
<?php } ?>

<div class="row mb-4">
    <!-- Visi Box -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title mb-3">
                    <i class="fas fa-eye fa-lg text-primary me-2"></i>
                    Visi Sekolah
                </h5>
                <div class="school-info-container">
                    <p class="card-text">
                        "Menjadi SMK unggulan yang menghasilkan lulusan berkompeten, berkarakter, dan berdaya saing tinggi 
                        dalam era digital dengan berlandaskan nilai-nilai budaya Indonesia."
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Misi Box -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title mb-3">
                    <i class="fas fa-bullseye fa-lg text-primary me-2"></i>
                    Misi Sekolah
                </h5>
                <div class="school-info-container">
                    <ul class="mission-list">
                        <li>Menyelenggarakan pendidikan kejuruan yang berkualitas dan inovatif</li>
                        <li>Mengembangkan karakter dan kompetensi siswa sesuai kebutuhan industri</li>
                        <li>Memperkuat kerjasama dengan dunia usaha dan industri</li>
                        <li>Meningkatkan kualitas pembelajaran berbasis teknologi</li>
                     </ul>
                 </div>
             </div>
         </div>
     </div>
 </div>

            <?php if ($current_page == 'absensi.php') { ?>
                <h2 class="main-title">Absensi Siswa</h2>
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
                                        while ($row = $siswa_result->fetch_assoc()) {
                                            ?>
                                            <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['nama']) ?></option>
                                        <?php } ?>
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
                            $absensi_result = $conn->query("SELECT absensi.id, siswa.nama, absensi.status, absensi.tanggal FROM absensi JOIN siswa ON absensi.siswa_id = siswa.id");
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
                            <?php } ?>
                        </tbody>
                    </table>
                </div>... <!-- Form Edit Absensi -->
                <?php if (isset($_GET['edit_absensi'])) {
                    $id = intval($_GET['edit_absensi']);
                    $edit_result = $conn->query("SELECT * FROM absensi WHERE id=$id");
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
                <?php } ?>
            <?php } ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
