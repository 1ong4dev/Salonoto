<?php include '../header.php'?>


<?php

// AJAX: Xem chi tiết phiếu nhập ngay trong file này
if (isset($_GET['ajax-detail'])) {
    $MaNhap = (int) $_GET['ajax-detail'];

    $sql = "SELECT ct.MaChiTietNhap, sp.TenSP, ct.SL, ct.GiaNhap, (ct.SL * ct.GiaNhap) AS ThanhTien
            FROM chitietphieunhap ct
            JOIN sanpham sp ON ct.MaSP = sp.MaSP
            WHERE ct.MaNhap = $MaNhap";
    $details = Database::GetData($sql);

    if ($details) {
        echo '<table class="table table-bordered table-sm">
                <thead class="table-secondary">
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Số lượng</th>
                        <th>Giá nhập</th>
                        <th>Thành tiền</th>
                        <th width="100">Công cụ</th>
                    </tr>
                </thead>
                <tbody>';
        foreach ($details as $d) {
            echo '<tr>
                <td>'.$d['TenSP'].'</td>
                <td>'.$d['SL'].'</td>
                <td>'.Helper::Currency($d['GiaNhap']).'</td>
                <td>'.Helper::Currency($d['ThanhTien']).'</td>
                <td>
                    <button class="btn btn-warning btn-sm" onclick="editDetail('.$d['MaChiTietNhap'].', \''.$d['TenSP'].'\', '.$d['SL'].', '.$d['GiaNhap'].')">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-danger btn-sm" onclick="removeDetail('.$d['MaChiTietNhap'].')">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            </tr>';
        }
        echo '</tbody></table>';
    } else {
        echo '<p class="text-danger">Không có chi tiết phiếu nhập!</p>';
    }

    exit; // Dừng tại đây, không load HTML bên dưới nữa
}

// Lấy danh sách sản phẩm và nhà cung cấp
$products = Database::GetData("SELECT MaSP, TenSP FROM SanPham");
$nhacungcap = Database::GetData("SELECT MaNCC, TenNCC FROM NhaCungCap");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

   // Thêm phiếu nhập với nhiều sản phẩm
if (isset($_POST['action']) && $_POST['action'] == 'add') {
    $MaNCC = $_POST['MaNCC'] ?? '';
    $GhiChu = $_POST['GhiChu'] ?? '';
    
    // Lấy danh sách sản phẩm từ form
    $danhSachSP = $_POST['products'] ?? [];

    if ($MaNCC && !empty($danhSachSP)) {
        $hasValidProduct = false;
        $errors = [];
        
        // Kiểm tra từng sản phẩm
        foreach ($danhSachSP as $index => $product) {
            $MaSP    = $product['MaSP'] ?? '';
            $SL      = (int) ($product['SL'] ?? 0);
            $GiaNhap = (float) ($product['GiaNhap'] ?? 0);
            
            if ($MaSP && $SL > 0 && $GiaNhap > 0) {
                $giaBan = Database::GetData("SELECT Gia FROM SanPham WHERE MaSP=$MaSP", ['cell'=>'Gia']);
                if (!$giaBan) {
                    $errors[] = "Không tìm thấy giá bán cho sản phẩm ID = $MaSP";
                } elseif ($GiaNhap >= $giaBan) {
                    $tenSP = Database::GetData("SELECT TenSP FROM SanPham WHERE MaSP=$MaSP", ['cell'=>'TenSP']);
                    $errors[] = "Sản phẩm '$tenSP': Giá nhập phải nhỏ hơn giá bán!";
                } else {
                    $hasValidProduct = true;
                }
            }
        }
        
        if ($hasValidProduct && empty($errors)) {
    try {
        // Bắt đầu transaction
        Database::NonQuery("START TRANSACTION");
        
        // Insert phiếu nhập và lấy ID
        $MaNhap = Database::NonQueryId("INSERT INTO phieunhap (MaNCC, GhiChu) 
                                        VALUES ($MaNCC, '$GhiChu')");
        
        // Insert chi tiết phiếu nhập
        foreach ($danhSachSP as $product) {
            $MaSP    = (int)($product['MaSP'] ?? 0);
            $SL      = (int)($product['SL'] ?? 0);
            $GiaNhap = (float)($product['GiaNhap'] ?? 0);

            if ($MaSP && $SL > 0 && $GiaNhap > 0) {
                // Lấy giá bán của sản phẩm
                $GiaBan = Database::GetData("SELECT Gia FROM SanPham WHERE MaSP = $MaSP", ['cell' => 'Gia']);

                if ($GiaBan && $GiaNhap < $GiaBan) {
                    // ✅ Giá nhập hợp lệ → thêm chi tiết phiếu nhập
                    Database::NonQuery("INSERT INTO chitietphieunhap (MaNhap, MaSP, SL, GiaNhap) 
                                        VALUES ($MaNhap, $MaSP, $SL, $GiaNhap)");

                    // Cập nhật số lượng tồn kho
                    Database::NonQuery("UPDATE SanPham SET SL = SL + $SL WHERE MaSP = $MaSP");
                } else {
                    // ❌ Giá nhập không hợp lệ → rollback và báo lỗi
                    Database::NonQuery("ROLLBACK");
                    $message = [
                        'type' => 'warning',
                        'text' => "Sản phẩm ID $MaSP: Giá nhập ($GiaNhap) phải nhỏ hơn giá bán ($GiaBan)!"
                    ];
                    return; // Dừng hẳn, không commit
                }
            }
        }

        // Commit transaction
        Database::NonQuery("COMMIT");
        
        $message = ['type' => 'success', 'text' => 'Nhập hàng thành công!'];
    } catch (Exception $e) {
        Database::NonQuery("ROLLBACK");
        $message = ['type' => 'error', 'text' => 'Có lỗi xảy ra: ' . $e->getMessage()];
    }


        } else {
            if (!empty($errors)) {
                $message = ['type'=>'warning','text'=>implode('<br>', $errors)];
            } else {
                $message = ['type'=>'warning','text'=>'Vui lòng nhập ít nhất một sản phẩm hợp lệ!'];
            }
        }
    } else {
        $message = ['type'=>'warning','text'=>'Vui lòng chọn nhà cung cấp và nhập ít nhất một sản phẩm!'];
    }
}


    // Sửa chi tiết phiếu nhập
    if (isset($_POST['action']) && $_POST['action'] == 'edit') {
        $MaChiTietNhap = $_POST['edit_id'] ?? '';
        $MaSP    = $_POST['MaSP'] ?? '';
        $SL      = (int) ($_POST['SL'] ?? 0);
        $GiaNhap = (float) ($_POST['GiaNhap'] ?? 0);

        if ($MaChiTietNhap && $MaSP && $SL > 0 && $GiaNhap > 0) {
            $giaBan = Database::GetData("SELECT Gia FROM SanPham WHERE MaSP=$MaSP", ['cell'=>'Gia']);
            if ($GiaNhap < $giaBan * 0.9) {
                try {
                    // Bắt đầu transaction
                    Database::NonQuery("START TRANSACTION");
                    
                    // Lấy thông tin cũ
                    $old = Database::GetData("SELECT SL, MaSP FROM chitietphieunhap WHERE MaChiTietNhap=$MaChiTietNhap", ['row'=>0]);
                    if ($old) {
                        // Hoàn nguyên số lượng cũ
                        Database::NonQuery("UPDATE SanPham SET SL = SL - {$old['SL']} WHERE MaSP = {$old['MaSP']}");
                        
                        // Cập nhật chi tiết phiếu nhập
                        Database::NonQuery("UPDATE chitietphieunhap 
                                            SET MaSP=$MaSP, SL=$SL, GiaNhap=$GiaNhap 
                                            WHERE MaChiTietNhap=$MaChiTietNhap");
                        
                        // Cập nhật số lượng mới
                        Database::NonQuery("UPDATE SanPham SET SL = SL + $SL WHERE MaSP = $MaSP");
                        
                        // Commit transaction
                        Database::NonQuery("COMMIT");
                        
                        $message = ['type'=>'success','text'=>'Cập nhật chi tiết phiếu nhập thành công!'];
                    } else {
                        Database::NonQuery("ROLLBACK");
                        $message = ['type'=>'error','text'=>'Không tìm thấy chi tiết phiếu nhập!'];
                    }
                } catch (Exception $e) {
                    Database::NonQuery("ROLLBACK");
                    $message = ['type'=>'error','text'=>'Có lỗi xảy ra: ' . $e->getMessage()];
                }
            } else {
                $message = ['type'=>'warning','text'=>'Giá nhập phải nhỏ hơn giá bán ít nhất 10%!'];
            }
        } else {
            $message = ['type'=>'warning','text'=>'Vui lòng nhập đầy đủ thông tin hợp lệ!'];
        }
    }
}

// Xóa phiếu nhập (và tất cả chi tiết)
if (isset($_GET['del-id'])) {
    $MaNhap = $_GET['del-id'];
    try {
        Database::NonQuery("START TRANSACTION");
        
        // Lấy tất cả chi tiết để hoàn nguyên số lượng
        $chiTietList = Database::GetData("SELECT MaSP, SL FROM chitietphieunhap WHERE MaNhap=$MaNhap");
        foreach ($chiTietList as $chiTiet) {
            Database::NonQuery("UPDATE SanPham SET SL = SL - {$chiTiet['SL']} WHERE MaSP = {$chiTiet['MaSP']}");
        }
        
        // Xóa chi tiết trước
        Database::NonQuery("DELETE FROM chitietphieunhap WHERE MaNhap=$MaNhap");
        // Xóa phiếu nhập
        Database::NonQuery("DELETE FROM phieunhap WHERE MaNhap=$MaNhap");
        
        Database::NonQuery("COMMIT");
        $message = ['type'=>'success','text'=>'Xóa phiếu nhập thành công!'];
    } catch (Exception $e) {
        Database::NonQuery("ROLLBACK");
        $message = ['type'=>'error','text'=>'Có lỗi xảy ra: ' . $e->getMessage()];
    }
}

// Xóa chi tiết phiếu nhập
if (isset($_GET['del-detail-id'])) {
    $MaChiTietNhap = $_GET['del-detail-id'];
    try {
        Database::NonQuery("START TRANSACTION");
        
        // Lấy thông tin chi tiết để hoàn nguyên số lượng
        $chiTiet = Database::GetData("SELECT MaSP, SL FROM chitietphieunhap WHERE MaChiTietNhap=$MaChiTietNhap", ['row'=>0]);
        if ($chiTiet) {
            Database::NonQuery("UPDATE SanPham SET SL = SL - {$chiTiet['SL']} WHERE MaSP = {$chiTiet['MaSP']}");
            Database::NonQuery("DELETE FROM chitietphieunhap WHERE MaChiTietNhap=$MaChiTietNhap");
        }
        
        Database::NonQuery("COMMIT");
        $message = ['type'=>'success','text'=>'Xóa chi tiết phiếu nhập thành công!'];
    } catch (Exception $e) {
        Database::NonQuery("ROLLBACK");
        $message = ['type'=>'error','text'=>'Có lỗi xảy ra: ' . $e->getMessage()];
    }
}
?>

<?php include '../sidebar.php'?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Nhập hàng</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">
                            <a href="<?=ADMIN_URL?>/"><i class="fas fa-home"></i></a>
                        </li>
                        <li class="breadcrumb-item active">Nhập hàng</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <?php include '../alert.php'?>

        <!-- Modal: Add -->
        <div class="modal fade" id="modal-add" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
                <form class="modal-content" method="POST">
                    <div class="modal-header bg-primary">
                        <h5 class="modal-title">Thêm phiếu nhập</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Nhà cung cấp <span class="text-danger">*</span></label>
                                    <select name="MaNCC" class="form-control" required>
                                        <option value="" disabled selected hidden>Chọn nhà cung cấp</option>
                                        <?php foreach($nhacungcap as $ncc): ?>
                                            <option value="<?=$ncc['MaNCC']?>"><?=$ncc['TenNCC']?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Ghi chú</label>
                                    <textarea name="GhiChu" class="form-control" rows="2" placeholder="Ghi chú về phiếu nhập..."></textarea>
                                </div>
                            </div>
                        </div>

                        <hr>
                        
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Danh sách sản phẩm</h6>
                            <button type="button" class="btn btn-sm btn-success" onclick="addProductRow()">
                                <i class="fas fa-plus"></i> Thêm sản phẩm
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered" id="products-table">
                                <thead class="table-secondary">
                                    <tr>
                                        <th width="35%">Sản phẩm</th>
                                        <th width="15%">Số lượng</th>
                                        <th width="20%">Giá nhập</th>
                                        <th width="20%">Thành tiền</th>
                                        <th width="10%">Xóa</th>
                                    </tr>
                                </thead>
                                <tbody id="products-tbody">
                                    <!-- Dòng sản phẩm sẽ được thêm bằng JavaScript -->
                                </tbody>
                                <tfoot>
                                    <tr class="table-info">
                                        <td colspan="3" class="text-right"><strong>Tổng cộng:</strong></td>
                                        <td><strong id="total-amount">0 VNĐ</strong></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Huỷ</button>
                        <button name="action" value="add" class="btn btn-success" id="btn-submit">Tạo phiếu nhập</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal: Edit Chi tiết -->
        <div class="modal fade" id="modal-edit" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable" role="document">
                <form class="modal-content" method="POST">
                    <div class="modal-header bg-primary">
                        <h5 class="modal-title">Sửa chi tiết phiếu nhập</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="edit_id" id="edit_id">
                        <div class="form-group">
                            <label>Sản phẩm</label>
                            <select name="MaSP" id="edit_MaSP" class="form-control" required>
                                <option value="" disabled selected hidden>Chọn sản phẩm</option>
                                <?php foreach($products as $sp): ?>
                                    <option value="<?=$sp['MaSP']?>"><?=$sp['TenSP']?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Số lượng</label>
                            <input type="number" name="SL" id="edit_SL" class="form-control" min="1" required>
                        </div>
                        <div class="form-group">
                            <label>Giá nhập</label>
                            <input type="number" step="0.01" name="GiaNhap" id="edit_GiaNhap" class="form-control" min="1" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Huỷ</button>
                        <button name="action" value="edit" class="btn btn-success">Cập nhật</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal: View Details -->
        <div class="modal fade" id="modal-details" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-info">
                        <h5 class="modal-title">Chi tiết phiếu nhập</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body" id="modal-details-body">
                        <!-- Nội dung sẽ được load bằng AJAX -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid">
            <div class="row my-2 d-flex-end">
                <button type="button" class="btn btn-success mx-2" data-toggle="modal" data-target="#modal-add">
                    <i class="fas fa-plus"></i> Tạo phiếu nhập
                </button>
                <form method="GET">
                    <div class="input-group">
                        <input type="text" name="keyword" placeholder="Từ khoá" class="form-control" value="<?=htmlspecialchars($_GET['keyword']??'')?>">
                        <div class="input-group-append">
                            <button class="btn btn-outline-info"><i class="fas fa-search"></i></button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Table hiển thị phiếu nhập -->
            <div class="row my-2">
                <div class="card" style="width: 100%">
                    <div class="card-body">
                        <table class="table table-hover table-bordered">
                            <thead class="table-warning">
                                <tr>
                                    <th>Mã Phiếu nhập</th>
                                    <th>Nhà cung cấp</th>
                                    <th>Tổng tiền</th>
                                    <th>Ngày nhập</th>
                                    <th>Ghi chú</th>
                                    <th width="150">Công cụ</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            $where = '';
                            if (!empty($_GET['keyword'])) {
                                $kw = addslashes($_GET['keyword']);
                                $where = "WHERE nc.TenNCC LIKE '%$kw%' OR pn.GhiChu LIKE '%$kw%'";
                            }

                            $sqlNhap = "SELECT pn.MaNhap, pn.TGNhap, pn.GhiChu, nc.TenNCC,
                                               SUM(ct.SL * ct.GiaNhap) AS TongTien
                                        FROM phieunhap pn
                                        JOIN nhacungcap nc ON pn.MaNCC = nc.MaNCC
                                        LEFT JOIN chitietphieunhap ct ON pn.MaNhap = ct.MaNhap
                                        $where
                                        GROUP BY pn.MaNhap, pn.TGNhap, pn.GhiChu, nc.TenNCC
                                        ORDER BY pn.TGNhap DESC";

                            $phieuNhap = Database::GetData($sqlNhap);

                            if ($phieuNhap) {
                                foreach($phieuNhap as $pn) {
                                    echo '<tr>
                                        <td>'.$pn['MaNhap'].'</td>
                                        <td>'.$pn['TenNCC'].'</td>
                                        <td>'.Helper::Currency($pn['TongTien'] ?? 0).'</td>
                                        <td>'.$pn['TGNhap'].'</td>
                                        <td>'.($pn['GhiChu'] ?? '').'</td>
                                        <td>
                                            <button class="btn btn-info btn-sm" onclick="viewDetails('.$pn['MaNhap'].')" title="Xem chi tiết">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <a onclick="removeRow('.$pn['MaNhap'].')" class="btn btn-danger btn-sm" title="Xóa">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        </td>
                                    </tr>';
                                }
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

<script>
let rowCounter = 0;
let productData = <?= json_encode($products) ?>;

// Thêm dòng sản phẩm mới
function addProductRow() {
    let tbody = document.getElementById('products-tbody');
    let row = document.createElement('tr');
    row.id = 'product-row-' + rowCounter;
    
    let productOptions = '<option value="">Chọn sản phẩm</option>';
    productData.forEach(function(product) {
        productOptions += `<option value="${product.MaSP}">${product.TenSP}</option>`;
    });
    
    row.innerHTML = `
        <td>
            <select name="products[${rowCounter}][MaSP]" class="form-control product-select" required onchange="calculateRowTotal(${rowCounter})">
                ${productOptions}
            </select>
        </td>
        <td>
            <input type="number" name="products[${rowCounter}][SL]" class="form-control quantity-input" min="1" required onchange="calculateRowTotal(${rowCounter})">
        </td>
        <td>
            <input type="number" name="products[${rowCounter}][GiaNhap]" step="0.01" class="form-control price-input" min="1" required onchange="calculateRowTotal(${rowCounter})">
        </td>
        <td>
            <span class="row-total">0 VNĐ</span>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-danger btn-sm" onclick="removeProductRow(${rowCounter})">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    
    tbody.appendChild(row);
    rowCounter++;
    
    // Nếu là dòng đầu tiên, disable nút submit
    if (tbody.children.length === 1) {
        document.getElementById('btn-submit').disabled = false;
    }
}

// Xóa dòng sản phẩm
function removeProductRow(index) {
    let row = document.getElementById('product-row-' + index);
    if (row) {
        row.remove();
        calculateTotal();
        
        // Nếu không còn dòng nào, disable nút submit
        let tbody = document.getElementById('products-tbody');
        if (tbody.children.length === 0) {
            document.getElementById('btn-submit').disabled = true;
        }
    }
}

// Tính thành tiền cho từng dòng
function calculateRowTotal(index) {
    let row = document.getElementById('product-row-' + index);
    if (!row) return;
    
    let quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
    let price = parseFloat(row.querySelector('.price-input').value) || 0;
    let total = quantity * price;
    
    row.querySelector('.row-total').textContent = formatCurrency(total);
    calculateTotal();
}

// Tính tổng cộng
function calculateTotal() {
    let total = 0;
    document.querySelectorAll('#products-tbody tr').forEach(function(row) {
        let quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
        let price = parseFloat(row.querySelector('.price-input').value) || 0;
        total += quantity * price;
    });
    
    document.getElementById('total-amount').textContent = formatCurrency(total);
}

// Format tiền tệ
function formatCurrency(amount) {
    return new Intl.NumberFormat('vi-VN', { 
        style: 'currency', 
        currency: 'VND' 
    }).format(amount);
}

// Khởi tạo khi mở modal
$('#modal-add').on('shown.bs.modal', function () {
    let tbody = document.getElementById('products-tbody');
    tbody.innerHTML = ''; // Xóa hết các dòng cũ
    rowCounter = 0;
    addProductRow(); // Thêm dòng đầu tiên
    document.getElementById('btn-submit').disabled = false;
});

// Reset form khi đóng modal
$('#modal-add').on('hidden.bs.modal', function () {
    document.getElementById('products-tbody').innerHTML = '';
    document.getElementById('total-amount').textContent = '0 VNĐ';
    rowCounter = 0;
});

function removeRow(id) {
    if(confirm('Bạn có chắc chắn muốn xoá phiếu nhập này không? Tất cả chi tiết sẽ bị xóa!')) {
        window.location = '?del-id='+id;
    }
}

function removeDetail(id) {
    if(confirm('Bạn có chắc chắn muốn xoá chi tiết này không?')) {
        window.location = '?del-detail-id='+id;
    }
}

function editDetail(MaChiTietNhap, MaSP, SL, GiaNhap) {
    document.getElementById('edit_id').value = MaChiTietNhap;
    document.getElementById('edit_MaSP').value = MaSP;
    document.getElementById('edit_SL').value = SL;
    document.getElementById('edit_GiaNhap').value = GiaNhap;
    $('#modal-edit').modal('show');
}

function viewDetails(MaNhap) {
    // Gọi AJAX đến chính file hiện tại với tham số MaNhap
    fetch('?ajax-detail=' + MaNhap)
        .then(response => response.text())
        .then(data => {
            document.getElementById('modal-details-body').innerHTML = data;
            $('#modal-details').modal('show');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra khi tải chi tiết phiếu nhập!');
        });
}

</script>