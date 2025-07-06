<?php 
session_start();

if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == 'admin') {
    if (
        isset($_POST['user_name']) &&
        isset($_POST['full_name']) &&
        isset($_POST['id']) &&
        isset($_POST['hospital_code']) // now array
    ) {
        include "../DB_connection.php";
        include "Model/User.php";

        function validate_input($data) {
            return is_array($data) ? $data : htmlspecialchars(stripslashes(trim($data)));
        }

        $user_name = validate_input($_POST['user_name']);
        $password = isset($_POST['password']) ? validate_input($_POST['password']) : '';
        $full_name = validate_input($_POST['full_name']);
        $id = validate_input($_POST['id']);
        $hospital_codes = validate_input($_POST['hospital_code']); // multiple

        // Validation
        if (empty($user_name)) {
            $em = "User name is required";
        } elseif (empty($full_name)) {
            $em = "Full name is required";
        } elseif (empty($id)) {
            $em = "ID is required";
        } elseif (empty($hospital_codes) || !is_array($hospital_codes)) {
            $em = "At least one hospital must be selected";
        }

        if (isset($em)) {
            header("Location: ../edit-user.php?error=$em&id=$id");
            exit();
        }

        // Get existing user to keep role/password if needed
        $existing_user = get_user_by_id($conn, $id);
        if ($existing_user == 0) {
            $em = "User not found";
            header("Location: ../edit-user.php?error=$em&id=$id");
            exit();
        }

        $role = $existing_user['role'];

        // Use new password if provided
        $hashed_password = !empty($password) 
            ? password_hash($password, PASSWORD_DEFAULT) 
            : $existing_user['password'];

        // Use the first hospital_code in users table
        $main_hospital = $hospital_codes[0];

        $user_data = [
            $full_name,
            $user_name,
            $hashed_password,
            $role,
            $main_hospital,
            $id,
            $role
        ];

        // Update both users table and user_hospitals
        update_user($conn, $user_data, $hospital_codes);

        $msg = "User updated successfully";
        header("Location: ../edit-user.php?success=$msg&id=$id");
        exit();

    } else {
        $em = "Unknown error occurred";
        header("Location: ../edit-user.php?error=$em");
        exit();
    }
} else { 
    $em = "First login";
    header("Location: ../edit-user.php?error=$em");
    exit();
}
?>
