<?php
include 'header.php'; // header đã include session_start() và database.php

if (!isset($_SESSION['TenTaiKhoan'])) {
    echo "<script>alert('Vui lòng đăng nhập để thanh toán!'); window.location='sign.php';</script>";
    exit;
}

$username = $_SESSION['TenTaiKhoan'];

// Lấy MaDonDatHang từ GET
$MaDonDatHang = isset($_GET['MaDonDatHang']) ? $_GET['MaDonDatHang'] : '';
if (!$MaDonDatHang) {
    echo "<script>alert('Không tìm thấy đơn hàng!'); window.location='index.php';</script>";
    exit;
}

// Lấy thông tin đơn hàng
$orderInfo = Database::GetData("SELECT * FROM dondathang WHERE MaDonDatHang='$MaDonDatHang' AND TenTaiKhoan='$username'");
if (!$orderInfo) {
    echo "<script>alert('Đơn hàng không tồn tại!'); window.location='index.php';</script>";
    exit;
}
$order = $orderInfo[0];

// Lấy thông tin user
$userInfo = Database::GetData("SELECT TenDayDu, SDT, DiaChi FROM Users WHERE TenTaiKhoan='$username'");
$user = $userInfo[0];

// Lấy chi tiết đơn hàng
$cartItems = Database::GetData("
    SELECT c.MaSP, c.SL, s.TenSP, IFNULL(s.GiaKhuyenMai,s.Gia) AS Gia, s.ThoiGianBaoHanh
    FROM chitietdondathang c
    JOIN SanPham s ON c.MaSP = s.MaSP
    WHERE c.MaDonDatHang='$MaDonDatHang'
");

// Tính tổng tiền từ chi tiết
$TongTien = 0;
foreach($cartItems as $item){
    $TongTien += $item['Gia'] * $item['SL'];
}

// Lấy mã giảm giá và giá trị
$MaGiamGia = $order['MaGiamGia'] ?? '';
$GiamGia = $order['GiamGia'] ?? 0;
$TongTienSauGiam = max(0, $TongTien - $GiamGia);

// Xử lý form submit (cập nhật phương thức thanh toán)
$errorMsg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $GhiChu = isset($_POST['GhiChu']) ? addslashes($_POST['GhiChu']) : '';
    $PhuongThucTT = isset($_POST['PhuongThucTT']) ? $_POST['PhuongThucTT'] : 'COD';

    // Cập nhật đơn và tạo bản ghi thanh toán
    Database::NonQuery("UPDATE dondathang SET GhiChu='$GhiChu' WHERE MaDonDatHang='$MaDonDatHang'");
    
    $MaGiaoDich = "GD" . rand(1000000,9999999);
    Database::NonQuery("INSERT INTO thanhtoan 
        (MaDonDatHang, TenTaiKhoan, TongTien, PhuongThucTT, GhiChu, MaGiaoDich)
        VALUES ($MaDonDatHang, '$username', $TongTienSauGiam, '$PhuongThucTT', '$GhiChu', '$MaGiaoDich')");

    echo "<script>alert('Đơn hàng của bạn đã được tạo và đang chờ xử lý!'); window.location='index.php';</script>";
    exit;
}
?>

<div class="product-big-title-area">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="product-bit-title text-center">
                    <h2>Thanh toán</h2>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Giữ nguyên style cũ từ version trước */
body { font-family: Arial, Helvetica, sans-serif; }
.payment-container { padding: 50px 0; background: #f4f4f4; }
.payment-card { background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border: 1px solid #e3e3e3; }
.payment-card h2, .payment-card h4, .payment-card h5 { color: #1976d2; margin-bottom: 20px; }
.btn-brand { background-color: #1976d2; border: none; color: #fff; font-weight: bold; font-size: 16px; padding: 12px; border-radius: 5px; transition: 0.3s; width: 100%; }
.btn-brand:hover { background-color: #1565c0; }
.form-check-label { cursor: pointer; }
.form-group label { font-weight: bold; }
</style>

<div class="payment-container">
    <div class="container">
        <div class="row">

            <!-- Thông tin người đặt -->
            <div class="col-md-7">
                <div class="payment-card mb-4">
                    <h2>Thông tin người đặt</h2>
                    <p><strong>Tên:</strong> <?= htmlspecialchars($user['TenDayDu']) ?></p>
                    <p><strong>SĐT:</strong> <?= htmlspecialchars($user['SDT']) ?></p>
                    <p><strong>Địa chỉ:</strong> <?= htmlspecialchars($user['DiaChi']) ?></p>
                    <div class="form-group mt-3">
                        <label>Ghi chú (tuỳ chọn)</label>
                        <textarea name="GhiChu" form="paymentForm" class="form-control" rows="3" placeholder="Nhập yêu cầu đặc biệt nếu có"><?= htmlspecialchars($order['GhiChu'] ?? '') ?></textarea>
                    </div>
                    <p><strong>Lưu ý khi thanh toán:</strong> Nếu bạn thanh toán bằng chuyển khoản Techcombank hoặc Momo, hãy nhập đúng số tiền và nội dung chuyển khoản với cú pháp : </p>
                    <p><strong> Tên - số điện thoại - ngày thanh toán</strong> (VD: Nguyễn Văn A - 0123456789 - 10/9).</p>
                </div>
            </div>

            <!-- Đơn hàng & phương thức thanh toán -->
            <div class="col-md-5">
                <div class="payment-card">
                   <h2>Đơn hàng của bạn</h2>

                        <ul class="list-group mb-3">
                            <?php foreach($cartItems as $item): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?= htmlspecialchars($item['TenSP'] . " × " . $item['SL']) ?>
                                    <span><?= number_format($item['Gia'] * $item['SL']) ?> đ</span>
                                </li>
                            <?php endforeach; ?>
                            <?php if($MaGiamGia): ?>
                                <li class="list-group-item d-flex justify-content-between">
                                    <strong>Mã giảm giá (<?= htmlspecialchars($MaGiamGia) ?>)</strong>
                                    <strong class="text-success">- <?= number_format($GiamGia) ?> đ</strong>
                                </li>
                            <?php endif; ?>
                            <li class="list-group-item d-flex justify-content-between">
                                <strong>Tổng cộng</strong>
                                <strong class="text-danger"><?= number_format($TongTienSauGiam) ?> đ</strong>
                            </li>
                        </ul>

                        <form method="POST" id="paymentForm">
                            <h4>Phương thức thanh toán</h4>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="PhuongThucTT" value="COD" checked>
                                <label class="form-check-label">Thanh toán khi nhận hàng (COD)</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="PhuongThucTT" value="ChuyenKhoan" id="bank">
                                <label class="form-check-label" for="bank">Chuyển khoản ngân hàng</label>
                            </div>
                            <div id="bank-info" class="p-3 border rounded bg-light mb-3" style="display:none;">
                                <p><strong>Ngân hàng:</strong> Techcombank</p>
                                <p><strong>Số tài khoản:</strong> 0123456789</p>
                                <p><strong>Chủ tài khoản:</strong> NGUYEN VAN A</p>
                                <img src="assets/img/tech.jpg" alt="QR Bank" style="width:500px; margin-top:10px;">
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="PhuongThucTT" value="Momo" id="momo">
                                <label class="form-check-label" for="momo">Thanh toán qua Momo</label>
                            </div>
                            <div id="momo-info" class="p-3 border rounded bg-light mb-3" style="display:none;">
                                <p><strong>Ngân hàng:</strong> Momo</p>
                                <p><strong>Số tài khoản:</strong> 0987654321</p>
                                <p><strong>Chủ tài khoản:</strong> NGUYEN VAN B</p>
                                <img src="assets/img/momo.jpg" alt="QR Bank" style="width:500px; margin-top:10px;">
                            </div>

                            <button type="submit" class="btn btn-brand btn-lg w-100">Xác nhận thanh toán</button>
                        </form>

                </div>
            </div>

        </div>
    </div>
</div>

<script>
document.querySelectorAll('input[name="PhuongThucTT"]').forEach(radio => {
    radio.addEventListener('change', function() {
        let bankInfo = document.getElementById('bank-info');
        let momoInfo = document.getElementById('momo-info');

        if (document.getElementById('bank').checked) {
            bankInfo.style.display = 'block';
            momoInfo.style.display = 'none';
        } else if (document.getElementById('momo').checked) {
            bankInfo.style.display = 'none';
            momoInfo.style.display = 'block';
        } else {
            bankInfo.style.display = 'none';
            momoInfo.style.display = 'none';
        }
    });
});
</script>

<?php include 'footer.php'; ?>
