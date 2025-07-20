<?php
session_start();
include "DB_connection.php";
include "app/Model/Devices.php";

$hospital_code = $_SESSION['current_hospital_code'] ?? null;

if (!isset($_SESSION['role'], $_SESSION['id'], $_SESSION['username']) || !$hospital_code) {
    header("Location: ../login.php?error=" . urlencode("❌ Access denied. Please log in."));
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: workorder.php");
    exit;
}

$id = $_GET['id'];
$workorder = get_workorder_by_id($conn, $id, $hospital_code);

if (!$workorder) {
    header("Location: workorder.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Edit Workorder</title>
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
            max-width: 600px;
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
        }
        .form-1 { display: flex; flex-direction: column; gap: 20px; }
        .input-holder { display: flex; flex-direction: column; }
        .input-holder label { font-weight: 600; margin-bottom: 6px; }
        .input-1 {
            padding: 12px 18px;
            border-radius: 10px;
            border: 1.5px solid #cbd5e1;
            font-size: 16px;
        }
        .edit-btn {
            background: linear-gradient(45deg, #38b2ac, #319795);
            border: none;
            color: #fff;
            font-weight: 700;
            padding: 14px 0;
            border-radius: 28px;
            font-size: 18px;
            cursor: pointer;
        }
        .success, .danger {
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: 600;
            margin-bottom: 20px;
        }
        .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .danger  { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
<?php include "inc/header.php"; ?>
<div class="body">
    <?php include "inc/nav.php"; ?>
    <section class="section-1">
        <h4 class="title">Edit Workorder <a href="workorder.php">Back</a></h4>

        <?php if (isset($_GET['error'])) { ?>
            <div class="danger"><?= htmlspecialchars($_GET['error']); ?></div>
        <?php } ?>
        <?php if (isset($_GET['success'])) { ?>
            <div class="success"><?= htmlspecialchars($_GET['success']); ?></div>
        <?php } ?>

        <form class="form-1" method="POST" action="app/update-workorder.php">
            <input type="hidden" name="id" value="<?= htmlspecialchars($workorder['id']); ?>" />

            <div class="input-holder">
                <label>Device Serial</label>
                <input type="text" name="device_serial" class="input-1" value="<?= htmlspecialchars($workorder['device_serial']); ?>" readonly />
            </div>

            <div class="input-holder">
                <label>Requested By</label>
                <input type="text" name="requested_by" class="input-1" value="<?= htmlspecialchars($workorder['requested_by']); ?>" />
            </div>

            <div class="input-holder">
                <label>Department</label>
                <input type="text" name="department" class="input-1" value="<?= htmlspecialchars($workorder['department']); ?>" />
            </div>

            <div class="input-holder">
                <label>Date Received</label>
                <input type="date" name="date_recevied" class="input-1" value="<?= htmlspecialchars($workorder['date_recevied']); ?>" />
            </div>

            <div class="input-holder">
                <label>Time Received</label>
                <input type="time" name="time_recevied" class="input-1" value="<?= htmlspecialchars($workorder['time_recevied']); ?>" />
            </div>

            <div class="input-holder">
                <label>Issue Description</label>
                <input type="text" name="issue_description" class="input-1" value="<?= htmlspecialchars($workorder['issue_description']); ?>" />
            </div>

            <div class="input-holder">
                <label>Repair Description</label>
                <input type="text" name="repair_description" class="input-1" value="<?= htmlspecialchars($workorder['repair_description']); ?>" />
            </div>

            <div class="input-holder">
                <label>Inhouse Fixed By</label>
                <input type="text" name="inhouse_fixed_by" class="input-1" value="<?= htmlspecialchars($workorder['inhouse_fixed_by']); ?>" />
            </div>

            <div class="input-holder">
                <label>Contacted Manufacturer</label>
                <select name="contacted_manufacturer" class="input-1">
                    <option value="Yes" <?= $workorder['contacted_manufacturer'] === 'Yes' ? 'selected' : '' ?>>Yes</option>
                    <option value="No" <?= $workorder['contacted_manufacturer'] === 'No' ? 'selected' : '' ?>>No</option>
                </select>
            </div>

            <div class="input-holder">
                <label>Outhouse Fixed By</label>
                <input type="text" name="outhouse_fixed_by" class="input-1" value="<?= htmlspecialchars($workorder['outhouse_fixed_by']); ?>" />
            </div>

            <div class="input-holder">
                <label>Repair Cost</label>
                <input type="text" name="repair_cost" class="input-1" value="<?= htmlspecialchars($workorder['repair_cost']); ?>" />
            </div>

            <div class="input-holder">
                <label>Repair Type</label>
                <select name="repair_type" class="input-1">
                    <?php
                    $types = ['Mechanical', 'Electrical', 'Electronics', 'UserCaused', 'PM', 'Other'];
                    foreach ($types as $type) {
                        $selected = ($workorder['repair_type'] === $type) ? 'selected' : '';
                        echo "<option value=\"$type\" $selected>$type</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="input-holder">
                <label>Used Spare Parts</label>
                <select name="used_spare_parts" class="input-1">
                    <option value="Yes" <?= $workorder['used_spare_parts'] === 'Yes' ? 'selected' : '' ?>>Yes</option>
                    <option value="No" <?= $workorder['used_spare_parts'] === 'No' ? 'selected' : '' ?>>No</option>
                </select>
            </div>

            <div class="input-holder">
                <label>Status</label>
                <select name="status" class="input-1">
                    <option value="pending" <?= $workorder['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="completed" <?= $workorder['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                </select>
            </div>

            <div class="input-holder">
                <label>Start Date</label>
                <input type="date" name="start_date" class="input-1" value="<?= htmlspecialchars($workorder['start_date']); ?>" />
            </div>

            <div class="input-holder">
                <label>Start Time</label>
                <input type="time" name="start_time" class="input-1" value="<?= htmlspecialchars($workorder['start_time']); ?>" />
            </div>

            <div class="input-holder">
                <label>End Date</label>
                <input type="date" name="end_date" class="input-1" value="<?= htmlspecialchars($workorder['end_date']); ?>" />
            </div>

            <div class="input-holder">
                <label>End Time</label>
                <input type="time" name="end_time" class="input-1" value="<?= htmlspecialchars($workorder['end_time']); ?>" />
            </div>

            <button class="edit-btn" type="submit">Update</button>
        </form>
    </section>
</div>

<script type="text/javascript">
		var active = document.querySelector("#navList li:nth-child(5)");
		if (active) active.classList.add("active");
	</script>
</body>
</html>
