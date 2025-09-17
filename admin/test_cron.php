<?php
// File: admin/test_cron.php (tạo trang này để test cron)
require_once '../config/database.php';

echo "<h2>Test Auto Cron System</h2>";

// 1. Kiểm tra trạng thái cron
echo "<h3>Trạng thái Cron:</h3>";
$status = Database::getCronStatus();
echo "<ul>";
echo "<li><strong>Lần chạy cuối:</strong> " . $status['last_cron_check'] . "</li>";
echo "<li><strong>Lần chạy tiếp theo:</strong> " . $status['next_cron_run'] . "</li>";
echo "</ul>";

// 2. Thống kê đơn hàng
echo "<h3>Thống kê đơn hàng:</h3>";

// Đơn hàng chờ xử lý
$pendingOrders = Database::GetData("
    SELECT COUNT(*) as total 
    FROM dondathang d 
    LEFT JOIN thanhtoan t ON d.MaDonDatHang = t.MaDonDatHang 
    WHERE d.TrangThai = 'ChoXuLy' 
    AND t.MaDonDatHang IS NULL
");

echo "<ul>";
echo "<li><strong>Đơn hàng chờ thanh toán:</strong> " . ($pendingOrders[0]['total'] ?? 0) . "</li>";

// Đơn hàng quá hạn (cần hủy)
$expiredOrders = Database::GetData("
    SELECT COUNT(*) as total 
    FROM dondathang d 
    LEFT JOIN thanhtoan t ON d.MaDonDatHang = t.MaDonDatHang 
    WHERE d.TrangThai = 'ChoXuLy' 
    AND t.MaDonDatHang IS NULL 
    AND d.CreatedAt < DATE_SUB(NOW(), INTERVAL 15 MINUTE)
");

echo "<li><strong>Đơn hàng quá hạn (cần hủy):</strong> " . ($expiredOrders[0]['total'] ?? 0) . "</li>";

// Đơn hàng đã bị hủy tự động
$cancelledOrders = Database::GetData("
    SELECT COUNT(*) as total 
    FROM dondathang 
    WHERE TrangThai = 'Huy' 
    AND GhiChu LIKE '%Tự động hủy%'
");

echo "<li><strong>Đơn hàng đã hủy tự động:</strong> " . ($cancelledOrders[0]['total'] ?? 0) . "</li>";
echo "</ul>";

// 3. Nút chạy cron thủ công
if (isset($_POST['run_cron'])) {
    echo "<div style='background: #d4edda; padding: 10px; border: 1px solid #c3e6cb; margin: 20px 0; border-radius: 5px;'>";
    echo "<strong>Kết quả:</strong> " . Database::runCronManually();
    echo "</div>";
    
    // Refresh lại thống kê
    echo "<script>setTimeout(function(){ window.location.reload(); }, 2000);</script>";
}

echo "<form method='POST' style='margin-top: 20px;'>";
echo "<button type='submit' name='run_cron' style='background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>Chạy Cron Thủ Công</button>";
echo "</form>";

// 4. Danh sách đơn hàng gần đây
echo "<h3>Đơn hàng chờ thanh toán (10 đơn gần nhất):</h3>";
$recentOrders = Database::GetData("
    SELECT d.MaDonDatHang, d.TenTaiKhoan, d.TongTien, d.CreatedAt,
           TIMESTAMPDIFF(MINUTE, d.CreatedAt, NOW()) as phut_da_qua
    FROM dondathang d 
    LEFT JOIN thanhtoan t ON d.MaDonDatHang = t.MaDonDatHang 
    WHERE d.TrangThai = 'ChoXuLy' 
    AND t.MaDonDatHang IS NULL
    ORDER BY d.CreatedAt DESC
    LIMIT 10
");

if ($recentOrders && count($recentOrders) > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin-top: 10px;'>";
    echo "<tr style='background: #f8f9fa;'>";
    echo "<th style='padding: 8px;'>Mã đơn</th>";
    echo "<th style='padding: 8px;'>Tài khoản</th>";
    echo "<th style='padding: 8px;'>Tổng tiền</th>";
    echo "<th style='padding: 8px;'>Thời gian tạo</th>";
    echo "<th style='padding: 8px;'>Đã qua (phút)</th>";
    echo "<th style='padding: 8px;'>Trạng thái</th>";
    echo "</tr>";
    
    foreach ($recentOrders as $order) {
        $phutDaQua = $order['phut_da_qua'];
        $trangThai = $phutDaQua >= 15 ? "<span style='color: red;'>Quá hạn</span>" : "<span style='color: green;'>Còn " . (15 - $phutDaQua) . " phút</span>";
        $rowColor = $phutDaQua >= 15 ? "background: #ffe6e6;" : "";
        
        echo "<tr style='$rowColor'>";
        echo "<td style='padding: 8px;'>" . $order['MaDonDatHang'] . "</td>";
        echo "<td style='padding: 8px;'>" . $order['TenTaiKhoan'] . "</td>";
        echo "<td style='padding: 8px;'>" . number_format($order['TongTien']) . " đ</td>";
        echo "<td style='padding: 8px;'>" . $order['CreatedAt'] . "</td>";
        echo "<td style='padding: 8px;'>" . $phutDaQua . "</td>";
        echo "<td style='padding: 8px;'>" . $trangThai . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p><em>Không có đơn hàng nào đang chờ thanh toán.</em></p>";
}

// Auto refresh mỗi 30 giây
echo "<script>";
echo "setTimeout(function(){ window.location.reload(); }, 30000);";
echo "console.log('Trang sẽ tự động refresh sau 30 giây...');";
echo "</script>";

echo "<p style='margin-top: 30px; color: #666; font-size: 12px;'>";
echo "* Trang này sẽ tự động refresh mỗi 30 giây để cập nhật dữ liệu mới nhất.<br>";
echo "* Cron tự động chạy mỗi 2 phút khi có người truy cập website.<br>";
echo "* Đơn hàng sẽ bị hủy tự động sau 15 phút không thanh toán.";
echo "</p>";
?>