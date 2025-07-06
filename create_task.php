<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "admin") {
    include "DB_connection.php";
    include "app/Model/User.php";

    // Get all users
    $users = get_all_users($conn);

    // Get all projects with total hours and task hours
    $projects_stmt = $conn->query("
        SELECT p.id, p.project_name, p.start_date, p.end_date,
               COALESCE(p.total_hours, 0) AS total_hours,
               COALESCE(SUM(t.hours), 0) AS used_hours
        FROM projects p
        LEFT JOIN tasks t ON p.id = t.project_id
        GROUP BY p.id
    ");
    $projects = $projects_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Validate deadline and hours
    if (isset($_POST['title'])) {
        $project_id = $_POST['title'];
        $stmt = $conn->prepare("SELECT start_date, end_date, total_hours FROM projects WHERE id = ?");
        $stmt->execute([$project_id]);
        $project = $stmt->fetch(PDO::FETCH_ASSOC);
        $start_date = $project['start_date'];
        $end_date = $project['end_date'];
        $total_hours = $project['total_hours'];

        $stmt2 = $conn->prepare("SELECT COALESCE(SUM(hours), 0) AS used_hours FROM tasks WHERE project_id = ?");
        $stmt2->execute([$project_id]);
        $result = $stmt2->fetch(PDO::FETCH_ASSOC);
        $used_hours = $result['used_hours'];

        if (isset($_POST['deadline'])) {
            $deadline = $_POST['deadline'];
            if ($deadline < $start_date || $deadline > $end_date) {
                $em = "Deadline must be between the project start and end dates.";
                header("Location: create-task.php?error=$em");
                exit();
            }
        }

        if (isset($_POST['hours'])) {
            $new_hours = floatval($_POST['hours']);
            $remaining_hours = $total_hours - $used_hours;
            if ($new_hours > $remaining_hours) {
                $em = "Task hours exceed remaining project hours ($remaining_hours hrs remaining).";
                header("Location: create-task.php?error=$em");
                exit();
            }
        }
    }
?>
<!DOCTYPE html>
<html>
<head>
    <title>Create Task</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <input type="checkbox" id="checkbox">
    <?php include "inc/header.php" ?>
    <div class="body">
        <?php include "inc/nav.php" ?>
        <section class="section-1">
            <h4 class="title">Create Task</h4>
            <form class="form-1" method="POST" action="app/add-task.php">
                <?php if (isset($_GET['error'])) { ?>
                    <div class="danger" role="alert">
                        <?php echo stripcslashes($_GET['error']); ?>
                    </div>
                <?php } ?>

                <?php if (isset($_GET['success'])) { ?>
                    <div class="success" role="alert">
                        <?php echo stripcslashes($_GET['success']); ?>
                    </div>
                <?php } ?>

                <div class="input-holder">
                    <label>Project Name</label>
                    <select name="title" class="input-1" id="project-select" required>
                        <option value="">-- Select Project --</option>
                        <?php foreach ($projects as $project): 
                            $remaining = $project['total_hours'] - $project['used_hours'];
                        ?>
                            <option value="<?= $project['project_name'] ?>"
                                data-start="<?= $project['start_date'] ?>"
                                data-end="<?= $project['end_date'] ?>"
                                data-remaining="<?= $remaining ?>">
                                <?= htmlspecialchars($project['project_name']) ?> (Remaining: <?= $remaining ?> hrs)
                            </option>
                        <?php endforeach; ?>
                    </select><br>
                </div>

                <div class="input-holder">
                    <label>Description</label>
                    <select id="desc-dropdown" class="input-1" style="margin-bottom: 10px;">
                        <option value="">-- Choose Description --</option>
                        <option value="Design Developing Report (DDR(001))">Design Developing Report (DDR(001)) </option>
                        <option value="Design Developing Modification (DDM(002))">Design Developing Modification (DDM(002)) </option>
                        <option value="Room Data Sheet (RDS(003))">Room Data Sheet (RDS(003))</option>
                        <option value="Room By Room (RBR(004))">Room By Room (RBR(004))</option>
                        <option value="Floor LayOut (FLO(005))">Floor LayOut (FLO(005))</option>
                        <option value="Room LayOut (RLO(006))">Room LayOut (RLO(006))</option>
                        <option value="Bill Of Quantity (BOQ(007))">Bill Of Quantity (BOQ(007))</option> 
                        <option value="Consolidated List Sheet (CLS(008))">Consolidated List Sheet (CLS(008))</option> 
                        <option value="Cost Estimation Sheet (CES(009))">Cost Estimation Sheet (CES(009))</option>
                        <option value="Recommended Models Sheet (RMS(010))">Recommended Models Sheet (RMS(010))</option>
                        <option value="Technical Specs Sheet(TSS(011))">Technical Specs Sheet (TSS(011)) </option>
                        <option value="ARCH working (ARC(012))">ARCH working (ARC(012))</option>
                        <option value="Interior Design Sheet (IDS(013))">Interior Design Sheet (IDS(013))</option>
                        <option value="Concept Design (CD(014))">Concept Design (CD(014))</option>
                        <option value="Medical Gases (MG(015))">Medical Gases (MG(015))</option>
                    </select>
                    <input type="text" name="description" id="desc-input" class="input-1" placeholder="Or write your own"><br>
                </div>

                <div class="input-holder">
                    <label>Deadline</label>
                    <input type="date" name="deadline" id="deadline" class="input-1" placeholder="Deadline"><br>
                </div>

                <div class="input-holder">
                    <label>Estimated Hours</label>
                    <input type="number" name="hours" class="input-1" id="hours" step="0.25" min="0" placeholder="e.g., 2.5" required>
                    <small class="text-muted" id="remaining-text"></small>
                </div>

                <div class="input-holder">
                    <label>Assigned to</label>
                    <select name="assigned_to" class="input-1">
                        <option value="0">Select employee</option>
                        <?php if ($users != 0) { 
                            foreach ($users as $user) {
                        ?>
                            <option value="<?= $user['id'] ?>"><?= $user['full_name'] ?></option>
                        <?php } } ?>
                    </select><br>
                </div>

                <button class="edit-btn">Create Task</button>
            </form>
        </section>
    </div>

    <script type="text/javascript">
    const projectSelect = document.getElementById('project-select');
    const deadlineInput = document.getElementById('deadline');
    const hoursInput = document.getElementById('hours');
    const remainingText = document.getElementById('remaining-text');

    function updateLimits() {
        const selected = projectSelect.options[projectSelect.selectedIndex];
        const start = selected.getAttribute('data-start');
        const end = selected.getAttribute('data-end');
        const remaining = selected.getAttribute('data-remaining');

        if (start && end) {
            deadlineInput.min = start;
            deadlineInput.max = end;
        }

        if (remaining !== null) {
            const remainingFloat = parseFloat(remaining);
            hoursInput.max = remainingFloat;
            remainingText.textContent = `Max allowed: ${remainingFloat} hours`;

            // Clear current input if it exceeds limit
            if (parseFloat(hoursInput.value) > remainingFloat) {
                hoursInput.value = '';
                alert("The entered hours exceed the remaining hours for this project.");
            }
        } else {
            remainingText.textContent = '';
            hoursInput.removeAttribute('max');
        }
    }

    projectSelect.addEventListener('change', updateLimits);
    hoursInput.addEventListener('input', function() {
        const max = parseFloat(hoursInput.max);
        if (parseFloat(hoursInput.value) > max) {
            alert("The entered hours exceed the remaining hours for this project.");
            hoursInput.value = '';
        }
    });
    window.addEventListener('DOMContentLoaded', updateLimits);

    // Set description input from dropdown
    document.getElementById('desc-dropdown').addEventListener('change', function() {
        document.getElementById('desc-input').value = this.value;
    });

    var active = document.querySelector("#navList li:nth-child(3)");
    active.classList.add("active");
</script>

</body>
</html>
<?php 
} else { 
    $em = "First login";
    header("Location: login.php?error=$em");
    exit();
} 
?>
