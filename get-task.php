<?php 
session_start();
include "DB_connection.php";

if (!isset($_SESSION['id']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['project_name'])) {
    $projectName = $_GET['project_name'];

    // نجيب ال project id الأول بناءً على اسم المشروع
    $stmt = $conn->prepare("SELECT id FROM projects WHERE project_name = ?");
    $stmt->execute([$projectName]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($project) {
        $projectId = $project['id'];

        // نجيب التاسكات المرتبطة بالمشروع
        $stmt = $conn->prepare("SELECT description FROM tasks WHERE project_id = ?");
        $stmt->execute([$projectId]);
        $tasks = $stmt->fetchAll(PDO::FETCH_COLUMN);

        echo json_encode($tasks);
    } else {
        echo json_encode([]); // مفيش مشروع بهذا الاسم
    }
    exit();
}
?>
