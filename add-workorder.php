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
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Add Device</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" />
    <link rel="stylesheet" href="css/style.css" />
    <style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #f7f9ff;
        color: #333;
    }

    .section-1 {
        background: #fff;
        padding: 30px 40px;
        margin: 40px auto;
        max-width: 800px;
        border-radius: 14px;
        box-shadow: 0 12px 28px rgba(102, 126, 234, 0.15);
    }

    .title {
        font-size: 24px;
        margin-bottom: 30px;
        color: #333;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .title a {
        background: #667eea;
        color: #fff;
        padding: 10px 18px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        box-shadow: 0 3px 8px rgba(102, 126, 234, 0.5);
        transition: background-color 0.3s ease;
    }

    .title a:hover {
        background: #5a6ccf;
    }

    .form-1 {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .input-holder {
        display: flex;
        flex-direction: column;
    }

    .input-holder label {
        font-weight: 600;
        margin-bottom: 6px;
        color: #4a4a4a;
    }

    .input-1,
    select {
        padding: 12px 18px;
        border-radius: 10px;
        border: 1.5px solid #cbd5e1;
        font-size: 16px;
        transition: border-color 0.3s ease;
    }

    .input-1:focus,
    select:focus {
        border-color: #667eea;
        outline: none;
        box-shadow: 0 0 8px rgba(102, 126, 234, 0.4);
    }

    .edit-btn {
        background: linear-gradient(45deg, #38b2ac, #319795);
        border: none;
        color: #fff;
        font-weight: 700;
        padding: 14px 0;
        border-radius: 28px;
        font-size: 18px;
        box-shadow: 0 5px 18px rgba(49, 151, 149, 0.6);
        cursor: pointer;
        transition: background-color 0.3s ease, box-shadow 0.3s ease;
        user-select: none;
    }

    .edit-btn:hover {
        background: linear-gradient(45deg, #2c7a7b, #285e61);
        box-shadow: 0 8px 24px rgba(40, 94, 97, 0.8);
        color: #e0f7f7;
    }

    .success {
        background-color: #d4edda;
        border: 1px solid #c3e6cb;
        padding: 12px 20px;
        border-radius: 8px;
        color: #155724;
        margin-bottom: 20px;
        font-weight: 600;
    }

    .danger {
        background-color: #f8d7da;
        border: 1px solid #f5c6cb;
        padding: 12px 20px;
        border-radius: 8px;
        color: #721c24;
        margin-bottom: 20px;
        font-weight: 600;
    }
    </style>
</head>


<body>
    <input type="checkbox" id="checkbox" />
    <?php include "inc/header.php"; include "DB_connection.php"; ?>
    <div class="body">
        <?php include "inc/nav.php" ?>
        <section class="section-1">
            <h4 class="title">Add Workorder <a href="workorder.php">Workorders</a></h4>

            <?php if (isset($_GET['error'])) { ?>
                <div class="danger"><?= htmlspecialchars($_GET['error']); ?></div>
            <?php } ?>
            <?php if (isset($_GET['success'])) { ?>
                <div class="success"><?= htmlspecialchars($_GET['success']); ?></div>
            <?php } ?>

            <form class="form-1" method="POST" action="app/add-workorder.php">
    <!-- Device Name -->
    <div class="input-holder">
        <label for="device_name">Device Name</label>
        <select name="device_name" id="device_name" required>
            <option value="">-- Select device name --</option>
            <?php
            $stmt = $conn->prepare("SELECT DISTINCT device_name FROM devices WHERE hospital_code = ?");
            $stmt->execute([$hospital_code]);
            foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $device_name) {
                echo '<option value="' . htmlspecialchars($device_name) . '">' . htmlspecialchars($device_name) . '</option>';
            }
            ?>
        </select>
    </div>

    <!-- Device Serial -->
    <div class="input-holder">
        <label for="device_serial">Device Serial</label>
        <select name="device_serial" id="device_serial" required>
            <option value="">-- Select serial number --</option>
        </select>
    </div>

    <!-- Requested By -->
    <div class="input-holder">
        <label for="requested_by">Requested By</label>
        <input type="text" name="requested_by" id="requested_by" class="input-1" required />
    </div>

    <!-- Department -->
    <div class="input-holder">
        <label for="department">Department</label>
        <input type="text" name="department" id="department" class="input-1" readonly required />
    </div>

    <!-- Date and Time Received -->
    <div class="input-holder">
        <label for="date_recevied">Date Received</label>
        <input type="date" name="date_recevied" id="date_recevied" class="input-1" required />
    </div>
    <div class="input-holder">
        <label for="time_recevied">Time Received</label>
        <input type="time" name="time_recevied" id="time_recevied" class="input-1" required />
    </div>

    <!-- Issue Description -->
    <div class="input-holder">
        <label for="issue_description">Issue Description</label>
        <input type="text" name="issue_description" id="issue_description" class="input-1" required />
    </div>

    <!-- Repair Description -->
    <div class="input-holder">
        <label for="repair_description">Repair Description</label>
        <input type="text" name="repair_description" id="repair_description" class="input-1" required />
    </div>

    <!-- Contacted Manufacturer -->
    <div class="input-holder">
        <label for="contacted_manufacturer">Contacted Manufacturer</label>
        <select name="contacted_manufacturer" id="contacted_manufacturer" required onchange="toggleFixFields(this)">
            <option value="">-- Select --</option>
            <option value="Yes">Yes</option>
            <option value="No">No</option>
        </select>
    </div>

    
   <!-- Inhouse Fixed By -->
<div class="input-holder" id="inhouse_holder" style="display:none;">
    <label for="inhouse_fixed_by">Inhouse Fixed By</label>
    <select name="inhouse_fixed_by" id="inhouse_fixed_by" class="input-1">
        <option value="">-- Select engineer --</option>
        <?php
        $stmt = $conn->prepare("SELECT full_name FROM users WHERE hospital_code = ? AND role = 'employee'");
        $stmt->execute([$hospital_code]);
        $users = $stmt->fetchAll(PDO::FETCH_COLUMN);
        foreach ($users as $name) {
            echo '<option value="' . htmlspecialchars($name) . '">' . htmlspecialchars($name) . '</option>';
        }
        ?>
    </select>
</div>



    <!-- Outhouse Fixed By -->
    <!-- Outhouse Fixed By (text input) -->
<div class="input-holder" id="outhouse_holder" style="display:none;">
    <label for="outhouse_fixed_by">Outhouse Fixed By</label>
    <input type="text" name="outhouse_fixed_by" id="outhouse_fixed_by" class="input-1" />
</div>


    <!-- Repair Cost -->
    <div class="input-holder">
        <label for="repair_cost">Repair Cost</label>
        <input type="text" name="repair_cost" id="repair_cost" class="input-1" required />
    </div>

    <!-- Repair Type -->
    <div class="input-holder">
        <label for="repair_type">Repair Type</label>
        <select name="repair_type" id="repair_type" required onchange="toggleOtherInput(this)">
            <option value="">-- Select --</option>
            <option value="Mechanical">Mechanical</option>
            <option value="Electrical">Electrical</option>
            <option value="Electronics">Electronics</option>
            <option value="UserCaused">UserCaused</option>
            <option value="PM">PM</option>
            <option value="other">Other</option>
        </select>
        <input type="text" name="repair_type_other" id="repair_type_other" class="input-1" style="display:none;" placeholder="Please specify" />
    </div>

    <!-- Used Spare Parts -->
    <div class="input-holder">
        <label for="used_spare_parts">Used Spare Parts</label>
        <select name="used_spare_parts" id="used_spare_parts" required>
            <option value="">-- Select --</option>
            <option value="Yes">Yes</option>
            <option value="No">No</option>
        </select>
    </div>

    <!-- Status -->
    <div class="input-holder">
        <label for="status">Status</label>
        <select name="status" id="status" required>
            <option value="pending">Pending</option>
            <option value="completed">Completed</option>
        </select>
    </div>

    <!-- Start & End Date/Time -->
    <div class="input-holder">
        <label for="start_date">Start Date</label>
        <input type="date" name="start_date" id="start_date" class="input-1" required />
    </div>
    <div class="input-holder">
        <label for="start_time">Start Time</label>
        <input type="time" name="start_time" id="start_time" class="input-1" required />
    </div>
    <div class="input-holder">
        <label for="end_date">End Date</label>
        <input type="date" name="end_date" id="end_date" class="input-1" required />
    </div>
    <div class="input-holder">
        <label for="end_time">End Time</label>
        <input type="time" name="end_time" id="end_time" class="input-1" required />
    </div>

    <button class="edit-btn" type="submit">Add Workorder</button>
</form>


        </section>
    </div>

    <!-- Highlight current nav item -->
   <script>
// Highlight current nav
document.querySelector("#navList li:nth-child(2)")?.classList.add("active");

// Device data: device_name → serial_number → department
const deviceInfo = <?php
    $stmt = $conn->prepare("SELECT device_name, serial_number, department FROM devices WHERE hospital_code = ?");
    $stmt->execute([$hospital_code]);
    $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $structured = [];
    foreach ($devices as $d) {
        $structured[$d['device_name']][$d['serial_number']] = $d['department'];
    }
    echo json_encode($structured);
?>;

// Engineers data from users table (role = employee)
const engineers = <?php
    $stmt = $conn->prepare("SELECT id, full_name FROM users WHERE hospital_code = ? AND role = 'employee'");
    $stmt->execute([$hospital_code]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>;

// DOM Elements
const deviceName       = document.getElementById("device_name");
const deviceSerial     = document.getElementById("device_serial");
const department       = document.getElementById("department");
const contactedField   = document.getElementById("contacted_manufacturer");
const inhouseHolder    = document.getElementById("inhouse_holder");
const outhouseHolder   = document.getElementById("outhouse_holder");
const inhouseInput     = document.getElementById("inhouse_fixed_by");
const outhouseInput    = document.getElementById("outhouse_fixed_by");

// When device name changes → update serials
deviceName.addEventListener("change", function () {
    const selectedName = this.value;
    deviceSerial.innerHTML = '<option value="">-- Select serial number --</option>';
    department.value = "";

    if (deviceInfo[selectedName]) {
        for (const serial in deviceInfo[selectedName]) {
            const option = document.createElement("option");
            option.value = serial;
            option.textContent = serial;
            deviceSerial.appendChild(option);
        }
    }
});

// When serial selected → auto-fill department
deviceSerial.addEventListener("change", function () {
    const selectedName = deviceName.value;
    const selectedSerial = this.value;
    department.value = deviceInfo[selectedName]?.[selectedSerial] || "";
});

// Show/hide 'Other' repair type input
function toggleOtherInput(select) {
    const otherInput = document.getElementById("repair_type_other");
    if (select.value === "other") {
        otherInput.style.display = "block";
        otherInput.required = true;
    } else {
        otherInput.style.display = "none";
        otherInput.required = false;
        otherInput.value = "";
    }
}
   

// Toggle inhouse/outhouse fixed by fields
function toggleFixFields(select) {
    const val = select.value;

    if (val === "Yes") {
        // Show outhouse only
        outhouseHolder.style.display = "block";
        outhouseInput.required = true;
        outhouseInput.value = "";

        inhouseHolder.style.display = "none";
        inhouseInput.required = false;
        inhouseInput.innerHTML = '<option value="No">No</option>';
    } else if (val === "No") {
        // Show inhouse only
        inhouseHolder.style.display = "block";
        inhouseInput.required = true;

        inhouseInput.innerHTML = '<option value="">-- Select engineer --</option>';
        engineers.forEach(user => {
            const opt = document.createElement("option");
            opt.value = user.full_name;
            opt.textContent = user.full_name;
            inhouseInput.appendChild(opt);
        });

        outhouseHolder.style.display = "none";
        outhouseInput.required = false;
        outhouseInput.value = "No";
    } else {
        // Hide both
        inhouseHolder.style.display = "none";
        inhouseInput.required = false;
        inhouseInput.innerHTML = '';

        outhouseHolder.style.display = "none";
        outhouseInput.required = false;
        outhouseInput.value = "";
    }
}

// On DOM load
document.addEventListener("DOMContentLoaded", function () {
    if (contactedField) {
        toggleFixFields(contactedField);
        contactedField.addEventListener("change", function () {
            toggleFixFields(this);
        });
    }

    const repairTypeSelect = document.getElementById("repair_type");
    if (repairTypeSelect) {
        toggleOtherInput(repairTypeSelect);
        repairTypeSelect.addEventListener("change", function () {
            toggleOtherInput(this);
        });
    }
});
</script>



<script type="text/javascript">
		var active = document.querySelector("#navList li:nth-child(5)");
		if (active) active.classList.add("active");
	</script>
</body>
</html>
<?php
