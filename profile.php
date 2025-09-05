<?php 
include 'config/config.php';
include 'config/Database.php';
session_start();

if (!isset($_SESSION['TenTaiKhoan'])) {
    header("Location: login.php");
    exit;
}

// Cập nhật thông tin cá nhân
if (isset($_POST['submit'])) {
    if (!empty($_FILES['avatar']['name'])) {
        // Đặt tên file cố định theo username, giữ đuôi gốc
        $extension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $image_name = $_SESSION['TenTaiKhoan'] . "." . $extension; 
        $image_path = '/Salonoto/assets/img/' . $image_name; 

        // Ghi đè file cũ
        move_uploaded_file($_FILES['avatar']['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . $image_path);

        // Cập nhật vào DB và session
        Database::NonQuery("UPDATE users SET Avatar = '$image_path' WHERE TenTaiKhoan = '" . $_SESSION['TenTaiKhoan'] . "'");
        $_SESSION['Avatar'] = $image_path;
    }

    $tendaydu = $_POST['tendaydu'] ?? '';
    $sdt = $_POST['sdt'] ?? '';
    $email = $_POST['email'] ?? '';
    $diachi = $_POST['diachi'] ?? '';

    $sql = "UPDATE users SET TenDayDu='$tendaydu', SDT='$sdt', Email='$email', DiaChi='$diachi'
            WHERE TenTaiKhoan='" . $_SESSION['TenTaiKhoan'] . "'";
    if (Database::NonQuery($sql)) {
        $message = '<p style="color: #48CFAD;">Cập nhật thông tin cá nhân thành công</p>';
    }
}

// Lấy dữ liệu người dùng
$user = Database::GetData("SELECT * FROM users WHERE TenTaiKhoan = '" . $_SESSION['TenTaiKhoan'] . "'", ['row' => 0]);

// Lấy danh sách đơn hàng
$orders = Database::GetData("SELECT * FROM dondathang WHERE TenTaiKhoan='" . $_SESSION['TenTaiKhoan'] . "' ORDER BY CreatedAt DESC");

// Lấy lịch sử đặt dịch vụ
$services = Database::GetData("SELECT * FROM datdichvu WHERE TenTaiKhoan='" . $_SESSION['TenTaiKhoan'] . "' ORDER BY NgayDat DESC");

function OrderStatusBadge($status) {
    switch ($status) {
        case 'ChuaThanhToan': return '<span class="badge bg-warning">Chưa thanh toán</span>';
        case 'DangGiaoHang': return '<span class="badge bg-info">Đang giao hàng</span>';
        case 'DaHoanThanh': return '<span class="badge bg-success">Đã hoàn thành</span>';
        case 'Huy': return '<span class="badge bg-danger">Hủy</span>';
        default: return '<span class="badge bg-secondary">Không xác định</span>';
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Thông tin cá nhân</title>
<link rel="stylesheet" href="/Salonoto/assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="profile__bg d-flex-center">
    <div class="profile__container">
        <!-- Cột 1: Avatar -->
        <div class="profile__avatar-col">
            <img class="profile__avatar" src="<?=$user['Avatar']?>" alt="Avatar">
        </div>

        <!-- Cột 2: Thông tin cá nhân -->
        <div class="profile__form">
            <div class="profile__form--header"><h3>Thông tin cá nhân</h3></div>
            <form class="profile__form--body" method="POST" enctype="multipart/form-data">
                <div class="profile__group"><b>Tên đăng nhập: </b><input type="text" value="<?=$user['TenTaiKhoan']?>" disabled></div>
                <div class="profile__group"><b>Họ tên: </b><input type="text" name="tendaydu" value="<?=$user['TenDayDu']?>"></div>
                <div class="profile__group"><b>Số điện thoại: </b><input type="text" name="sdt" value="<?=$user['SDT']?>"></div>
                <div class="profile__group"><b>Email: </b><input type="email" name="email" value="<?=$user['Email']?>"></div>
                <div class="profile__group"><b>Địa chỉ: </b><input type="text" name="diachi" value="<?=$user['DiaChi']?>"></div>
                <div class="profile__group"><b>Ảnh đại diện: </b><input type="file" name="avatar"></div>
                <div class="profile__group"><span><b>Ngày tạo tài khoản: </b> <?=date('d-m-Y', strtotime($user['CreatedAt']))?></span></div>
                <div class="profile__group d-flex-center">
                    <div>
                        <input class="btn" name="submit" type="submit" value="Cập nhật">
                        <a class="btn" href="/Salonoto/change-password.php">Đổi mật khẩu</a>
                        <a class="btn" href="/Salonoto/index.php">Trang chủ</a>
                    </div>
                </div>
            </form>
            <?php if (isset($message)) echo '<div class="profile__form--footer">'.$message.'</div>'; ?>
        </div>

        <!-- Cột 3: Lịch sử -->
        <div class="profile__history">
            <!-- Lịch sử đơn hàng -->
            <div class="profile__history-card">
                <h3>Lịch sử đơn hàng</h3>
                <div class="history-table-wrapper">
                    <?php if ($orders): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Mã đơn</th>
                                <th>Tổng tiền</th>
                                <th>Trạng thái</th>
                                <th>Ngày tạo</th>
                                <th>Công cụ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?=$order['MaDonDatHang']?></td>
                                <td><?=number_format($order['TongTien'],0,',','.')?> đ</td>
                                <td><?=OrderStatusBadge($order['TrangThai'])?></td>
                                <td><?=date('d-m-Y H:i', strtotime($order['CreatedAt']))?></td>
                                <td>
                                    <a href="print-order.php?order-id=<?=$order['MaDonDatHang']?>" class="btn btn-info" title="Xem đơn">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                        <p>Bạn chưa có đơn hàng nào.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Lịch sử dịch vụ -->
            <div class="profile__history-card">
                <h3>Lịch sử đặt dịch vụ</h3>
                <div class="history-table-wrapper">
                    <?php if ($services): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Mã dịch vụ</th>
                                <th>Xe</th>
                                <th>Biển số</th>
                                <th>Ngày đặt</th>
                                <th>Ngày hẹn</th>
                                <th>Trạng thái</th>
                                <th>Ghi chú</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($services as $svc): ?>
                            <tr>
                                <td><?=$svc['MaDatDichVu']?></td>
                                <td><?=$svc['DongXe']?></td>
                                <td><?=$svc['BienSoXe']?></td>
                                <td><?=date('d-m-Y H:i', strtotime($svc['NgayDat']))?></td>
                                <td><?=date('d-m-Y H:i', strtotime($svc['NgayHen']))?></td>
                                <td><?=$svc['TrangThai']?></td>
                                <td><?=$svc['GhiChu']?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                        <p>Bạn chưa có lịch đặt dịch vụ nào.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
