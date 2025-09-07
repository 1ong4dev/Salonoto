<?php 
include 'config/config.php';
include 'config/Database.php';
session_start();

if (!isset($_SESSION['TenTaiKhoan'])) {
    header("Location: login.php");
    exit;
}

// Lấy dữ liệu người dùng
$user = Database::GetData("SELECT * FROM users WHERE TenTaiKhoan = '" . $_SESSION['TenTaiKhoan'] . "'", ['row' => 0]);

// Lấy danh sách đơn hàng
$orders = Database::GetData("
    SELECT * FROM dondathang 
    WHERE TenTaiKhoan='" . $_SESSION['TenTaiKhoan'] . "' 
    ORDER BY CreatedAt DESC
");

// Lấy chi tiết đơn hàng cho mỗi đơn
foreach ($orders as &$order) {
    $orderId = $order['MaDonDatHang'];
    $order['ChiTiet'] = Database::GetData("
        SELECT c.MaSP, c.SL, s.TenSP, s.Gia 
        FROM chitietdondathang c
        INNER JOIN sanpham s ON c.MaSP = s.MaSP
        WHERE c.MaDonDatHang = '$orderId'
    ");
}

// Lấy lịch sử dịch vụ
$services = Database::GetData("SELECT * FROM datdichvu WHERE TenTaiKhoan='" . $_SESSION['TenTaiKhoan'] . "' ORDER BY NgayDat DESC");

function OrderStatusBadge($status) {
    switch ($status) {
        case 'ChoXuLy': return '<span class="badge bg-warning">Chờ xử lý</span>';
        case 'DangGiaoHang': return '<span class="badge bg-info">Đang giao hàng</span>';
        case 'DaHoanThanh': return '<span class="badge bg-success">Đã hoàn thành</span>';
        case 'Huy': return '<span class="badge bg-danger">Hủy</span>';
        case 'HoanHang': return '<span class="badge bg-info">Hoàn hàng</span>';
        default: return '<span class="badge bg-secondary">Không xác định</span>';
    }
}

function ServiceStatusBadge($status) {
    switch ($status) {
        case 'ChoXuLy': return '<span class="badge bg-warning">Chờ xử lý</span>';
        case 'XacNhan': return '<span class="badge bg-info">Xác nhận</span>';
        case 'DaHoanThanh': return '<span class="badge bg-success">Đã hoàn thành</span>';
        case 'Huy': return '<span class="badge bg-danger">Hủy</span>';
        case 'TuChoi': return '<span class="badge bg-danger">Từ chối</span>';
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
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
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
    </div>

    <!-- Cột 3: Lịch sử -->
    <div class="profile__history">

        <!-- Lịch sử đơn hàng -->
        <div class="profile__history-card">
            <h3>Lịch sử đơn hàng</h3>
            <div class="history-table-wrapper">
                <?php if ($orders): ?>
                <table class="table table-bordered table-hover">
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
                        <!-- Nút xem chi tiết luôn hiển thị -->
                        <button class="btn btn-info btn-sm" title="Xem chi tiết"
                            onclick="showOrderDetail('<?=$order['MaDonDatHang']?>')">
                            <i class="fas fa-eye"></i>
                        </button>

                        <!-- Nút in chỉ hiển thị khi đơn đã hoàn thành -->
                        <?php if ($order['TrangThai'] == 'DaHoanThanh'): ?>
                            <a href="print-order.php?order-id=<?=$order['MaDonDatHang']?>" 
                            class="btn btn-success btn-sm" title="In đơn">
                                <i class="fas fa-print"></i>
                            </a>
                        <?php endif; ?>
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

        <!-- Lịch sử dịch vụ (giữ nguyên) -->
        <div class="profile__history-card">
            <h3>Lịch sử đặt dịch vụ</h3>
            <div class="history-table-wrapper">
                <?php if ($services): ?>
                <table class="table table-bordered table-hover">
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
                            <td><?=ServiceStatusBadge($svc['TrangThai'])?></td>
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

<!-- Modal chi tiết hóa đơn -->
<div class="modal fade" id="modal-order-detail" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title">Chi tiết hóa đơn</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Đóng">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="order-detail-body">
                <!-- Nội dung chi tiết sẽ được JS populate -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<script>
const orders = <?=json_encode($orders)?>;

function showOrderDetail(orderId) {
    const order = orders.find(o => o.MaDonDatHang == orderId);
    if (!order) return;

    let html = `<p><b>Mã đơn:</b> ${order.MaDonDatHang}</p>`;
    html += `<p><b>Tổng tiền:</b> ${parseFloat(order.TongTien).toLocaleString()} đ</p>`;

    html += `<table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Số lượng</th>
                        <th>Giá</th>
                        <th>Mã Giảm Giá</th>
                        <th>Giảm Giá</th>
                    </tr>
                </thead>
                <tbody>`;
    order.ChiTiet.forEach(item => {
        html += `<tr>
                    <td>${item.TenSP}</td>
                    <td>${item.SL}</td>
                    <td>${parseFloat(item.Gia).toLocaleString()} đ</td>
                    <td></td>
                    <td></td>
                 </tr>`;
    });

    // thêm dòng hiển thị mã giảm giá
    if (order.MaGiamGia) {
        html += `<tr>
                    <td colspan="3" class="text-end"><b>Khuyến mãi</b></td>
                    <td>${order.MaGiamGia}</td>
                    <td>- ${parseFloat(order.GiamGia).toLocaleString()} đ</td>
                 </tr>`;
    }

    html += `</tbody></table>`;

    document.getElementById('order-detail-body').innerHTML = html;
    $('#modal-order-detail').modal('show');
}


</script>

</body>
</html> 