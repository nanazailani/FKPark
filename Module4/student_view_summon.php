<?php
require_once '../config.php';

// Get summon ID from QR
$summonID = (int)($_GET['summon_id'] ?? 0);

if ($summonID <= 0) {
    die("Invalid summon.");
}


$sql = "
SELECT 
    s.SummonID,
    s.SummonDate,
    s.SummonTime,
    s.Location,
    s.SummonStatus,
    vt.ViolationName,
    vt.ViolationPoints,
    v.PlateNumber,
    u.UserName
FROM Summon s
JOIN ViolationType vt ON s.ViolationTypeID = vt.ViolationTypeID
JOIN Vehicle v ON s.VehicleID = v.VehicleID
JOIN User u ON v.UserID = u.UserID
WHERE s.SummonID = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $summonID);
$stmt->execute();
$result = $stmt->get_result();
$summon = $result->fetch_assoc();

if (!$summon) {
    die("Summon not found.");
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Summon Details</title>
    <link rel="stylesheet" href="../templates/student_style.css">
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

    <div class="summon-card">
        <h2>🚨 Summon Details</h2>

        <p><strong>Summon ID:</strong> <?= $summon['SummonID'] ?></p>
        <p><strong>Student:</strong> <?= htmlspecialchars($summon['UserName']) ?></p>
        <p><strong>Plate Number:</strong> <?= htmlspecialchars($summon['PlateNumber']) ?></p>
        <p><strong>Violation:</strong> <?= htmlspecialchars($summon['ViolationName']) ?></p>
        <p><strong>Demerit Points:</strong> <?= $summon['ViolationPoints'] ?></p>
        <p><strong>Date:</strong>
            <?= date('d F Y, g:i A', strtotime($summon['SummonDate'] . ' ' . $summon['SummonTime'])) ?>
        </p>
        <p><strong>Location:</strong> <?= htmlspecialchars($summon['Location']) ?></p>
        <p>
            <strong>Status:</strong>
            <span class="<?= $summon['SummonStatus'] === 'Unpaid' ? 'status-unpaid' : 'status-paid' ?>">
                <?= $summon['SummonStatus'] ?>
            </span>
        </p>

        <?php if ($summon['SummonStatus'] === 'Unpaid'): ?>
            <a href="student_pay_summon.php?summon_id=<?= $summonID ?>" class="pay-btn">
                💳 Pay Summon
            </a>
        <?php else: ?>
            <p class="status-paid">✅ This summon has been paid.</p>
        <?php endif; ?>
    </div>

</body>

</html>