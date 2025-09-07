<?php 
include 'header.php'; 
require_once 'config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Lấy username từ session
$username = isset($_SESSION['TenTaiKhoan']) ? $_SESSION['TenTaiKhoan'] : '';

// Lấy thông tin người dùng (Họ tên và SDT) từ bảng users
$userInfo = Database::GetData("SELECT TenDayDu, SDT FROM users WHERE TenTaiKhoan='$username'", ['row'=>0]);
$TenDayDu = $userInfo['TenDayDu'] ?? '';
$SDT      = $userInfo['SDT'] ?? '';

// Xử lý khi submit form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $BienSoXe = addslashes($_POST['BienSoXe']);
    $DongXe   = addslashes($_POST['DongXe']);
    $MaDichVu = (int)$_POST['MaDichVu'];
    $NgayHen  = $_POST['NgayHen'];
    $GhiChu   = addslashes($_POST['GhiChu']);

    // --- Kiểm tra ngày hẹn ---
    $ngayHenDate = strtotime($NgayHen);
    $tomorrow    = strtotime('tomorrow');

    if ($ngayHenDate < $tomorrow) {
        echo "<script>alert('Thời gian hẹn phải ít nhất sau hôm nay 1 ngày!');</script>";
    } else {
        // Lưu thông tin vào bảng DatDichVu
        $sql = "INSERT INTO DatDichVu (TenTaiKhoan, MaDichVu, DongXe, BienSoXe, NgayHen, GhiChu) 
                VALUES ('$username', $MaDichVu, '$DongXe', '$BienSoXe', '$NgayHen', '$GhiChu')";

        if (Database::NonQuery($sql)) {
            echo "<script>alert('Đặt lịch thành công!'); window.location='index.php';</script>";
            exit;
        } else {
            echo "<script>alert('Lỗi khi đặt lịch!');</script>";
        }
    }
}

// Lấy danh sách dịch vụ
$result_dv = Database::GetData("SELECT MaDichVu, TenDichVu FROM DichVu");

// Ngày mai để set min cho input datetime-local
$tomorrowMin = date('Y-m-d\T00:00', strtotime('+1 day'));
?>

<style>
body { font-family: Arial, Helvetica, sans-serif; }
.booking-container { padding: 50px 0; background: #f4f4f4; }
.booking-form { background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border: 1px solid #e3e3e3; }
.booking-form h2 { color: #1976d2; text-transform: uppercase; font-weight: bold; margin-bottom: 25px; text-align: center; }
.form-group label { font-weight: bold; color: #333; }
.input-group { display: flex; align-items: center; }
.input-group-text { background: #1976d2; color: #fff; border: none; padding: 10px 15px; border-radius: 5px 0 0 5px; }
.form-control { border-radius: 0 5px 5px 0; font-size: 15px; flex: 1; }
.form-control:focus { border-color: #1976d2; box-shadow: 0 0 5px rgba(25,118,210,0.5); }
.btn-brand { background-color: #1976d2; border: none; color: #fff; font-weight: bold; font-size: 16px; padding: 12px; border-radius: 5px; transition: 0.3s; width: 100%; }
.btn-brand:hover { background-color: #1565c0; }
</style>

<div class="booking-container">
    <div class="container">
        <div class="col-md-6 col-md-offset-3">
            <form method="POST" class="booking-form">
                <h2>Đặt Lịch Dịch Vụ Ô Tô</h2>

                <div class="form-group">
                    <label>Tài khoản đặt:</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($username); ?>" disabled>
                </div>

                <div class="form-group">
                    <label>Họ và tên:</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($TenDayDu); ?>" disabled>
                </div>

                <div class="form-group">
                    <label>Số điện thoại:</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($SDT); ?>" disabled>
                </div>

                <div class="form-group">
                    <label>Biển số xe:</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa fa-car"></i></span>
                        <input type="text" name="BienSoXe" class="form-control" placeholder="VD: 51A-12345" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Loại xe:</label>
                    <select name="DongXe" class="form-control" required>
                        <option value="">-- Chọn loại xe --</option>
                        <option value="Sedan">Xe Mini</option>
                        <option value="SUV">Xe Sedan</option>
                        <option value="Bán tải">Xe SUV</option>
                        <option value="Van">Dòng Luxury</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Dịch vụ:</label>
                    <select name="MaDichVu" class="form-control" required>
                        <option value="">-- Chọn dịch vụ --</option>
                        <?php foreach($result_dv as $row): ?>
                            <option value="<?php echo $row['MaDichVu']; ?>">
                                <?php echo htmlspecialchars($row['TenDichVu']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Thời gian hẹn:</label>
                    <!-- chỉ thêm min, không đổi thẻ -->
                    <input type="datetime-local" name="NgayHen" class="form-control" required min="<?=$tomorrowMin?>">
                </div>

                <div class="form-group">
                    <label>Ghi chú thêm:</label>
                    <textarea name="GhiChu" class="form-control" rows="3" placeholder="Nhập yêu cầu đặc biệt nếu có"></textarea>
                </div>

                <button type="submit" class="btn btn-brand">
                    <i class="fa fa-calendar-check-o"></i> Đặt lịch ngay
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Thêm validate JS -->
<script>
document.querySelector('form.booking-form').addEventListener('submit', function(e) {
    const input = document.querySelector('input[name="NgayHen"]');
    const selected = new Date(input.value);
    const tomorrow = new Date();
    tomorrow.setHours(0,0,0,0);
    tomorrow.setDate(tomorrow.getDate() + 1);

    if (selected < tomorrow) {
        e.preventDefault();
        alert("Bạn phải đặt lịch ít nhất sau hôm nay 1 ngày!");
    }
});
</script>

<?php include 'footer.php'; ?>
