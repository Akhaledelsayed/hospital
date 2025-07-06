<?php 
session_start();

if (isset($_SESSION['role']) && isset($_SESSION['id'])) {

	include "DB_connection.php";
	include "app/Model/Devices.php";
	include "app/Model/User.php";

	// عدد المستشفيات الأصلية (ثابت)
	$original_hospitals_count = 3;

	// إضافة مستشفى جديدة (تخزين مؤقت فقط مثال)
	if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_hospital'])) {
		$new_label = trim($_POST['hospital_name'] ?? '');
		$new_link = trim($_POST['hospital_link'] ?? '#');
		$new_image = '';
		$new_code = trim($_POST['hospital_code'] ?? '');

		// رفع الصورة
		if (isset($_FILES['hospital_image']) && $_FILES['hospital_image']['error'] == 0) {
			$upload_dir = "uploads/";
			if (!is_dir($upload_dir)) {
				mkdir($upload_dir, 0755, true);
			}
			$tmp_name = $_FILES['hospital_image']['tmp_name'];
			$name = basename($_FILES['hospital_image']['name']);
			$target_file = $upload_dir . time() . "_" . $name;
			if (move_uploaded_file($tmp_name, $target_file)) {
				$new_image = $target_file;
			}
		}

		if (!isset($_SESSION['extra_hospitals'])) {
			$_SESSION['extra_hospitals'] = [];
		}
		$_SESSION['extra_hospitals'][] = [
			"label" => $new_label,
			"bg" => "bg-gradient-2",
			"image" => $new_image,
			"link" => $new_link,
			"code" => $new_code,
		];

		header("Location: " . $_SERVER['PHP_SELF']);
		exit();
	}

	// حذف مستشفى مؤقتة حسب index
	if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_hospital'])) {
		$del_index = intval($_POST['delete_hospital']);
		// فقط نحذف إذا الفهرس ينتمي للمستشفيات الإضافية
		if ($del_index >= $original_hospitals_count && isset($_SESSION['extra_hospitals'][$del_index - $original_hospitals_count])) {
			unset($_SESSION['extra_hospitals'][$del_index - $original_hospitals_count]);
			$_SESSION['extra_hospitals'] = array_values($_SESSION['extra_hospitals']); // إعادة ترتيب الفهارس
		}
		header("Location: " . $_SERVER['PHP_SELF']);
		exit();
	}

	
	$cards = [
			[
				"label" => "Katamya Hospital",
				"bg" => "bg-gradient-1",
				"image" => "img/katameya.png",
				"link" => "katameya.php",
				"code" => "1",
			],
			[
				"label" => "Welcare Hospital",
				"bg" => "bg-gradient-2",
				"image" => "img/welcarelogo.png",
				"link" => "katameya.php",
				"code" => "2",
			],
			[
				"label" => "Zahraa EL-Madaan Hospital",
				"bg" => "bg-gradient-green",
				"image" => "img/zahra.png",
				"link" => "katameya.php",
				"code" => "3",
			],
		];

		if (isset($_SESSION['extra_hospitals'])) {
			$cards = array_merge($cards, $_SESSION['extra_hospitals']);
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
			box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
			opacity: 1;
			transform: translateX(0);
			text-decoration: none;
			height: 370px;
			display: flex;
			flex-direction: column;
			justify-content: flex-start;
			align-items: center;
		}
		.card:hover {
			transform: scale(1.05);
			box-shadow: 0 12px 30px rgba(0, 0, 0, 0.25);
			transition: transform 0.3s ease, box-shadow 0.3s ease;
		}

		.card i {
			display: none;
		}

		.card-img-top {
			width: 100%;
			height: 220px;
			object-fit: cover;
			border-radius: 16px 16px 0 0;
			margin-bottom: 15px;
			box-shadow: inset 0 0 20px rgba(0,0,0,0.15);
			transition: transform 0.3s ease;
		}

		.card:hover .card-img-top {
			transform: scale(1.05);
		}

		.card-title {
			font-size: 22px;
			font-weight: 700;
			margin-top: auto;
			color: #fff;
		}

		.bg-gradient-1 { background: linear-gradient(90deg, #667eea, #764ba2); }
		.bg-gradient-2 { background: linear-gradient(90deg, #f7971e, #ffd200); }
		.bg-gradient-3 { background: linear-gradient(90deg, #ff758c, #ff7eb3); }
		.bg-gradient-4 { background: linear-gradient(90deg, #00c6ff, #0072ff); }
		.bg-gradient-7 { background: linear-gradient(90deg, #ffafbd, #ffc3a0); }
		.bg-gradient-8 { background: linear-gradient(90deg,rgb(35, 114, 89), #185a9d); }
		.bg-gradient-green { background: linear-gradient(90deg,rgb(114, 168, 88), #a8e063); }

		.row.g-4 {
			justify-content: center;
		}
		@media (min-width: 768px) {
			.col-md-4 {
				flex: 0 0 33.3333%;
				max-width: 33.3333%;
			}
		}
		@media (max-width: 767px) {
			.col-md-4 {
				max-width: 100%;
				flex: 0 0 100%;
			}
		}

		.position-relative {
			position: relative;
		}
		.position-absolute {
			position: absolute;
		}
		.top-0 {
			top: 0;
		}
		.end-0 {
			right: 0;
		}
		.m-2 {
			margin: 0.5rem !important;
		}
		.btn-sm.btn-danger {
			border-radius: 50%;
			width: 30px;
			height: 30px;
			padding: 0;
			line-height: 26px;
			font-weight: bold;
		}
		small {
			color: rgba(255, 255, 255, 0.8);
			margin-top: 5px;
		}
	</style>
</head>
<body>
	<input type="checkbox" id="checkbox" />
	<?php include "inc/header.php" ?>
	<div class="body">
		<?php include "inc/nav2.php" ?>
		<section class="section-1 p-4">
			<div class="container-fluid">
				<!-- Add Hospital Button -->
				<div class="mb-4 text-center">
					<button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addHospitalModal">
						<i class="fa fa-plus"></i> Add New Hospital
					</button>
				</div>

				<!-- Add Hospital Modal -->
				<div class="modal fade" id="addHospitalModal" tabindex="-1" aria-labelledby="addHospitalModalLabel" aria-hidden="true">
					<div class="modal-dialog modal-dialog-centered">
						<div class="modal-content">
							<form method="post" enctype="multipart/form-data">
								<div class="modal-header">
									<h5 class="modal-title" id="addHospitalModalLabel">Add New Hospital</h5>
									<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
								</div>
								<div class="modal-body">
									<div class="mb-3">
										<label for="hospital_name" class="form-label">Hospital Name</label>
										<input type="text" class="form-control" id="hospital_name" name="hospital_name" required />
									</div>
									<div class="mb-3">
										<label for="hospital_link" class="form-label">Hospital Link</label>
										<input type="text" class="form-control" id="hospital_link" name="hospital_link" />
									</div>
									<div class="mb-3">
										<label for="hospital_code" class="form-label">Hospital Code</label>
										<input type="text" class="form-control" id="hospital_code" name="hospital_code" required />
									</div>
									<div class="mb-3">
										<label for="hospital_image" class="form-label">Hospital Image</label>
										<input type="file" class="form-control" id="hospital_image" name="hospital_image" accept="image/*" />
									</div>
								</div>
								<div class="modal-footer">
									<button type="submit" name="add_hospital" class="btn btn-primary">Add Hospital</button>
									<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
								</div>
							</form>
						</div>
					</div>
				</div>

				<div class="row g-4">
					<?php foreach ($cards as $index => $hospital): ?>
						<div class="col-md-4 col-sm-6 col-12">
							<div class="card position-relative <?= htmlspecialchars($hospital['bg']) ?>">
								<!-- Delete button only for extra hospitals -->
								<?php if ($index >= $original_hospitals_count): ?>
									<form method="post" class="position-absolute top-0 end-0 m-2" onsubmit="return confirm('هل أنت متأكد من حذف هذا المستشفى؟');">
										<input type="hidden" name="delete_hospital" value="<?= $index ?>" />
										<button type="submit" class="btn btn-sm btn-danger" title="Delete Hospital">&times;</button>
									</form>
								<?php endif; ?>

								<a href="<?= htmlspecialchars($hospital['link']) ?>?hospital_code=<?= urlencode($hospital['code']) ?>">

									<?php if (!empty($hospital['image'])): ?>
										<img src="<?= htmlspecialchars($hospital['image']) ?>" alt="<?= htmlspecialchars($hospital['label']) ?>" class="card-img-top" />
									<?php else: ?>
										<img src="img/default_hospital.png" alt="Default Hospital" class="card-img-top" />
									<?php endif; ?>
									<h5 class="card-title"><?= htmlspecialchars($hospital['label']) ?></h5>
									<?php if (!empty($hospital['code'])): ?>
										<small>Code: <?= htmlspecialchars($hospital['code']) ?></small>
									<?php endif; ?>
								</a>
							</div>
						</div>
					<?php endforeach; ?>
				</div>

			</div>
		</section>
	</div>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
} else {
	header("Location: login.php");
	exit();
}
?>
