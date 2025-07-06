<?php 
session_start();

if (isset($_SESSION['role']) && isset($_SESSION['id'])) {

    if (
        isset($_POST['id']) &&
        isset($_POST['user_name']) &&
        isset($_POST['password']) &&
        isset($_POST['full_name']) &&
        isset($_POST['hospital_code']) && // مصفوفة متعددة
        $_SESSION['role'] == 'admin'
    ) {
        include "../DB_connection.php";

        function validate_input($data) {
            $data = trim($data);
            $data = stripslashes($data);
            $data = htmlspecialchars($data);
            return $data;
        }

        $id = validate_input($_POST['id']);
        $user_name = validate_input($_POST['user_name']);
        $password = validate_input($_POST['password']);
        $full_name = validate_input($_POST['full_name']);
        $hospital_codes = $_POST['hospital_code']; // ✅ دي مصفوفة

        // التحقق من الإدخالات
        if (empty($id)) {
            $em = "ID is required";
            header("Location: ../add-user.php?error=$em");
            exit();
        } else if (empty($user_name)) {
            $em = "User name is required";
            header("Location: ../add-user.php?error=$em");
            exit();
        } else if (empty($password)) {
            $em = "Password is required";
            header("Location: ../add-user.php?error=$em");
            exit();
        } else if (empty($full_name)) {
            $em = "Full name is required";
            header("Location: ../add-user.php?error=$em");
            exit();
        } else if (empty($hospital_codes) || !is_array($hospital_codes)) {
            $em = "Hospital code(s) required";
            header("Location: ../add-user.php?error=$em");
            exit();
        } else {
            include "Model/User.php";
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // حفظ في جدول users
            $stmt = $conn->prepare("INSERT INTO users (id, full_name, username, password, role, created_at) VALUES (?, ?, ?, ?, 'employee', NOW())");
            $stmt->execute([$id, $full_name, $user_name, $hashed_password]);

            // حفظ في جدول user_hospitals
            $stmt2 = $conn->prepare("INSERT INTO user_hospitals (username, hospital_code) VALUES (?, ?)");
            foreach ($hospital_codes as $code) {
                $stmt2->execute([$user_name, $code]);
            }

            $sm = "User created successfully";
            header("Location: ../add-user.php?success=$sm");
            exit();
        }
    } else {
        $em = "Unknown error occurred";
        header("Location: ../add-user.php?error=$em");
        exit();
    }

} else { 
    $em = "Please login first";
    header("Location: ../add-user.php?error=$em");
    exit();
}
?>
