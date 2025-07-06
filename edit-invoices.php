<?php 
session_start();
$hospital_code = $_SESSION['current_hospital_code'] ?? null;
if (isset($_SESSION['role']) && isset($_SESSION['id']) && $_SESSION['role'] == "admin") {
    include "DB_connection.php";
    include "app/Model/Devices.php";
    
    // التحقق من وجود id في الرابط
    if (!isset($_GET['id'])) {
        header("Location: invoices.php");
        exit();
    }
    
    $id = $_GET['id'];
    $invoice = get_invoice_by_id($conn, $id,$hospital_code);

    // التحقق من صحة البيانات المرسلة
    if ($invoice == 0) {
        header("Location: invoices.php");
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
            <h4 class="title">Edit Invoices <a href="invoices.php">Invoices</a></h4>
            
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
            <form class="form-1" method="POST" action="app/update-invoices.php">
                <input type="hidden" name="original_id" value="<?= htmlspecialchars($invoice['id']) ?>">
                <div class="input-holder">
                    <label for="company_name">Company Name</label>
                    <input type="text" name="company_name" id="company_name" class="input-1" placeholder="Companyname" value="<?= htmlspecialchars($invoice['company_name']); ?>"  />
                </div>
                
                <div class="input-holder">
                    <label for="invoice_date">Invoice Date</label>
                    <input type="text" name="invoice_date" id="invoice_date" class="input-1" placeholder="Invoicedate" value="<?= htmlspecialchars($invoice['invoice_date']); ?>"  />
                </div>

<div class="input-holder">
                    <label for="device_serial">Device Serial</label>
                    <input type="text" name="device_serial" id="device_serial" class="input-1" placeholder="Deviceserial" value="<?= htmlspecialchars($invoice['device_serial']); ?>"  />
                </div>

<div class="input-holder">
                    <label for="device_name">Device Name</label>
                    <input type="text" name="device_name" id="device_name" class="input-1" placeholder="Devicename" value="<?= htmlspecialchars($invoice['device_name']); ?>"  />
                </div>

                 <div class="input-holder">
                    <label for="qt">QT</label>
                    <input type="text" name="qt" id="qt" class="input-1" placeholder="Qt" value="<?= htmlspecialchars($invoice['qt']); ?>"  />
                </div>
                <div class="input-holder">
                    <label for="price">Price </label>
                    <input type="text" name="price" id="price" class="input-1" placeholder="Price" value="<?= htmlspecialchars($invoice['price']); ?>"  />
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
