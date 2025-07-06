<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "admin") {
    include "DB_connection.php";
    include "app/Model/Task.php";
    include "app/Model/User.php";

    if (!isset($_GET['id'])) {
        header("Location: tasks.php");
        exit();
    }

    $id = $_GET['id'];
    $task = get_task_by_id($conn, $id);

    if ($task == 0) {
        header("Location: tasks.php");
        exit();
    }

    $users = get_all_users($conn);

    // Get project dates
    $stmt = $conn->prepare("SELECT start_date, end_date FROM projects WHERE project_name = ?");
    $stmt->execute([$task['title']]);
    $project_dates = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Task</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <input type="checkbox" id="checkbox">
    <?php include "inc/header.php" ?>
    <div class="body">
        <?php include "inc/nav.php" ?>
        <section class="section-1">
            <h4 class="title">Edit Task <a href="tasks.php">Tasks</a></h4>
            <form class="form-1" method="POST" action="app/update-task.php">
                <?php if (isset($_GET['error'])) { ?>
                    <div class="danger" role="alert">
                        <?= stripcslashes($_GET['error']); ?>
                    </div>
                <?php } ?>

                <?php if (isset($_GET['success'])) { ?>
                    <div class="success" role="alert">
                        <?= stripcslashes($_GET['success']); ?>
                    </div>
                <?php } ?>

                <div class="input-holder">
                    <label>Title</label>
                    <input type="text" name="title" class="input-1" placeholder="Task Title" value="<?= htmlspecialchars($task['title']) ?>"><br>
                </div>

                <div class="input-holder">
                    <label>Description</label>
                    <textarea name="description" rows="5" class="input-1"><?= htmlspecialchars($task['description']) ?></textarea><br>
                </div>

                <div class="input-holder">
                    <label>Deadline</label>
                    <input type="date" name="deadline" class="input-1" id="deadline" value="<?= htmlspecialchars($task['deadline']) ?>"><br>
                </div>

                <div class="input-holder">
                    <label>Estimated Hours</label>
                    <input type="number" step="0.1" min="0" name="hours" class="input-1" placeholder="Hours" value="<?= htmlspecialchars($task['hours']) ?>"><br>
                </div>

                <div class="input-holder">
                    <label>Assigned to</label>
                    <select name="assigned_to" class="input-1">
                        <option value="0">Select employee</option>
                        <?php if ($users != 0) { 
                            foreach ($users as $user) {
                                $selected = ($task['assigned_to'] == $user['id']) ? 'selected' : '';
                        ?>
                            <option value="<?= $user['id'] ?>" <?= $selected ?>><?= htmlspecialchars($user['full_name']) ?></option>
                        <?php } } ?>
                    </select><br>
                </div>

                <input type="hidden" name="id" value="<?= $task['id'] ?>">

                <button class="edit-btn">Update</button>
            </form>
        </section>
    </div>

    <script>
        const projectStartDate = "<?= $project_dates['start_date'] ?? '' ?>";
        const projectEndDate = "<?= $project_dates['end_date'] ?? '' ?>";

        document.addEventListener("DOMContentLoaded", function () {
            const deadlineInput = document.getElementById("deadline");
            if (deadlineInput && projectStartDate && projectEndDate) {
                deadlineInput.min = projectStartDate;
                deadlineInput.max = projectEndDate;
            }
        });

        var active = document.querySelector("#navList li:nth-child(4)");
        active.classList.add("active");
    </script>
</body>
</html>
<?php } else { 
   $em = "First login";
   header("Location: login.php?error=$em");
   exit();
}
?>
