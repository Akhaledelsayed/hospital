<?php 
session_start();
$hospital_code = $_SESSION['current_hospital_code'] ?? null;

if (!isset($_SESSION['role'], $_SESSION['id']) || !$hospital_code) {
    echo "<h2 style='text-align:center; color:red;'>❌ Access denied. Please log in.</h2>";
    exit;
}

include "DB_connection.php";
include "app/Model/Devices.php";

// Access control logic
$role = $_SESSION['role'];
$user_id = $_SESSION['id'];
$hasAccess = false;

if ($role === "admin") {
    $hasAccess = true;
} else {
    // Check if user is assigned to this hospital
    $stmt = $conn->prepare("SELECT hospital_code FROM user_hospitals WHERE username = (SELECT username FROM users WHERE id = ?)");
    $stmt->execute([$user_id]);
    $user_hospitals = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (in_array($hospital_code, $user_hospitals)) {
        $hasAccess = true;
    }
}

if (!$hasAccess) {
    echo "<h2 style='text-align:center; color:red;'>❌ You are not assigned to this hospital.</h2>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <title>Biomedical Reports Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="css/style.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <style>
    .main-content {
      margin: auto;
      padding: 30px 40px;
      background-color: #ffffff;
      min-height: 80vh;
      max-width: 1000px;
      border-radius: 12px;
      box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
    }

    h1 {
      text-align: center;
      font-size: 28px;
      margin-bottom: 30px;
      color: #333;
      font-weight: 700;
    }

    form .input-holder {
      margin-bottom: 20px;
    }

    label {
      display: block;
      margin-bottom: 8px;
      font-weight: bold;
      font-size: 15px;
      color: #333;
    }

    input[type="date"],
    select {
      width: 100%;
      padding: 10px 12px;
      border-radius: 8px;
      border: 1px solid #ccc;
      font-size: 14px;
    }

    .button-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
      gap: 16px;
      margin-top: 30px;
    }

    .button-grid button {
      padding: 14px 18px;
      color: white;
      border: none;
      border-radius: 10px;
      cursor: pointer;
      font-size: 14px;
      font-weight: 600;
      background: linear-gradient(to right, #667eea, #764ba2);
      transition: all 0.3s ease-in-out;
    }

    .button-grid button:hover {
      background: linear-gradient(to right, #5a67d8, #6b46c1);
      transform: scale(1.04);
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    }

    @media (max-width: 600px) {
      .main-content {
        padding: 20px;
      }

      .button-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>

<body>
  <input type="checkbox" id="checkbox" />
  <?php include "inc/header.php"; ?>
  <div class="body">
    <?php include "inc/nav.php"; ?>
    <section class="section-1">

      <div class="main-content">
        <h1>Biomedical Report Dashboard</h1>

        <form id="reportForm" method="GET" target="_blank">
          <!-- تاريخ البداية -->
          <div class="input-holder">
            <label for="start_date">Start Date</label>
            <input type="date" name="start_date" id="start_date" required>
          </div>

          <!-- تاريخ النهاية -->
          <div class="input-holder">
            <label for="end_date">End Date</label>
            <input type="date" name="end_date" id="end_date" required>
          </div>

          <!-- المستشفى -->
          <input type="hidden" name="hospital_code" value="<?= htmlspecialchars($hospital_code) ?>">


          <!-- الأزرار -->
          <div class="button-grid">
            <button type="button" onclick="submitTo('workorder_forhospital_report.php')">Work Orders by Hospital</button>
            <button type="button" onclick="submitTo('workorder_perdepartment_report.php')">Work Orders by Department</button>
            <button type="button" onclick="submitTo('down_time_report.php')">Down Time</button>
            <button type="button" onclick="submitTo('new_devices_report.php')">NEW DEVICES</button>
            <button type="button" onclick="submitTo('purchasing_order_report.php')">Purchasing Order </button>
            <button type="button" onclick="submitTo('calibration_report.php')">PM vs All</button>
            <button type="button" onclick="submitTo('grouped_equipment_report.php')">Grouped by Model</button>
            <button type="button" onclick="submitTo('by_employee_report.php')">By Employees</button>
            <button type="button" onclick="submitTo('repair_count_report.php')">Repair Count</button>
          </div>
        </form>

        <script>
          function submitTo(actionUrl) {
            const form = document.getElementById('reportForm');
            form.action = actionUrl;
            form.submit();
          }
        </script>
      </div>

    </section>
  </div>
</body>

</html>
