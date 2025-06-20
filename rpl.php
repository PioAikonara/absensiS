<?php
session_start();
include 'config.php';

if (!isset($_SESSION['admin'])) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekayasa Perangkat Lunak - SMKN 40 Jakarta</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #A9B5DF;
            color: #2D336B;
        }

        .content {
            min-height: 100vh;
             padding: 30px;
        }

        .card {
            border: none;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            margin-bottom: 20px;
            background-color: #fff;
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .feature-icon {
            font-size: 2.5rem;
            color: #7886C7;
            margin-bottom: 15px;
        }

        .kompetensi-item {
            padding: 15px;
            border-radius: 8px;
            background-color: rgba(120, 134, 199, 0.1);
            margin-bottom: 10px;
            transition: transform 0.2s;
        }

        .kompetensi-item:hover {
            transform: translateX(5px);
            background-color: rgba(120, 134, 199, 0.2);
        }
    </style>
</head>
<body>
<div class="content w-100 d-flex justify-content-center align-items-center" style="min-height: 100vh;">
    <div class="container text-center">
        <h2 class="mb-4">Rekayasa Perangkat Lunak</h2>
        
        <div class="row mb-4 justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title mb-3">
                            <i class="fas fa-laptop-code text-primary"></i>
                            Tentang Jurusan RPL
                        </h3>
                        <p class="card-text">
                            Rekayasa Perangkat Lunak (RPL) adalah program keahlian yang mempersiapkan siswa untuk menjadi 
                            tenaga terampil di bidang pengembangan perangkat lunak. Program ini memfokuskan pada 
                            pemrograman, pengembangan aplikasi, dan manajemen sistem informasi.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4 justify-content-center">
            <div class="col-md-5">
                <div class="card h-100">
                    <div class="card-body">
                        <h4 class="card-title mb-3">Kompetensi Keahlian</h4>
                        <div class="kompetensi-list">
                            <div class="kompetensi-item">
                                <i class="fas fa-code me-2"></i>
                                Pemrograman Web & Mobile
                            </div>
                            <div class="kompetensi-item">
                                <i class="fas fa-database me-2"></i>
                                Basis Data
                            </div>
                            <div class="kompetensi-item">
                                <i class="fas fa-project-diagram me-2"></i>
                                Analisis & Desain Sistem
                            </div>
                            <div class="kompetensi-item">
                                <i class="fas fa-cogs me-2"></i>
                                Pemrograman Berorientasi Objek
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-5">
                <div class="card h-100">
                    <div class="card-body">
                        <h4 class="card-title mb-3">Prospek Karir</h4>
                        <div class="kompetensi-list">
                            <div class="kompetensi-item">
                                <i class="fas fa-laptop-code me-2"></i>
                                Software Developer
                            </div>
                            <div class="kompetensi-item">
                                <i class="fas fa-globe me-2"></i>
                                Web Developer
                            </div>
                            <div class="kompetensi-item">
                                <i class="fas fa-mobile-alt me-2"></i>
                                Mobile App Developer
                            </div>
                            <div class="kompetensi-item">
                                <i class="fas fa-tasks me-2"></i>
                                System Analyst
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <a href="index.php" class="btn btn-primary mt-4">Kembali</a>
    </div>
</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>