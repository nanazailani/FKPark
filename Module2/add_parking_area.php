<?php
require '../config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
//clear cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $conn->real_escape_string($_POST['AreaCode']);
    $name = $conn->real_escape_string($_POST['AreaName']);
    $type = $conn->real_escape_string($_POST['AreaType']);
    $desc = $conn->real_escape_string($_POST['AreaDescription']);
    $cap  = intval($_POST['Capacity']);
    $loc  = $conn->real_escape_string($_POST['LocationDesc']);
    $status = $conn->real_escape_string($_POST['StatusID']);

    $id = 'PA' . str_pad(rand(1,999),3,'0',STR_PAD_LEFT);

    $stmt = $conn->prepare("
        INSERT INTO parking_area 
        (ParkingAreaID, AreaCode, AreaName, AreaType, AreaDescription, Capacity, LocationDesc, AreaStatus)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param('sssssiss', $id, $code, $name, $type, $desc, $cap, $loc, $status);

    $stmt->execute();

    header('Location: manage_parking_area.php');
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<title>Add Parking Area</title>
<link rel="stylesheet" href="../templates/admin_style.css?v=3">

<!-- ✅ LOCAL FORM STYLE ONLY -->
<style>
.form-grid {
    display: grid;
    grid-template-columns: 160px 1fr;
    row-gap: 14px;
    column-gap: 20px;
    align-items: center;
}

.form-grid label {
    font-weight: 600;
    color: #773f00;
}

.form-grid input,
.form-grid select,
.form-grid textarea {
    width: 100%;
    padding: 10px 12px;
    border-radius: 10px;
    border: 1px solid #FFD7B8;
    font-family: inherit;
}

.form-actions {
    grid-column: 2 / 3;
    margin-top: 15px;
}
</style>
</head>

<body>
<?php include_once('../templates/admin_sidebar.php'); ?>

<div class="main-content">
  <div class="page-box">
    <header class="header">Add Parking Area</header>

    <div class="box">
      <form method="post" class="form-grid">

        <label>Area Code</label>
        <input type="text" name="AreaCode" required>

        <label>Area Name</label>
        <input type="text" name="AreaName" required>

        <label>Area Type</label>
        <select name="AreaType">
          <option>Student</option>
          <option>Staff</option>
          <option>Visitor</option>
        </select>

        <label>Capacity</label>
        <input type="number" name="Capacity" value="10" required>

        <label>Location</label>
        <input type="text" name="LocationDesc">

        <label>Description</label>
        <textarea name="AreaDescription" rows="3"></textarea>

        <label>Status</label>
        <select name="StatusID">
          <option value="Active">Active</option>
          <option value="Inactive">Inactive</option>
        </select>

        <div class="form-actions">
          <button class="btn-success" type="submit">Create Area</button>
<br>
<a class="btn-danger" href="manage_parking_area.php">Cancel</a>

        </div>

      </form>
    </div>

  </div>
</div>
<script>
            //pageshow - event bila page show. e.g - tekan background
            window.addEventListener("pageshow", function (event) 
            {
                //true kalau the page is cached 
                if (event.persisted) 
                {
                    //page reload
                    window.location.reload();
                }
            });
        </script>
</body>
</html>
