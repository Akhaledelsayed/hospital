<?php 
session_start();
include "DB_connection.php";

// تأكد إن المستخدم مسجل دخول
if (!isset($_SESSION['id']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

// طلب AJAX: جلب التاسكات المرتبطة بمشروع
if (isset($_GET['project_name'])) {
    $stmt = $conn->prepare("
        SELECT description , hours 
        FROM tasks 
        WHERE title = ? 
          AND assigned_to = ? 
          AND deadline >= CURDATE()
    ");
    $stmt->execute([$_GET['project_name'], $_SESSION['id']]);
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($tasks);
    exit();
}

// طلب AJAX: تحميل بيانات اليوم
if (isset($_GET['load_today'])) {
    $today = date('Y-m-d');
    $stmt = $conn->prepare("SELECT * FROM sheet_data_3 WHERE user_name = ? AND date = ?");
    $stmt->execute([$_SESSION['id'], $today]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($data);
    exit();
}

// المشاريع الفعالة
$stmt = $conn->prepare("SELECT DISTINCT project_name FROM projects WHERE end_date >= ?");
$stmt->execute([date('Y-m-d')]);
$projects = $stmt->fetchAll(PDO::FETCH_COLUMN);

$days = ["Saturday", "Sunday", "Monday", "Tuesday", "Wednesday"];
$time_slots = ["ten_am", "eleven_am", "twelve_pm", "one_pm", "two_pm", "three_pm", "four_pm", "five_pm", "six_pm"];
$display_slots = ["10:00 AM", "11:00 AM", "12:00 PM", "1:00 PM", "2:00 PM", "3:00 PM", "4:00 PM", "5:00 PM", "6:00 PM"];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Timesheet</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family:  Arial, sans-serif;
        }
        .timesheet-container {
            padding: 50px;
            overflow: auto;
        }
        .timesheet-section {
            background: #fff;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            overflow-x: auto;
        }
        .table-wrapper {
            overflow-x: auto;
            position: relative;
        }
        table {
            width: 50%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table thead th {
            position: sticky;
            top: 0;
            background-color: #343a40;
            color: white;
            text-align: center;
            font-size: 18px;
            z-index: 2;
        }
        table, th, td {
            border: 1px solid #dee2e6;
        }
        td {
            background-color: #fff;
            text-align: center;
            vertical-align: middle;
        }
        select {
            width: 100%;
            padding: 8px;
            font-size: 15px;
            box-sizing: border-box;
        }
        input[name="date[]"] {
            width: 90px;
            font-size: 13px;
            text-align: center;
        }
        .task-dropdown {
            min-width: 150px;
            font-size: 15px;
        }
        button[type="submit"] {
            width: 100%;
        }
        h2 {
            color: #343a40;
            margin-bottom: 25px;
            font-size: 36px;
        }
    </style>
</head>
<body>
<input type="checkbox" id="checkbox">
<?php include "inc/header.php"; ?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-2 p-0">
            <?php include "inc/nav.php"; ?>
        </div>

        <!-- Main content -->
        <div class="col-md-10 timesheet-container">
            <div class="timesheet-section">
                <h2>🕒 Timesheet</h2>
                <form action="save_timesheet.php" method="POST">
                    <div class="table-wrapper">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Day</th>
                                    <th>Date</th>
                                    <th>Project</th>
                                    <?php foreach ($display_slots as $slot): ?>
                                        <th><?= $slot ?></th>
                                    <?php endforeach; ?>
                                    <th>Notes</th>
                                    <th>Save</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($days as $index => $day): ?>
                                    <tr>
                                        <td><?= $day ?></td>
                                        <td><input type="text" name="date[]" value="<?= ($day == date('l')) ? date('Y-m-d') : '' ?>" readonly></td>
                                        <td>
                                            <select name="project_name[]" class="project-select">
                                                <option value="">-- Select --</option>
                                                <?php foreach ($projects as $p): ?>
                                                    <option value="<?= htmlspecialchars($p) ?>"><?= htmlspecialchars($p) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <?php foreach ($time_slots as $slot): ?>
                                            <td>
                                                <select name="<?= $slot ?>[]" class="task-dropdown">
                                                    <option value="">-- Task --</option>
                                                </select>
                                            </td>
                                        <?php endforeach; ?>
                                        <td><input type="text" name="notes[]"></td>
                                        <td><button type="submit" name="save_row" value="<?= $index ?>" class="btn btn-primary btn-sm">Save</button></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// تحميل التاسكات عند اختيار المشروع
let taskLimits = {}; // لتخزين الحد الأقصى لكل مهمة

document.querySelectorAll('.project-select').forEach(function(select) {
    select.addEventListener('change', function() {
        var projectName = this.value;
        var row = this.closest('tr');
        if (projectName) {
            fetch('<?= $_SERVER['PHP_SELF'] ?>?project_name=' + encodeURIComponent(projectName))
                .then(response => response.json())
                .then(tasks => {
                    taskLimits = {};
                    row.querySelectorAll('.task-dropdown').forEach(function(taskDropdown) {
                        if (!taskDropdown.dataset.selected) {
                            taskDropdown.innerHTML = '<option value="">-- Select Task --</option>';
                            tasks.forEach(function(taskItem) {
                                if (typeof taskItem === 'object') {
                                    var option = document.createElement('option');
                                    option.value = taskItem.description;
                                    option.textContent = taskItem.description;
                                    taskDropdown.appendChild(option);
                                    taskLimits[taskItem.description] = parseInt(taskItem.hours);
                                }
                            });
                        }
                    });
                });
        }
    });
});

// منع تجاوز عدد مرات اختيار التاسك
document.querySelectorAll('.task-dropdown').forEach(function(dropdown) {
    dropdown.addEventListener('change', function() {
        const selectedTask = this.value;
        if (selectedTask) {
            let count = 0;
            document.querySelectorAll('.task-dropdown').forEach(function(d) {
                if (d.value === selectedTask) {
                    count++;
                }
            });
            if (count > taskLimits[selectedTask]) {
                alert("❗ لقد تجاوزت الحد المسموح لهذه التاسك: " + selectedTask + " (الحد: " + taskLimits[selectedTask] + " ساعة)");
                this.value = "";
            }
        }
    });
});

// إغلاق خلايا التاسكات لو فيها بيانات محفوظة لليوم الحالي
document.addEventListener('DOMContentLoaded', function() {
    fetch('<?= $_SERVER['PHP_SELF'] ?>?load_today=1')
        .then(response => response.json())
        .then(data => {
            if (data.length > 0) {
                document.querySelectorAll('tbody tr').forEach((row, rowIndex) => {
                    const dateInput = row.querySelector('input[name="date[]"]');
                    if (dateInput && dateInput.value === '<?= date('Y-m-d') ?>') {
                        const recordsForThisDate = data.filter(record => record.date === dateInput.value);
                        recordsForThisDate.forEach(record => {
                            <?php foreach ($time_slots as $slot): ?>
                                if (record["<?= $slot ?>"]) {
                                    const dropdown = row.querySelector('select[name="<?= $slot ?>[]"]');
                                    if (dropdown && !dropdown.disabled && !dropdown.value) {
                                        dropdown.value = record["<?= $slot ?>"];
                                        dropdown.disabled = true;
                                        dropdown.dataset.selected = "true";
                                    }
                                }
                            <?php endforeach; ?>
                        });
                    }
                });
            }
        });
});
</script>

</body>
</html>
