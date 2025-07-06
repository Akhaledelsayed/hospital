<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) ) {

	include "DB_connection.php";
	include "app/Model/User.php";
	include "app/Model/Devices.php";

	if ($_SESSION['role'] == "admin") {
		$num_users = count_users($conn);
	} else {
		// Get the current user's hospital_code from DB
		$user_id = $_SESSION['id'];
		$sql = "SELECT hospital_code FROM users WHERE id = ?";
		$stmt = $conn->prepare($sql);
		$stmt->execute([$user_id]);
		$row = $stmt->fetch(PDO::FETCH_ASSOC);

		if ($row && isset($row['hospital_code'])) {
			$hospital_code = $row['hospital_code'];

			$num_devices = count_user_devices($conn, $hospital_code);
			$num_factories = count_user_factories($conn, $hospital_code);
			$num_companies = count_user_companies($conn, $hospital_code);
			$num_invoices = count_user_invoices($conn, $hospital_code);
			$num_workorders = count_user_workorders($conn, $hospital_code);
		} else {
			$em = "Hospital code not found. Please login again.";
			header("Location: login.php?error=$em");
			exit();
		}
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>Dashboard</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" />
	<link rel="stylesheet" href="css/style.css" />

	<style>
		body {
			background-color: #fff !important;
			direction: ltr;
			font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
		}
		.card {
			border-radius: 20px;
			color: #fff;
			padding: 20px;
			text-align: center;
			transition: transform 0.3s ease, opacity 0.5s ease;
			box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
			opacity: 0;
			transform: translateX(-30px);
			animation: fadeInRight 1s forwards;
		}
		.card:nth-child(1) { animation-delay: 0.1s; }
		.card:nth-child(2) { animation-delay: 0.2s; }
		.card:nth-child(3) { animation-delay: 0.3s; }
		.card:nth-child(4) { animation-delay: 0.4s; }
		.card:nth-child(5) { animation-delay: 0.5s; }
		.card:nth-child(6) { animation-delay: 0.6s; }
		.card:nth-child(7) { animation-delay: 0.7s; }
		.card:nth-child(8) { animation-delay: 0.8s; }
		.card:nth-child(9) { animation-delay: 0.9s; }

		@keyframes fadeInRight {
			to {
				opacity: 1;
				transform: translateX(0);
			}
		}

		.card i {
			font-size: 30px;
			background: rgba(255, 255, 255, 0.2);
			border-radius: 50%;
			padding: 15px;
			margin-bottom: 10px;
		}
		.card-title {
			font-size: 16px;
			font-weight: bold;
		}
		.counter {
			font-size: 24px;
			font-weight: bold;
		}

		/* Gradients from left to right for each card */
		.bg-gradient-1 { background: linear-gradient(90deg, #667eea, #764ba2); }
		.bg-gradient-2 { background: linear-gradient(90deg, #f7971e, #ffd200); }
		.bg-gradient-3 { background: linear-gradient(90deg, #ff758c, #ff7eb3); }
		.bg-gradient-4 { background: linear-gradient(90deg, #43cea2, #185a9d); }
		.bg-gradient-5 { background: linear-gradient(90deg, #00c6ff, #0072ff); }
		.bg-gradient-6 { background: linear-gradient(90deg, #f953c6, #b91d73); }
		.bg-gradient-7 { background: linear-gradient(90deg, #ff9966, #ff5e62); }
		.bg-gradient-8 { background: linear-gradient(90deg, #56ccf2, #2f80ed); }
		.bg-gradient-9 { background: linear-gradient(90deg, #11998e, #38ef7d); }
	</style>
</head>
<body>
	<input type="checkbox" id="checkbox" />
	<?php include "inc/header.php" ?>
	<div class="body">
		<?php include "inc/nav.php" ?>
		<section class="section-1 p-4">
			<div class="container-fluid">
				<div class="row g-4">
					<?php
					if ($_SESSION['role'] == "admin") {
						$cards = [
							["icon" => "fa-users", "count" => $num_users, "label" => "Employees", "bg" => "bg-gradient-1"],
							["icon" => "fa-tasks", "count" => $num_task, "label" => "All Tasks", "bg" => "bg-gradient-2"],
							["icon" => "fa-window-close-o", "count" => $overdue_task, "label" => "Overdue", "bg" => "bg-gradient-3"],
							["icon" => "fa-clock-o", "count" => $nodeadline_task, "label" => "No Deadline", "bg" => "bg-gradient-4"],
							["icon" => "fa-exclamation-triangle", "count" => $todaydue_task, "label" => "Due Today", "bg" => "bg-gradient-5"],
							["icon" => "fa-bell", "count" => $overdue_task, "label" => "Notifications", "bg" => "bg-gradient-6"],
							["icon" => "fa-square-o", "count" => $pending, "label" => "Pending", "bg" => "bg-gradient-7"],
							["icon" => "fa-spinner", "count" => $in_progress, "label" => "In Progress", "bg" => "bg-gradient-8"],
							["icon" => "fa-check-square-o", "count" => $completed, "label" => "Completed", "bg" => "bg-gradient-9"]
						];
					} else {
						$cards = [
							["icon" => "fa-tasks", "count" => $num_my_task, "label" => "My Tasks", "bg" => "bg-gradient-1"],
							["icon" => "fa-window-close-o", "count" => $overdue_task, "label" => "Overdue", "bg" => "bg-gradient-3"],
							["icon" => "fa-clock-o", "count" => $nodeadline_task, "label" => "No Deadline", "bg" => "bg-gradient-4"],
							["icon" => "fa-square-o", "count" => $pending, "label" => "Pending", "bg" => "bg-gradient-7"],
							["icon" => "fa-spinner", "count" => $in_progress, "label" => "In Progress", "bg" => "bg-gradient-8"],
							["icon" => "fa-check-square-o", "count" => $completed, "label" => "Completed", "bg" => "bg-gradient-9"]
						];
					}

					foreach ($cards as $index => $card): ?>
						<div class="col-sm-6 col-md-4 col-lg-3">
							<div class="card <?=$card['bg']?>" style="animation-delay: <?=($index * 0.1)?>s">
								<i class="fa <?=$card['icon']?>"></i>
								<div class="counter" data-count="<?=$card['count']?>">0</div>
								<div class="card-title"><?=$card['label']?></div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	</div>

	<script>
		// Animated counter
		document.addEventListener("DOMContentLoaded", function () {
			document.querySelectorAll(".counter").forEach(function (el) {
				let countTo = parseInt(el.getAttribute("data-count"));
				let count = 0;
				let step = Math.ceil(countTo / 50);
				let interval = setInterval(function () {
					count += step;
					if (count >= countTo) {
						el.textContent = countTo;
						clearInterval(interval);
					} else {
						el.textContent = count;
					}
				}, 30);
			});
		});
	</script>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php 
} else {
	$em = "Please login first";
	header("Location: login.php?error=$em");
	exit();
}
?>
