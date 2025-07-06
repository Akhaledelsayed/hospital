<?php 
session_start();

if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "admin") {
    include "DB_connection.php";

    if (!isset($_GET['id'])) {
        header("Location: purchasing-order.php");
        exit();
    }

    $id = $_GET['id'];

    // Check if the purchasing_order exists
    $stmt = $conn->prepare("SELECT * FROM purchasing_order WHERE id = ?");
    $stmt->execute([$id]);

    if ($stmt->rowCount() == 0) {
        $em = "purchasing_order not found.";
        header("Location: purchasing-order.php?error=$em");
        exit();
    }

    // Try to delete the purchasing_order
    try {
        $delete_stmt = $conn->prepare("DELETE FROM purchasing_order WHERE id = ?");
        $deleted = $delete_stmt->execute([$id]);

        if ($deleted) {
            $sm = "purchasing_order deleted successfully.";
            header("Location: purchasing-order.php?success=$sm");
            exit();
        } else {
            $em = "Failed to delete the purchasing_order.";
            header("Location: purchasing-order.php?error=$em");
            exit();
        }
    } catch (PDOException $e) {
        $em = "Cannot delete purchasing_order due to linked data.";
        header("Location: purchasing-order.php?error=$em");
        exit();
    }

} else {
    $em = "Please login first.";
    header("Location: login.php?error=$em");
    exit();
}
