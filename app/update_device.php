<?php
session_start();

if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        include "../DB_connection.php";

        // استقبال البيانات من النموذج
        $original_serial     = $_POST['original_serial'];
        $serial_number       = $_POST['serial_number'];
        $floor               = $_POST['floor'];
        $department          = $_POST['department'];
        $department_now      = $_POST['department_now'];
        $room                = $_POST['room'];
        $device_name         = $_POST['device_name'];
        $accessories         = $_POST['accessories'];
        $manufacturer        = $_POST['manufacturer'];
        $origin              = $_POST['origin'];
        $company             = $_POST['company'];
        $model               = $_POST['model'];
        $qt                  = $_POST['qt'];
        $bmd_code            = $_POST['bmd_code'];
        $arrival_date        = $_POST['arrival_date'];
        $installation_date   = $_POST['installation_date'];
        $purchasing_order_date = $_POST['purchaseorder_date'];
        $price               = $_POST['price'];
        $warranty_period     = $_POST['warranty_period'];
        $warranty_start      = $_POST['warranty_start'];
        $warranty_end        = $_POST['warranty_end'];
        $company_contact     = $_POST['company_contact'];
        $company_telephone   = $_POST['company_telephone']; // تم تصحيح الاسم هنا
        $device_safety_test  = $_POST['device_safety_test'];

        // تحديث البيانات في قاعدة البيانات
        $sql = "UPDATE devices SET 
                    serial_number = ?, 
                    floor = ?, 
                    department = ?, 
                    department_now = ?, 
                    room = ?, 
                    device_name = ?, 
                    accessories = ?, 
                    manufacturer = ?, 
                    origin = ?, 
                    company = ?, 
                    model = ?, 
                    qt = ?, 
                    bmd_code = ?, 
                    arrival_date = ?, 
                    installation_date = ?, 
                    purchaseorder_date = ?, 
                    price = ?, 
                    warranty_period = ?, 
                    warranty_start = ?, 
                    warranty_end = ?, 
                    company_contact = ?, 
                    company_tel = ?, 
                    device_safety_test = ?
                WHERE serial_number = ?";

        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $success = $stmt->execute([
                $serial_number,
                $floor,
                $department,
                $department_now,
                $room,
                $device_name,
                $accessories,
                $manufacturer,
                $origin,
                $company,
                $model,
                $qt,
                $bmd_code,
                $arrival_date,
                $installation_date,
                $purchasing_order_date,
                $price,
                $warranty_period,
                $warranty_start,
                $warranty_end,
                $company_contact,
                $company_telephone,
                $device_safety_test,
                $original_serial
            ]);

            if ($success) {
                $_SESSION['success'] = "the data update successfully";
                header("Location: ../device.php");
                exit;
            } else {
                $_SESSION['error'] = "failed to update. try agin";
                header("Location: ../edit_device.php?serial_number=" . urlencode($original_serial));
                exit;
            }
        } else {
            $_SESSION['error'] = "failed ";
            header("Location: ../edit_device.php?serial_number=" . urlencode($original_serial));
            exit;
        }

    } else {
        header("Location: ../edit_device.php");
        exit;
    }
} else {
    header("Location: ../login.php");
    exit;
}
