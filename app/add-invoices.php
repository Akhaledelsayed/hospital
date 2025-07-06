<?php
session_start();

$hospital_code = $_SESSION['current_hospital_code'] ?? null;

if (!isset($_SESSION['role'], $_SESSION['id'], $_SESSION['username']) || !$hospital_code) {
    $em = "❌ Access denied. Please log in.";
    header("Location: ../login.php?error=$em");
    exit;
}

include "../DB_connection.php";

if (
    isset($_POST['company_name']) &&
    isset($_POST['invoice_date']) &&
    isset($_POST['device_serial']) &&
    isset($_POST['device_name']) &&
    isset($_POST['qt']) &&
    isset($_POST['price'])
) {
    function validate_input($data) {
        return htmlspecialchars(stripslashes(trim($data)));
    }

    $company_name = validate_input($_POST['company_name']);
    $invoice_date = validate_input($_POST['invoice_date']);
    $device_serial = validate_input($_POST['device_serial']);
    $device_name = validate_input($_POST['device_name']);
    $qt = (int) validate_input($_POST['qt']);
    $price = validate_input($_POST['price']);

    try {
        $sql = "INSERT INTO invoices (
                    company_name, invoice_date, device_serial, device_name, qt, price
                ) VALUES (
                    :company_name, :invoice_date, :device_serial, :device_name, :qt, :price
                )";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':company_name' => $company_name,
            ':invoice_date' => $invoice_date,
            ':device_serial' => $device_serial,
            ':device_name' => $device_name,
            ':qt' => $qt,
            ':price' => $price
        ]);

        $msg = "Invoice added successfully";
        header("Location: ../add-invoices.php?success=" . urlencode($msg));
        exit();
    } catch (PDOException $e) {
        $em = "Database error: " . $e->getMessage();
        header("Location: ../add-invoices.php?error=" . urlencode($em));
        exit();
    }

} else {
    $em = "All fields are required";
    header("Location: ../add-invoices.php?error=" . urlencode($em));
    exit();
}
