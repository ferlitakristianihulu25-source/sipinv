<?php
// Cek session, kalau belum login redirect ke halaman login
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'pages/auth/login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'SIPINV' ?> — PT Salim Ivomas Pratama</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        * { box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f6f9;
            margin: 0;
        }

        /* ===== NAVBAR ATAS ===== */
        .navbar-top {
            background: linear-gradient(135deg, #1a5c2a, #2d8a45);
            height: 60px;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px 0 270px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        .navbar-top .brand {
            color: white;
            font-weight: 700;
            font-size: 1rem;
            letter-spacing: 0.5px;
        }
        .navbar-top .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
            color: rgba(255,255,255,0.9);
            font-size: 0.88rem;
        }
        .navbar-top .user-info .badge-role {
            background: rgba(255,255,255,0.2);
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            text-transform: capitalize;
        }
        .navbar-top .btn-logout {
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            border-radius: 8px;
            padding: 4px 12px;
            font-size: 0.82rem;
            text-decoration: none;
            transition: all 0.2s;
        }
        .navbar-top .btn-logout:hover {
            background: rgba(255,255,255,0.25);
            color: white;
        }

        /* ===== SIDEBAR ===== */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            height: 100vh;
            background: #1a1f2e;
            z-index: 1100;
            overflow-y: auto;
            padding-top: 0;
        }
        .sidebar-brand {
            background: linear-gradient(135deg, #1a5c2a, #2d8a45);
            padding: 18px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            height: 60px;
        }
        .sidebar-brand span {
            color: white;
            font-weight: 700;
            font-size: 1.1rem;
        }
        .sidebar-brand small {
            color: rgba(255,255,255,0.7);
            font-size: 0.7rem;
            display: block;
            line-height: 1;
        }
        .sidebar-menu {
            padding: 16px 0;
        }
        .sidebar-label {
            color: rgba(255,255,255,0.35);
            font-size: 0.68rem;
            font-weight: 600;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            padding: 12px 20px 6px;
        }
        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 20px;
            color: rgba(255,255,255,0.65);
            text-decoration: none;
            font-size: 0.88rem;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }
        .sidebar-menu a:hover {
            background: rgba(255,255,255,0.07);
            color: white;
            border-left-color: #2d8a45;
        }
        .sidebar-menu a.active {
            background: rgba(45,138,69,0.2);
            color: #4dbb6d;
            border-left-color: #2d8a45;
            font-weight: 600;
        }
        .sidebar-menu a i {
            font-size: 1rem;
            width: 20px;
            text-align: center;
        }

        /* ===== KONTEN UTAMA ===== */
        .main-content {
            margin-left: 250px;
            padding-top: 60px;
            min-height: 100vh;
        }
        .content-area {
            padding: 24px;
        }

        /* ===== CARD ===== */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        }
        .card-header {
            background: white;
            border-bottom: 1px solid #f0f0f0;
            border-radius: 12px 12px 0 0 !important;
            padding: 16px 20px;
            font-weight: 600;
        }

        /* ===== TABEL ===== */
        .table thead th {
            background: #f8f9fa;
            font-size: 0.82rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6c757d;
            border-bottom: 2px solid #e9ecef;
        }
        .table td {
            vertical-align: middle;
            font-size: 0.88rem;
        }

        /* ===== BADGE ===== */
        .badge { font-weight: 500; }

        /* ===== PAGE HEADER ===== */
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
        }
        .page-header h4 {
            margin: 0;
            font-weight: 700;
            color: #1a1f2e;
        }
        .page-header p {
            margin: 2px 0 0;
            color: #6c757d;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>