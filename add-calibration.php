<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PM Checked Certificate</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
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
        h2 {
            text-align: center;
            margin: 20px 0;
            flex: 1;
        }
        .section {
            margin-bottom: 20px;
        }
        .device-info {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
        }
        .device-info div {
            position: relative;
        }
        .device-info input,
        .device-info select {
            width: 100%;
            border: 1px solid #ccc;
            padding: 6px;
            font-size: 14px;
        }
        .table-section {
            margin-top: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th, td {
            border: 1px solid #333;
            padding: 8px;
            text-align: center;
        }
        .status-group {
            display: flex;
            gap: 20px;
            align-items: center;
            margin-bottom: 20px;
        }
        .status-group label {
            margin-right: 5px;
            padding: 5px;
            border-radius: 4px;
            transition: background-color 0.2s;
        }
        .status-group input[type="checkbox"]:checked + span {
            background-color: #e0f7fa;
            border: 1px solid #0288d1;
            padding: 3px 5px;
            border-radius: 4px;
        }
        .footer-text {
            font-weight: bold;
            margin: 10px 0;
        }
        .print-button {
            display: block;
            margin: 30px auto;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }
        /* Print-specific styles */
        @media print {
            .print-button,
            input[type="file"],
            select,
            input[type="text"],
            input[type="date"],
            input[type="time"],
            input[type="radio"],
            input[type="checkbox"] {
                display: none !important;
            }
            .device-info input,
            .device-info select {
                border: none;
            }
            .device-info div::after {
                content: attr(data-value);
                display: block;
                font-size: 14px;
                font-weight: normal;
            }
            .table-section td[data-value]::after {
                content: attr(data-value);
                display: block;
                font-size: 14px;
            }
            .status-group::after {
                content: attr(data-value);
                display: inline-block;
                font-size: 14px;
                margin-left: 10px;
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
    <h2>PM Checked Certificate</h2>
    <div>
        <input type="file" accept="image/*" onchange="previewLogo(this, 'logo2')">
        <img src="logo2.png" id="logo2" class="logo" alt="Logo 2">
    </div>
</div>

<div class="section device-info">
    <div data-value=""><strong>Device:</strong> <input type="text" name="device" oninput="this.parentElement.setAttribute('data-value', this.value)"></div>
    <div data-value=""><strong>Model:</strong> <input type="text" name="model" oninput="this.parentElement.setAttribute('data-value', this.value)"></div>
    <div data-value=""><strong>S/N:</strong> <input type="text" name="sn" oninput="this.parentElement.setAttribute('data-value', this.value)"></div>
    <div data-value=""><strong>Code:</strong> <input type="text" name="code" oninput="this.parentElement.setAttribute('data-value', this.value)"></div>
    <div data-value=""><strong>Department:</strong> <input type="text" name="department" oninput="this.parentElement.setAttribute('data-value', this.value)"></div>
    <div data-value=""><strong>Floor:</strong> <input type="text" name="floor" oninput="this.parentElement.setAttribute('data-value', this.value)"></div>
    <div data-value=""><strong>PM Date:</strong> <input type="date" name="pm_date" oninput="this.parentElement.setAttribute('data-value', this.value)"></div>
    <div data-value=""><strong>PM Due:</strong> <input type="date" name="pm_due" oninput="this.parentElement.setAttribute('data-value', this.value)"></div>
    <div data-value=""><strong>PM Time:</strong> <input type="time" name="pm_time" oninput="this.parentElement.setAttribute('data-value', this.value)"></div>
    <div data-value=""><strong>Mfr:</strong> <input type="text" name="mfr" oninput="this.parentElement.setAttribute('data-value', this.value)"></div>
    <div data-value=""><strong>Risk Level:</strong>
        <select name="risk_level" onchange="this.parentElement.setAttribute('data-value', this.value)">
            <option value=""></option>
            <option value="High">High</option>
            <option value="Medium">Medium</option>

        </select>
    </div>
    <div><strong>PMI. Freq:</strong> Every 6 months</div>
</div>
<div class="table-section">
    <h3>QUALITATIVE TASKS</h3>
    <table>
        <thead>
        <tr>
            <th>Task</th>
            <th>Pass</th>
            <th>Fail</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>Chassis/Housing/Label</td>
            <td data-value=""><input type="radio" name="task1" value="Pass" onchange="updateTableDataValue(this)"></td>
            <td data-value=""><input type="radio" name="task1" value="Fail" onchange="updateTableDataValue(this)"></td>
        </tr>
        <tr>
            <td>AC Plug</td>
            <td data-value=""><input type="radio" name="task2" value="Pass" onchange="updateTableDataValue(this)"></td>
            <td data-value=""><input type="radio" name="task2" value="Fail" onchange="updateTableDataValue(this)"></td>
        </tr>
        <tr>
            <td>Mount/Line cord</td>
            <td data-value=""><input type="radio" name="task3" value="Pass" onchange="updateTableDataValue(this)"></td>
            <td data-value=""><input type="radio" name="task3" value="Fail" onchange="updateTableDataValue(this)"></td>
        </tr>
        <tr>
            <td>Controls/Switches</td>
            <td data-value=""><input type="radio" name="task4" value="Pass" onchange="updateTableDataValue(this)"></td>
            <td data-value=""><input type="radio" name="task4" value="Fail" onchange="updateTableDataValue(this)"></td>
        </tr>
        <tr>
            <td>Battery/Charger</td>
            <td data-value=""><input type="radio" name="task5" value="Pass" onchange="updateTableDataValue(this)"></td>
            <td data-value=""><input type="radio" name="task5" value="Fail" onchange="updateTableDataValue(this)"></td>
        </tr>
        <tr>
            <td>Alarms: Occlusion</td>
            <td data-value=""><input type="radio" name="task6" value="Pass" onchange="updateTableDataValue(this)"></td>
            <td data-value=""><input type="radio" name="task6" value="Fail" onchange="updateTableDataValue(this)"></td>
        </tr>
        <tr>
            <td>Alarms: Near End</td>
            <td data-value=""><input type="radio" name="task7" value="Pass" onchange="updateTableDataValue(this)"></td>
            <td data-value=""><input type="radio" name="task7" value="Fail" onchange="updateTableDataValue(this)"></td>
        </tr>
        <tr>
            <td>Alarms: Program End</td>
            <td data-value=""><input type="radio" name="task8" value="Pass" onchange="updateTableDataValue(this)"></td>
            <td data-value=""><input type="radio" name="task8" value="Fail" onchange="updateTableDataValue(this)"></td>
        </tr>
        <tr>
            <td>Alarms: Low Battery</td>
            <td data-value=""><input type="radio" name="task9" value="Pass" onchange="updateTableDataValue(this)"></td>
            <td data-value=""><input type="radio" name="task9" value="Fail" onchange="updateTableDataValue(this)"></td>
        </tr>
        <tr>
            <td>Alarms: End Battery</td>
            <td data-value=""><input type="radio" name="task10" value="Pass" onchange="updateTableDataValue(this)"></td>
            <td data-value=""><input type="radio" name="task10" value="Fail" onchange="updateTableDataValue(this)"></td>
        </tr>
        </tbody>
    </table>
</div>
<div class="status-group" data-value="">
    <strong>Status:</strong>
    <label><input type="checkbox" name="status" value="Passed" onchange="updateStatus(this)"> <span>Passed</span></label>
    <label><input type="checkbox" name="status" value="Service required" onchange="updateStatus(this)"> <span>Service required</span></label>
    <label><input type="checkbox" name="status" value="Removed from service" onchange="updateStatus(this)"> <span>Removed from service</span></label>
</div>

<div class="footer-text">In Charge of PM: Eng./</div>
<div class="footer-text">Checked By: Eng./</div>
<div class="footer-text">Approved By: MPC</div>

<button class="print-button" onclick="window.print()">🖨️ Print Certificate</button>

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

    function updateTableDataValue(radio) {
        // Clear data-value for both Pass and Fail cells in the same row
        const row = radio.parentElement.parentElement;
        const cells = row.querySelectorAll('td[data-value]');
        cells.forEach(cell => cell.setAttribute('data-value', ''));
        // Set data-value for the selected cell
        radio.parentElement.setAttribute('data-value', radio.value);
    }

    function updateStatus(checkbox) {
        // Uncheck all other checkboxes and update data-value
        const checkboxes = document.querySelectorAll('.status-group input[type="checkbox"]');
        checkboxes.forEach(cb => {
            if (cb !== checkbox) {
                cb.checked = false;
            }
        });
        // Update data-value of status-group
        const statusGroup = checkbox.parentElement.parentElement;
        statusGroup.setAttribute('data-value', checkbox.checked ? checkbox.value : '');
    }
</script>

</body>
</html>