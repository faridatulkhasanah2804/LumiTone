<?php
session_start();
require_once "components/config/db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) == 1) {

        $user = mysqli_fetch_assoc($result);

        if (password_verify($password, $user['password'])) {

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['fullname'] = $user['fullname'];

            header("Location: dashboard.php");
            exit();

        } else {

            echo "<script>
                    alert('Password salah!');
                    window.location='index.php';
                  </script>";
        }

    } else {

        echo "<script>
                alert('Email tidak terdaftar!');
                window.location='index.php';
              </script>";
    }

}
?>