<?php 
session_start();

if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "admin") {
    include "DB_connection.php";

    if (!isset($_GET['manufacturer_name'])) {
        header("Location: manufacturer.php");
        exit();
    }

    $manufacturer_name = $_GET['manufacturer_name'];

    // Check if the manufacturer exists
    $stmt = $conn->prepare("SELECT * FROM manufacturer WHERE manufacturer_name = ?");
    $stmt->execute([$manufacturer_name]);

    if ($stmt->rowCount() == 0) {
        $em = "manufacturer not found.";
        header("Location: manufacturer.php?error=$em");
        exit();
    }

    // Try to delete the manufacturer
    try {
        $delete_stmt = $conn->prepare("DELETE FROM manufacturer WHERE manufacturer_name = ?");
        $deleted = $delete_stmt->execute([$manufacturer_name]);

        if ($deleted) {
            $sm = "manufacturer deleted successfully.";
            header("Location: manufacturer.php?success=$sm");
            exit();
        } else {
            $em = "Failed to delete the manufacturer.";
            header("Location: manufacturer.php?error=$em");
            exit();
        }
    } catch (PDOException $e) {
        $em = "Cannot delete manufacturer due to linked data.";
        header("Location: manufacturer.php?error=$em");
        exit();
    }

} else {
    $em = "Please login first.";
    header("Location: login.php?error=$em");
    exit();
}
