<?php include '../header.php'?>
<?php

// ==========================
// XỬ LÝ HỦY ĐƠN HÀNG (CHỈ KHI CHỜ XỬ LÝ)
// ==========================
if (isset($_GET['cancel']) && isset($_GET['MaDonDatHang'])) {
    $MaDonDatHang = $_GET['MaDonDatHang'];
    
    try {
        // Kiểm tra trạng thái hiện tại
        $order = Database::GetData("SELECT TrangThai FROM dondathang WHERE MaDonDatHang='$MaDonDatHang'", ['row'=>0]);
        if ($order && $order['TrangThai'] == 'ChoXuLy') {
            Database::NonQuery("UPDATE dondathang SET TrangThai='Huy' WHERE MaDonDatHang='$MaDonDatHang'");
            echo "<script>alert('Đơn hàng đã được hủy thành công');</script>";
        } else {
            echo "<script>alert('Chỉ có thể hủy đơn hàng đang chờ xử lý');</script>";
        }
    } catch(Exception $e) {
        echo "<script>alert('Lỗi khi hủy đơn hàng: ".$e->getMessage()."');</script>";
    }
}

// ==========================
// XỬ LÝ CHUYỂN ĐƠN HÀNG SANG ĐANG GIAO HÀNG
// ==========================
if (isset($_GET['ship']) && isset($_GET['MaDonDatHang'])) {
    $MaDonDatHang = $_GET['MaDonDatHang'];

    try {
        // Kiểm tra trạng thái hiện tại
        $order = Database::GetData("SELECT TrangThai FROM dondathang WHERE MaDonDatHang='$MaDonDatHang'", ['row'=>0]);
        if (!$order || $order['TrangThai'] != 'XacNhan') {
            echo "<script>alert('Chỉ có thể giao hàng khi đơn hàng đã được xác nhận');</script>";
        } else {
            Database::NonQuery("UPDATE dondathang SET TrangThai='DangGiaoHang' WHERE MaDonDatHang='$MaDonDatHang'");
            echo "<script>alert('Đơn hàng đã chuyển sang trạng thái đang giao hàng');</script>";
        }
    } catch(Exception $e) {
        echo "<script>alert('Lỗi khi cập nhật trạng thái giao hàng: ".$e->getMessage()."');</script>";
    }
}

// ==========================
// XỬ LÝ HOÀN THÀNH ĐƠN HÀNG + TẠO HÓA ĐƠN
// ==========================
if (isset($_GET['complete']) && isset($_GET['MaDonDatHang'])) {
    $MaDonDatHang = $_GET['MaDonDatHang'];

    $connect = Database::BeginTransaction();
    try {
        // Kiểm tra trạng thái hiện tại
        $order = Database::GetData("SELECT * FROM dondathang WHERE MaDonDatHang='$MaDonDatHang'", ['row'=>0]);
        if (!$order || $order['TrangThai'] != 'DangGiaoHang') {
            throw new Exception('Chỉ có thể hoàn thành đơn hàng đang giao hàng');
        }

        // Cập nhật trạng thái đơn hàng
        Database::NonQueryTrans($connect, "UPDATE dondathang SET TrangThai='DaHoanThanh' WHERE MaDonDatHang='$MaDonDatHang'");
        
        // Kiểm tra xem đã có hóa đơn chưa
        $checkHD = Database::GetData("SELECT MaHoaDon FROM hoadon WHERE MaDonDatHang='$MaDonDatHang'", ['row'=>0]);

        if (!$checkHD) {
            $TongTien = $order['TongTien'];
            $GhiChu = $order['GhiChu'] ?? null;

            // Tạo hóa đơn
            $sqlHD = "INSERT INTO hoadon (MaDonDatHang, TongTien, GhiChu) VALUES ('$MaDonDatHang', '$TongTien', " . ($GhiChu ? "'$GhiChu'" : "NULL") . ")";
            $MaHoaDon = Database::NonQueryIdTrans($connect, $sqlHD);

            // Lấy chi tiết đơn hàng để tạo chi tiết hóa đơn
            $chiTiet = Database::GetData("
                SELECT ctdh.*, sp.TenSP, sp.Gia
                FROM chitietdondathang ctdh
                JOIN sanpham sp ON ctdh.MaSP = sp.MaSP
                WHERE ctdh.MaDonDatHang='$MaDonDatHang'
            ");

            foreach ($chiTiet as $item) {
                $MaSP = $item['MaSP'];
                $TenSP = addslashes($item['TenSP']);
                $SL = $item['SL'];
                $Gia = $item['DonGia']; // Sử dụng DonGia từ chitietdondathang
                $NgayBatDauBH = $item['NgayBatDauBH'] ? "'" . $item['NgayBatDauBH'] . "'" : "NULL";
                $NgayKetThucBH = $item['NgayKetThucBH'] ? "'" . $item['NgayKetThucBH'] . "'" : "NULL";

                $sqlCTHD = "
                    INSERT INTO chitiethoadon (MaHoaDon, MaSP, TenSP, SL, Gia, NgayBatDauBH, NgayKetThucBH)
                    VALUES ('$MaHoaDon', '$MaSP', '$TenSP', '$SL', '$Gia', $NgayBatDauBH, $NgayKetThucBH)
                ";
                Database::NonQueryTrans($connect, $sqlCTHD);
            }
        }

        Database::Commit($connect);
        echo "<script>alert('Đơn hàng đã hoàn thành và tạo hóa đơn thành công');</script>";

    } catch(Exception $e) {
        Database::Rollback($connect);
        echo "<script>alert('Lỗi khi hoàn thành đơn hàng: ".$e->getMessage()."');</script>";
    }
}

// ==========================
// XỬ LÝ HOÀN HÀNG + HOÀN TRẢ SỐ LƯỢNG VÀO KHO
// ==========================
if (isset($_GET['return']) && isset($_GET['MaDonDatHang'])) {
    $MaDonDatHang = $_GET['MaDonDatHang'];
    
    $connect = Database::BeginTransaction();
    try {
        // Kiểm tra trạng thái hiện tại
        $order = Database::GetData("SELECT TrangThai FROM dondathang WHERE MaDonDatHang='$MaDonDatHang'", ['row'=>0]);
        if (!$order || $order['TrangThai'] != 'DaHoanThanh') {
            throw new Exception('Chỉ có thể hoàn hàng khi đơn hàng đã hoàn thành');
        }

        // Cập nhật trạng thái đơn hàng
        Database::NonQueryTrans($connect, "UPDATE dondathang SET TrangThai='HoanHang' WHERE MaDonDatHang='$MaDonDatHang'");
        
        // Lấy thông tin phiếu xuất để hoàn trả số lượng
        $phieuXuat = Database::GetData("SELECT MaXuat FROM phieuxuat WHERE MaDonDatHang='$MaDonDatHang' AND TrangThai='DaXuat'", ['row'=>0]);
        
        if ($phieuXuat) {
            $MaXuat = $phieuXuat['MaXuat'];
            
            // Lấy chi tiết phiếu xuất để hoàn trả số lượng
            $chiTiet = Database::GetData("SELECT MaSP, SL FROM chitietphieuxuat WHERE MaXuat='$MaXuat'");
            
            foreach ($chiTiet as $item) {
                $MaSP = $item['MaSP'];
                $SL = $item['SL'];
                // Hoàn trả số lượng vào kho
                Database::NonQueryTrans($connect, "UPDATE sanpham SET SL = SL + $SL WHERE MaSP = $MaSP");
            }
            
            // Cập nhật trạng thái phiếu xuất thành Huy
            Database::NonQueryTrans($connect, "UPDATE phieuxuat SET TrangThai='Huy' WHERE MaXuat='$MaXuat'");
        }

        Database::Commit($connect);
        echo "<script>alert('Đơn hàng đã chuyển sang trạng thái hoàn hàng và hoàn trả số lượng vào kho');</script>";
        
    } catch(Exception $e) {
        Database::Rollback($connect);
        echo "<script>alert('Lỗi khi hoàn hàng: ".$e->getMessage()."');</script>";
    }
}

// ==========================
// TẠO PHIẾU XUẤT CHO ĐƠN HÀNG ĐÃ XÁC NHẬN
// ==========================
function CreatePhieuXuatForConfirmedOrder($MaDonDatHang) {
    $connect = Database::BeginTransaction();
    try {
        // Kiểm tra xem đã có phiếu xuất chưa
        $existingPX = Database::GetData("SELECT MaXuat FROM phieuxuat WHERE MaDonDatHang='$MaDonDatHang'", ['row'=>0]);
        if ($existingPX) {
            Database::Rollback($connect);
            return ['success' => true, 'message' => 'Phiếu xuất đã tồn tại'];
        }

        // Lấy chi tiết đơn hàng để tạo phiếu xuất
        $chiTiet = Database::GetData("
            SELECT ctdh.*, sp.TenSP, sp.Gia
            FROM chitietdondathang ctdh
            JOIN sanpham sp ON ctdh.MaSP = sp.MaSP
            WHERE ctdh.MaDonDatHang='$MaDonDatHang'
        ");

        if (!$chiTiet) {
            throw new Exception('Không tìm thấy chi tiết đơn hàng');
        }

        // Kiểm tra tồn kho trước khi tạo phiếu xuất
        foreach ($chiTiet as $item) {
            $currentStock = Database::GetData("SELECT SL FROM sanpham WHERE MaSP = " . $item['MaSP'], ['row'=>0]);
            if (!$currentStock || $currentStock['SL'] < $item['SL']) {
                throw new Exception('Sản phẩm ' . $item['TenSP'] . ' không đủ số lượng trong kho');
            }
        }

        // Tạo phiếu xuất
        $TongTienXuat = 0;
        foreach ($chiTiet as $item) {
            $TongTienXuat += $item['SL'] * $item['DonGia'];
        }

        $sqlPX = "INSERT INTO phieuxuat (MaDonDatHang, NguoiLap, LyDoXuat, TrangThai, TongTien) 
                  VALUES ('$MaDonDatHang', NULL, 'BanHang', 'DaXuat', '$TongTienXuat')";
        $MaXuat = Database::NonQueryIdTrans($connect, $sqlPX);

        // Thêm chi tiết phiếu xuất và trừ SL sản phẩm
        foreach ($chiTiet as $item) {
            $MaSP = $item['MaSP'];
            $SL = $item['SL'];
            $GiaXuat = $item['DonGia'];

            // Thêm chi tiết phiếu xuất
            Database::NonQueryTrans($connect, "
                INSERT INTO chitietphieuxuat (MaXuat, MaSP, SL, GiaXuat)
                VALUES ('$MaXuat', '$MaSP', '$SL', '$GiaXuat')
            ");

            // Trừ SL sản phẩm
            Database::NonQueryTrans($connect, "
                UPDATE sanpham SET SL = SL - $SL WHERE MaSP = $MaSP
            ");
        }

        Database::Commit($connect);
        return ['success' => true, 'message' => 'Tạo phiếu xuất thành công'];

    } catch(Exception $e) {
        Database::Rollback($connect);
        return ['success' => false, 'message' => 'Lỗi khi tạo phiếu xuất: ' . $e->getMessage()];
    }
}

// ==========================
// TỰ ĐỘNG TẠO PHIẾU XUẤT CHO CÁC ĐƠN HÀNG XÁC NHẬN CHƯA CÓ PHIẾU XUẤT
// ==========================
function AutoCreatePhieuXuatForConfirmedOrders() {
    // Lấy các đơn hàng đã xác nhận nhưng chưa có phiếu xuất
    $ordersNeedPX = Database::GetData("
        SELECT d.MaDonDatHang 
        FROM dondathang d
        LEFT JOIN phieuxuat px ON d.MaDonDatHang = px.MaDonDatHang
        WHERE d.TrangThai = 'XacNhan' AND px.MaXuat IS NULL
    ");
    
    if ($ordersNeedPX) {
        foreach ($ordersNeedPX as $order) {
            $result = CreatePhieuXuatForConfirmedOrder($order['MaDonDatHang']);
            if ($result['success']) {
                echo "<script>console.log('Đã tạo phiếu xuất cho đơn hàng: " . $order['MaDonDatHang'] . "');</script>";
            }
        }
    }
}

// ==========================
// HÀM HIỂN THỊ BADGE TRẠNG THÁI
// ==========================
function OrderStatusBadge($status) {
    switch ($status) {
        case 'ChoXuLy': return '<span class="badge bg-warning">Chờ xử lý</span>';
        case 'XacNhan': return '<span class="badge bg-primary">Xác nhận</span>';
        case 'DangGiaoHang': return '<span class="badge bg-info">Đang giao hàng</span>';
        case 'DaHoanThanh': return '<span class="badge bg-success">Đã hoàn thành</span>';
        case 'Huy': return '<span class="badge bg-danger">Hủy</span>';
        case 'HoanHang': return '<span class="badge bg-secondary">Hoàn hàng</span>';
        default: return '<span class="badge bg-dark">Không xác định</span>';
    }
}

// Gọi hàm tự động tạo phiếu xuất (sau khi đã định nghĩa tất cả functions)
AutoCreatePhieuXuatForConfirmedOrders();

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
                                    <th width="300">Công cụ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $page = $_GET['page'] ?? 1;
                                $pager = (new Pagination())->get('dondathang', $page, ROW_OF_PAGE);

                                $keyword = $_GET['keyword'] ?? '';
                                $where = '';
                                if ($keyword) {
                                    $keyword = addslashes($keyword);
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
                                        $status = $order['TrangThai'];

                                        // Nút hủy (chỉ khi chờ xử lý)
                                        if ($status == 'ChoXuLy') {
                                            $btns .= '<a href="?cancel=1&MaDonDatHang=' . $order['MaDonDatHang'] . '" 
                                                     class="btn btn-danger btn-sm" title="Hủy đơn hàng" 
                                                     onclick="return confirm(\'Bạn có chắc muốn hủy đơn hàng này?\')">
                                                     <i class="fas fa-times"></i> Hủy
                                                     </a>';
                                        }

                                        // Nút giao hàng (chỉ khi đã xác nhận)
                                        if ($status == 'XacNhan') {
                                            $btns .= '<a href="?ship=1&MaDonDatHang=' . $order['MaDonDatHang'] . '" 
                                                     class="btn btn-info btn-sm" title="Giao hàng">
                                                     <i class="fas fa-truck"></i> Giao hàng
                                                     </a>';
                                        }

                                        // Nút hoàn thành (chỉ khi đang giao hàng)
                                        if ($status == 'DangGiaoHang') {
                                            $btns .= '<a href="?complete=1&MaDonDatHang=' . $order['MaDonDatHang'] . '" 
                                                     class="btn btn-primary btn-sm" title="Hoàn thành">
                                                     <i class="fas fa-check-circle"></i> Hoàn thành
                                                     </a>';
                                        }

                                        // Nút in hóa đơn và hoàn hàng (chỉ khi đã hoàn thành)
                                        if ($status == 'DaHoanThanh') {
                                            $btns .= '<a href="print-order.php?order-id=' . $order['MaDonDatHang'] . '" 
                                                     class="btn btn-secondary btn-sm" title="In hóa đơn">
                                                     <i class="fas fa-print"></i>
                                                     </a> ';
                                                     
                                            $btns .= '<a href="?return=1&MaDonDatHang=' . $order['MaDonDatHang'] . '" 
                                                     class="btn btn-warning btn-sm" title="Hoàn hàng" 
                                                     onclick="return confirm(\'Bạn có chắc muốn hoàn hàng này?\')">
                                                     <i class="fas fa-undo"></i> Hoàn hàng
                                                     </a>';
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
                <!-- PHÂN TRANG -->
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