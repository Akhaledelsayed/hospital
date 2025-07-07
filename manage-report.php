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

    .button-grid button.selected {
      background: linear-gradient(to right, #38a169, #2f855a);
      transform: scale(1.04);
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
    }

    .select-reports-button {
      display: block;
      margin: 20px auto;
      padding: 14px 24px;
      color: white;
      border: none;
      border-radius: 10px;
      cursor: pointer;
      font-size: 14px;
      font-weight: 600;
      background: linear-gradient(to right, #ed8936, #d69e2e);
      transition: all 0.3s ease-in-out;
    }

    .select-reports-button:hover {
      background: linear-gradient(to right, #dd6b20, #c05621);
      transform: scale(1.04);
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    }

    .select-reports-button.active {
      background: linear-gradient(to right, #9f7aea, #b794f4);
      transform: scale(1.04);
    }

    .print-button {
      display: none;
      margin: 20px auto 0;
      padding: 14px 24px;
      color: white;
      border: none;
      border-radius: 10px;
      cursor: pointer;
      font-size: 14px;
      font-weight: 600;
      background: linear-gradient(to right, #3182ce, #2b6cb0);
      transition: all 0.3s ease-in-out;
    }

    .print-button:hover {
      background: linear-gradient(to right, #2b6cb0, #2c5282);
      transform: scale(1.04);
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
    }

    .print-button:disabled {
      background: #ccc;
      cursor: not-allowed;
      transform: none;
      box-shadow: none;
    }

    .error-message {
      color: red;
      text-align: center;
      margin-top: 10px;
      display: none;
    }

    .loading-message {
      color: #3182ce;
      text-align: center;
      margin-top: 10px;
      display: none;
    }

    @media (max-width: 600px) {
      .main-content {
        padding: 20px;
      }

      .button-grid {
        grid-template-columns: 1fr;
      }

      .select-reports-button,
      .print-button {
        width: 100%;
        padding: 12px;
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
          <!-- Start Date -->
          <div class="input-holder">
            <label for="start_date">Start Date</label>
            <input type="date" name="start_date" id="start_date" required>
          </div>

          <!-- End Date -->
          <div class="input-holder">
            <label for="end_date">End Date</label>
            <input type="date" name="end_date" id="end_date" required>
          </div>

          <!-- Hospital Code -->
          <input type="hidden" name="hospital_code" value="<?= htmlspecialchars($hospital_code) ?>">

          <!-- Select Reports Button -->
          <button type="button" class="select-reports-button" id="selectReportsButton">Select Reports</button>

          <!-- Button Grid -->
          <div class="button-grid">
            <button type="button" data-url="workorder_forhospital_report.php">Work Orders by Hospital</button>
            <button type="button" data-url="workorder_perdepartment_report.php">Work Orders by Department</button>
            <button type="button" data-url="down_time_report.php">Down Time</button>
            <button type="button" data-url="new_devices_report.php">NEW DEVICES</button>
            <button type="button" data-url="purchasing_order_report.php">Purchasing Order</button>
            <button type="button" data-url="calibration_report.php">PM vs All</button>
            <button type="button" data-url="grouped_equipment_report.php">Grouped by Model</button>
            <button type="button" data-url="by_employee_report.php">By Employees</button>
            <button type="button" data-url="repair_count_report.php">Repair Count</button>
          </div>

          <!-- Print Button -->
          <button type="button" class="print-button" id="printButton" disabled>Print Selected Reports</button>
          <div class="error-message" id="errorMessage">Please select at least one report to print.</div>
          <div class="loading-message" id="loadingMessage">Opening reports for printing, please wait...</div>
        </form>

        <script>
          let isSelectionMode = false;
          let selectedUrls = [];

          // Handle Select Reports button
          const selectReportsButton = document.getElementById('selectReportsButton');
          const printButton = document.getElementById('printButton');
          const loadingMessage = document.getElementById('loadingMessage');
          const errorMessage = document.getElementById('errorMessage');

          selectReportsButton.addEventListener('click', function () {
            isSelectionMode = !isSelectionMode;
            this.classList.toggle('active', isSelectionMode);
            this.textContent = isSelectionMode ? 'Cancel Selection' : 'Select Reports';
            printButton.style.display = isSelectionMode ? 'block' : 'none';
            printButton.disabled = selectedUrls.length === 0;
            errorMessage.style.display = 'none';
            loadingMessage.style.display = 'none';

            // Clear selections when exiting selection mode
            if (!isSelectionMode) {
              document.querySelectorAll('.button-grid button').forEach(button => {
                button.classList.remove('selected');
              });
              selectedUrls = [];
            }
          });

          // Handle button grid clicks
          document.querySelectorAll('.button-grid button').forEach(button => {
            button.addEventListener('click', function () {
              const url = this.getAttribute('data-url');
              if (isSelectionMode) {
                // Selection mode: toggle selection
                if (this.classList.contains('selected')) {
                  this.classList.remove('selected');
                  selectedUrls = selectedUrls.filter(u => u !== url);
                } else {
                  this.classList.add('selected');
                  if (!selectedUrls.includes(url)) {
                    selectedUrls.push(url);
                  }
                }
                printButton.disabled = selectedUrls.length === 0;
                errorMessage.style.display = 'none';
                loadingMessage.style.display = 'none';
              } else {
                // Default mode: submit form to open report
                const form = document.getElementById('reportForm');
                form.action = url;
                form.submit();
              }
            });
          });

          // Handle print button click
          printButton.addEventListener('click', async function () {
            if (selectedUrls.length === 0) {
              errorMessage.style.display = 'block';
              loadingMessage.style.display = 'none';
              return;
            }

            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;

            if (!startDate || !endDate) {
              alert('Please select both start and end dates.');
              loadingMessage.style.display = 'none';
              return;
            }

            // Show loading message and disable print button
            loadingMessage.style.display = 'block';
            errorMessage.style.display = 'none';
            printButton.disabled = true;

            // Maintain order of buttons as they appear in the DOM
            const buttonUrls = Array.from(document.querySelectorAll('.button-grid button'))
              .map(button => button.getAttribute('data-url'));
            const orderedSelectedUrls = buttonUrls.filter(url => selectedUrls.includes(url));

            // Construct query string
            const queryString = `?start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}&hospital_code=${encodeURIComponent('<?= htmlspecialchars($hospital_code) ?>')}`;

            // Function to open and print a single report
            const printReport = async (url) => {
              return new Promise(resolve => {
                const printWindow = window.open(url + queryString, '_blank');
                if (!printWindow) {
                  console.error('Failed to open window for:', url);
                  resolve(); // Continue even if window fails
                  return;
                }
                let printTriggered = false;
                printWindow.onload = () => {
                  try {
                    printWindow.print();
                    printTriggered = true;
                    setTimeout(() => resolve(), 1000); // Wait briefly after print
                  } catch (e) {
                    console.error('Print error for:', url, e);
                    resolve();
                  }
                };
                // Fallback if onload doesn't fire
                setTimeout(() => {
                  if (!printTriggered) {
                    try {
                      printWindow.print();
                    } catch (e) {
                      console.error('Fallback print error for:', url, e);
                    }
                    resolve();
                  }
                }, 3000);
              });
            };

            // Print all selected reports in order
            for (const url of orderedSelectedUrls) {
              await printReport(url);
              // Additional delay to prevent browser overload
              await new Promise(resolve => setTimeout(resolve, 1000));
            }

            // Reset UI
            loadingMessage.style.display = 'none';
            printButton.disabled = selectedUrls.length === 0;
          });
        </script>
      </div>
    </section>
  </div>
</body>

</html>
