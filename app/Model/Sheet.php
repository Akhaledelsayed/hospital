<?php
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "admin") {
    include "DB_connection.php";

    // Fetch all timesheet entries
    $stmt = $conn->prepare("SELECT s.*, u.full_name FROM sheet_data_3 s
                            LEFT JOIN users u ON s.user_id = u.id
                            ORDER BY s.date DESC");
    $stmt->execute();
    $sheet_entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Sheet Data</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include "inc/header.php"; ?>
    <div class="body">
        <?php include "inc/nav.php"; ?>
        <section class="section-1">
            <h3 class="title-2">🗂️ Sheet Data</h3>
            <?php if (count($sheet_entries) > 0) { ?>
            <table class="main-table">
                <tr>
                    <th>#</th>
                    <th>User</th>
                    <th>Task Description</th>
                    <th>Date</th>
                    <th>Time</th>
                </tr>
                <?php $i=0; foreach ($sheet_entries as $entry) { ?>
                <tr>
                    <td><?= ++$i ?></td>
                    <td><?= $entry['full_name'] ?></td>
                    <td><?= htmlspecialchars($entry['task']) ?></td>
                    <td><?= $entry['date'] ?></td>
                    <td><?= $entry['time'] ?></td>
                </tr>
                <?php } ?>
            </table>
            <?php } else { ?>
                <p>No sheet data found.</p>
            <?php } ?>
        </section>
    </div>
</body>
</html>
<?php } else {
    header("Location: login.php");
    exit();
}
?>
