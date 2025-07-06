<?php
session_start();
include "DB_connection.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['id'])) {
        header("Location: login.php");
        exit();
    }

    if (!isset($_POST['save_row'])) {
        echo json_encode(["success" => false, "error" => "No row specified"]);
        exit();
    }

    $row = (int)$_POST['save_row'];

    // تحقق إن الداتا موجودة
    if (!isset($_POST['date'][$row]) || !isset($_POST['project_name'][$row])) {
        echo json_encode(["success" => false, "error" => "Data missing for row $row"]);
        exit();
    }

    $date = $_POST['date'][$row] ?? '';
    $project_name = $_POST['project_name'][$row] ?? '';
    $notes = $_POST['notes'][$row] ?? '';

    $slots = [
        'ten_am', 'eleven_am', 'twelve_pm', 'one_pm',
        'two_pm', 'three_pm', 'four_pm', 'five_pm', 'six_pm'
    ];

    $tasks = [];
    foreach ($slots as $slot) {
        $tasks[$slot] = $_POST[$slot][$row] ?? '';
    }

    try {
        $stmt = $conn->prepare("
            INSERT INTO sheet_data_3 
            (day, date, project_name, ten_am, eleven_am, twelve_pm, one_pm, two_pm, three_pm, four_pm, five_pm, six_pm, notes, user_name)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            date('l', strtotime($date)),
            $date,
            $project_name,
            $tasks['ten_am'],
            $tasks['eleven_am'],
            $tasks['twelve_pm'],
            $tasks['one_pm'],
            $tasks['two_pm'],
            $tasks['three_pm'],
            $tasks['four_pm'],
            $tasks['five_pm'],
            $tasks['six_pm'],
            $notes,
            $_SESSION['id']
        ]);

        header("Location: time-sheet.php");
        exit();
    } catch (Exception $e) {
        echo json_encode(["success" => false, "error" => $e->getMessage()]);
    }
}
?>
