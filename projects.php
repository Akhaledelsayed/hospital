<?php
session_start();
include 'DB_connection.php';
include 'app/Model/User.php';

// Get all users for dropdowns
$users = get_all_users($conn);

// Add project
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['project_name'], $_POST['project_id'])) {
    $id = $_POST['project_id'];
    $name = $_POST['project_name'];
    $start = $_POST['start_date'];
    $end = $_POST['end_date'];
    $desc = $_POST['description'];
    $stakeholder = $_POST['stakeholder'];
    $engineers = $_POST['engineer'];
    $hours = $_POST['total_hours'];

    // Combine selected engineers into a comma-separated string
    $engineer_ids = implode(',', $engineers);

    $stmt = $conn->prepare("INSERT INTO projects (id, project_name, start_date, end_date, description, stakeholder_name, engineer_ids, total_hours) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$id, $name, $start, $end, $desc, $stakeholder, $engineer_ids, $hours]);

    header("Location: projects.php");
    exit();
}

// Fetch all projects
$result = $conn->query("SELECT * FROM projects");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Projects</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <style>
        body { background-color: #f8f9fa; font-family: Arial, sans-serif; }
        .form-section, .table-section {
            background: #fff; padding: 25px; border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1); margin-bottom: 30px;
        }
        h2, h3 { color: #343a40; }
        .btn-warning, .btn-danger { width: 100px; }
        .nav-item.active .nav-link {
            background-color: #007bff; border-radius: 5px; color: white;
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-2 p-0">
            <?php include 'inc/nav.php'; ?>
        </div>

        <!-- Main content -->
        <div class="col-md-10 p-4">
            <!-- Projects Table -->
            <div class="table-section mb-4">
                <h2 class="mb-4">📁 All Projects</h2>
                <table class="table table-bordered text-center align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Stakeholder</th>
                            <th>Engineers</th>
                            <th>Total Hours</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch(PDO::FETCH_ASSOC)): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['project_name']) ?></td>
                            <td><?= htmlspecialchars($row['description']) ?></td>
                            <td><?= $row['start_date'] ?></td>
                            <td><?= $row['end_date'] ?></td>
                            <td><?= htmlspecialchars($row['stakeholder_name']) ?></td>
                            <td>
                                <?php
                                    $engineer_names = [];
                                    $ids = explode(',', $row['engineer_ids']);
                                    foreach ($ids as $eid) {
                                        $e = $conn->query("SELECT full_name FROM users WHERE id=" . intval($eid))->fetchColumn();
                                        if ($e) $engineer_names[] = $e;
                                    }
                                    echo htmlspecialchars(implode(', ', $engineer_names));
                                ?>
                            </td>
                            <td><?= htmlspecialchars($row['total_hours']) ?></td>
                            <td>
                                <a href="edit-project.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                <a href="delete-project.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Add Project Form -->
            <div class="form-section">
                <h3 class="mb-3">➕ Add New Project</h3>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Project ID</label>
                        <input type="number" name="project_id" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Project Name</label>
                        <input type="text" name="project_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" placeholder="Brief description..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Stakeholder Name</label>
                        <input type="text" name="stakeholder" class="form-control" placeholder="Stakeholder full name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Project Engineers (select up to 2)</label>
                        <select name="engineer[]" class="form-control" multiple required>
                            <?php foreach ($users as $user): ?>
                                <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['full_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Hold Ctrl (Windows) or Cmd (Mac) to select multiple.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Total Hours</label>
                        <input type="number" name="total_hours" class="form-control" placeholder="Estimated total hours" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Project</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
