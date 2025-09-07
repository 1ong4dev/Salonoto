<?php include '../header.php' ?>

<?php
// Lấy danh sách sản phẩm, số lượng tồn và nhà cung cấp
$sql = "SELECT sp.MaSP, sp.TenSP, k.SLTon, 
       GROUP_CONCAT(DISTINCT nc.TenNCC SEPARATOR ', ') AS NhaCungCap
        FROM SanPham sp
        LEFT JOIN Kho k ON sp.MaSP = k.MaSP
        LEFT JOIN NhapHang nh ON sp.MaSP = nh.MaSP
        LEFT JOIN NhaCungCap nc ON nh.MaNCC = nc.MaNCC
        GROUP BY sp.MaSP, sp.TenSP, k.SLTon
        ORDER BY sp.TenSP ASC";
$products = Database::GetData($sql);

// Lấy page nếu dùng phân trang (nếu cần)
$page = isset($_GET['page']) ? $_GET['page'] : 1;
?>

<?php include '../sidebar.php' ?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">Kho sản phẩm</h1>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row my-2">
                <div class="card" style="width: 100%">
                    <div class="card-body">
                        <table class="table table-hover table-bordered">
                            <thead class="table-warning">
                                <tr>
                                    <th>Mã SP</th>
                                    <th>Tên SP</th>
                                    <th>Số lượng tồn</th>
                                    <th>Nhà cung cấp</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($products) {
                                    foreach ($products as $sp) {
                                        echo "<tr>
                                                <td>{$sp['MaSP']}</td>
                                                <td>{$sp['TenSP']}</td>
                                                <td>{$sp['SLTon']}</td>
                                                <td>{$sp['NhaCungCap']}</td>
                                              </tr>";
                                    }
                                } else {
                                    echo '<tr><td colspan="4" class="text-center">Không có dữ liệu</td></tr>';
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

<?php include '../footer.php' ?>
