<?php include '../header.php' ?>

<?php
// Lấy danh sách sản phẩm và số lượng trực tiếp từ bảng SanPham
$sql = "SELECT MaSP, TenSP, SL
        FROM SanPham
        ORDER BY TenSP ASC";
$products = Database::GetData($sql);
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
                <div class="card w-100">
                    <div class="card-body">
                        <table class="table table-hover table-bordered">
                            <thead class="table-warning">
                                <tr>
                                    <th>Mã SP</th>
                                    <th>Tên SP</th>
                                    <th>Số lượng</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($products) {
                                    foreach ($products as $sp) {
                                        echo "<tr>
                                                <td>{$sp['MaSP']}</td>
                                                <td>{$sp['TenSP']}</td>
                                                <td>{$sp['SL']}</td>
                                              </tr>";
                                    }
                                } else {
                                    echo '<tr><td colspan="3" class="text-center">Không có dữ liệu</td></tr>';
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
