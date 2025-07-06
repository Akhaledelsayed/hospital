<?php 
session_start();

if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "admin") {
    include "DB_connection.php";

    if (!isset($_GET['company_name'])) {
        header("Location: company.php");
        exit();
    }

    $company_name = $_GET['company_name'];

    // Check if the company exists
    $stmt = $conn->prepare("SELECT * FROM company WHERE company_name = ?");
    $stmt->execute([$company_name]);

    if ($stmt->rowCount() == 0) {
        $em = "Company not found.";
        header("Location: company.php?error=$em");
        exit();
    }

    // Try to delete the company
    try {
        $delete_stmt = $conn->prepare("DELETE FROM company WHERE company_name = ?");
        $deleted = $delete_stmt->execute([$company_name]);

        if ($deleted) {
            $sm = "Company deleted successfully.";
            header("Location: company.php?success=$sm");
            exit();
        } else {
            $em = "Failed to delete the company.";
            header("Location: company.php?error=$em");
            exit();
        }
    } catch (PDOException $e) {
        $em = "Cannot delete company due to linked data.";
        header("Location: company.php?error=$em");
        exit();
    }

} else {
    $em = "Please login first.";
    header("Location: login.php?error=$em");
    exit();
}
