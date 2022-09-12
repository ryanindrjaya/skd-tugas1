<?php
require_once('./email_handler.php');
require_once('../db/index.php');

session_start();

if (isset($_POST['name'], $_POST["username"], $_POST["email"], $_POST["password"], $_POST["c_password"], $_POST['alamat'])) {
  $name = $_POST['name'];
  $username = $_POST['username'];
  $email = $_POST['email'];
  $password = $_POST["password"];
  $c_password = $_POST["c_password"];
  $alamat = $_POST['alamat'];

  if ($password !== $c_password) {
    $_SESSION['error'] = "Password tidak sama!";
    header("Location: ../register.php");
    exit();
  }

  if ($username == trim($username) && str_contains($username, ' ')) {
    $_SESSION['error'] = "Username tidak boleh menggunakan spasi!";
    header("Location: ../register.php");
    exit();
  }

  $pattern = '/^(?=.*[!@#$%^&*-])(?=.*[0-9])(?=.*[A-Z]).{8,20}$/';
  if (!preg_match($pattern, $password)) {
    $_SESSION['error'] = "Password kurang kuat";
    header("Location: ../register.php");
    exit();
  }

  $name = $conn->escape_string($name);
  $username = $conn->escape_string($username);
  $email = $conn->escape_string($email);
  $province = $conn->escape_string($province);
  $token = bin2hex(random_bytes(36));

  $password_hash = password_hash($password, PASSWORD_DEFAULT);

  $sql = "INSERT INTO user (name, username, password, email, status, role, token, alamat) VALUES ('$name', '$username', '$password_hash', '$email', 0 , 'user', '$token', '$alamat')";
  $result = mysqli_query($conn, $sql);

  if ($result) {
    $sendEmail = sendEmailConfirmation($email, $token);
    if ($sendEmail) {
      $_SESSION['error'] = "Registrasi berhasil, harap cek email anda untuk melakukan konfirmasi";
      header("Location: ../confirm.php");
    } else {
      $_SESSION['error'] = "Gagal mengirim email konfirmasi";
      header("Location: ../register.php");
    }
  } else {
    $_SESSION['error'] = "Register failed";
    header("Location: ../register.php");
  }
} else {
  $_SESSION["error"] = "Username or password is not set";
  header("Location: ../register.php");
}
