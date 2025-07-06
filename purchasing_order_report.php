<?php
session_start();
include "DB_connection.php";

// جلب أسماء الشركات لاستخدامها في القائمة المنسدلة
$companies_stmt = $conn->query("SELECT DISTINCT company_name FROM company");
$companies = $companies_stmt->fetchAll(PDO::FETCH_COLUMN);

$orders = [];
$total_price = 0;
$conditions_text = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['company_name'], $_POST['date'], $_POST['conditions'])) {
    $company = $_POST['company_name'];
    $date = $_POST['date'];
    $conditions_text = $_POST['conditions'];

    $sql = "SELECT device_name, qt, price
            FROM purchasing_order
            WHERE company_name = ? AND purchasing_order_date = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$company, $date]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($orders as $row) {
        $clean_price = floatval(str_replace(',', '', $row['price']));
        $total_price += $clean_price * intval($row['qt']);
    }
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>أمر توريد</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            direction: rtl; 
            margin: 40px; 
            position: relative;
            min-height: 100vh;
        }
        h2, h3 { text-align: center; }
        form { text-align: center; margin-bottom: 20px; }
        label { margin-left: 10px; }
        textarea { margin-top: 5px; width: 60%; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: #fefefe;
        }
        th, td {
            border: 1px solid #aaa;
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #dfe6f1;
        }
        .total {
            margin-top: 20px;
            text-align: left;
            font-weight: bold;
            font-size: 16px;
        }
        .conditions {
            margin-top: 30px;
            font-size: 14px;
            border-top: 1px dashed #aaa;
            padding-top: 15px;
        }
        .print-btn {
            display: inline-block;
            padding: 8px 20px;
            background-color: #4a67e8;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin-top: 20px;
        }
        .print-btn:hover {
            background-color: #3246d3;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo {
            width: 150px;
            height: 70px;
            object-fit: contain;
        }
        .footer {
            position: fixed;
            bottom: 20px;
            left: 40px;
            right: 40px;
            display: flex;
            justify-content: space-between;
            font-size: 10px; /* Reduced font size to match the smaller text */
            color: #666;
            background-color: #333;
            padding: 5px;
        }
        .footer-column {
            width: 48%;
            text-align: center;
        }
        .footer-column p {
            margin: 1px 0; /* Reduced margin for smaller text */
        }
        .footer-column a {
            color: #1e90ff;
            text-decoration: none;
        }
        .footer-column a:hover {
            text-decoration: underline;
        }
        .wavy-underline {
            border-bottom: 1px wavy #800080; /* Changed to purple to match the image */
            display: inline;
        }
        .footer-separator {
            border-top: 1px solid #ddd;
            margin: 3px 0; /* Reduced margin for smaller text */
        }
        .footer-email {
            color: #000;
            font-size: 9px; /* Slightly smaller for email details */
        }
        @media print {
            .no-print, .print-btn, input[type="file"] {
                display: none !important;
            }
            .footer {
                position: fixed;
                bottom: 10mm;
                left: 20mm;
                right: 20mm;
                display: flex;
                justify-content: space-between;
                font-size: 8pt; /* Smaller font for print */
                color: #666;
                background-color: #fff;
                padding: 3mm;
                border-top: 1px solid #000;
            }
            .wavy-underline {
                border-bottom: 1px wavy #800080;
            }
            .footer-email {
                color: #000;
                font-size: 7pt;
            }
        }
    </style>
</head>
<body>
<div class="header">
    <div>
        <input type="file" accept="image/*" onchange="previewLogo(this, 'logo1')">
        <img src="logo1.png" id="logo1" class="logo" alt="Logo 1">
    </div>
    <h2>أمر توريد</h2>
    <div>
        <input type="file" accept="image/*" onchange="previewLogo(this, 'logo2')">
        <img src="logo2.png" id="logo2" class="logo" alt="Logo 2">
    </div>
</div>

<div class="no-print">
    <h3>تقرير أوامر التوريد</h3>
    <form method="post">
        <label>الشركة:
            <select name="company_name" required>
                <option value="">اختر</option>
                <?php foreach ($companies as $c): ?>
                    <option value="<?= htmlspecialchars($c) ?>" <?= (isset($_POST['company_name']) && $_POST['company_name'] === $c) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>التاريخ:
            <input type="date" name="date" value="<?= $_POST['date'] ?? '' ?>" required>
        </label><br><br>
        <label>شروط التوريد:</label><br>
        <textarea name="conditions" rows="5" required"><?= htmlspecialchars($conditions_text) ?></textarea><br><br>
        <button type="submit">عرض التقرير</button>
    </form>
</div>

<?php if (!empty($orders)): ?>
    <h3>أمر توريد للشركة: <?= htmlspecialchars($_POST['company_name']) ?></h3>
    <h4>بتاريخ: <?= htmlspecialchars($_POST['date']) ?></h4>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>اسم الجهاز</th>
                <th>الكمية</th>
                <th>سعر الوحدة</th>
                <th>الإجمالي</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $index => $order):
                $unit_price = floatval(str_replace(',', '', $order['price']));
                $qty = intval($order['qt']);
                $subtotal = $unit_price * $qty;
            ?>
            <tr>
                <td><?= $index + 1 ?></td>
                <td><?= htmlspecialchars($order['device_name']) ?></td>
                <td><?= $qty ?></td>
                <td><?= number_format($unit_price, 2) ?></td>
                <td><?= number_format($subtotal, 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <p class="total">الإجمالي الكلي: <?= number_format($total_price, 2) ?> جنيه</p>

    <div class="conditions">
        <strong>شروط التوريد:</strong><br>
        <div style="white-space: pre-line;"><?= nl2br(htmlspecialchars($conditions_text)) ?></div>
    </div>

    <div class="no-print" style="text-align:center;">
        <a href="#" onclick="window.print();" class="print-btn">🖨️ طباعة أمر التوريد</a>
    </div>

<?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
    <p style="text-align:center; color:red;">لا توجد أوامر توريد لهذه الشركة في هذا التاريخ.</p>
<?php endif; ?>

<div class="footer"> 

    <div class="footer-column">
                    <div class="footer-separator"></div>

        <p><span class="wavy-underline">العنوان: 6346 الحدبة الوسطى المقطم</span></p>
        <p>القاهرة، مصر</p>
        <p>البريد الإلكتروني: <a href="info@mpc-egy.com">info@mpc-egy.com</a></p>
        <p>الهاتف:  + 201001666125</p>
    </div>
    <div class="footer-column">
                <div class="footer-separator"></div>

        <p><span class="wavy-underline">Address: 6346 AL Hadaba EL - Wosta AL Mokattam</span></p>
        <p>Cairo, Egypt</p>
        <p>Website: <a href="www.mpc-egy.com">www.mpc-egy.com</a></p>
        <p>Phone: +20 27259579</p>
    </div>
   
</div>

<script>
    function previewLogo(input, logoId) {
        const file = input.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById(logoId).src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    }
</script>

</body>
</html>