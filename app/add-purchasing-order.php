<input type="number" name="qt" required min="1" />
<input type="number" name="price" required step="0.01" min="0" />

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
    isset($_POST['device_name']) &&
    isset($_POST['company_name']) &&
    isset($_POST['purchasing_order_date']) &&
    isset($_POST['qt']) &&
    isset($_POST['price'])
) {

if (!is_numeric($_POST['qt']) || !is_numeric($_POST['price'])) {
    $em = "❌ Quantity and Price must be numeric values only.";
    header("Location: ../add-purchasing-order.php?error=$em");
    exit();
}



    function validate_input($data) {
        return htmlspecialchars(stripslashes(trim($data)));
    }

    // ✅ Use "other_device_name" if "Other" was selected
    $device_name = $_POST['device_name'] === 'other'
        ? validate_input($_POST['other_device_name'])
        : validate_input($_POST['device_name']);

    $data = [
        'device_name' => $device_name,
        'company_name' => validate_input($_POST['company_name']),
        'purchasing_order_date' => validate_input($_POST['purchasing_order_date']),
        'qt' => validate_input($_POST['qt']),
        'price' => validate_input($_POST['price']),
    ];
    

    try {
        $sql = "INSERT INTO purchasing_order (
                    device_name, company_name, purchasing_order_date, qt, price
                ) VALUES (
                    :device_name, :company_name, :purchasing_order_date, :qt, :price
                )";

        $stmt = $conn->prepare($sql);
        $stmt->execute($data);

        $msg = "Purchasing order added successfully";
        header("Location: ../add-purchasing-order.php?success=$msg");
        exit();
    } catch (PDOException $e) {
        $em = "Database error: " . $e->getMessage();
        header("Location: ../add-purchasing-order.php?error=$em");
        exit();
    }
} else {
    $em = "All fields are required";
    header("Location: ../add-purchasing-order.php?error=$em");
    exit();
}
?>
