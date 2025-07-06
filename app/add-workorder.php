<?php
session_start();

$hospital_code = $_SESSION['current_hospital_code'] ?? null;

if (!isset($_SESSION['role'], $_SESSION['id'], $_SESSION['username']) || !$hospital_code) {
    $em = "❌ Access denied. Please log in.";
    header("Location: ../login.php?error=$em");
    exit;
}

include "../DB_connection.php";


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include "../DB_connection.php";

    // 1. Collect device_serial first to get hospital_code
    $device_serial = $_POST['device_serial'] ?? null;
    if (!$device_serial) {
        $em = "Device serial is required.";
        header("Location: ../add-workorder.php?error=" . urlencode($em));
        exit();
    }

    // Get hospital_code from devices table
    // Ensure hospital_code is available from device
$stmt = $conn->prepare("SELECT hospital_code FROM devices WHERE serial_number = ?");
$stmt->execute([$device_serial]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    $em = "Device not found or hospital not linked.";
    header("Location: ../add-workorder.php?error=" . urlencode($em));
    exit();
}

$hospital_code_raw = $row['hospital_code']; // e.g., 1
$hospital_code_padded = str_pad($hospital_code_raw, 2, '0', STR_PAD_LEFT); // e.g., 01
$year = date('Y');
$month = date('m');

// Get max existing counter for current hospital, year, and month
$stmt = $conn->prepare("
    SELECT MAX(CAST(SUBSTRING_INDEX(w.id, '-', -1) AS UNSIGNED)) AS max_counter
    FROM workorders w
    JOIN devices d ON w.device_serial = d.serial_number
    WHERE d.hospital_code = ? AND DATE_FORMAT(w.created_at, '%Y-%m') = ?
");
$stmt->execute([$hospital_code_raw, "$year-$month"]);
$maxCounter = $stmt->fetchColumn();

// Generate next available counter
$nextCounter = str_pad(($maxCounter ?? 0) + 1, 3, '0', STR_PAD_LEFT);

// Build new unique workorder ID
$generated_id = "$hospital_code_padded-$year-$month-$nextCounter";


    // 3. Define fields to collect
    $fields = [
        "device_name", "requested_by", "department", "date_recevied", "time_recevied",
        "issue_description", "repair_description", "inhouse_fixed_by", "contacted_manufacturer",
        "outhouse_fixed_by", "repair_cost", "repair_type", "used_spare_parts", "status",
        "start_date", "start_time", "end_date", "end_time"
    ];

    $data = [];
    foreach ($fields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            $em = "Please fill all fields.";
            header("Location: ../add-workorder.php?error=" . urlencode($em));
            exit();
        }
        $data[$field] = htmlspecialchars(trim($_POST[$field]));
    }

    // Add device_serial and generated ID
    $data['device_serial'] = $device_serial;
    $data['id'] = $generated_id;
    $data['created_by'] = $_SESSION['username'];

    // Handle 'other' in repair_type
    if ($data['repair_type'] === 'other') {
        $other = trim($_POST['repair_type_other'] ?? '');
        if (empty($other)) {
            $em = "You selected 'Other' but didn't specify it.";
            header("Location: ../add-workorder.php?error=" . urlencode($em));
            exit();
        }
        $data['repair_type'] = htmlspecialchars($other);
    }

    // Downtime calculation
    $start = strtotime($data['start_date'] . ' ' . $data['start_time']);
    $end = strtotime($data['end_date'] . ' ' . $data['end_time']);
    $data['downtime_duration'] = ($end > $start)
        ? floor(($end - $start) / 3600) . 'h ' . floor((($end - $start) % 3600) / 60) . 'm'
        : "Invalid";

    try {
        $sql = "INSERT INTO workorders (
            id, device_name, device_serial, requested_by, department, date_recevied, time_recevied,
            issue_description, repair_description, inhouse_fixed_by, contacted_manufacturer,
            outhouse_fixed_by, repair_cost, repair_type, used_spare_parts, status,
            start_date, start_time, end_date, end_time, downtime_duration, created_by, created_at
        ) VALUES (
            :id, :device_name, :device_serial, :requested_by, :department, :date_recevied, :time_recevied,
            :issue_description, :repair_description, :inhouse_fixed_by, :contacted_manufacturer,
            :outhouse_fixed_by, :repair_cost, :repair_type, :used_spare_parts, :status,
            :start_date, :start_time, :end_date, :end_time, :downtime_duration, :created_by, NOW()
        )";

        $stmt = $conn->prepare($sql);
        $stmt->execute($data);

        $success = "Workorder added successfully with ID: $generated_id";
        header("Location: ../add-workorder.php?success=" . urlencode($success));
        exit();

    } catch (PDOException $e) {
        $em = "Database error: " . $e->getMessage();
        header("Location: ../add-workorder.php?error=" . urlencode($em));
        exit();
    }
} else {
    $em = "Invalid request";
    header("Location: ../add-workorder.php?error=" . urlencode($em));
    exit();
}
?>
