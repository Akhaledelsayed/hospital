<?php
session_start();
$hospital_code = $_SESSION['current_hospital_code'] ?? null;
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        include "../DB_connection.php";

        function sanitize($value) {
            return htmlspecialchars(stripslashes(trim($value)));
        }

        $original_id = $_POST['original_id'] ?? null;
        $device_name = ($_POST['device_name'] === 'other') 
                        ? sanitize($_POST['other_device_name'] ?? '') 
                        : sanitize($_POST['device_name'] ?? '');

        $company_name = sanitize($_POST['company_name'] ?? '');
        $purchasing_order_date = $_POST['purchasing_order_date'] ?? null;
        $qt = (int) ($_POST['qt'] ?? 0);
        $price = (float) ($_POST['price'] ?? 0);

        // Check required fields
        if (!$original_id || !$device_name || !$company_name || !$purchasing_order_date || !$qt || !$price) {
            $_SESSION['error'] = "❌ All fields are required.";
            header("Location: ../edit-purchasing-order.php?id=$original_id&error=" . urlencode($_SESSION['error']));
            exit();
        }

        try {
            $sql = "UPDATE purchasing_order SET 
                        device_name = ?, 
                        company_name = ?, 
                        purchasing_order_date = ?, 
                        qt = ?, 
                        price = ?
                    WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $success = $stmt->execute([
                $device_name,
                $company_name,
                $purchasing_order_date,
                $qt,
                $price,
                $original_id
            ]);

            if ($success) {
                $_SESSION['success'] = "✅ Purchasing order updated successfully.";
                header("Location: ../edit-purchasing-order.php?id=$original_id&success=" . urlencode($_SESSION['success']));
                exit();
            } else {
                $_SESSION['error'] = "❌ Failed to update purchasing order.";
                header("Location: ../edit-purchasing-order.php?id=$original_id&error=" . urlencode($_SESSION['error']));
                exit();
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = "❌ Database error: " . $e->getMessage();
            header("Location: ../edit-purchasing-order.php?id=$original_id&error=" . urlencode($_SESSION['error']));
            exit();
        }

    } else {
        $_SESSION['error'] = "❌ Invalid request method.";
        header("Location: ../edit-purchasing-order.php?error=" . urlencode($_SESSION['error']));
        exit();
    }
} else {
    $_SESSION['error'] = "❌ You must be logged in as an admin.";
    header("Location: ../login.php?error=" . urlencode($_SESSION['error']));
    exit();
}
