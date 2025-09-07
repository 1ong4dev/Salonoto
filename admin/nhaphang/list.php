<?php include '../header.php'?>

<?php
// Lấy danh sách sản phẩm và nhà cung cấp
$products = Database::GetData("SELECT MaSP, TenSP FROM SanPham");
$nhacungcap = Database::GetData("SELECT MaNCC, TenNCC FROM NhaCungCap");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Thêm phiếu nhập
    if (isset($_POST['action']) && $_POST['action'] == 'add') {
        $MaSP    = $_POST['MaSP'] ?? '';
        $SL      = (int) ($_POST['SL'] ?? 0);
        $MaNCC   = $_POST['MaNCC'] ?? '';
        $GiaNhap = (float) ($_POST['GiaNhap'] ?? 0);

        if ($MaSP && $SL > 0 && $MaNCC && $GiaNhap > 0) {
            // Lấy giá bán từ bảng SanPham
            $giaBan = Database::GetData("SELECT Gia FROM SanPham WHERE MaSP=$MaSP", ['cell'=>'Gia']);
            if ($GiaNhap < $giaBan * 0.9) {
                // Insert phiếu nhập
                Database::NonQuery("INSERT INTO NhapHang (MaSP, SL, GiaNhap, MaNCC) 
                                    VALUES ($MaSP, $SL, $GiaNhap, $MaNCC)");

                // Cập nhật kho
                $exist = Database::GetData("SELECT * FROM Kho WHERE MaSP=$MaSP", ['row'=>0]);
                if ($exist) {
                    Database::NonQuery("UPDATE Kho SET SLTon = SLTon + $SL WHERE MaSP=$MaSP");
                } else {
                    Database::NonQuery("INSERT INTO Kho (MaSP, SLTon) VALUES ($MaSP, $SL)");
                }

                $message = ['type'=>'success','text'=>'Nhập hàng thành công!'];
            } else {
                $message = ['type'=>'warning','text'=>'Giá nhập phải nhỏ hơn giá bán ít nhất 10%!'];
            }
        } else {
            $message = ['type'=>'warning','text'=>'Vui lòng nhập đầy đủ thông tin hợp lệ!'];
        }
    }

    // Sửa phiếu nhập
    if (isset($_POST['action']) && $_POST['action'] == 'edit') {
        $id      = $_POST['edit_id'] ?? '';
        $MaSP    = $_POST['MaSP'] ?? '';
        $SL      = (int) ($_POST['SL'] ?? 0);
        $MaNCC   = $_POST['MaNCC'] ?? '';
        $GiaNhap = (float) ($_POST['GiaNhap'] ?? 0);

        if ($id && $MaSP && $SL > 0 && $MaNCC && $GiaNhap > 0) {
            $giaBan = Database::GetData("SELECT Gia FROM SanPham WHERE MaSP=$MaSP", ['cell'=>'Gia']);
            if ($GiaNhap < $giaBan * 0.9) {
                // Lấy SL cũ
                $old = Database::GetData("SELECT SL, MaSP FROM NhapHang WHERE MaNhap=$id", ['row'=>0]);
                if ($old) {
                    $delta = $SL - $old['SL'];
                    Database::NonQuery("UPDATE Kho SET SLTon = SLTon + $delta WHERE MaSP={$old['MaSP']}");
                }

                Database::NonQuery("UPDATE NhapHang 
                                    SET MaSP=$MaSP, SL=$SL, GiaNhap=$GiaNhap, MaNCC=$MaNCC 
                                    WHERE MaNhap=$id");
                $message = ['type'=>'success','text'=>'Cập nhật phiếu nhập thành công!'];
            } else {
                $message = ['type'=>'warning','text'=>'Giá nhập phải nhỏ hơn giá bán ít nhất 10%!'];
            }
        } else {
            $message = ['type'=>'warning','text'=>'Vui lòng nhập đầy đủ thông tin hợp lệ!'];
        }
    }
} // ✅ đóng if ($_SERVER['REQUEST_METHOD'] == 'POST')

// Xóa phiếu nhập
if (isset($_GET['del-id'])) {
    $id = $_GET['del-id'];
    $old = Database::GetData("SELECT MaSP, SL FROM NhapHang WHERE MaNhap=$id", ['row'=>0]);
    if ($old) {
        Database::NonQuery("UPDATE Kho SET SLTon = SLTon - {$old['SL']} WHERE MaSP={$old['MaSP']}");
    }
    Database::NonQuery("DELETE FROM NhapHang WHERE MaNhap=$id");
    $message = ['type'=>'success','text'=>'Xóa phiếu nhập thành công!'];
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
            <div class="modal-dialog modal-dialog-scrollable" role="document">
                <form class="modal-content" method="POST">
                    <div class="modal-header bg-primary">
                        <h5 class="modal-title">Thêm phiếu nhập</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Sản phẩm</label>
                            <select name="MaSP" class="form-control" required>
                                <option value="" disabled selected hidden>Chọn sản phẩm</option>
                                <?php foreach($products as $sp): ?>
                                    <option value="<?=$sp['MaSP']?>"><?=$sp['TenSP']?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Nhà cung cấp</label>
                            <select name="MaNCC" class="form-control" required>
                                <option value="" disabled selected hidden>Chọn nhà cung cấp</option>
                                <?php foreach($nhacungcap as $ncc): ?>
                                    <option value="<?=$ncc['MaNCC']?>"><?=$ncc['TenNCC']?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Số lượng</label>
                            <input type="number" name="SL" class="form-control" min="1" required>
                        </div>
                        <div class="form-group">
                            <label>Giá nhập</label>
                            <input type="number" step="0.01" name="GiaNhap" class="form-control" min="1" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Huỷ</button>
                        <button name="action" value="add" class="btn btn-success">Thêm</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal: Edit -->
        <div class="modal fade" id="modal-edit" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable" role="document">
                <form class="modal-content" method="POST">
                    <div class="modal-header bg-primary">
                        <h5 class="modal-title">Sửa phiếu nhập</h5>
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
                            <label>Nhà cung cấp</label>
                            <select name="MaNCC" id="edit_MaNCC" class="form-control" required>
                                <option value="" disabled selected hidden>Chọn nhà cung cấp</option>
                                <?php foreach($nhacungcap as $ncc): ?>
                                    <option value="<?=$ncc['MaNCC']?>"><?=$ncc['TenNCC']?></option>
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

        <div class="container-fluid">
            <div class="row my-2 d-flex-end">
                <button type="button" class="btn btn-success mx-2" data-toggle="modal" data-target="#modal-add">
                    <i class="fas fa-plus"></i>
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
                                    <th>Sản phẩm</th>
                                    <th>Nhà cung cấp</th>
                                    <th>Số lượng</th>
                                    <th>Giá nhập</th>
                                    <th>Tổng tiền</th>
                                    <th>Ngày nhập</th>
                                    <th width="111">Công cụ</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            $where = '';
                            if (!empty($_GET['keyword'])) {
                                $kw = addslashes($_GET['keyword']);
                                $where = "WHERE sp.TenSP LIKE '%$kw%' OR nc.TenNCC LIKE '%$kw%'";
                            }

                            $sqlNhap = "SELECT n.MaNhap, n.MaSP, n.MaNCC, sp.TenSP, nc.TenNCC, 
                                               n.SL, n.GiaNhap, (n.SL*n.GiaNhap) AS TongTien, n.TGNhap
                                        FROM NhapHang n
                                        JOIN SanPham sp ON n.MaSP = sp.MaSP
                                        JOIN NhaCungCap nc ON n.MaNCC = nc.MaNCC
                                        $where
                                        ORDER BY n.TGNhap DESC";

                            $nhapHang = Database::GetData($sqlNhap);

                            if ($nhapHang) {
                                foreach($nhapHang as $nh) {
                                    echo '<tr>
                                        <td>'.$nh['MaNhap'].'</td>
                                        <td>'.$nh['TenSP'].'</td>
                                        <td>'.$nh['TenNCC'].'</td>
                                        <td>'.$nh['SL'].'</td>
                                        <td>'.Helper::Currency($nh['GiaNhap']).'</td>
                                        <td>'.Helper::Currency($nh['TongTien']).'</td>
                                        <td>'.$nh['TGNhap'].'</td>
                                        <td>
                                            <button class="btn btn-warning" 
                                                onclick="editRow('.$nh['MaNhap'].',\''.$nh['MaSP'].'\',\''.$nh['MaNCC'].'\',\''.$nh['SL'].'\',\''.$nh['GiaNhap'].'\')">
                                                <i class="fas fa-marker"></i>
                                            </button>
                                            <a onclick="removeRow('.$nh['MaNhap'].')" class="btn btn-danger"><i class="fas fa-trash-alt"></i></a>
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
function removeRow(id) {
    if(confirm('Bạn có chắc chắn muốn xoá không?')) {
        window.location = '?del-id='+id;
    }
}

function editRow(id, MaSP, MaNCC, SL, GiaNhap) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_MaSP').value = MaSP;
    document.getElementById('edit_MaNCC').value = MaNCC;
    document.getElementById('edit_SL').value = SL;
    document.getElementById('edit_GiaNhap').value = GiaNhap;
    $('#modal-edit').modal('show');
}
</script>
