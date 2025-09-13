<?php
include 'config/config.php';
include 'config/Database.php';
session_start();

$alert = "";

if (isset($_POST['submit'])) {
    $TenTaiKhoan = trim($_POST['TenTaiKhoan'] ?? '');
    $SDT         = trim($_POST['SDT'] ?? '');
    $Email       = trim($_POST['Email'] ?? '');

    if ($TenTaiKhoan && $SDT && $Email) {
        $user = Database::GetData("
            SELECT * FROM users 
            WHERE TenTaiKhoan='$TenTaiKhoan' AND SDT='$SDT' AND Email='$Email'
        ", ['row' => 0]);

        if ($user) {
            // Reset mật khẩu thành "1" (mã hóa trước khi lưu)
            $newPassword = password_hash("1", PASSWORD_BCRYPT);

            if (Database::NonQuery("UPDATE users SET MatKhau='$newPassword' WHERE TenTaiKhoan='$TenTaiKhoan'")) {
                $alert = '<div class="alert alert-success">Mật khẩu đã được cấp lại: <b>1</b>. Vui lòng đăng nhập và đổi mật khẩu!</div>';
            } else {
                $alert = '<div class="alert alert-danger">Có lỗi xảy ra, vui lòng thử lại!</div>';
            }
        } else {
            $alert = '<div class="alert alert-danger">Thông tin không chính xác!</div>';
        }
    } else {
        $alert = '<div class="alert alert-warning">Vui lòng nhập đầy đủ thông tin!</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Quên mật khẩu</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
</head>
<body class="d-flex justify-content-center align-items-center vh-100">

<div class="card p-4" style="max-width:400px; width:100%;">
    <h3 class="text-center">Quên mật khẩu</h3>
    <?= $alert ?>
    <form method="POST">
        <div class="form-group">
            <label>Tên tài khoản</label>
            <input type="text" name="TenTaiKhoan" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Số điện thoại</label>
            <input type="text" name="SDT" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="Email" class="form-control" required>
        </div>
        <button type="submit" name="submit" class="btn btn-primary btn-block">Cấp lại mật khẩu</button>
        <a href="sign.php" class="btn btn-secondary btn-block">Quay lại đăng nhập</a>
    </form>
</div>

</body>
</html>
