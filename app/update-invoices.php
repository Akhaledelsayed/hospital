<?php
session_start();

if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        include "../DB_connection.php";

        function sanitize($value) {
            return htmlspecialchars(stripslashes(trim($value)));
        }

        $id = $_POST['original_id'] ?? null;
        $company_name = sanitize($_POST['company_name'] ?? '');
        $invoice_date = $_POST['invoice_date'] ?? null;
        $device_serial = sanitize($_POST['device_serial'] ?? '');
        $device_name = sanitize($_POST['device_name'] ?? '');
        $qt = (int)($_POST['qt'] ?? 0);
        $price = (float)($_POST['price'] ?? 0);

        if (!$id || !$company_name || !$invoice_date || !$device_serial || !$device_name || !$qt || !$price) {
            $error = "❌ All fields are required.";
            header("Location: ../edit-invoices.php?id=$id&error=" . urlencode($error));
            exit();
        }

        try {
            $sql = "UPDATE invoices SET 
                        company_name = ?, 
                        invoice_date = ?, 
                        device_serial = ?, 
                        device_name = ?, 
                        qt = ?, 
                        price = ? 
                    WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                $company_name,
                $invoice_date,
                $device_serial,
                $device_name,
                $qt,
                $price,
                $id
            ]);

            $success = "✅ Invoice updated successfully.";
            header("Location: ../edit-invoices.php?id=$id&success=" . urlencode($success));
            exit();
        } catch (PDOException $e) {
            $error = "❌ Database error: " . $e->getMessage();
            header("Location: ../edit-invoices.php?id=$id&error=" . urlencode($error));
            exit();
        }
    } else {
        header("Location: ../invoices.php?error=" . urlencode("❌ Invalid request"));
        exit();
    }
} else {
    header("Location: ../login.php?error=" . urlencode("❌ Please login first"));
    exit();
}
