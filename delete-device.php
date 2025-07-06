<?php 
session_start();

if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "admin") {
    include "DB_connection.php";

    if (!isset($_GET['serial_number'])) {
        header("Location: device.php");
        exit();
    }

    $serial_number = $_GET['serial_number'];

    // التحقق من وجود الجهاز
    $stmt = $conn->prepare("SELECT * FROM Devices WHERE serial_number = ?");
    $stmt->execute([$serial_number]);

    if ($stmt->rowCount() == 0) {
        $em = "Device not found.";
        header("Location: device.php?error=$em");
        exit();
    }

    // محاولة حذف الجهاز
    try {
        $delete_stmt = $conn->prepare("DELETE FROM Devices WHERE serial_number = ?");
        $deleted = $delete_stmt->execute([$serial_number]);

        if ($deleted) {
            $sm = "Deleted successfully.";
            header("Location: device.php?success=$sm");
            exit();
        } else {
            $em = "Failed to delete the device.";
            header("Location: device.php?error=$em");
            exit();
        }
    } catch (PDOException $e) {
        $em = "Cannot delete device due to linked data.";
        header("Location: device.php?error=$em");
        exit();
    }

} else {
    $em = "Please login first.";
    header("Location: login.php?error=$em");
    exit();
}
