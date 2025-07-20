<?php 
session_start();
$hospital_code = $_SESSION['current_hospital_code'] ?? null;
if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "admin") {
    include "DB_connection.php";
    include "app/Model/User.php";
    
    
    $users = get_all_users($conn,$hospital_code);
  
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
        table.main-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 14px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        thead th {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: #fff;
            font-weight: 700;
            padding: 18px 28px;
            border-radius: 12px 12px 0 0;
            box-shadow: 0 4px 18px rgba(118, 75, 162, 0.6);
            user-select: none;
            letter-spacing: 0.07em;
            text-align: left;
            transition: background 0.4s ease;
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
            padding: 18px 28px;
            vertical-align: middle;
            font-size: 16px;
            color: #3c3c3c;
            letter-spacing: 0.02em;
        }
        tbody tr:nth-child(even) {
            background: #fafbff;
        }
        /* أزرار */
        .btn {
            padding: 9px 18px;
            border-radius: 28px;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            margin-right: 12px;
            display: inline-flex;
            align-items: center;
            min-width: 100px;
            justify-content: center;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            transition: background-color 0.3s ease, color 0.3s ease, box-shadow 0.3s ease;
            user-select: none;
            gap: 8px;
            color: #fff;
        }
        .btn i {
            font-size: 18px;
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
            box-shadow: 0 3px 8px rgba(102,126,234,0.5);
            transition: background-color 0.3s ease;
        }
        .title a:hover {
            background: #5a6ccf;
        }

        /* لجعل الجدول متجاوب */
        @media (max-width: 768px) {
            .main-table thead {
                display: none;
            }
            .main-table, .main-table tbody, .main-table tr, .main-table td {
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
            .main-table td:nth-of-type(1)::before { content: "#"; }
            .main-table td:nth-of-type(2)::before { content: "ID"; }
            .main-table td:nth-of-type(3)::before { content: "Full Name"; }
            .main-table td:nth-of-type(4)::before { content: "Username"; }
            .main-table td:nth-of-type(5)::before { content: "Hospital code"; }
            .main-table td:nth-of-type(6)::before { content: "Role"; }
            .main-table td:nth-of-type(7)::before { content: "Action"; }
        }
    </style>
</head>
<body>
    <input type="checkbox" id="checkbox" />
    <?php include "inc/header.php"; ?>
    <div class="body">
        <?php include "inc/nav.php"; ?>
        <section class="section-1">
            <h4 class="title">Manage Users <a href="add-user.php">Add User</a></h4>

            <!-- عرض رسائل النجاح -->
            <?php if (isset($_GET['success'])) { ?>
                <div class="success" role="alert">
                    <?= htmlspecialchars($_GET['success']) ?>
                </div>
            <?php } ?>
            
            <?php if ($users != 0) { ?>
                <table class="main-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Username</th>
                            <th>Hospital code</th>
                            <th>Role</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 0; foreach ($users as $user) { ?>
                            <tr>
                                <td><?= ++$i ?></td>
                                <td><?= htmlspecialchars($user['id']) ?></td>
                                <td><?= htmlspecialchars($user['full_name']) ?></td>
                                <td><?= htmlspecialchars($user['username']) ?></td>
                                <td><?= $hospital_code ?></td>
                                <td><?= htmlspecialchars($user['role']) ?></td>
                                <td>
                                    <a href="edit-user.php?id=<?= htmlspecialchars($user['id']) ?>" class="btn edit-btn">
                                        <i class="fa fa-pencil"></i> Edit
                                    </a>
                                    <a href="delete-user.php?id=<?= htmlspecialchars($user['id']) ?>" class="btn delete-btn">
                                        <i class="fa fa-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            <?php } else { ?>
                <h3>Empty</h3>
            <?php } ?>
        </section>
    </div>

    <script type="text/javascript">
        var active = document.querySelector("#navList li:nth-child(1)");
        if (active) active.classList.add("active");
    </script>
</body>
</html>
<?php 
} else { 
    $em = "First login";
    header("Location: login.php?error=$em");
    exit();
} 
?>
