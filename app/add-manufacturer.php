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
        isset($_POST['manufacturer_name']) &&
        isset($_POST['contact_name']) &&
        isset($_POST['contact_title']) &&
        isset($_POST['contact_mobile']) &&
        isset($_POST['contact_email']) &&
        isset($_POST['note']) 
    ) {
        include "../DB_connection.php"; // Ensure this is a PDO connection

        function validate_input($data) {
            return htmlspecialchars(stripslashes(trim($data)));
        }

        // Ensure session username exists
        if (!isset($_SESSION['username'])) {
            $em = "You must be logged in to add a manufacturer.";
            header("Location: ../login.php?error=$em");
            exit();
        }

        // Sanitize and collect input
        $data = [
            'manufacturer_name'     => validate_input($_POST['manufacturer_name']),
            'contact_name'         => validate_input($_POST['contact_name']),
            'contact_title'        => validate_input($_POST['contact_title']),
            'contact_mobile'       => validate_input($_POST['contact_mobile']),
            'contact_email'         => validate_input($_POST['contact_email']),
            'hospital_code'         => $hospital_code,
            'note'         => validate_input($_POST['note']),
            'created_by'            => $_SESSION['username']
        ];

        try {
            $sql = "INSERT INTO manufacturer (
                        manufacturer_name, 
                        contact_name, contact_title, contact_mobile,
                        contact_email,note, 
                        hospital_code, created_by
                    ) VALUES (
                        :manufacturer_name, 
                        :contact_name, :contact_title, :contact_mobile,
                        :contact_email,:note,
                        :hospital_code, :created_by
                    )";

            $stmt = $conn->prepare($sql);
            $stmt->execute($data);

            $msg = "Manufacturer added successfully";
            header("Location: ../add-manufacturer.php?success=$msg");
            exit();

        } catch (PDOException $e) {
            $em = "Database error: " . $e->getMessage();
            header("Location: ../add-manufacturer.php?error=$em");
            exit();
        }

    } else {
        $em = "All fields are required";
        header("Location: ../add-manufacturer.php?error=$em");
        exit();
    }

