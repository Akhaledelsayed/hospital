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
	<title>Add Company</title>
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
	<?php include "inc/header.php" ?>
	<div class="body">
		<?php include "inc/nav.php" ?>
		<section class="section-1">
			<h4 class="title">Add Company <a href="company.php">Companies</a></h4>

			<?php if (isset($_GET['error'])) { ?>
      	  	<div class="danger"><?= htmlspecialchars($_GET['error']); ?></div>
      	  	<?php } ?>
      	  	<?php if (isset($_GET['success'])) { ?>
      	  	<div class="success"><?= htmlspecialchars($_GET['success']); ?></div>
      	  	<?php } ?><form class="form-1" method="POST" action="app/add-company.php">
<?php
	// قائمة رموز الدول الشائعة
	$country_codes = [
		"+20" => "🇪🇬 ",
		"+966" => "🇸🇦  ",
		"+971" => "🇦🇪 ",
		"+1" => "🇺🇸🇦 ",
		"+44" => "🇬🇧 ",
		"+49" => "🇩🇪 ",
		"+33" => "🇫🇷 ",
		"+91" => "🇮🇳 ",
		"+81" => "🇯🇵 "
		// يمكنك إضافة المزيد حسب الحاجة
	];

	$fields = [
		"company_name" => "Name", 
		"phone" => "Phone", 
		"company_address" => "Address",
		"company_email" => "Email", 
		"contact1_name" => "Contact Name1", 
		"contact1_title" => "Contact Title1",
		"contact1_mobile" => "Contact Phone1",
		"contact2_name" => "Contact Name2", 
		"contact2_title" => "Contact Title2",
		"contact2_mobile" => "Contact Phone2",
		"contact3_name" => "Contact Name3", 
		"contact3_title" => "Contact Title3",
		"contact3_mobile" => "Contact Phone3"
	];

	foreach ($fields as $name => $label) {
		$type = "text";
		$extra = "";
		$required = true;

		if (strpos($name, 'contact2_') !== false || strpos($name, 'contact3_') !== false) {
			$required = false;
		}

		if (strpos($name, 'email') !== false) {
			$type = "email";
		}

		if (strpos($name, 'mobile') !== false || $name === 'phone') {
			$type = "tel";
			$extra = 'pattern="[0-9]{6,}" title="Numbers only, at least 6 digits"';
			echo '<div class="input-holder">
					<label for="'.$name.'">'.$label.($required ? ' *' : '').'</label>
					<div style="display:flex; gap:8px;">
						<select name="'.$name.'_code" class="input-1" style="max-width: 110px;" '.($required ? 'required' : '').'>';
							foreach ($country_codes as $code => $country) {
								$selected = ($code == "+20") ? "selected" : "";
								echo "<option value=\"$code\" $selected>$country ($code)</option>";
							}
						echo '</select>
						<input type="'.$type.'" name="'.$name.'" id="'.$name.'" class="input-1" style="flex:1;" '.($required ? 'required' : '').' '.$extra.' />
					</div>
				</div>';
		} else {
			echo '<div class="input-holder">
					<label for="'.$name.'">'.$label.($required ? ' *' : '').'</label>
					<input type="'.$type.'" name="'.$name.'" id="'.$name.'" class="input-1" '.($required ? 'required' : '').' '.$extra.' />
				  </div>';
		}
	}
?>
	<button class="edit-btn" type="submit">Add Company</button>
</form>


		</section>
	</div>

	<script type="text/javascript">
		var active = document.querySelector("#navList li:nth-child(2)");
		if (active) active.classList.add("active");
	</script>
</body>
</html>

