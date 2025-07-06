<?php 
session_start();

if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "admin") {
    include "DB_connection.php";

    if (!isset($_GET['id'])) {
        header("Location: invoices.php");
        exit();
    }

    $id = $_GET['id'];

    // Check if the invoices exists
    $stmt = $conn->prepare("SELECT * FROM invoices WHERE id = ?");
    $stmt->execute([$id]);

    if ($stmt->rowCount() == 0) {
        $em = "invoices not found.";
        header("Location: invoices.php?error=$em");
        exit();
    }

    // Try to delete the invoices
    try {
        $delete_stmt = $conn->prepare("DELETE FROM invoices WHERE id = ?");
        $deleted = $delete_stmt->execute([$id]);

        if ($deleted) {
            $sm = "invoices deleted successfully.";
            header("Location: invoices.php?success=$sm");
            exit();
        } else {
            $em = "Failed to delete the invoices.";
            header("Location: invoices.php?error=$em");
            exit();
        }
    } catch (PDOException $e) {
        $em = "Cannot delete invoices due to linked data.";
        header("Location: invoices.php?error=$em");
        exit();
    }

} else {
    $em = "Please login first.";
    header("Location: login.php?error=$em");
    exit();
}
