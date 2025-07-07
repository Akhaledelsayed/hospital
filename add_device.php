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
			box-shadow: 0 3px 8px rgba(102,126,234,0.5);
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
		.input-1, select {
			padding: 12px 18px;
			border-radius: 10px;
			border: 1.5px solid #cbd5e1;
			font-size: 16px;
			transition: border-color 0.3s ease;
		}
		.input-1:focus, select:focus {
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
		.success, .danger {
			padding: 12px 20px;
			border-radius: 8px;
			margin-bottom: 20px;
			font-weight: 600;
		}
		.success {
			background-color: #d4edda;
			border: 1px solid #c3e6cb;
			color: #155724;
		}
		.danger {
			background-color: #f8d7da;
			border: 1px solid #f5c6cb;
			color: #721c24;
		}
	</style>
</head>
<body>
<div class="section-1">
	<div class="title">
		<span>Add New Device</span>
		<a href="device.php">Back to Devices</a>
	</div>

	<?php
	if ($_SERVER["REQUEST_METHOD"] == "POST") {
		function clean($data) {
			return htmlspecialchars(trim($data));
		}

		$fields = [
			'floor', 'department', 'department_now', 'room', 'device_name', 'accessories',
			'manufacturer', 'origin', 'company', 'model', 'serial_number', 'qt', 'bmd_code',
			'arrival_date', 'installation_date', 'purchaseorder_date', 'price',
			'warranty_start', 'warranty_end',
			'company_contact', 'company_tel', 'hospital_code','device_safety_test'
		];


		
		foreach ($fields as $field) {
			$$field = clean($_POST[$field] ?? '');
		}

		$assigned_user = $_SESSION['username']; // ✅ المستخدم الحالي
		$warranty_period = '';

		if (!empty($warranty_start) && !empty($warranty_end)) {
			$start = new DateTime($warranty_start);
			$end = new DateTime($warranty_end);
			$diff = $start->diff($end);
			$warranty_period = $diff->y; // ✅ عدد السنوات فقط
		}

		if ($serial_number && $hospital_code && $assigned_user) {
			$sql = "INSERT INTO Devices (
				Floor, Department, Department_now, Room, device_name, Accessories,
				Manufacturer, Origin, Company, Model, Serial_number, QT, BMD_Code,
				Arrival_Date, Installation_Date, purchaseorder_date, Price, Warranty_Period,
				warranty_start, warranty_end, company_contact, company_Tel,
				hospital_code, assigned_user,device_safety_test
			) VALUES (
				?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?
			)";
			$stmt = $conn->prepare($sql);
			$success = $stmt->execute([
				$floor, $department, $department_now, $room, $device_name, $accessories,
				$manufacturer, $origin, $company, $model, $serial_number, $qt, $bmd_code,
				$arrival_date, $installation_date, $purchaseorder_date, $price,
				$warranty_period, $warranty_start, $warranty_end, $company_contact,
				$company_tel, $hospital_code, $assigned_user ,$device_safety_test
			]);

			echo $success ? "<div class='success'>Device added successfully.</div>"
						  : "<div class='danger'>Error occurred while adding the device.</div>";
		} else {
			echo "<div class='danger'>Please fill in all required fields (Serial Number, Hospital).</div>";
		}
	}
	?>

	<form method="POST" class="form-1">
		<?php
		function input($label, $name, $type = "text", $required = false) {
			$req = $required ? 'required' : '';
			echo "<div class='input-holder'>
					<label for='$name'>$label" . ($required ? " *" : "") . "</label>
					<input type='$type' name='$name' id='$name' class='input-1' $req />
				  </div>";
		}
		echo "<div class='input-holder'>
				<label for='Floor'>Floor *</label>
				<select name='Floor' id='Floor' class='input-1' required>
				<option value=''>-- Select Floor --</option>";
		$deps = $conn->query("SELECT DISTINCT Floor FROM Devices WHERE Floor IS NOT NULL AND Floor != ''and hospital_code = $hospital_code");
		while ($row = $deps->fetch()) {
			echo "<option value='" . htmlspecialchars($row['Floor']) . "'>" . htmlspecialchars($row['Floor']) . "</option>";
		}
		echo "</select></div>";

		// ✅ Department (اختيار من قاعدة البيانات)
		echo "<div class='input-holder'>
				<label for='department'>Department *</label>
				<select name='department' id='department' class='input-1' required>
				<option value=''>-- Select Department --</option>";
		$deps = $conn->query("SELECT DISTINCT department FROM Devices WHERE department IS NOT NULL AND department != ''and hospital_code = $hospital_code");
		while ($row = $deps->fetch()) {
			echo "<option value='" . htmlspecialchars($row['department']) . "'>" . htmlspecialchars($row['department']) . "</option>";
		}
		echo "</select></div>";

		
		// ✅ Department_now (نفس الشيء)
		echo "<div class='input-holder'>
				<label for='department_now'>Current Department *</label>
				<select name='department_now' id='department_now' class='input-1' required>
				<option value=''>-- Select Department --</option>";
		$deps_now = $conn->query("SELECT DISTINCT department_now FROM Devices WHERE department_now IS NOT NULL AND department_now != ''and hospital_code = $hospital_code");
		while ($row = $deps_now->fetch()) {
			echo "<option value='" . htmlspecialchars($row['department_now']) . "'>" . htmlspecialchars($row['department_now']) . "</option>";
		}
		echo "</select></div>";

echo '<div class="input-holder">
        <label for="room">Room *</label>
        <input type="text" name="room" id="room" class="input-1" required oninput="this.value = this.value.toUpperCase();" />
      </div>';
input("Device Name", "device_name", "text", true);
input("Accessories", "accessories", "text", true);
		echo "<div class='input-holder'>
        <label for='manufacturer'>Manufacturer *</label>
        <select name='manufacturer' id='manufacturer' class='input-1' required>
            <option value=''>-- Select Manufacturer --</option>";
						$manufacturers = $conn->query("SELECT DISTINCT manufacturer FROM devices WHERE manufacturer IS NOT NULL AND manufacturer != ''and hospital_code = $hospital_code");
						while ($row = $manufacturers->fetch()) {
							echo "<option value='" . htmlspecialchars($row['manufacturer']) . "'>" . htmlspecialchars($row['manufacturer']) . "</option>";
						}
						echo "</select>
							</div>";

		input("Origin", "origin","text", true);
		echo "<div class='input-holder'>
        <label for='company'>Company *</label>
        <select name='company' id='company' class='input-1' required>
            <option value=''>-- Select Company --</option>";
					$companies = $conn->query("SELECT DISTINCT company FROM devices WHERE company IS NOT NULL AND company != ''and hospital_code = $hospital_code");
					while ($row = $companies->fetch()) {
						echo "<option value='" . htmlspecialchars($row['company']) . "'>" . htmlspecialchars($row['company']) . "</option>";
					}
					echo "</select>
						</div>";
		

input("Model", "model", "text", true);
input("Serial Number", "serial_number", "text", true);
input("Quantity", "qt", "number", true);
input("BMD Code", "bmd_code", "text", true);
input("Arrival Date", "arrival_date", "date", true);
input("Installation Date", "installation_date", "date", true);
input("Purchase Order Date", "purchaseorder_date", "date", true);
// ✅ حقل السعر مع اختيار العملة
echo '<div class="input-holder">
        <label for="price">Price *</label>
        <div style="display: flex; gap: 8px;">
            <select name="currency" class="input-1" style="max-width: 100px;" required>
                <option value="EGP" selected>EGP</option>
                <option value="USD">USD</option>
                <option value="EUR">EUR</option>
                <option value="SAR">SAR</option>
                <option value="AED">AED</option>
                <option value="GBP">GBP</option>
                <option value="JPY">JPY</option>
            </select>
            <input type="number" name="price" id="price" class="input-1" style="flex: 1;" required pattern="^\d+(\.\d{1,2})?$" title="Enter a valid number (e.g. 100 or 100.50)" />
        </div>
      </div>';

input("Warranty Start", "warranty_start", "date", true);
input("Warranty End", "warranty_end", "date", true);
input("Company Contact", "company_contact", "text", true);
input("Company Telephone", "company_tel");
		echo '<div class="input-holder">
                                <label for="device_safety_test">Device Safety Test *</label>
                                <select name="device_safety_test" id="device_safety_test" required>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                </select>
                            </div>';

		?>

		<input type="hidden" name="hospital_code" value="<?= htmlspecialchars($hospital_code) ?>" />
		<input type="hidden" name="assigned_user" value="<?= htmlspecialchars($_SESSION['username']) ?>" />

		

		<button type="submit" class="edit-btn">Add Device</button>
		
	</form>
</div>
</body>
</html>
