<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../config.php';
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// Validate ID
if (!isset($_GET['id'])) {
    die("ERROR: Missing summon ID.");
}

$id = $_GET['id'];

// Fetch summon (CORRECT TABLE + CORRECT COLUMN NAMES)
$result = mysqli_query($conn, "
    SELECT S.*, V.VehicleID, VT.ViolationName 
    FROM Summon S
    LEFT JOIN ViolationType VT ON S.ViolationTypeID = VT.ViolationTypeID
    LEFT JOIN Vehicle V ON S.VehicleID = V.VehicleID
    WHERE S.SummonID = '$id'
");


$data = mysqli_fetch_assoc($result);

$enf = mysqli_query($conn, "
    SELECT P.*
    FROM PunishmentDuration P
    LEFT JOIN Vehicle V ON P.StudentID = V.StudentID
    WHERE V.VehicleID = '{$data['VehicleID']}'
    ORDER BY P.PunishmentDurationID DESC
    LIMIT 1
");

$enfData = mysqli_fetch_assoc($enf);
$enfStatus = $enfData['Status'] ?? '';
$enfID = $enfData['PunishmentDurationID'] ?? '';

if (!$data) {
    die("ERROR: Summon record not found.");
}

// Fetch violation types for dropdown
$violations = mysqli_query($conn, "SELECT * FROM ViolationType");

// Update logic
if (isset($_POST['update'])) {

    $violationID = $_POST['violation'];
    $status = $_POST['status'];

    mysqli_query(
        $conn,
        "UPDATE Summon SET 
            ViolationTypeID = '$violationID',
            SummonStatus = '$status'
        WHERE SummonID = '$id'"
    );
    if (!empty($_POST['enforcement_status']) && !empty($_POST['enforcement_id'])) {
        $newEnfStatus = $_POST['enforcement_status'];
        $eid = $_POST['enforcement_id'];

        mysqli_query($conn, "
            UPDATE PunishmentDuration
            SET Status='$newEnfStatus'
            WHERE PunishmentDurationID='$eid'
        ");
    }

    header("Location: security_edit_summon.php?id=$id&updated=1");
    exit;
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Edit Summon</title>
    <link rel="stylesheet" href="../templates/security_style.css">

    <style>
        body {
            background: #FFF9D7;
            font-family: Arial, sans-serif;
        }

        .edit-container {
            background: #ffffff;
            padding: 25px;
            border-radius: 20px;
            width: 95%;
            margin-top: 10px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
            border-left: 8px solid #FFE28A;
        }

        label {
            font-weight: bold;
            color: #5A4B00;
        }

        select,
        textarea {
            width: 100%;
            box-sizing: border-box;
            padding: 12px;
            border-radius: 12px;
            border: 1px solid #ECCB7E;
            background: #FFF9D7;
            margin-bottom: 15px;
            padding-left: 14px;
            padding-right: 14px;
            margin-top: 10px;
        }

        textarea {
            height: 110px;
            padding-left: 14px;
            padding-right: 14px;
        }

        .update-btn {
            background: #FFC93C;
            color: #5A4B00;
            padding: 12px 22px;
            font-size: 16px;
            font-weight: 700;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: 0.2s ease;
            margin-top: 10px;
        }

        .update-btn:hover {
            background: #FFBB22;
            transform: scale(1.03);
        }

        .back-btn {
            display: block;
            width: 100%;
            margin-top: 15px;
            padding: 10px;
            text-align: center;
            text-decoration: none;
            color: #5A4B00;
            font-weight: bold;
        }

        .back-btn:hover {
            text-decoration: underline;
        }

        /* Success popup animation */
        @keyframes fadeInOut {
            0% {
                opacity: 0;
                transform: translateY(-10px);
            }

            10% {
                opacity: 1;
                transform: translateY(0);
            }

            90% {
                opacity: 1;
            }

            100% {
                opacity: 0;
                transform: translateY(-10px);
            }
        }

        .top-header-bar {
            width: 100%;
            background: #F9E39A;
            padding: 18px 25px;
            border-radius: 18px;
            border: 2px solid #F4D77A;
            font-size: 24px;
            font-weight: 700;
            color: #5A4B00;
            margin: 10px auto 25px auto;
            box-sizing: border-box;
        }
    </style>
</head>

<body>

    <?php include '../templates/security_sidebar.php'; ?>

    <div class="main-content">
        <div class="top-header-bar">✏️ Update Summon</div>
        <div style="display:flex; justify-content:center; width:100%;">
            <div class="edit-container">

                <!-- SUCCESS ANIMATION -->
                <?php if (isset($_GET['updated'])): ?>
                    <script>
                        document.addEventListener("DOMContentLoaded", () => {
                            const msg = document.createElement("div");
                            msg.innerHTML = "✓ Summon Updated Successfully!";
                            msg.style.position = "fixed";
                            msg.style.top = "20px";
                            msg.style.right = "20px";
                            msg.style.padding = "15px 25px";
                            msg.style.background = "#B7F5B0";
                            msg.style.border = "2px solid #7ED957";
                            msg.style.borderRadius = "12px";
                            msg.style.fontWeight = "700";
                            msg.style.color = "#2E7D32";
                            msg.style.zIndex = "9999";
                            msg.style.boxShadow = "0 4px 10px rgba(0,0,0,0.15)";
                            msg.style.animation = "fadeInOut 3s ease forwards";

                            document.body.appendChild(msg);

                            setTimeout(() => msg.remove(), 3200);
                        });
                    </script>
                <?php endif; ?>

                <form method="post">

                    <label>Violation Type:</label>
                    <select name="violation">
                        <?php while ($v = mysqli_fetch_assoc($violations)) { ?>
                            <option value="<?= $v['ViolationTypeID'] ?>"
                                <?= ($v['ViolationTypeID'] == $data['ViolationTypeID']) ? "selected" : "" ?>>
                                <?= $v['ViolationName'] ?>
                            </option>
                        <?php } ?>
                    </select>

                    <label>Status:</label>
                    <select name="status">
                        <option value="Unpaid" <?= $data['SummonStatus'] == "Unpaid" ? "selected" : "" ?>>Unpaid</option>
                        <option value="Paid" <?= $data['SummonStatus'] == "Paid" ? "selected" : "" ?>>Paid</option>
                    </select>


                    <?php if (!empty($enfID)): ?>
                        <input type="hidden" name="enforcement_id" value="<?= $enfID ?>">
                        <label>Enforcement Status:</label>
                        <select name="enforcement_status">
                            <option value="Active" <?= $enfStatus == "Active" ? "selected" : "" ?>>Active</option>
                            <option value="Completed" <?= $enfStatus == "Completed" ? "selected" : "" ?>>Completed</option>
                        </select>
                    <?php endif; ?>

                    <button class="update-btn" name="update">💾 Update Summon</button>

                    <a class="back-btn" href="security_summon_list.php">← Back to Summon List</a>
                </form>

            </div>
        </div>

</body>

</html>