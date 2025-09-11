<?php 
include 'header.php'; 
require_once 'config/Database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra đăng nhập
if (!isset($_SESSION['TenTaiKhoan'])) {
    echo "<script>alert('Bạn cần đăng nhập trước khi đặt lịch!'); window.location='sign.php';</script>";
    exit;
}

$username = $_SESSION['TenTaiKhoan'];

// Lấy thông tin người dùng
$userInfo = Database::GetData("SELECT TenDayDu, SDT FROM users WHERE TenTaiKhoan='$username'", ['row'=>0]);
$TenDayDu = $userInfo['TenDayDu'] ?? '';
$SDT      = $userInfo['SDT'] ?? '';

// Lấy danh sách dịch vụ
$services = Database::GetData("SELECT MaDichVu, TenDichVu, Gia FROM dichvu WHERE TrangThai='HoatDong'");

// Ngày mai để set min cho input datetime-local
$tomorrowMin = date('Y-m-d\T00:00', strtotime('+1 day'));

// Mảng liên kết dịch vụ => loại sản phẩm
$serviceToSP = [
    11 => 8,
    12 => 2,
    13 => 6,
    14 => 1
];

// Xử lý submit form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $BienSoXe    = addslashes($_POST['BienSoXe']);
    $ModelXe     = addslashes($_POST['ModelXe']);
    $NgayHen     = $_POST['NgayHen'];
    $GhiChu      = addslashes($_POST['GhiChu']);
    $MaDichVuArr = $_POST['MaDichVu'] ?? [];
    $MaSPArr     = $_POST['MaSP'] ?? [];

    $ngayHenDate = strtotime($NgayHen);
    $tomorrow    = strtotime('tomorrow');

    if ($ngayHenDate < $tomorrow) {
        echo "<script>alert('Thời gian hẹn phải ít nhất sau hôm nay 1 ngày!');</script>";
    } elseif (empty($MaDichVuArr)) {
        echo "<script>alert('Bạn phải chọn ít nhất 1 dịch vụ!');</script>";
    } else {
        // Lưu vào datdichvu và lấy ID
        $MaDatDichVu = Database::NonQueryId(
            "INSERT INTO datdichvu (TenTaiKhoan, ModelXe, BienSoXe, NgayHen, GhiChu)
             VALUES ('$username', '$ModelXe', '$BienSoXe', '$NgayHen', '$GhiChu')"
        );

        foreach ($MaDichVuArr as $MaDichVu) {
            $MaDichVu = (int)$MaDichVu;
            $GiaDV = Database::GetData("SELECT Gia FROM dichvu WHERE MaDichVu=$MaDichVu", ['row'=>0,'cell'=>'Gia']);

            // Lưu bản ghi dịch vụ
            Database::NonQuery("INSERT INTO datdichvu_chitiet (MaDatDichVu, MaDichVu, Gia, SL)
                                VALUES ($MaDatDichVu, $MaDichVu, $GiaDV, 1)");

            // Nếu dịch vụ có liên kết loại sản phẩm, lưu sản phẩm được chọn
            if (isset($serviceToSP[$MaDichVu])) {
                $MaLoaiSP = $serviceToSP[$MaDichVu];

                foreach ($MaSPArr as $MaSP) {
                    $MaSP = (int)$MaSP;
                    $spInfo = Database::GetData("SELECT Gia, MaLoaiSP FROM sanpham WHERE MaSP=$MaSP AND SL>0", ['row'=>0]);
                    if ($spInfo && $spInfo['MaLoaiSP'] == $MaLoaiSP) {
                        $GiaSP = $spInfo['Gia'];
                        Database::NonQuery("INSERT INTO datdichvu_chitiet (MaDatDichVu, MaSP, Gia, SL)
                                            VALUES ($MaDatDichVu, $MaSP, $GiaSP, 1)");
                    }
                }
            }
        }

        echo "<script>alert('Đặt lịch thành công!'); window.location='index.php';</script>";
        exit;
    }
}
?>

<style>
.booking-container { padding: 50px 0; background: #f4f4f4; font-family: Arial, sans-serif; }
.booking-form { background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border: 1px solid #e3e3e3; }
.booking-form h2 { color: #1976d2; text-align: center; margin-bottom: 25px; text-transform: uppercase; }
.form-group label { font-weight: bold; }
.input-group { display: flex; align-items: center; }
.input-group-text { background: #1976d2; color: #fff; padding: 10px 15px; border-radius: 5px 0 0 5px; }
.form-control { flex:1; border-radius:0 5px 5px 0; padding:10px; }
.btn-brand { width:100%; background:#1976d2; color:#fff; padding:12px; border:none; border-radius:5px; }
.btn-brand:hover { background:#1565c0; }
.form-check { margin-bottom:5px; }
.product-list { margin-left:20px; display:none; border:1px solid #ccc; padding:10px; border-radius:5px; max-height:200px; overflow-y:auto; }
</style>

<div class="booking-container">
    <div class="container">
        <div class="col-md-6 col-md-offset-3">
            <form method="POST" class="booking-form">
                <h2>Đặt Lịch Dịch Vụ Ô Tô</h2>

                <div class="form-group">
                    <label>Tài khoản:</label>
                    <input type="text" class="form-control" value="<?=htmlspecialchars($username)?>" disabled>
                </div>

                <div class="form-group">
                    <label>Họ và tên:</label>
                    <input type="text" class="form-control" value="<?=htmlspecialchars($TenDayDu)?>" disabled>
                </div>

                <div class="form-group">
                    <label>Số điện thoại:</label>
                    <input type="text" class="form-control" value="<?=htmlspecialchars($SDT)?>" disabled>
                </div>

                <div class="form-group">
                    <label>Biển số xe:</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa fa-car"></i></span>
                        <input type="text" name="BienSoXe" class="form-control" placeholder="VD: 51A-12345" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Model xe:</label>
                    <input type="text" name="ModelXe" class="form-control" placeholder="VD: Toyota Vios" required>
                </div>

                <div class="form-group">
                    <label>Dịch vụ:</label>
                    <?php foreach($services as $row):
                        $MaDichVu = $row['MaDichVu'];
                    ?>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input service-checkbox" 
                                   name="MaDichVu[]" value="<?=$MaDichVu?>" id="dv_<?=$MaDichVu?>">
                            <label class="form-check-label" for="dv_<?=$MaDichVu?>">
                                <?=$row['TenDichVu']?> (<?=number_format($row['Gia'],0,',','.')?> VND)
                            </label>
                        </div>

                        <?php if (isset($serviceToSP[$MaDichVu])): 
                            $MaLoaiSP = $serviceToSP[$MaDichVu];
                            $products = Database::GetData("SELECT * FROM sanpham WHERE MaLoaiSP=$MaLoaiSP AND SL>0");
                        ?>
                            <div id="products_dv_<?=$MaDichVu?>" class="product-list">
                                <?php foreach($products as $sp): ?>
                                    <div class="form-check">
                                        <input type="checkbox" name="MaSP[]" value="<?=$sp['MaSP']?>" id="sp_<?=$sp['MaSP']?>">
                                        <label for="sp_<?=$sp['MaSP']?>"><?=$sp['TenSP']?> - <?=number_format($sp['Gia'],0,',','.')?> VND (<?=$sp['SL']?>)</label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                    <?php endforeach; ?>
                </div>

                <div class="form-group">
                    <label>Thời gian hẹn:</label>
                    <input type="datetime-local" name="NgayHen" class="form-control" required min="<?=$tomorrowMin?>">
                </div>

                <div class="form-group">
                    <label>Ghi chú:</label>
                    <textarea name="GhiChu" class="form-control" rows="3" placeholder="Nhập yêu cầu đặc biệt nếu có"></textarea>
                </div>

                <button type="submit" class="btn btn-brand">Đặt lịch ngay</button>
            </form>
        </div>
    </div>
</div>

<script>
// Hiển thị SP theo dịch vụ đã chọn
document.querySelectorAll('.service-checkbox').forEach(function(cb) {
    cb.addEventListener('change', function() {
        const dvId = this.value;
        const productDiv = document.getElementById('products_dv_' + dvId);
        if (productDiv) {
            productDiv.style.display = this.checked ? 'block' : 'none';
        }
    });
});

// Validate ngày hẹn
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
