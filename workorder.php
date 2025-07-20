<?php 
session_start();
$hospital_code = $_SESSION['current_hospital_code'] ?? null;

if (!isset($_SESSION['role'], $_SESSION['id']) || !$hospital_code) {
    echo "<h2 style='text-align:center; color:red;'>❌ Access denied. Please log in.</h2>";
    exit;
}

include "DB_connection.php";
include "app/Model/Devices.php";

// Access control logic
$role = $_SESSION['role'];
$user_id = $_SESSION['id'];
$hasAccess = false;

if ($role === "admin") {
    $hasAccess = true;
} else {
    // Check if user is assigned to this hospital
    $stmt = $conn->prepare("SELECT hospital_code FROM user_hospitals WHERE username = (SELECT username FROM users WHERE id = ?)");
    $stmt->execute([$user_id]);
    $user_hospitals = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (in_array($hospital_code, $user_hospitals)) {
        $hasAccess = true;
    }
}

if (!$hasAccess) {
    echo "<h2 style='text-align:center; color:red;'>❌ You are not assigned to this hospital.</h2>";
    exit;
}

// جلب جميع المستخدمين من قاعدة البيانات
$workorders = get_all_workorders($conn, $hospital_code);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Manage Users</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" />
    <link rel="stylesheet" href="css/style.css" />
    <style>
    /* الحاوية لتفعيل التمرير الأفقي */
    .table-wrapper {
        max-height: 600px;
        overflow-y: auto;
        overflow-x: auto;
        margin-bottom: 20px;
        box-shadow: 0 8px 24px rgba(102, 126, 234, 0.1);
        border-radius: 12px;
        background-color: #fff;

        max-width: 1050px;
        /* ✅ يمنع تجاوز الجرس - عدل حسب مكان الجرس */
        margin-right: auto;
        margin-left: auto;
        padding: 10px;
    }


    table.main-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 14px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        min-width: 1400px;
        /* زيادة العرض لجعل التمرير ضروري */
    }

    thead th {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: #fff;
        font-weight: 700;
        padding: 18px 20px;
        border-radius: 12px 12px 0 0;
        box-shadow: 0 4px 18px rgba(118, 75, 162, 0.6);
        user-select: none;
        letter-spacing: 0.07em;
        text-align: left;
        transition: background 0.4s ease;
        white-space: nowrap;
    }

    thead th:hover {
        background: linear-gradient(135deg, #5a67d8, #6b46c1);
        cursor: default;
    }

    tbody tr {
        background: #ffffff;
        box-shadow: 0 6px 16px rgba(102, 126, 234, 0.15);
        border-radius: 14px;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
        cursor: default;
    }

    tbody tr:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 28px rgba(102, 126, 234, 0.35);
        background: #f0f3ff;
    }

    tbody td {
        padding: 18px 20px;
        vertical-align: middle;
        font-size: 14px;
        color: #3c3c3c;
        letter-spacing: 0.02em;
        white-space: nowrap;
    }

    tbody tr:nth-child(even) {
        background: #fafbff;
    }

    /* أزرار */
    .btn {
        padding: 6px 12px;
        border-radius: 28px;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
        margin-right: 8px;
        display: inline-flex;
        align-items: center;
        min-width: 80px;
        justify-content: center;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        transition: background-color 0.3s ease, color 0.3s ease, box-shadow 0.3s ease;
        user-select: none;
        gap: 6px;
        color: #fff;
    }

    .btn i {
        font-size: 16px;
    }

    .edit-btn {
        background: linear-gradient(45deg, #38b2ac, #319795);
        border: none;
        box-shadow: 0 5px 18px rgba(49, 151, 149, 0.6);
    }

    .edit-btn:hover {
        background: linear-gradient(45deg, #2c7a7b, #285e61);
        box-shadow: 0 8px 24px rgba(40, 94, 97, 0.8);
        color: #e0f7f7;
    }

    .delete-btn {
        background: linear-gradient(45deg, #e53e3e, #9b2c2c);
        border: none;
        box-shadow: 0 5px 18px rgba(155, 44, 44, 0.6);
    }

    .delete-btn:hover {
        background: linear-gradient(45deg, #822424, #5a1818);
        box-shadow: 0 8px 24px rgba(90, 24, 24, 0.8);
        color: #fee2e2;
    }

    /* رسالة النجاح */
    .success {
        background-color: #d4edda;
        border: 1px solid #c3e6cb;
        padding: 12px 20px;
        border-radius: 8px;
        color: #155724;
        margin-bottom: 20px;
        font-weight: 600;
    }

    /* العنوان ورابط الإضافة */
    .title {
        font-size: 24px;
        margin-bottom: 25px;
        color: #333;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .title a {
        background: #667eea;
        color: #fff;
        padding: 10px 18px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        box-shadow: 0 3px 8px rgba(102, 126, 234, 0.5);
        transition: background-color 0.3s ease;
    }

    .title a:hover {
        background: #5a6ccf;
    }

    /* لجعل الجدول متجاوب */
    @media (max-width: 768px) {
        .table-wrapper::-webkit-scrollbar {
            width: 8px;
        }

        .table-wrapper::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 8px;
        }

        .table-wrapper::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        .main-table thead {
            display: none;
        }

        .main-table,
        .main-table tbody,
        .main-table tr,
        .main-table td {
            display: block;
            width: 100%;
        }

        .main-table tr {
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 15px;
        }

        .main-table td {
            padding-left: 50%;
            position: relative;
            text-align: right;
            font-size: 14px;
            white-space: normal;
        }

        .main-table td::before {
            position: absolute;
            top: 12px;
            left: 15px;
            width: 45%;
            padding-right: 10px;
            white-space: nowrap;
            font-weight: 600;
            text-align: left;
            color: #555;
        }

        /* أسماء الأعمدة في الوضع المتجاوب */
        .main-table td:nth-of-type(1)::before { content: "#"; }
        .main-table td:nth-of-type(2)::before { content: "Id"; }
        .main-table td:nth-of-type(3)::before { content: "Device Name"; }
        .main-table td:nth-of-type(4)::before { content: "Device Serial"; }
        .main-table td:nth-of-type(5)::before { content: "Requested By"; }
        .main-table td:nth-of-type(6)::before { content: "Department"; }
        .main-table td:nth-of-type(7)::before { content: "Date Received"; }
        .main-table td:nth-of-type(8)::before { content: "Time Received"; }
        .main-table td:nth-of-type(9)::before { content: "Issue Description"; }
        .main-table td:nth-of-type(10)::before { content: "Repair Description"; }
        .main-table td:nth-of-type(11)::before { content: "Inhouse Fixed By"; }
        .main-table td:nth-of-type(12)::before { content: "Contacted Manufacturer"; }
        .main-table td:nth-of-type(13)::before { content: "Outhouse Fixed By"; }
        .main-table td:nth-of-type(14)::before { content: "Repair Cost"; }
        .main-table td:nth-of-type(15)::before { content: "Repair Type"; }
        .main-table td:nth-of-type(16)::before { content: "Used Spare Parts"; }
        .main-table td:nth-of-type(17)::before { content: "Status"; }
        .main-table td:nth-of-type(18)::before { content: "Start Date"; }
        .main-table td:nth-of-type(19)::before { content: "Start Time"; }
        .main-table td:nth-of-type(20)::before { content: "End Date"; }
        .main-table td:nth-of-type(21)::before { content: "End Time"; }
        .main-table td:nth-of-type(22)::before { content: "Downtime Duration"; }
        .main-table td:nth-of-type(23)::before { content: "Created by"; }
        .main-table td:nth-of-type(24)::before { content: "Created At"; }
        .main-table td:nth-of-type(25)::before { content: "Action"; }
        }

    
    </style>
</head>
<body>
    <input type="checkbox" id="checkbox" />
        <?php include "inc/header.php"; ?>

    <div class="body">
        <?php include "inc/nav.php"; ?>
        <section class="section-1">
            <h4 class="title">Manage WorkOrders <a href="add-workorder.php">Add WorkOrder</a></h4>
            <div style="margin-bottom: 20px; text-align: right;">
                <label for="statusFilter"><strong>🔍 Filter by Status:</strong></label>
                <select id="statusFilter" onchange="filterStatus()"
                    style="padding: 6px 10px; border-radius: 8px; border: 1px solid #ccc; font-weight: 600;">
                    <option value="all">All</option>
                    <option value="Completed"> Completed</option>
                    <option value="Pending"> Pending</option>
                </select>
            </div>



            <!-- عرض رسائل النجاح -->
            <?php if (isset($_GET['success'])) { ?>
            <div class="success" role="alert">
                <?= htmlspecialchars($_GET['success']) ?>
            </div>
            <?php } ?>

            <?php if ($workorders != 0) { ?>
            <div class="main-container" style="max-width: 95vw; overflow-x: auto;">

                <div class="table-wrapper">
                    <table class="main-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Id</th>
                                <th>Device Name</th>
                                <th>Device Serial</th>
                                <th>Requested By</th>
                                <th>Department</th>
                                <th>Date Recevied</th>
                                <th>Time Recevied</th>
                                <th>Issue Description</th>
                                <th>Repair Description</th>
                                <th>Inhouse Fixed By</th>
                                <th>Contacted Manufacturer</th>
                                <th>Outhouse Fixed By</th>
                                <th>Repair Cost</th>
                                <th>Repair Type</th>
                                <th>Used Spare Parts</th>
                                <th>Status</th>
                                <th>Start Date</th>
                                <th>Start Time</th>
                                <th>End Date</th>
                                <th>End Time</th>
                                <th>Downtime Duration</th>
                                <th>Created by</th>
                                <th>Created At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 0; foreach ($workorders as $workorder) { ?>
                            <tr>
                                <td><?= ++$i ?></td>
                                <td><?= htmlspecialchars($workorder['id'] ?? '') ?></td>
                                <td><?= htmlspecialchars($workorder['device_name'] ?? '') ?></td>
                                <td><?= htmlspecialchars($workorder['device_serial']) ?></td>
                                <td><?= htmlspecialchars($workorder['requested_by'] ?? '') ?></td>
                                <td><?= htmlspecialchars($workorder['department'] ?? '') ?></td>
                                <td><?= htmlspecialchars($workorder['date_recevied'] ?? '') ?></td>
                                <td><?= htmlspecialchars($workorder['time_recevied'] ?? '') ?></td>
                                <td><?= htmlspecialchars($workorder['issue_description'] ?? '') ?></td>
                                <td><?= htmlspecialchars($workorder['repair_description'] ?? '') ?></td>
                                <td><?= htmlspecialchars($workorder['inhouse_fixed_by'] ?? '') ?></td>
                                <td><?= htmlspecialchars($workorder['contacted_manufacturer'] ?? '') ?></td>
                                <td><?= htmlspecialchars($workorder['outhouse_fixed_by'] ?? '') ?></td>
                                <td><?= htmlspecialchars($workorder['repair_cost'] ?? '') ?></td>
                                <td><?= htmlspecialchars($workorder['repair_type'] ?? '') ?></td>
                                <td><?= htmlspecialchars($workorder['used_spare_parts'] ?? '') ?></td>
                                <td class="status-td">
                                    <?php 
        $status = $workorder['status'] ?? '';
        if (strtolower($status) == 'completed') {
            echo ' <span class="status-text">Completed</span>';
        } elseif (strtolower($status) == 'pending') {
            echo ' <span class="status-text">Pending</span>';
        } else {
            echo '<span class="status-text">'.htmlspecialchars($status).'</span>';
        }
    ?>
                                </td>

                                <td><?= htmlspecialchars($workorder['start_date'] ?? '') ?></td>
                                <td><?= htmlspecialchars($workorder['start_time'] ?? '') ?></td>
                                <td><?= htmlspecialchars($workorder['end_date'] ?? '') ?></td>
                                <td><?= htmlspecialchars($workorder['end_time'] ?? '') ?></td>
                                <td><?= htmlspecialchars($workorder['downtime_duration'] ?? '') ?></td>
                                <td><?= htmlspecialchars($workorder['created_by'] ?? '') ?></td>
                                <td><?= htmlspecialchars($workorder['created_at'] ?? '') ?></td>
                                

                                <td>
                                    
                                    <a href="edit-workorder.php?id=<?= $workorder['id'] ?>" class="btn edit-btn"
                                        title="Edit">
                                        <i class="fa fa-pencil"></i> Edit
                                    </a>
                                    <?php if ($_SESSION['role'] === 'admin'): ?>
                                    <a href="delete-workorder.php?id=<?= $workorder['id'] ?>" class="btn delete-btn"
                                        title="Delete"
                                        onclick="return confirm('Are you sure you want to delete this workorder?');">
                                        <i class="fa fa-trash"></i> Delete
                                    </a>
                                     <?php else: ?>
                                        <span style="color: #999;">No access</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php } else { ?>
            <p>No workorders found.</p>
            <?php } ?>
        </section>
    </div>
    <script>
    function filterStatus() {
        var filter = document.getElementById("statusFilter").value.toLowerCase();
        var rows = document.querySelectorAll(".main-table tbody tr");

        rows.forEach(function(row) {
            var statusText = row.querySelector(".status-td .status-text");
            if (!statusText) return;

            var value = statusText.textContent.toLowerCase();

            if (filter === "all" || value === filter) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    }
    </script>

<script type="text/javascript">
		var active = document.querySelector("#navList li:nth-child(5)");
		if (active) active.classList.add("active");
	</script>

</body>

</html>

<?php
