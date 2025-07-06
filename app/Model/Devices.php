
<?php 

function count_user_devices($conn, $hospital_code){
    $sql = "SELECT COUNT(*) FROM Devices WHERE hospital_code = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$hospital_code]);
    return $stmt->fetchColumn();
}

function get_device_by_serial($conn, $serial_number) {
    $sql = "SELECT * FROM devices WHERE serial_number = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$serial_number]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function get_all_devices($conn,$hospital_code) {
    $sql = "SELECT serial_number, floor, Department, Department_now, Room, device_name, Accessories, Manufacturer, Origin, Company, Model,QT, BMD_Code, Arrival_Date, Installation_Date, purchaseorder_date, Price, Warranty_Period, warranty_start, warranty_end, company_contact, company_Tel,device_safety_test, hospital_code , assigned_user FROM devices where hospital_code = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$hospital_code]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_all_companies($conn,$hospital_code) {
    $sql = "SELECT company_name, phone, company_address, company_email, contact1_name,  contact1_title,  contact1_mobile,  contact2_name,  contact2_title,  contact2_mobile,  contact3_name,contact3_mobile, contact3_title, note, hospital_code, created_by FROM company where hospital_code = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$hospital_code]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_company_by_name($conn,$company_name)
{
    $sql = "SELECT * FROM company WHERE company_name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$company_name]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}


function count_factories($conn, $hospital_code){
    $sql = "SELECT COUNT(DISTINCT manufacturer) FROM Devices WHERE hospital_code = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$hospital_code]);
    return $stmt->fetchColumn();
}

function get_all_factories($conn,$hospital_code) {
    $sql = "SELECT manufacturer_name, phone,contact_email, contact_name,  contact_title,  contact_mobile,  note, hospital_code, created_by FROM manufacturer where hospital_code = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$hospital_code]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_factory_by_name($conn,$manufacturer_name)
{
    $sql = "SELECT manufacturer_name, phone,contact_email, contact_name,  contact_title,  contact_mobile,  note, hospital_code, created_by FROM manufacturer WHERE  manufacturer_name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$manufacturer_name]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}


function count_companies($conn, $hospital_code){
    $sql = "SELECT COUNT(DISTINCT  company) FROM  devices WHERE hospital_code = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$hospital_code]);
    return $stmt->fetchColumn();
}


function count_user_types($conn, $hospital_code){
    $sql = "SELECT COUNT(DISTINCT assigned_user) FROM Devices WHERE hospital_code = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$hospital_code]);
    return $stmt->fetchColumn();
}

function count_user_invoices($conn, $hospital_code){
    $sql = "SELECT COUNT(*) 
            FROM invoices i
            JOIN company c ON i.company_name = c.company_name
            WHERE c.hospital_code = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$hospital_code]);
    return $stmt->fetchColumn();
}

function get_invoice_by_id($conn, $id, $hospital_code) {
    $sql = "SELECT id, company_name, invoice_date, 
                  device_serial, device_name, qt, price
            FROM invoices
            WHERE company_name IN (SELECT company_name FROM company WHERE hospital_code = ?) AND id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$hospital_code, $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function get_all_invoices($conn, $hospital_code) {
    $sql = "SELECT id, company_name, invoice_date, 
                  device_serial, device_name, qt, price
            FROM invoices
            WHERE company_name IN (SELECT company_name FROM company WHERE hospital_code = ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$hospital_code]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function count_user_workorders($conn, $hospital_code){
    $sql = "SELECT COUNT(*) 
            FROM workorders w
            JOIN Devices d ON w.device_serial = d.serial_number
            WHERE d.hospital_code = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$hospital_code]);
    return $stmt->fetchColumn();
}

function get_workorder_by_id($conn, $id, $hospital_code) {
    $sql = "SELECT w.id, w.device_serial, w.device_name, w.requested_by, w.department, w.date_recevied, w.time_recevied, 
                   w.issue_description, w.repair_description, w.inhouse_fixed_by, w.contacted_manufacturer, 
                   w.outhouse_fixed_by, w.repair_cost, w.repair_type, w.used_spare_parts, w.status,
                   w.start_date, w.start_time, w.end_date, w.end_time, w.downtime_duration, 
                   w.created_at, w.created_by FROM workorders w
            JOIN devices d ON w.device_serial = d.serial_number
            WHERE w.id = ? AND d.hospital_code = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id, $hospital_code]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}


function get_all_workorders($conn, $hospital_code) {
    $sql = "SELECT w.id, w.device_serial, w.device_name, w.requested_by, w.department, w.date_recevied, w.time_recevied, 
                   w.issue_description, w.repair_description, w.inhouse_fixed_by, w.contacted_manufacturer, 
                   w.outhouse_fixed_by, w.repair_cost, w.repair_type, w.used_spare_parts, w.status,
                   w.start_date, w.start_time, w.end_date, w.end_time, w.downtime_duration, 
                   w.created_at, w.created_by
            FROM workorders w
            JOIN Devices d ON w.device_serial = d.serial_number
            WHERE d.hospital_code = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$hospital_code]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


function get_all_orders($conn, $hospital_code) {
    $sql = "SELECT 
                id, 
                device_name, 
                company_name, 
                purchasing_order_date, 
                qt, 
                price
            FROM purchasing_order 
            WHERE company_name IN (
                SELECT company_name FROM company WHERE hospital_code = ?
            )";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$hospital_code]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


function get_order_by_id($conn, $id, $hospital_code) {
    $sql = "SELECT 
                id, 
                device_name, 
                company_name, 
                purchasing_order_date, 
                qt, 
                price
            FROM purchasing_order 
            WHERE company_name IN (
                SELECT company_name FROM company WHERE hospital_code = ?   ) AND id = ?";
    
    $stmt = $conn->prepare($sql);
   $stmt->execute([$hospital_code, $id]); // ✅ Corrected order
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function get_all_pm_plans($conn, $hospital_code) {
    $sql = "SELECT 
                id,
                device_name,
                quantity,
                num_of_pm,
                total_pm,
                month_1,
                month_2,
                month_3,
                month_4,
                month_5,
                month_6,
                month_7,
                month_8,
                month_9,
                month_10,
                month_11,
                month_12,
                calibration_month,
                hospital_code
            FROM preventive_maintenance_plan
            WHERE hospital_code = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$hospital_code]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_pm_plan_by_id($conn, $id, $hospital_code) {
    $sql = "SELECT 
                id,
                device_name,
                quantity,
                num_of_pm,
                total_pm,
                month_1,
                month_2,
                month_3,
                month_4,
                month_5,
                month_6,
                month_7,
                month_8,
                month_9,
                month_10,
                month_11,
                month_12,
                calibration_month,
                hospital_code
            FROM preventive_maintenance_plan
            WHERE hospital_code = ? AND id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$hospital_code, $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function get_current_month_pm_plans($conn, $hospital_code, $current_month) {
        $month_col = "month_" . $current_month;
        $sql = "SELECT * FROM preventive_maintenance_plan WHERE hospital_code = ? AND `$month_col` = 1";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$hospital_code]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
