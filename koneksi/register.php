<?php
session_start();
require_once "koneksi.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($email) || empty($username) || empty($password)) {
        die("Semua field wajib diisi!");
    }

    // cek email/username sudah ada
    $check = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
    $check->bind_param("ss", $email, $username);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        die("Email atau username sudah terdaftar!");
    }

    // hash password
    $hash = password_hash($password, PASSWORD_BCRYPT);

    $role = "user";

    $stmt = $conn->prepare("
        INSERT INTO users (email, username, password, role)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param("ssss", $email, $username, $hash, $role);

    if ($stmt->execute()) {
        echo "Registrasi berhasil! Silakan login.";
    } else {
        echo "Gagal registrasi!";
    }
}
?>

<form method="POST">
    <input type="email" name="email" placeholder="Email" required><br>
    <input type="text" name="username" placeholder="Username" required><br>
    <input type="password" name="password" placeholder="Password" required><br>
    <button type="submit">Register</button>
</form>