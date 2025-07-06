<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // معالجة الصورة
  if (!empty($_FILES['profile_image']['tmp_name'])) {
    $uploadDir = 'uploads/';
    $imageName = basename($_FILES['profile_image']['name']);
    $uploadPath = $uploadDir . $imageName;
    move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadPath);
$_SESSION['user_image'] = $uploadPath;
  }

  // حفظ الإيميل ورقم الهاتف
  $_SESSION['email'] = $_POST['email'] ?? '';
  $_SESSION['phone'] = $_POST['phone'] ?? '';
}

$userImage = $_SESSION['user_image'] ?? 'img/default-user.png';
$email = $_SESSION['email'] ?? '';
$phone = $_SESSION['phone'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Profile</title>
  <style>
    .profile-container {
      max-width: 400px;
      margin: 50px auto;
      padding: 20px;
      border: 1px solid #ddd;
      border-radius: 10px;
      text-align: center;
    }

    .profile-img-container {
      position: relative;
      display: inline-block;
      cursor: pointer;
    }

    .profile-img-container input {
      display: none;
    }

    .profile-img-container img {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      object-fit: cover;
      border: 3px solid #007bff;
    }

    form {
      margin-top: 20px;
    }

    input[type="email"], input[type="text"] {
      width: 90%;
      padding: 10px;
      margin: 10px 0;
      border-radius: 5px;
      border: 1px solid #ccc;
    }

    button {
      padding: 10px 20px;
      margin-top: 15px;
      border: none;
      background: #007bff;
      color: white;
      border-radius: 5px;
      cursor: pointer;
    }

    .logout {
      margin-top: 20px;
      display: inline-block;
      color: red;
      text-decoration: none;
    }
  </style>
</head>
<body>
    <?php
session_start();
$profileImage = isset($_SESSION['user_image']) ? $_SESSION['user_image'] : 'img/default-user.png';
?>

  <div class="profile-container">
    <h2>My Profile</h2>
    <form method="POST" enctype="multipart/form-data">
      <label class="profile-img-container">
        <img src="<?= htmlspecialchars($userImage) ?>" id="preview" alt="Profile Image">
        <input type="file" name="profile_image" accept="image/*" onchange="loadFile(event)">
      </label>

      <input type="text" name="phone" placeholder="Phone Number" value="<?= htmlspecialchars($phone) ?>" required>
      <input type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($email) ?>" required>
      <br>
      <button type="submit">Save Profile</button>
    </form>

    <a href="logout.php" class="logout">Logout</a>
  </div>

  <script>
    const loadFile = (event) => {
      const output = document.getElementById('preview');
      output.src = URL.createObjectURL(event.target.files[0]);
      output.onload = () => URL.revokeObjectURL(output.src);
    };
  </script>
</body>
</html>
