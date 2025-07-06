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
    isset($_POST['phone']) &&
    isset($_POST['company_address']) &&
    isset($_POST['company_email']) &&
    isset($_POST['contact1_name']) &&
    isset($_POST['contact1_title']) &&
    isset($_POST['contact1_mobile']) &&
    isset($_POST['contact2_name']) &&
    isset($_POST['contact2_title']) &&
    isset($_POST['contact2_mobile']) &&
    isset($_POST['contact3_name']) &&
    isset($_POST['contact3_title']) &&
    isset($_POST['contact3_mobile'])
) {

    function validate_input($data) {
        return htmlspecialchars(trim($data));
    }

    // Collect sanitized data
    $data = [
        'company_name'       => validate_input($_POST['company_name']),
        'phone'              => validate_input($_POST['phone']),
        'company_address'    => validate_input($_POST['company_address']),
        'company_email'      => validate_input($_POST['company_email']),
        'contact1_name'      => validate_input($_POST['contact1_name']),
        'contact1_title'     => validate_input($_POST['contact1_title']),
        'contact1_mobile'    => validate_input($_POST['contact1_mobile']),
        'contact2_name'      => validate_input($_POST['contact2_name']),
        'contact2_title'     => validate_input($_POST['contact2_title']),
        'contact2_mobile'    => validate_input($_POST['contact2_mobile']),
        'contact3_name'      => validate_input($_POST['contact3_name']),
        'contact3_title'     => validate_input($_POST['contact3_title']),
        'contact3_mobile'    => validate_input($_POST['contact3_mobile']),
        'hospital_code'      => $hospital_code,
        'created_by'         => $_SESSION['username']
    ];

    try {
        $sql = "INSERT INTO company (
                    company_name, phone, company_address, company_email, 
                    contact1_name, contact1_title, contact1_mobile,
                    contact2_name, contact2_title, contact2_mobile,
                    contact3_name, contact3_title, contact3_mobile,
                    hospital_code, created_by
                ) VALUES (
                    :company_name, :phone, :company_address, :company_email, 
                    :contact1_name, :contact1_title, :contact1_mobile,
                    :contact2_name, :contact2_title, :contact2_mobile,
                    :contact3_name, :contact3_title, :contact3_mobile,
                    :hospital_code, :created_by
                )";

        $stmt = $conn->prepare($sql);
        $stmt->execute($data);

        $msg = "✅ Company added successfully.";
        header("Location: ../add-company.php?success=$msg");
        exit();

    } catch (PDOException $e) {
        $em = "❌ Database error: Company name may already exist. " . $e->getMessage();
        header("Location: ../add-company.php?error=$em");
        exit();
    }

} else {
    $em = "❌ All required fields must be filled.";
    header("Location: ../add-company.php?error=$em");
    exit();
}
?>
