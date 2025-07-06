<?php
session_start();
include 'DB_connection.php';

// التأكد من وجود معرف المشروع
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // حذف المشروع
    $stmt = $conn->prepare("DELETE FROM projects WHERE id = ?");
    $stmt->execute([$id]);

    // الرجوع إلى صفحة المشاريع
    header("Location: projects.php");
    exit();
} else {
    echo "No project ID specified.";
}
