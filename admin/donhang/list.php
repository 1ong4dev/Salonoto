<?php include '../header.php'?>

<?php
// Xử lý Hoàn thành
if (isset($_GET['complete']) && isset($_GET['MaDonDatHang'])) {
    $MaDonDatHang = $_GET['MaDonDatHang'];

    Database::NonQuery("UPDATE dondathang SET TrangThai='DaHoanThanh' WHERE MaDonDatHang='$MaDonDatHang'");
}

// Xử lý Hoàn hàng
if (isset($_GET['return']) && isset($_GET['MaDonDatHang'])) {
    $MaDonDatHang = $_GET['MaDonDatHang'];

    // 1. Cập nhật trạng thái
    Database::NonQuery("UPDATE dondathang SET TrangThai='HoanHang' WHERE MaDonDatHang='$MaDonDatHang'");

    // 2. Lấy chi tiết đơn hàng
    $chiTiet = Database::GetData("SELECT MaSP, SL FROM chitietdondathang WHERE MaDonDatHang='$MaDonDatHang'");

    // 3. Cộng lại SLTon trong kho
    foreach ($chiTiet as $item) {
        $MaSP = $item['MaSP'];
        $SL = $item['SL'];

        Database::NonQuery("UPDATE kho SET SLTon = SLTon + $SL WHERE MaSP = $MaSP");
    }
}

// Hàm hiển thị badge trạng thái 123
function OrderStatusBadge($status) {
    switch ($status) {
        case 'ChoXuLy':
            return '<span class="badge bg-warning">Chờ xử lý</span>';
        case 'DangGiaoHang':
            return '<span class="badge bg-info">Đang giao hàng</span>';
        case 'DaHoanThanh':
            return '<span class="badge bg-success">Đã hoàn thành</span>';
        case 'Huy':
            return '<span class="badge bg-danger">Hủy</span>';
        case 'HoanHang':
            return '<span class="badge bg-secondary">Hoàn hàng</span>';
        default:
            return '<span class="badge bg-dark">Không xác định</span>';
    }
}
?>

<?php include '../sidebar.php'?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1 class="m-0">Đơn hàng</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?=ADMIN_URL?>/"><i class="fas fa-home"></i></a></li>
                        <li class="breadcrumb-item active">Đơn hàng</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <?php include '../alert.php'?>

        <div class="container-fluid">
    <div class="row my-2">
        <div class="card w-100">
            <div class="card-body">
                <table class="table table-hover table-bordered">
                    <thead class="table-warning">
                        <tr>
                            <th>Mã đơn hàng</th>
                            <th>Tổng tiền</th>
                            <th>Mã giảm giá</th>
                            <th>Giảm giá</th>
                            <th>Trạng thái</th>
                            <th>Ngày tạo</th>
                            <th>Người dùng</th>
                            <th width="225">Công cụ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $page = isset($_GET['page']) ? $_GET['page'] : 1;
                        $pager = (new Pagination())->get('dondathang', $page, ROW_OF_PAGE);

                        $keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
                        $where = '';
                        if ($keyword) {
                            $where = "WHERE MaDonDatHang LIKE '%$keyword%' OR TenTaiKhoan LIKE '%$keyword%'";
                        }

                        $sql = "SELECT d.*, u.TenDayDu 
                                FROM dondathang d
                                JOIN users u ON d.TenTaiKhoan = u.TenTaiKhoan
                                $where
                                ORDER BY CreatedAt DESC
                                LIMIT " . $pager['StartIndex'] . ", " . ROW_OF_PAGE;

                        $orders = Database::GetData($sql);

                        if ($orders) {
                            foreach ($orders as $order) {
                                $btns = '<a href="print-order.php?order-id=' . $order['MaDonDatHang'] . '" class="btn btn-info" title="Xem đơn"><i class="fas fa-eye"></i></a>';

                                if ($order['TrangThai'] == 'DangGiaoHang') {
                                    $btns .= ' <a href="?complete=1&MaDonDatHang=' . $order['MaDonDatHang'] . '" class="btn btn-primary" title="Hoàn thành"><i class="fas fa-check"></i></a>';
                                    $btns .= ' <a href="?return=1&MaDonDatHang=' . $order['MaDonDatHang'] . '" class="btn btn-warning" title="Hoàn hàng"><i class="fas fa-undo"></i></a>';
                                }

                                echo '<tr>
                                    <th>' . $order['MaDonDatHang'] . '</th>
                                    <td>' . Helper::Currency($order['TongTien']) . '</td>
                                    <td>' . ($order['MaGiamGia'] ?? '-') . '</td>
                                    <td>' . ($order['GiamGia'] ? Helper::Currency($order['GiamGia']) : '0 đ') . '</td>
                                    <td>' . OrderStatusBadge($order['TrangThai']) . '</td>
                                    <td>' . Helper::DateTime($order['CreatedAt']) . '</td>
                                    <td>' . $order['TenDayDu'] . '</td>
                                    <td>' . $btns . '</td>
                                </tr>';
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
</div>

    </section>
</div>
<?php include '../footer.php'?>
