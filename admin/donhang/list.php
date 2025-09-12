<?php include '../header.php'?>
<?php

// ==========================
// XỬ LÝ HOÀN THÀNH ĐƠN HÀNG
// ==========================
if (isset($_GET['complete']) && isset($_GET['MaDonDatHang'])) {
    $MaDonDatHang = $_GET['MaDonDatHang'];

    $connect = Database::BeginTransaction();
    try {
        Database::NonQueryTrans($connect, "UPDATE dondathang SET TrangThai='DaHoanThanh' WHERE MaDonDatHang='$MaDonDatHang'");
        
        $checkHD = Database::GetData("SELECT MaHoaDon FROM hoadon WHERE MaDonDatHang='$MaDonDatHang'", ['row'=>0]);

        if (!$checkHD) {
            $order = Database::GetData("SELECT * FROM dondathang WHERE MaDonDatHang='$MaDonDatHang'", ['row'=>0]);
            if ($order) {
                $TongTien = $order['TongTien'];
                $GhiChu = $order['GhiChu'] ?? null;

                $sqlHD = "INSERT INTO hoadon (MaDonDatHang, TongTien, GhiChu) VALUES ('$MaDonDatHang', '$TongTien', " . ($GhiChu ? "'$GhiChu'" : "NULL") . ")";
                $MaHoaDon = Database::NonQueryIdTrans($connect, $sqlHD);

                $chiTiet = Database::GetData("
                    SELECT ctdh.*, sp.TenSP
                    FROM chitietdondathang ctdh
                    JOIN sanpham sp ON ctdh.MaSP = sp.MaSP
                    WHERE ctdh.MaDonDatHang='$MaDonDatHang'
                ");

                foreach ($chiTiet as $item) {
                    $MaSP = $item['MaSP'];
                    $TenSP = addslashes($item['TenSP']);
                    $SL = $item['SL'];
                    $Gia = $item['DonGia']; // ✅ lấy giá đã lưu trong chi tiết đơn hàng
                    $NgayBatDauBH = $item['NgayBatDauBH'] ? "'" . $item['NgayBatDauBH'] . "'" : "NULL";
                    $NgayKetThucBH = $item['NgayKetThucBH'] ? "'" . $item['NgayKetThucBH'] . "'" : "NULL";

                    $sqlCTHD = "
                        INSERT INTO chitiethoadon (MaHoaDon, MaSP, TenSP, SL, Gia, NgayBatDauBH, NgayKetThucBH)
                        VALUES ('$MaHoaDon', '$MaSP', '$TenSP', '$SL', '$Gia', $NgayBatDauBH, $NgayKetThucBH)
                    ";
                    Database::NonQueryTrans($connect, $sqlCTHD);
                }
            }
        }

        Database::Commit($connect);

    } catch(Exception $e) {
        Database::Rollback($connect);
        echo "<script>alert('Lỗi khi tạo hóa đơn: ".$e->getMessage()."');</script>";
    }
}

// ==========================
// XỬ LÝ HOÀN HÀNG
// ==========================
if (isset($_GET['return']) && isset($_GET['MaDonDatHang'])) {
    $MaDonDatHang = $_GET['MaDonDatHang'];

    $connect = Database::BeginTransaction();
    try {
        Database::NonQueryTrans($connect, "UPDATE dondathang SET TrangThai='HoanHang' WHERE MaDonDatHang='$MaDonDatHang'");

        $chiTiet = Database::GetData("SELECT MaSP, SL FROM chitietdondathang WHERE MaDonDatHang='$MaDonDatHang'");
        foreach ($chiTiet as $item) {
            $MaSP = $item['MaSP'];
            $SL = $item['SL'];
            Database::NonQueryTrans($connect, "UPDATE sanpham SET SL = SL + $SL WHERE MaSP = $MaSP");
        }

        Database::Commit($connect);

    } catch(Exception $e) {
        Database::Rollback($connect);
        echo "<script>alert('Lỗi khi hoàn hàng: ".$e->getMessage()."');</script>";
    }
}

// ==========================
// HÀM HIỂN THỊ BADGE TRẠNG THÁI
// ==========================
function OrderStatusBadge($status) {
    switch ($status) {
        case 'ChoXuLy': return '<span class="badge bg-warning">Chờ xử lý</span>';
        case 'DangGiaoHang': return '<span class="badge bg-info">Đang giao hàng</span>';
        case 'DaHoanThanh': return '<span class="badge bg-success">Đã hoàn thành</span>';
        case 'Huy': return '<span class="badge bg-danger">Hủy</span>';
        case 'HoanHang': return '<span class="badge bg-secondary">Hoàn hàng</span>';
        default: return '<span class="badge bg-dark">Không xác định</span>';
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
                    <div class="col-12 d-flex justify-content-end">
                        <form method="GET" class="d-flex">
                            <div class="input-group">
                                <input type="text" name="keyword" placeholder="Từ khoá" 
                                    value="<?=htmlspecialchars($_GET['keyword'] ?? '')?>" 
                                    class="form-control">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-info" type="submit"><i class="fas fa-search"></i></button>
                                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modal-add">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>



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
                                $page = $_GET['page'] ?? 1;
                                $pager = (new Pagination())->get('dondathang', $page, ROW_OF_PAGE);

                                $keyword = $_GET['keyword'] ?? '';
                                $where = '';
                                if ($keyword) {
                                    $keyword = addslashes($keyword); // chống ký tự đặc biệt
                                    $where = "WHERE d.MaDonDatHang LIKE '%$keyword%' OR d.TenTaiKhoan LIKE '%$keyword%'";
                                }

                                $sql = "SELECT d.*, u.TenDayDu 
                                        FROM dondathang d
                                        JOIN users u ON d.TenTaiKhoan = u.TenTaiKhoan
                                        $where
                                        ORDER BY d.CreatedAt DESC
                                        LIMIT " . $pager['StartIndex'] . ", " . ROW_OF_PAGE;

                                $orders = Database::GetData($sql);

                                if ($orders) {
                                    foreach ($orders as $order) {
                                        $btns = '';

                                        // Icon máy in chỉ khi đơn hoàn thành
                                        if ($order['TrangThai'] == 'DaHoanThanh') {
                                            $btns .= '<a href="print-order.php?order-id=' . $order['MaDonDatHang'] . '" class="btn btn-info" title="In hóa đơn"><i class="fas fa-print"></i></a>';
                                        }

                                        // Nút Hoàn thành / Hoàn hàng
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
