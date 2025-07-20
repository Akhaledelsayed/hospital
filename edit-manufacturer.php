<?php 
session_start();
if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "admin") {
    include "DB_connection.php";
    include "app/Model/Devices.php";
    
    // التحقق من وجود id في الرابط
    if (!isset($_GET['manufacturer_name'])) {
        header("Location: manufacturer.php");
        exit();
    }
    
    $manufacturer_name = $_GET['manufacturer_name'];
    $manufacturer = get_factory_by_name($conn, $manufacturer_name);

    // التحقق من صحة البيانات المرسلة
    if ($manufacturer == 0) {
        header("Location: manufacturer.php");
        exit();
    }

 ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Edit manufacturer</title>
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
            <h4 class="title">Edit Manufacturer <a href="manufacturer.php">Manufacturers</a></h4>
            
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
            <form class="form-1" method="POST" action="app/update-manufacturer.php">
                <input type="hidden" name="original_name" value="<?= htmlspecialchars($manufacturer['manufacturer_name']); ?>" />
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" name="manufacturer_name" id="manufacturer_name" class="input-1" placeholder="Name" value="<?= htmlspecialchars($manufacturer['manufacturer_name']); ?>" required />
                </div>

               <div class="form-group">
                    <label for="contact_name">Contact Name</label>
                    <input type="text" name="contact_name" id="contact_name" class="input-1" placeholder="Contactname" value="<?= htmlspecialchars($manufacturer['contact_name']); ?>" required />
                </div>


              <div class="form-group">
                    <label for="contact_title">Contact Title</label>
                    <input type="text" name="contact_title" id="contact_title" class="input-1" placeholder="Contacttitle" value="<?= htmlspecialchars($manufacturer['contact_title']); ?>" required />
                </div>



         
              <div class="form-group">
                    <label for="contact_mobile">Contact Mobile</label>
                    <input type="text" name="contact_mobile" id="contact_mobile" class="input-1" placeholder="Contactmobile" value="<?= htmlspecialchars($manufacturer['contact_mobile']); ?>" required />
                </div>
               
                 <div class="form-group">
                    <label for="contact_email">Contact Email</label>
                    <input type="text" name="contact_email" id="contact_email" class="input-1" placeholder="Contactmail" value="<?= htmlspecialchars($manufacturer['contact_email']); ?>"  />
                </div>
               
                 
 <div class="form-group">
                    <label for="note">Note</label>
                    <input type="text" name="note" id="note" class="input-1" placeholder="Note" value="<?= htmlspecialchars($manufacturer['note']); ?>"  />
                </div>
<div class="form-group">
                    <label for="hospital_code">Hospital Code</label>
                    <input type="text" name="hospital_code" id="hospital_code" class="input-1" placeholder="Hospitalcode" value="<?= htmlspecialchars($manufacturer['hospital_code']); ?>" required />
                </div>
                <button class="edit-btn" type="submit">Update</button>
            </form>
        </section>
    </div>

    <script type="text/javascript">
        var active = document.querySelector("#navList li:nth-child(4)");
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
