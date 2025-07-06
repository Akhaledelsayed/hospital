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
    isset($_POST['floor'], $_POST['department'], $_POST['department_now'], $_POST['room'],
    $_POST['device_name'], $_POST['accessories'], $_POST['manufacturer'], $_POST['origin'],
    $_POST['company'], $_POST['model'], $_POST['serial_number'], $_POST['qt'], $_POST['bmd_code'],
    $_POST['arrival_date'], $_POST['installation_date'], $_POST['purchaseorder_date'],
    $_POST['price'], $_POST['warranty_start'], $_POST['warranty_end'],
    $_POST['company_contact'], $_POST['company_telephone'], $_POST['safety_test'])
) {
    function validate_input($data) {
        return htmlspecialchars(stripslashes(trim($data)));
    }

    $data = [
        'floor' => validate_input($_POST['floor']),
        'department' => validate_input($_POST['department']),
        'department_now' => validate_input($_POST['department_now']),
        'room' => validate_input($_POST['room']),
        'device_name' => validate_input($_POST['device_name']),
        'accessories' => validate_input($_POST['accessories']),
        'manufacturer' => validate_input($_POST['manufacturer']),
        'origin' => validate_input($_POST['origin']),
        'company' => validate_input($_POST['company']),
        'model' => validate_input($_POST['model']),
        'serial_number' => validate_input($_POST['serial_number']),
        'qt' => validate_input($_POST['qt']),
        'bmd_code' => validate_input($_POST['bmd_code']),
        'arrival_date' => $_POST['arrival_date'],
        'installation_date' => $_POST['installation_date'],
        'purchaseorder_date' => $_POST['purchaseorder_date'],
        'price' => validate_input($_POST['price']),
        'warranty_start' => $_POST['warranty_start'],
        'warranty_end' => $_POST['warranty_end'],
        'company_contact' => validate_input($_POST['company_contact']),
        'company_telephone' => validate_input($_POST['company_telephone']),
        'hospital_code' => $hospital_code,
        'safety_test' => validate_input($_POST['safety_test']),
        'assigned_user' => $_SESSION['username']
    ];

    // Calculate warranty period
    $start = new DateTime($data['warranty_start']);
    $end = new DateTime($data['warranty_end']);
    $diff = $start->diff($end);
    $data['warranty_period'] = $diff->y;

    try {
        $sql = "INSERT INTO devices (
                    floor, department, department_now, room, device_name, accessories,
                    manufacturer, origin, company, model, serial_number, qt, bmd_code,
                    arrival_date, installation_date, purchaseorder_date, price,
                    warranty_period, warranty_start, warranty_end, company_contact,
                    company_telephone, hospital_code, safety_test, assigned_user
                ) VALUES (
                    :floor, :department, :department_now, :room, :device_name, :accessories,
                    :manufacturer, :origin, :company, :model, :serial_number, :qt, :bmd_code,
                    :arrival_date, :installation_date, :purchaseorder_date, :price,
                    :warranty_period, :warranty_start, :warranty_end, :company_contact,
                    :company_telephone, :hospital_code, :safety_test, :assigned_user
                )";

        $stmt = $conn->prepare($sql);
        $stmt->execute($data);

        $msg = "✅ Device added successfully.";
        header("Location: ../add_device.php?success=$msg");
        exit;

    } catch (PDOException $e) {
        $em = "❌ Error: Serial number might already exist. " . $e->getMessage();
        header("Location: ../add_device.php?error=$em");
        exit;
    }
} else {
    $em = "❌ All fields are required.";
    header("Location: ../add_device.php?error=$em");
    exit;
}
?>
