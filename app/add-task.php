<?php 
session_start();

if (isset($_SESSION['role']) && isset($_SESSION['id'])) {

    if (
        isset($_POST['title']) && 
        isset($_POST['description']) && 
        isset($_POST['assigned_to']) && 
        isset($_POST['deadline']) && 
        isset($_POST['hours']) &&
        $_SESSION['role'] == 'admin'
    ) {
        include "../DB_connection.php";

        function validate_input($data) {
            $data = trim($data);
            $data = stripslashes($data);
            $data = htmlspecialchars($data);
            return $data;
        }

        $title = validate_input($_POST['title']); // project_id
        $description = validate_input($_POST['description']);
        $assigned_to = validate_input($_POST['assigned_to']);
        $deadline = validate_input($_POST['deadline']);
        $hours = validate_input($_POST['hours']);

        if (empty($title)) {
            $em = "Title is required";
            header("Location: ../create_task.php?error=$em");
            exit();
        } elseif (empty($description)) {
            $em = "Description is required";
            header("Location: ../create_task.php?error=$em");
            exit();
        } elseif ($assigned_to == 0) {
            $em = "Select User";
            header("Location: ../create_task.php?error=$em");
            exit();
        } elseif (!is_numeric($hours) || $hours <= 0) {
            $em = "Estimated hours must be a positive number";
            header("Location: ../create_task.php?error=$em");
            exit();
        } else {
            // Get total allowed hours and used hours for the project
            $stmt = $conn->prepare("
                SELECT 
                    COALESCE(p.total_hours, 0) AS total_hours,
                    COALESCE(SUM(t.hours), 0) AS used_hours
                FROM projects p
                LEFT JOIN tasks t ON p.project_name = t.title
                WHERE p.project_name = ?
            ");
            $stmt->execute([$title]);
            $project = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($project) {
                $remaining = $project['total_hours'] - $project['used_hours'];
                
                // Check if the new task hours exceed the remaining hours
                if ($hours > $remaining) {
                    $em = "Cannot assign $hours hours. Only $remaining hours remaining for this project.";
                    header("Location: ../create_task.php?error=$em");
                    exit();
                }

                // If the new task doesn't exceed the limit, add it
                include "Model/Task.php";
                include "Model/Notification.php";

                $data = array($title, $description, $assigned_to, $deadline, $hours);
                insert_task($conn, $data);

                $notif_data = array("'$title' has been assigned to you. Please review and start working on it", $assigned_to, 'New Task Assigned');
                insert_notification($conn, $notif_data);

                $sm = "Task created successfully";
                header("Location: ../create_task.php?success=$sm");
                exit();
            } else {
                $em = "Project not found";
                header("Location: ../create_task.php?error=$em");
                exit();
            }
        }

    } else {
        $em = "Unknown error occurred";
        header("Location: ../create_task.php?error=$em");
        exit();
    }

} else { 
    $em = "First login";
    header("Location: ../create_task.php?error=$em");
    exit();
}
?>
