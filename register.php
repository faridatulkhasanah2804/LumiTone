<?php
session_start();
require_once "components/config/db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Cek password
    if ($password != $confirm_password) {
        echo "<script>
                alert('Konfirmasi password tidak sesuai!');
                window.location='index.php';
              </script>";
        exit();
    }

    // Cek email sudah ada atau belum
    $cek = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");

    if (mysqli_num_rows($cek) > 0) {
        echo "<script>
                alert('Email sudah terdaftar!');
                window.location='index.php';
              </script>";
        exit();
    }

    // Hash password
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Simpan ke database
    $query = "INSERT INTO users (fullname, email, password)
              VALUES ('$fullname', '$email', '$passwordHash')";

    if (mysqli_query($conn, $query)) {

        echo "<script>
                alert('Registrasi berhasil!');
                window.location='index.php';
              </script>";

    } else {

        echo "<script>
                alert('Registrasi gagal!');
                window.location='index.php';
              </script>";

    }

}
?>