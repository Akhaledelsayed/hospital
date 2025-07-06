<?php
session_start();

if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "admin") {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        include "../DB_connection.php";
        include "company.php";

        // Sanitize and validate inputs
        $original_name = trim($_POST['original_name']);
        $name = trim($_POST['company_name']);
        $phone = trim($_POST['phone']);
        $address = trim($_POST['company_address']);
        $email = trim($_POST['company_email']);
        $contact1_name = trim($_POST['contact1_name']);
        $contact1_mobile = trim($_POST['contact1_mobile']);
         $contact2_name = trim($_POST['contact2_name']);
        $contact2_mobile = trim($_POST['contact2_mobile']);
         $contact3_name = trim($_POST['contact3_name']);
        $contact3_mobile = trim($_POST['contact3_mobile']);
        $hospital_code = trim($_POST['hospital_code']);

        if (empty($name) || empty($phone) || empty($address) || empty($email) || empty( $contact1_name) || empty($contact1_mobile) ||empty($contact2_name) || empty($contact2_mobile) ||empty( $contact3_name) || empty($contact3_mobile) ||empty($hospital_code)) {
            $em = "All fields are required";
            header("Location: ../edit-company.php?company_name=$original_name&error=$em");
            exit();
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $em = "Invalid email format";
            header("Location: ../edit-company.php?company_name=$original_name&error=$em");
            exit();
        }

        // Update the company
        $sql = "UPDATE company SET
                    company_name = ?,
                    phone = ?,
                   company_address = ?,
                    company_email = ?,
                    contact1_name = ?,
                    contact1_mobile=?,
                    contact2_name = ?,
                    contact2_mobile=?,
                    contact3_name = ?,
                    contact3_mobile=?,
                    hospital_code = ?
                WHERE company_name = ?";

        $stmt = $conn->prepare($sql);
        $res = $stmt->execute([$name, $phone, $address, $email,  $contact1_name,$contact1_mobile, $contact2_name,$contact2_mobile,$contact3_name,$contact3_mobile,$hospital_code, $original_name]);

        if ($res) {
            $sm = "Company updated successfully!";
            header("Location: ../edit-company.php?company_name=$name&success=$sm");
            exit();
        } else {
            $em = "An error occurred while updating.";
            header("Location: ../edit-company.php?company_name=$original_name&error=$em");
            exit();
        }
    } else {
        header("Location: ../company.php");
        exit();
    }
} else {
    $em = "Unauthorized access";
    header("Location: ../login.php?error=$em");
    exit();
}
