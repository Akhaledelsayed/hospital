<?php
session_start();
include "../DB_connection.php";
include "../app/Model/Devices.php";

$hospital_code = $_SESSION['current_hospital_code'] ?? null;

if (!isset($_SESSION['role'], $_SESSION['id'], $_SESSION['username']) || !$hospital_code) {
    header("Location: ../login.php?error=" . urlencode("❌ Access denied. Please log in."));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    function clean($data) {
        return htmlspecialchars(trim($data));
    }

    $id = clean($_POST['id']);
    $device_serial = clean($_POST['device_serial']);
    $requested_by = clean($_POST['requested_by']);
    $department = clean($_POST['department']);
    $date_recevied = clean($_POST['date_recevied']);
    $time_recevied = clean($_POST['time_recevied']);
    $issue_description = clean($_POST['issue_description']);
    $repair_description = clean($_POST['repair_description']);
    $inhouse_fixed_by = clean($_POST['inhouse_fixed_by']);
    $contacted_manufacturer = clean($_POST['contacted_manufacturer']);
    $outhouse_fixed_by = clean($_POST['outhouse_fixed_by']);
    $repair_cost = clean($_POST['repair_cost']);
    $repair_type = clean($_POST['repair_type']);
    $used_spare_parts = clean($_POST['used_spare_parts']);
    $status = clean($_POST['status']);
    $start_date = clean($_POST['start_date']);
    $start_time = clean($_POST['start_time']);
    $end_date = clean($_POST['end_date']);
    $end_time = clean($_POST['end_time']);

    if (empty($id) || empty($repair_type) || empty($end_date) || empty($end_time)) {
        $error = "Required fields are missing.";
        header("Location: ../edit-workorder.php?id=$id&error=$error");
        exit();
    }

    $sql = "UPDATE workorders SET
                device_serial = ?,
                requested_by = ?,
                department = ?,
                date_recevied = ?,
                time_recevied = ?,
                issue_description = ?,
                repair_description = ?,
                inhouse_fixed_by = ?,
                contacted_manufacturer = ?,
                outhouse_fixed_by = ?,
                repair_cost = ?,
                repair_type = ?,
                used_spare_parts = ?,
                status = ?,
                start_date = ?,
                start_time = ?,
                end_date = ?,
                end_time = ?
            WHERE id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        $device_serial,
        $requested_by,
        $department,
        $date_recevied,
        $time_recevied,
        $issue_description,
        $repair_description,
        $inhouse_fixed_by,
        $contacted_manufacturer,
        $outhouse_fixed_by,
        $repair_cost,
        $repair_type,
        $used_spare_parts,
        $status,
        $start_date,
        $start_time,
        $end_date,
        $end_time,
        $id
    ]);

    $success = "Workorder updated successfully!";
    header("Location: ../edit-workorder.php?id=$id&success=$success");
    exit();

} else {
    $error = "Invalid request method.";
    header("Location: ../workorder.php?error=$error");
    exit();
}
