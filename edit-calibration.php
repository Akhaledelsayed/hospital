<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "admin") {
    include "DB_connection.php";
    include "app/Model/User.php";
    
    // التحقق من وجود id في الرابط
    if (!isset($_GET['id'])) {
        header("Location: calibration.php");
        exit();
    }
    
    $id = $_GET['id'];
    $user = get_user_by_id($conn, $id);

    // التحقق من صحة البيانات المرسلة
    if ($user == 0) {
        header("Location: calibration.php");
        exit();
    }

 ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Edit User</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" />
    <link rel="stylesheet" href="css/style.css" />
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f7f9ff;
            color: #333;
        }
        .section-1 {
            background: #fff;
            padding: 30px 40px;
            margin: 40px auto;
            max-width: 600px;
            border-radius: 14px;
            box-shadow: 0 12px 28px rgba(102, 126, 234, 0.15);
        }
        .title {
            font-size: 24px;
            margin-bottom: 30px;
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

        .form-1 {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .input-holder {
            display: flex;
            flex-direction: column;
        }
        .input-holder label {
            font-weight: 600;
            margin-bottom: 6px;
            color: #4a4a4a;
        }
        .input-1 {
            padding: 12px 18px;
            border-radius: 10px;
            border: 1.5px solid #cbd5e1;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        .input-1:focus {
            border-color: #667eea;
            outline: none;
            box-shadow: 0 0 8px rgba(102, 126, 234, 0.4);
        }

        .edit-btn {
            background: linear-gradient(45deg, #38b2ac, #319795);
            border: none;
            color: #fff;
            font-weight: 700;
            padding: 14px 0;
            border-radius: 28px;
            font-size: 18px;
            box-shadow: 0 5px 18px rgba(49, 151, 149, 0.6);
            cursor: pointer;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
            user-select: none;
        }
        .edit-btn:hover {
            background: linear-gradient(45deg, #2c7a7b, #285e61);
            box-shadow: 0 8px 24px rgba(40, 94, 97, 0.8);
            color: #e0f7f7;
        }

        .success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            padding: 12px 20px;
            border-radius: 8px;
            color: #155724;
            margin-bottom: 20px;
            font-weight: 600;
        }
        .danger {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 12px 20px;
            border-radius: 8px;
            color: #721c24;
            margin-bottom: 20px;
            font-weight: 600;
        }
        small {
            color: #666;
            font-size: 13px;
            margin-top: -12px;
            margin-bottom: 12px;
            user-select: none;
        }
    </style>
</head>
<body>
    <input type="checkbox" id="checkbox" />
    <?php include "inc/header.php"; ?>
    
    <div class="body">
        <?php include "inc/nav.php"; ?>
        
        <section class="section-1">
            <h4 class="title">Edit Calibration <a href="calibration.php">Calibration</a></h4>
            
            <!-- عرض رسائل الخطأ أو النجاح -->
            <?php if (isset($_GET['error'])) { ?>
                <div class="danger" role="alert">
                    <?= htmlspecialchars($_GET['error']); ?>
                </div>
            <?php } ?>

            <?php if (isset($_GET['success'])) { ?>
                <div class="success" role="alert">
                    <?= htmlspecialchars($_GET['success']); ?>
                </div>
            <?php } ?>
            
            <!-- نموذج تعديل المستخدم -->
            <form class="form-1" method="POST" action="app/update-user.php">
                <div class="input-holder">
                    <label for="id">Id</label>
                    <input type="text" name="id" id="id" class="input-1" placeholder="Id" value="<?= htmlspecialchars($user['id']); ?>" required />
                </div>
                <div class="input-holder">
                    <label for="device_serial">Device Serial</label>
                    <input type="text" name="device_serial" id="device_serial" class="input-1" placeholder="Deviceserial" value="<?= htmlspecialchars($user['deviceserial']); ?>"  />
                </div>
                
                <div class="input-holder">
                    <label for="device_name">Device Name</label>
                    <input type="text" name="device_name" id="device_name" class="input-1" placeholder="Devicename" value="<?= htmlspecialchars($user['devicename']); ?>"  />
                </div>
                 <div class="input-holder">
                    <label for="qt">QT</label>
                    <input type="text" name="qt" id="qt" class="input-1" placeholder="Qt" value="<?= htmlspecialchars($user['qt']); ?>"  />
                </div>
                <div class="input-holder">
                    <label for="department">Department </label>
                    <input type="text" name="department" id="department" class="input-1" placeholder="Department" value="<?= htmlspecialchars($user['department']); ?>"  />
                </div>
               
                
                <div class="input-holder">
                    <label for="duration">Duration</label>
                    <input type="text" name="duration" id="duration" class="input-1" placeholder="Duration" value="<?= htmlspecialchars($user['duration']); ?>"  />
                </div>
                 
 <div class="input-holder">
                    <label for="em_name">EM Name</label>
                    <input type="text" name="em_name" id="em_name" class="input-1" placeholder="Emname" value="<?= htmlspecialchars($user['emname']); ?>"  />
                </div>

 <div class="input-holder">
                    <label for="month">Month</label>
                    <input type="text" name="month" id="month" class="input-1" placeholder="Month" value="<?= htmlspecialchars($user['month']); ?>"  />
                </div>
 <div class="input-holder">
                    <label for="model">Model</label>
                    <input type="text" name="model" id="model" class="input-1" placeholder="Model" value="<?= htmlspecialchars($user['model']); ?>"  />
                </div>
<div class="input-holder">
                    <label for="hospital_code">Hospital Code</label>
                    <input type="text" name="hospital_code" id="hospital_code" class="input-1" placeholder="Hospitalcode" value="<?= htmlspecialchars($user['hospitalcode']); ?>" required />
                </div>


                <button class="edit-btn" type="submit">Update</button>
            </form>
        </section>
    </div>

    <script type="text/javascript">
        var active = document.querySelector("#navList li:nth-child(2)");
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
