<?php 
session_start();

if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "admin") {
    include "DB_connection.php";

    if (!isset($_GET['id'])) {
        header("Location: workorder.php");
        exit();
    }

    $id = $_GET['id'];

    // Check if the workorder exists
    $stmt = $conn->prepare("SELECT * FROM workorders WHERE id = ?");
    $stmt->execute([$id]);

    if ($stmt->rowCount() == 0) {
        $em = "workorder not found.";
        header("Location: workorder.php?error=$em");
        exit();
    }

    // Try to delete the workorder
    try {
        $delete_stmt = $conn->prepare("DELETE FROM workorders WHERE id = ?");
        $deleted = $delete_stmt->execute([$id]);

        if ($deleted) {
            $sm = "workorder deleted successfully.";
            header("Location: workorder.php?success=$sm");
            exit();
        } else {
            $em = "Failed to delete the workorder.";
            header("Location: workorder.php?error=$em");
            exit();
        }
    } catch (PDOException $e) {
        $em = "Cannot delete workorder due to linked data.";
        header("Location: workorder.php?error=$em");
        exit();
    }

} else {
    $em = "Please login first.";
    header("Location: login.php?error=$em");
    exit();
}
