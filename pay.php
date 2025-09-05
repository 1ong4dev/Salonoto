<?php
include 'header.php';
require_once 'config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$username = isset($_SESSION['TenTaiKhoan']) ? $_SESSION['TenTaiKhoan'] : '';

if (!$username) {
    echo "<script>alert('Vui lòng đăng nhập để thanh toán!'); window.location='login.php';</script>";
    exit;
}

// Lấy thông tin user
$userInfo = Database::GetData("SELECT TenDayDu, SDT, DiaChi FROM Users WHERE TenTaiKhoan='$username'");
$user = $userInfo[0];

// Lấy giỏ hàng của user
$cartItems = Database::GetData("
    SELECT g.MaSP, g.SL, s.TenSP, s.Gia
    FROM GioHang g
    JOIN SanPham s ON g.MaSP = s.MaSP
    WHERE g.TenTaiKhoan = '$username'
");

$TongTien = 0;
foreach($cartItems as $item){
    $TongTien += $item['Gia'] * $item['SL'];
}

// Xử lý form submit
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $PhuongThucTT  = $_POST['PhuongThucTT'];
    $GhiChu        = isset($_POST['GhiChu']) ? addslashes($_POST['GhiChu']) : '';

    // Tạo mã đơn hàng duy nhất
    $MaDonDatHang = "DH" . rand(100000,999999);
    $TrangThai = 'ChuaThanhToan'; // trạng thái enum

    // 1. Lưu đơn hàng vào bảng dondathang
    $sql = "INSERT INTO dondathang (MaDonDatHang, TongTien, TrangThai, TenTaiKhoan)
            VALUES ('$MaDonDatHang', $TongTien, '$TrangThai', '$username')";
    $insertOrder = Database::NonQuery($sql);

    if ($insertOrder) {
        // 2. Lưu chi tiết đơn hàng
        foreach ($cartItems as $item) {
            $MaSP = $item['MaSP'];
            $SL   = $item['SL'];
            $sqlDetail = "INSERT INTO chitietdondathang (MaSP, MaDonDatHang, SL)
                        VALUES ($MaSP, '$MaDonDatHang', $SL)";
            Database::NonQuery($sqlDetail);
        }

        // 3. Tạo bản ghi thanh toán
        $MaGiaoDich = "GD" . rand(1000000,9999999);
        $sqlPayment = "INSERT INTO thanhtoan 
            (TenTaiKhoan, TongTien, PhuongThucTT, TrangThaiTT, MaGiaoDich, MaDonDatHang, GhiChu)
            VALUES ('$username', $TongTien, '$PhuongThucTT', 'ChoXuLy', '$MaGiaoDich', '$MaDonDatHang', '$GhiChu')";
        Database::NonQuery($sqlPayment);

        // 4. Xóa giỏ hàng
        Database::NonQuery("DELETE FROM GioHang WHERE TenTaiKhoan='$username'");

        echo "<script>alert('Đơn hàng của bạn đã được tạo và đang chờ xử lý!'); window.location='index.php';</script>";
        exit;
    } else {
        echo "<script>alert('Lỗi khi tạo đơn hàng!');</script>";
    }
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
                    <p><strong>Tên:</strong> <?php echo $user['TenDayDu']; ?></p>
                    <p><strong>SĐT:</strong> <?php echo $user['SDT']; ?></p>
                    <p><strong>Địa chỉ:</strong> <?php echo $user['DiaChi']; ?></p>
                    <div class="form-group mt-3">
                        <label>Ghi chú (tuỳ chọn)</label>
                        <textarea name="GhiChu" class="form-control" rows="3" placeholder="Nhập yêu cầu đặc biệt nếu có"></textarea>
                    </div>
                    <p><strong>Lưu ý khi thanh toán:</strong> Nếu bạn thanh bằng phương thức chuyển khoản Techcombank hoặc Momo thì vui lòng nhập đúng số tiền cần thanh toán và nội dung chuyển khoản như sau: Tên tài khoản + Ngày đặt hàng , VD : "hieu 23/8/2025" </p>
                </div>
            </div>

            <!-- Đơn hàng & phương thức thanh toán -->
            <div class="col-md-5">
                <div class="payment-card">
                    <h2>Đơn hàng của bạn</h2>
                    <ul class="list-group mb-3">
                        <?php foreach($cartItems as $item): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?php echo $item['TenSP'] . " × " . $item['SL']; ?>
                                <span><?php echo number_format($item['Gia'] * $item['SL']); ?> đ</span>
                            </li>
                        <?php endforeach; ?>
                        <li class="list-group-item d-flex justify-content-between">
                            <strong>Tổng cộng</strong>
                            <strong class="text-danger"><?php echo number_format($TongTien); ?> đ</strong>
                        </li>
                    </ul>

                    <h4>Phương thức thanh toán</h4>
                    <form method="POST">
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
                            <img src="assets/img/tech.jpg" alt="QR Techcombank" class="img-fluid rounded shadow-sm mt-2">
                        </div>

                        <!-- Thanh toán Momo -->
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="PhuongThucTT" value="Momo" id="momo">
                            <label class="form-check-label" for="momo">Thanh toán qua Momo</label>
                        </div>

                        <div id="momo-info" class="p-3 border rounded bg-light mb-3" style="display:none;">
                            <p><strong>Ngân hàng:</strong> Momo</p>
                            <p><strong>Số tài khoản:</strong> 0987654321</p>
                            <p><strong>Chủ tài khoản:</strong> NGUYEN VAN B</p>
                            <img src="assets/img/momo.jpg" alt="QR Momo" class="img-fluid rounded shadow-sm mt-2">
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
