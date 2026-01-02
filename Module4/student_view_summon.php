<?php
// Import / connect to database config (sambung ke database)
require_once '../config.php';

// Dapatkan summon ID dari QR link / URL (ambil dari parameter GET)
$summonID = (int)($_GET['summon_id'] ?? 0);

// Kalau tiada ID atau ID tak valid, sistem stop (elak error)
if ($summonID <= 0) {
    die("Invalid summon.");
}

/* SQL query ini digunakan untuk ambil semua maklumat berkaitan saman
Kita gabungkan (JOIN) beberapa table: Summon, ViolationType, Vehicle, dan User
supaya student boleh lihat full detail siapa pemilik kenderaan,
jenis kesalahan (violation), plate number, status saman, tarikh, masa..*/
$sql = "
SELECT 
    s.SummonID,          -- ID unik untuk saman (unique summon ID)
    s.SummonDate,        -- Tarikh saman dikeluarkan (date issued)
    s.SummonTime,        -- Masa saman dikeluarkan (time issued)
    s.Location,          -- Lokasi kesalahan berlaku (place of violation)
    s.SummonStatus,      -- Status saman: Paid / Unpaid
    vt.ViolationName,    -- Nama jenis kesalahan (contoh: Parking Without Pass)
    vt.ViolationPoints,  -- Mata demerit untuk kesalahan ini
    v.PlateNumber,       -- Nombor plat kenderaan student
    u.UserName           -- Nama pemilik / student
    -- Data diambil ikut SummonID sahaja (filter 1 saman specific)
FROM Summon s
JOIN ViolationType vt ON s.ViolationTypeID = vt.ViolationTypeID
    -- Hubungkan saman dengan jenis kesalahan (setiap saman ada 1 violation type)
JOIN Vehicle v ON s.VehicleID = v.VehicleID
    -- Hubungkan saman dengan kenderaan yang terlibat
JOIN User u ON v.UserID = u.UserID
    -- Dari kenderaan kita trace kepada pemilik / student
    -- WHERE = penapis data → hanya ambil satu saman ikut ID yang dihantar dari URL
WHERE s.SummonID = ?
";

// Prepare statement untuk security (elak SQL Injection)
$stmt = $conn->prepare($sql);
// Bind parameter (masukkan SummonID sebagai integer)
$stmt->bind_param("i", $summonID);
// Execute query dan ambil result dari database
$stmt->execute();
$result = $stmt->get_result();
// Ambil data saman sebagai associative array
$summon = $result->fetch_assoc();

// Kalau tak jumpa saman dalam database
if (!$summon) {
    die("Summon not found.");
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Summon Details</title>
    <link rel="stylesheet" href="../templates/student_style.css">
    <!-- CSS styling untuk kad saman (bagi nampak kemas & modern) -->
    <style>
        body {
            background: #f4f7fb;
            font-family: 'Segoe UI', Tahoma, sans-serif;
        }

        .summon-card {
            max-width: 420px;
            margin: 80px auto;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            padding: 28px 32px;
            border-left: 6px solid #4f7cff;
        }

        .summon-card h2 {
            margin-bottom: 20px;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .summon-card p {
            margin: 10px 0;
            font-size: 15px;
            color: #333;
        }

        .summon-card strong {
            color: #555;
            width: 130px;
            display: inline-block;
        }

        .status-unpaid {
            color: #e67e22;
            font-weight: bold;
        }

        .status-paid {
            color: #27ae60;
            font-weight: bold;
        }

        .pay-btn {
            display: block;
            margin-top: 22px;
            padding: 12px;
            background: #4f7cff;
            color: #fff;
            text-align: center;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: background 0.2s ease;
        }

        .pay-btn:hover {
            background: #3b63d6;
        }
    </style>
</head>

<body>

    <!-- Bahagian utama yang paparkan detail saman -->
    <div class="summon-card">
        <h2>🚨 Summon Details</h2>

        <!-- Papar basic info saman -->
        <p><strong>Summon ID:</strong> <?= $summon['SummonID'] ?></p>
        <p><strong>Student:</strong> <?= htmlspecialchars($summon['UserName']) ?></p>
        <p><strong>Plate Number:</strong> <?= htmlspecialchars($summon['PlateNumber']) ?></p>
        <p><strong>Violation:</strong> <?= htmlspecialchars($summon['ViolationName']) ?></p>
        <p><strong>Demerit Points:</strong> <?= $summon['ViolationPoints'] ?></p>
        <!-- Format tarikh + masa supaya lebih jelas dibaca -->
        <p><strong>Date:</strong>
            <?= date('d F Y, g:i A', strtotime($summon['SummonDate'] . ' ' . $summon['SummonTime'])) ?>
        </p>
        <p><strong>Location:</strong> <?= htmlspecialchars($summon['Location']) ?></p>
        <!-- Tukar warna ikut status saman (Paid / Unpaid) -->
        <p>
            <strong>Status:</strong>
            <span class="<?= $summon['SummonStatus'] === 'Unpaid' ? 'status-unpaid' : 'status-paid' ?>">
                <?= $summon['SummonStatus'] ?>
            </span>
        </p>

        <!-- Jika saman belum bayar, tunjuk button untuk bayar -->
        <?php if ($summon['SummonStatus'] === 'Unpaid'): ?>
            <a href="student_pay_summon.php?summon_id=<?= $summonID ?>" class="pay-btn">
                💳 Pay Summon
            </a>
            <!-- Jika saman sudah dibayar, tunjuk mesej selesai -->
        <?php else: ?>
            <p class="status-paid">✅ This summon has been paid.</p>
        <?php endif; ?>
    </div>

</body>

</html>