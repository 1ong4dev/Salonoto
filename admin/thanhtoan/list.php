<?php include '../header.php'?>

<?php
// Xử lý xác nhận hoặc hủy thanh toán
if (isset($_GET['action']) && isset($_GET['MaGiaoDich'])) {
    $MaGiaoDich = $_GET['MaGiaoDich'];
    $action = $_GET['action'];

    // Lấy MaDonDatHang từ thanhtoan
    $payment = Database::GetData("SELECT MaDonDatHang FROM thanhtoan WHERE MaGiaoDich='$MaGiaoDich'", ['row'=>0]);
    if ($payment) {
        $MaDonDatHang = $payment['MaDonDatHang'];

        if ($action == 'confirm') {
            // Thanh toán hoàn tất
            Database::NonQuery("UPDATE thanhtoan SET TrangThaiTT='HoanTat', UpdatedAt=NOW() WHERE MaGiaoDich='$MaGiaoDich'");
            Database::NonQuery("UPDATE dondathang SET TrangThai='DangGiaoHang' WHERE MaDonDatHang='$MaDonDatHang'");

            // Trừ số lượng trong kho
            $chiTiet = Database::GetData("SELECT MaSP, SL FROM chitietdondathang WHERE MaDonDatHang='$MaDonDatHang'");
            if ($chiTiet) {
                foreach ($chiTiet as $item) {
                    $MaSP = $item['MaSP'];
                    $SL   = $item['SL'];
                    Database::NonQuery("UPDATE kho SET SLTon = SLTon - $SL WHERE MaSP = $MaSP");
                }
            }
        }
        elseif ($action == 'cancel') {
            // Thanh toán hủy
            Database::NonQuery("UPDATE thanhtoan SET TrangThaiTT='Huy', UpdatedAt=NOW() WHERE MaGiaoDich='$MaGiaoDich'");
            Database::NonQuery("UPDATE dondathang SET TrangThai='Huy' WHERE MaDonDatHang='$MaDonDatHang'");
        }
    }
}

// Hàm hiển thị badge trạng thái thanh toán
function PaymentBadgeTT($status) {
    switch($status){
        case 'ChoXuLy':
            return '<span class="badge bg-warning">Chờ xử lý</span>';
        case 'HoanTat':
            return '<span class="badge bg-success">Hoàn tất</span>';
        case 'Huy':
            return '<span class="badge bg-danger">Hủy</span>';
        default:
            return '<span class="badge bg-secondary">Không xác định</span>';
    }
}
?>

<?php include '../sidebar.php'?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Thanh toán</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?=ADMIN_URL?>/"><i class="fas fa-home"></i></a></li>
                        <li class="breadcrumb-item active">Thanh toán</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <?php include '../alert.php'?>
        <div class="container-fluid">
            <!-- Tìm kiếm -->
            <div class="row my-2 d-flex-end">
                <form method="GET">
                    <div class="input-group">
                        <input type="text" name="keyword" placeholder="Từ khoá" class="form-control">
                        <div class="input-group-append">
                            <button class="btn btn-outline-info"><i class="fas fa-search"></i></button>
                        </div>
                    </div>
                </form>
            </div>

          <!-- Bảng thanh toán 123 -->
<div class="row my-2">
    <div class="card w-100">
        <div class="card-body">
            <table class="table table-hover table-bordered">
                <thead class="table-warning">
                    <tr>
                        <th>Mã TT</th>
                        <th>Giao dịch</th>
                        <th>Người dùng</th>
                        <th>Tổng tiền</th>
                        <th>Mã giảm giá</th>
                        <th>Giảm giá</th>
                        <th>Phương thức TT</th>
                        <th>Trạng thái</th>
                        <th>Ngày TT</th>
                        <th>Ngày cập nhật</th>
                        <th>Ghi chú</th>
                        <th>Công cụ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $page = isset($_GET['page']) ? $_GET['page'] : 1;
                    $pager = (new Pagination())->get('thanhtoan', $page, ROW_OF_PAGE);

                    $keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
                    $where = '';
                    if ($keyword) {
                        $where = "WHERE t.MaGiaoDich LIKE '%$keyword%' OR t.TenTaiKhoan LIKE '%$keyword%'";
                    }

                    $sql = "
                        SELECT t.*, u.TenDayDu
                        FROM thanhtoan t
                        JOIN users u ON t.TenTaiKhoan = u.TenTaiKhoan
                        $where
                        ORDER BY t.NgayTT DESC
                        LIMIT " . $pager['StartIndex'] . ", " . ROW_OF_PAGE;
                    
                    $payments = Database::GetData($sql);

                    if ($payments) {
                        foreach ($payments as $payment) {
                            echo '<tr>
                                <td>' . $payment['MaTT'] . '</td>
                                <td>' . $payment['MaGiaoDich'] . '</td>
                                <td>' . $payment['TenDayDu'] . '</td>
                                <td>' . Helper::Currency($payment['TongTien']) . '</td>
                                <td>' . ($payment['MaGiamGia'] ?? '-') . '</td>
                                <td>' . ($payment['GiamGia'] ? Helper::Currency($payment['GiamGia']) : '0 đ') . '</td>
                                <td>' . $payment['PhuongThucTT'] . '</td>
                                <td>' . PaymentBadgeTT($payment['TrangThaiTT']) . '</td>
                                <td>' . Helper::DateTime($payment['NgayTT']) . '</td>
                                <td>' . Helper::DateTime($payment['UpdatedAt']) . '</td>
                                <td>' . $payment['GhiChu'] . '</td>
                                <td>';
                            if ($payment['TrangThaiTT'] == 'ChoXuLy') {
                                echo '<a href="?action=confirm&MaGiaoDich=' . $payment['MaGiaoDich'] . '" class="btn btn-success btn-sm"><i class="fas fa-check"></i></a> ';
                                echo '<a href="?action=cancel&MaGiaoDich=' . $payment['MaGiaoDich'] . '" class="btn btn-danger btn-sm"><i class="fas fa-times"></i></a>';
                            } else {
                                echo '<span class="text-muted">Đã xử lý</span>';
                            }
                            echo '</td></tr>';
                        }
                    } else {
                        echo '<tr><td colspan="100%" class="text-center">Không có dữ liệu</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


            <!-- Phân trang -->
            <div class="row my-2 d-flex-between">
                <div>Hiển thị từ <?=$pager['StartPage']?> đến <?=$pager['EndPage']?> của <?=$pager['TotalItems']?> bản ghi</div>
                <ul class="pagination">
                    <?php
                    for ($i = 1; $i <= $pager['TotalPages']; $i++) {
                        $active = $page == $i ? 'active' : '';
                        echo '<li class="page-item ' . $active . '">
                            <a class="page-link" href="?page=' . $i . '">' . $i . '</a>
                        </li>';
                    }
                    ?>
                </ul>
            </div>
        </div>
    </section>
</div>

<?php include '../footer.php'?>
