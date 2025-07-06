<?php
session_start();
include 'DB_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $old_id = $_POST['old_id'];
    $new_id = $_POST['new_id'];
    $name = $_POST['project_name'];
    $description = $_POST['description'];
    $start = $_POST['start_date'];
    $end = $_POST['end_date'];
    $stakeholder = $_POST['stakeholder'];
    $total_hours = isset($_POST['total_hours']) ? intval($_POST['total_hours']) : 0;
    $engineers = isset($_POST['engineer']) ? $_POST['engineer'] : [];

    // Validate max 2 engineers
    if (count($engineers) > 2) {
        echo "❌ Error: You can select a maximum of 2 engineers.";
        exit();
    }

    $engineer_ids = implode(',', $engineers);

    try {
        if ($old_id != $new_id) {
            // Check for duplicate ID
            $checkStmt = $conn->prepare("SELECT id FROM projects WHERE id = ?");
            $checkStmt->execute([$new_id]);

            if ($checkStmt->fetch()) {
                echo "❌ Error: The new Project ID <strong>" . htmlspecialchars($new_id) . "</strong> already exists.";
                exit();
            }

            // Update including ID change
            $stmt = $conn->prepare("UPDATE projects 
                SET id = ?, project_name = ?, description = ?, start_date = ?, end_date = ?, stakeholder_name = ?, engineer_ids = ?, total_hours = ?
                WHERE id = ?");
            $stmt->execute([$new_id, $name, $description, $start, $end, $stakeholder, $engineer_ids, $total_hours, $old_id]);

        } else {
            // Update without changing ID
            $stmt = $conn->prepare("UPDATE projects 
                SET project_name = ?, description = ?, start_date = ?, end_date = ?, stakeholder_name = ?, engineer_ids = ?, total_hours = ?
                WHERE id = ?");
            $stmt->execute([$name, $description, $start, $end, $stakeholder, $engineer_ids, $total_hours, $old_id]);
        }

        $_SESSION['success'] = "✅ Project updated successfully.";
        header("Location: projects.php");
        exit();

    } catch (PDOException $e) {
        echo "❌ Database Error: " . htmlspecialchars($e->getMessage());
        exit();
    }

} else {
    echo "Invalid request.";
    exit();
}
?>
