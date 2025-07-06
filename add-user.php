<?php 
session_start();
$hospital_code = $_SESSION['current_hospital_code'] ?? null;

if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "admin") {
	include "DB_connection.php";

	// Fetch users from users_inoffice using PDO
	$stmt = $conn->prepare("SELECT username, id FROM users_inoffice");
	$stmt->execute();
	$usersInOffice = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<title>Add User</title>
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
			box-shadow: 0 3px 8px rgba(102,126,234,0.5);
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
		}
		.input-1 {
			padding: 12px 18px;
			border-radius: 10px;
			border: 1.5px solid #cbd5e1;
			font-size: 16px;
		}
		.input-1:focus {
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
			cursor: pointer;
		}
		.success, .danger {
			padding: 12px 20px;
			border-radius: 8px;
			font-weight: 600;
			margin-bottom: 20px;
		}
		.success {
			background-color: #d4edda;
			color: #155724;
			border: 1px solid #c3e6cb;
		}
		.danger {
			background-color: #f8d7da;
			color: #721c24;
			border: 1px solid #f5c6cb;
		}
	</style>
</head>
<body>
	<?php include "inc/header.php"; ?>
	<div class="body">
		<?php include "inc/nav.php"; ?>
		<section class="section-1">
			<h4 class="title">Add Users <a href="user.php">Users</a></h4>

			<?php if (isset($_GET['error'])) { ?>
				<div class="danger"><?= htmlspecialchars($_GET['error']); ?></div>
			<?php } ?>
			<?php if (isset($_GET['success'])) { ?>
				<div class="success"><?= htmlspecialchars($_GET['success']); ?></div>
			<?php } ?>

			<form class="form-1" method="POST" action="app/add-user.php">
				<div class="input-holder">
					<label for="user_name">Username</label>
					<select name="user_name" id="user_name" class="input-1" onchange="fillID()" required>
						<option value="" disabled selected>Choose Username</option>
						<?php foreach ($usersInOffice as $user): ?>
							<option value="<?= htmlspecialchars($user['username']) ?>" data-id="<?= htmlspecialchars($user['id']) ?>">
								<?= htmlspecialchars($user['username']) ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="input-holder">
					<label for="id">ID</label>
					<input type="text" name="id" id="id" class="input-1" placeholder="User ID" readonly required />
				</div>
				<div class="input-holder">
					<label for="full_name">Full Name</label>
					<input type="text" name="full_name" id="full_name" class="input-1" placeholder="Full Name" required />
				</div>
				<div class="input-holder">
    <label for="hospital_code">Hospital(s)</label>
    <select name="hospital_code[]" id="hospital_code" class="input-1" multiple required>
        <?php
        $stmt = $conn->prepare("SELECT hospital_code, name FROM hospitals");
        $stmt->execute();
        $hospitals = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($hospitals as $hospital) {
            echo '<option value="' . htmlspecialchars($hospital['hospital_code']) . '">' .
                    htmlspecialchars($hospital['name']) .
                 '</option>';
        }
        ?>
    </select>
    <small>Hold Ctrl (Windows) or Command (Mac) to select multiple</small>
</div>
				<div class="input-holder">
					<label for="password">Password</label>
					<input type="password" name="password" id="password" class="input-1" placeholder="Password" required />
				</div>
				<button class="edit-btn" type="submit">Add</button>
			</form>
		</section>
	</div>

	<script>
		function fillID() {
			const select = document.getElementById('user_name');
			const selected = select.options[select.selectedIndex];
			const id = selected.getAttribute('data-id');
			document.getElementById('id').value = id;
		}
	</script>
</body>
</html>
<?php 
} else {
	header("Location: login.php?error=Please login first");
	exit();
}
?>
