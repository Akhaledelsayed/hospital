<!DOCTYPE html>
<html lang="ar">

<head>
    <meta charset="UTF-8">
    <title>External Calibration</title>
    <style>
    body {
        font-family: 'Segoe UI', sans-serif;
        background-color: #f4f8fb;
        padding: 30px;
        direction: rtl;
    }

    h2 {
        text-align: center;
        color: #0056b3;
    }

    .form-container {
        max-width: 600px;
        margin: auto;
        background: #fff;
        border-radius: 15px;
        box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.1);
        padding: 30px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    label {
        display: block;
        font-weight: bold;
        margin-bottom: 5px;
        color: #333;
    }

    input[type="text"],
    input[type="date"],
    textarea {
        width: 100%;
        padding: 10px;
        border-radius: 10px;
        border: 1px solid #ccc;
    }

    textarea {
        resize: vertical;
        height: 100px;
    }



    .submit-btn {
        background-color: #007bff;
        color: white;
        padding: 12px 25px;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        font-size: 16px;
        margin-top: 15px;
        display: block;
        width: 100%;
    }

    .submit-btn:hover {
        background-color: #0056b3;
    }

    .success-message {
        color: green;
        font-weight: bold;
        text-align: center;
        margin-top: 20px;
    }
    </style>
</head>

<body>

    <h2>External Calibration Form</h2>

    <div class="form-container">
        <form method="post">
            <div class="form-group">
                <label for="calibration_date">Calibration Date:</label>
                <input type="date" id="calibration_date" name="calibration_date" required>
            </div>

            <div class="form-group">
                <label for="device_name"> Device Name:</label>
                <input type="text" id="device_name" name="device_name" required>
            </div>

            <div class="form-group">
                <label for="company_name">Company Name :</label>
                <input type="text" id="company_name" name="company_name" required>
            </div>

            <div class="form-group">
                <label for="notes">Notes:</label>
                <textarea id="notes" name="notes"></textarea>
            </div>

            <button type="submit" class="submit-btn">Save Calibration </button>
        </form>

        <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $date = $_POST['calibration_date'];
        $device = $_POST['device_name'];
        $company = $_POST['company_name'];
        $notes = $_POST['notes'];

        // ممكن هنا حفظ البيانات في قاعدة بيانات أو ملف CSV
        // مؤقتاً نعرض رسالة نجاح فقط
        echo "<div class='success-message'>✔️ تم حفظ المعايرة بنجاح!</div>";
    }
    ?>
    </div>

</body>

</html>