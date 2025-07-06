<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id'])) {

    include "DB_connection.php";
    include "app/Model/User.php";
    include "app/Model/Devices.php";

    // إذا تم إرسال hospital_code في الرابط
    if (isset($_GET['hospital_code'])) {
        $_SESSION['current_hospital_code'] = $_GET['hospital_code'];
    }

    $hospital_code = $_SESSION['current_hospital_code'] ?? null;
    $user_id = $_SESSION['id'];
    $role = $_SESSION['role'];

    // تحقق من صلاحية الدخول
    $hasAccess = false;

    if ($role == "admin") {
        $hasAccess = true; // الـ admin يدخل أي مستشفى
    } else {
        // جلب المستشفيات المرتبط بها المستخدم
        $sql = "SELECT hospital_code FROM user_hospitals WHERE username = (SELECT username FROM users WHERE id = ?) ";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$user_id]);
        $user_hospitals = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // هل المستشفى المحددة موجودة ضمن المستشفيات المرتبطة بالمستخدم؟
        if (in_array($hospital_code, $user_hospitals)) {
            $hasAccess = true;
        }
    }

    if ($hasAccess) {
        // حساب الأعداد
        $num_users = count_users($conn, $hospital_code);
        $num_devices = count_user_devices($conn, $hospital_code);
        $num_factories = count_factories($conn, $hospital_code);
        $num_companies = count_companies($conn, $hospital_code);
        $num_invoices = count_user_invoices($conn, $hospital_code);
        $num_workorders = count_user_workorders($conn, $hospital_code);

        // يمكنك الآن عرض الصفحة بشكل طبيعي
    } else {
        echo "<h2 style='text-align:center; color:red;'>❌you not assigned here </h2>";
        exit;
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
			cursor: default;
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
			display: inline-block;
			width: 60px;
			height: 60px;
			line-height: 30px;
			text-align: center;
		}
		.card-title {
			font-size: 16px;
			font-weight: bold;
		}
		.counter {
			font-size: 24px;
			font-weight: bold;
		}

		/* Gradients for cards */
		.bg-gradient-1 { background: linear-gradient(90deg, #667eea, #764ba2); }
		.bg-gradient-2 { background: linear-gradient(90deg, #f7971e, #ffd200); }
		.bg-gradient-3 { background: linear-gradient(90deg, #ff758c, #ff7eb3); }
		.bg-gradient-4 { background: linear-gradient(90deg, #43cea2, #185a9d); }
		.bg-gradient-5 { background: linear-gradient(90deg, #00c6ff, #0072ff); }
		.bg-gradient-6 { background: linear-gradient(90deg, #f953c6, #b91d73); }
		.bg-gradient-7 { background: linear-gradient(90deg, #ff9966, #ff5e62); }
		.bg-gradient-8 { background: linear-gradient(90deg, #56ccf2, #2f80ed); }
		.bg-gradient-9 { background: linear-gradient(90deg, #11998e, #38ef7d); }

		/* Responsive tweaks */
		@media (max-width: 767.98px) {
			.card i {
				width: 50px;
				height: 50px;
				font-size: 24px;
				line-height: 24px;
				padding: 10px;
			}
			.counter {
				font-size: 20px;
			}
			.card-title {
				font-size: 14px;
			}
		}
	</style>
</head>
<body>
	<input type="checkbox" id="checkbox" />
	<?php include "inc/header.php"; ?>
	<div class="body">
		<?php include "inc/nav.php"; ?>
		<section class="section-1 p-4">
			<div class="container-fluid">
				<div class="row g-4">
					<?php
						if ($_SESSION['role'] == "admin" || ($_SESSION['role'] == "employee" && in_array($_SESSION['current_hospital_code'], $user_hospitals))) {
							$cards = [
								["icon" => "fa-users", "count" => $num_users, "label" => "Users", "bg" => "bg-gradient-1"],
								["icon" => "fa-desktop", "count" => $num_devices, "label" => "Devices", "bg" => "bg-gradient-2"],
								["icon" => "fa-window-close-o", "count" => $num_workorders, "label" => "Work Orders", "bg" => "bg-gradient-3"],
								["icon" => "fa-clock-o", "count" => $num_factories, "label" => "Manufactures", "bg" => "bg-gradient-4"],
								["icon" => "fa-exclamation-triangle", "count" =>  $num_companies, "label" => "Companies", "bg" => "bg-gradient-5"],
								["icon" => "fa-bell", "count" => $num_invoices, "label" => "Invoices", "bg" => "bg-gradient-6"],
							];
						}

						if (isset($cards)) {
							foreach ($cards as $index => $card): ?>
								<div class="col-sm-6 col-md-4">
									<div class="card <?=$card['bg']?>" style="animation-delay: <?=($index * 0.1)?>s;">
										<i class="fa <?=$card['icon']?>"></i>
										<div class="counter" data-count="<?=$card['count']?>">0</div>
										<div class="card-title"><?=$card['label']?></div>
									</div>
								</div>
							<?php endforeach;
}
?>

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

<?php } else {
	header("Location: login.php");
	exit();
}
?>
