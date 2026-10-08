<?php
/**
 * Admin Print Master Layout
 * Lost & Found SMK Informatika Sumedang
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Rekap Laporan - Lost & Found SMK Informatika Sumedang') ?></title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #ffffff !important;
            color: #0f172a;
            padding: 20px;
        }
        .admin-report-sheet {
            max-width: 1200px;
            margin: 0 auto;
            background: #ffffff;
        }
        .admin-report-photo {
            width: 44px;
            height: 44px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #e2e8f0;
        }
        .text-navy {
            color: #0f172a !important;
        }
        @media print {
            body {
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="admin-print-shell">
        <?php require APP_PATH . "/views/{$viewPath}.php"; ?>
    </div>
</body>
</html>
