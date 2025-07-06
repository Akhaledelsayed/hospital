<?php
session_start();

if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        include "../DB_connection.php";

        $original_name    = trim($_POST['original_name']);
        $manufacturer_name = trim($_POST['manufacturer_name']);
        $phone            = trim($_POST['phone']);
        $contact_name     = trim($_POST['contact_name']);
        $contact_title    = trim($_POST['contact_title']);
        $contact_mobile   = trim($_POST['contact_mobile']);
        $contact_email    = trim($_POST['contact_email']);
        $note             = trim($_POST['note']);
        $hospital_code    = trim($_POST['hospital_code']);

        if (empty($manufacturer_name) || empty($phone) || empty($contact_name) || empty($contact_title) || empty($contact_mobile) || empty($contact_email) || empty($hospital_code)) {
            $em = "All fields are required";
            header("Location: ../edit-manufacturer.php?manufacturer_name=" . urlencode($original_name) . "&error=" . urlencode($em));

            exit();
        }

        if (!filter_var($contact_email, FILTER_VALIDATE_EMAIL)) {
            $em = "Invalid email format";
            header("Location: ../edit-manufacturer.php?manufacturer_name=" . urlencode($original_name) . "&error=" . urlencode($em));

            exit();
        }

        $sql = "UPDATE manufacturer SET
                    manufacturer_name = ?, 
                    phone = ?, 
                    contact_name = ?, 
                    contact_title = ?, 
                    contact_mobile = ?, 
                    contact_email = ?, 
                    note = ?, 
                    hospital_code = ?
                WHERE manufacturer_name = ?";

        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([
            $manufacturer_name, $phone, $contact_name, $contact_title,
            $contact_mobile, $contact_email, $note, $hospital_code,
            $original_name
        ]);

        if ($result) {
            $sm = "Manufacturer updated successfully!";
            header("Location: ../edit-manufacturer.php?manufacturer_name=$manufacturer_name&success=$sm");
            exit();
        } else {
            $em = "Failed to update manufacturer.";
            header("Location: edit-manufacturer.php?manufacturer_name=$original_name&error=$em");
            exit();
        }
    } else {
        header("Location: ../manufacturer.php");
        exit();
    }
} else {
    $em = "Unauthorized access";
    header("Location: ../login.php?error=$em");
    exit();
}
