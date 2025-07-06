<?php 

function get_all_users($conn, $hospital_code){
    $sql = "
        SELECT * FROM users 
        WHERE role = 'employee' AND hospital_code = ?

        UNION

        SELECT u.* 
        FROM users u
        INNER JOIN user_hospitals uh ON u.username = uh.username
        WHERE u.role = 'employee' AND uh.hospital_code = ?
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$hospital_code, $hospital_code]);

    if($stmt->rowCount() > 0){
        return $stmt->fetchAll();
    } else {
        return [];
    }
}


function insert_user($conn, $data){
    // تعديل: إضافة الـ ID يدويًا
    $sql = "INSERT INTO users (id, full_name, username, password, role,hospital_code) VALUES(?, ?, ?, ?, ?,?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute($data);
}

function update_user($conn, $userData, $hospitalCodes) {
    // Update main user info
    $sql = "UPDATE users 
            SET full_name = ?, username = ?, password = ?, role = ?, hospital_code = ?
            WHERE id = ? AND role = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute($userData);

    // Clean old hospital mappings
    $username = $userData[1]; // username
    $conn->prepare("DELETE FROM user_hospitals WHERE username = ?")->execute([$username]);

    // Insert new mappings
    $insertStmt = $conn->prepare("INSERT INTO user_hospitals (username, hospital_code) VALUES (?, ?)");
    foreach ($hospitalCodes as $code) {
        $insertStmt->execute([$username, $code]);
    }
}




function delete_user($conn, $data){
    $sql = "DELETE FROM users WHERE id=? AND role=?";
    $stmt = $conn->prepare($sql);
    $stmt->execute($data);
}

function get_user_by_id($conn, $id){
    $sql = "SELECT * FROM users WHERE id = ? ";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);

    return $stmt->rowCount() > 0 ? $stmt->fetch() : 0;
}

function update_profile($conn, $data){
    $sql = "UPDATE users SET full_name=?,  password=? WHERE id=? ";
    $stmt = $conn->prepare($sql);
    $stmt->execute($data);
}

function count_users($conn, $hospital_code){
    $sql = "
        SELECT COUNT(DISTINCT username) AS total FROM (
            SELECT username FROM users 
            WHERE role = 'employee' AND hospital_code = ?

            UNION

            SELECT u.username
            FROM users u
            INNER JOIN user_hospitals uh ON u.username = uh.username
            WHERE u.role = 'employee' AND uh.hospital_code = ?
        ) AS combined_users
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$hospital_code, $hospital_code]);
    return $stmt->fetchColumn();
}
