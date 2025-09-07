<?php include '../header.php'?>

<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Thêm khuyến mãi
    if (isset($_POST['action']) && $_POST['action'] == 'add') {
        $code = $_POST['code'] ?? '';
        $value = $_POST['value'] ?? '';
        $type = $_POST['type'] ?? '';
        $expire = $_POST['expire'] ?? '';
        $limit = $_POST['limit'] ?? 1;

        // validate
        if ($type == 'PERCENT' && $value > 100) {
            $message = ['type' => 'warning', 'text' => 'Giảm giá % không được lớn hơn 100'];
        } elseif (!empty($code) && !empty($value)) {
            $sql = "INSERT INTO MaGiamGia (MaCode, GiaTri, Kieu, HanSuDung, SoLanSuDung) 
                    VALUES ('$code', '$value', '$type', '$expire', '$limit')";
            if (Database::NonQuery($sql)) {
                $message = ['type' => 'success', 'text' => 'Thêm thành công'];
            }
        } else {
            $message = ['type' => 'warning'];
            if (empty($code)) $message['text'][] = 'Mã code không được trống';
            if (empty($value)) $message['text'][] = 'Giá trị không được trống';
        }
    }

    // Sửa khuyến mãi
    if (isset($_POST['action']) && $_POST['action'] == 'edit') {
        $id = $_GET['edit-id'] ?? '';
        $value = $_POST['value'] ?? '';
        $type = $_POST['type'] ?? '';
        $expire = $_POST['expire'] ?? '';
        $limit = $_POST['limit'] ?? 1;

        if ($type == 'PERCENT' && $value > 100) {
            $message = ['type' => 'warning', 'text' => 'Giảm giá % không được lớn hơn 100'];
        } elseif (!empty($value)) {
            $sql = "UPDATE MaGiamGia 
                    SET GiaTri = '$value', Kieu = '$type', HanSuDung = '$expire', SoLanSuDung = '$limit'
                    WHERE MaCode = '$id'";
            if (Database::NonQuery($sql)) {
                $message = ['type' => 'success', 'text' => 'Cập nhật thành công'];
            }
        } else {
            $message = ['type' => 'warning'];
            if (empty($value)) $message['text'][] = 'Giá trị không được trống';
        }
    }
}

// Xoá khuyến mãi
if (isset($_GET['del-id'])) {
    $id = $_GET['del-id'];
    $sql = "DELETE FROM MaGiamGia WHERE MaCode = '$id'";
    if (Database::NonQuery($sql)) {
        $message = ['type' => 'success', 'text' => 'Xoá thành công'];
    }
}
?>

<?php include '../sidebar.php'?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Khuyến mãi</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">
                            <a href="<?=ADMIN_URL?>/"><i class="fas fa-home"></i></a>
                        </li>
                        <li class="breadcrumb-item active">Khuyến mãi</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <?php include '../alert.php'?>

        <!-- Modal: Thêm -->
        <div class="modal fade" id="modal-add" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable" role="document">
                <form class="modal-content" method="POST">
                    <div class="modal-header bg-primary">
                        <h5 class="modal-title">Thêm khuyến mãi</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group"><label>Mã code</label><input type="text" name="code" class="form-control"></div>
                        <div class="form-group"><label>Giá trị</label><input type="number" name="value" class="form-control"></div>
                        <div class="form-group">
                            <label>Kiểu</label>
                            <select name="type" class="form-control" id="type-add">
                                <option value="PERCENT">%</option>
                                <option value="AMOUNT">VND</option>
                            </select>
                        </div>
                        <div class="form-group"><label>Hạn sử dụng</label><input type="date" name="expire" class="form-control"></div>
                        <div class="form-group"><label>Số lần sử dụng</label><input type="number" name="limit" value="1" class="form-control"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Huỷ</button>
                        <button name="action" value="add" class="btn btn-success">Thêm</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal: Sửa -->
        <?php
        $id = $_GET['edit-id'] ?? '';
        $km = [];
        if ($id != '') {
            $sql = "SELECT * FROM MaGiamGia WHERE MaCode = '$id'";
            $km = Database::GetData($sql, ['row'=>0]);
        }
        ?>
        <div class="modal fade" id="modal-edit" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable" role="document">
                <form class="modal-content" method="POST">
                    <div class="modal-header bg-primary">
                        <h5 class="modal-title">Sửa khuyến mãi</h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group"><label>Mã code</label><input type="text" value="<?=$km['MaCode']?>" class="form-control" disabled></div>
                        <div class="form-group"><label>Giá trị</label><input type="number" name="value" value="<?=$km['GiaTri']?>" class="form-control"></div>
                        <div class="form-group">
                            <label>Kiểu</label>
                            <select name="type" class="form-control" id="type-edit">
                                <option value="PERCENT" <?=$km['Kieu']=='PERCENT'?'selected':''?>>%</option>
                                <option value="AMOUNT" <?=$km['Kieu']=='AMOUNT'?'selected':''?>>VND</option>
                            </select>
                        </div>
                        <div class="form-group"><label>Hạn sử dụng</label><input type="date" name="expire" value="<?=$km['HanSuDung']?>" class="form-control"></div>
                        <div class="form-group"><label>Số lần sử dụng</label><input type="number" name="limit" value="<?=$km['SoLanSuDung']?>" class="form-control"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Huỷ</button>
                        <button name="action" value="edit" class="btn btn-success">Sửa</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="container-fluid">
            <div class="row my-2 d-flex-end">
                <button type="button" class="btn btn-success mx-2" data-toggle="modal" data-target="#modal-add"><i class="fas fa-plus"></i></button>
                <form method="GET">
                    <div class="input-group">
                        <input type="text" name="keyword" placeholder="Mã code" class="form-control">
                        <div class="input-group-append">
                            <button class="btn btn-outline-info"><i class="fas fa-search"></i></button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="row my-2">
                <div class="card" style="width: 100%">
                    <div class="card-body">
                        <table class="table table-hover table-bordered">
                            <thead class="table-warning">
                                <tr>
                                    <th>Mã Code</th>
                                    <th>Giá trị</th>
                                    <th>Kiểu</th>
                                    <th>Hạn sử dụng</th>
                                    <th>Số lần sử dụng</th>
                                    <th width="111">Công cụ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $page = $_GET['page'] ?? 1;
                                $pager = (new Pagination())->get('MaGiamGia', $page, ROW_OF_PAGE);

                                $keyword = $_GET['keyword'] ?? '';
                                $filter = $keyword ? "WHERE MaCode LIKE '%$keyword%'" : '';

                                $sql = "SELECT * FROM MaGiamGia $filter ORDER BY MaCode DESC LIMIT ".$pager['StartIndex'].', '.ROW_OF_PAGE;
                                $data = Database::GetData($sql);

                                if ($data) {
                                    foreach ($data as $row) {
                                        echo '
                                        <tr>
                                            <td>'.$row['MaCode'].'</td>
                                            <td>'.($row['Kieu']=='PERCENT' ? $row['GiaTri'].'%' : number_format($row['GiaTri']).' VND').'</td>
                                            <td>'.($row['Kieu']=='PERCENT' ? '%' : 'VND').'</td>
                                            <td>'.$row['HanSuDung'].'</td>
                                            <td>'.$row['SoLanSuDung'].'</td>
                                            <td>
                                                <a href="?edit-id='.$row['MaCode'].'" class="btn btn-warning"><i class="fas fa-marker"></i></a>
                                                <a onclick="removeRow(\''.$row['MaCode'].'\')" class="btn btn-danger"><i class="fas fa-trash-alt"></i></a>
                                            </td>
                                        </tr>';
                                    }
                                } else {
                                    echo '<tr><td colspan="100%" class="text-center">Không có dữ liệu</td></tr>';
                                }
                                ?>
                                <button type="button" data-toggle="modal" data-target="#modal-edit" hidden><i class="fas fa-plus"></i></button>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="row my-2 d-flex-between">
                <div>Hiển thị từ <?=$pager['StartPage']?> đến <?=$pager['EndPage']?> của <?=$pager['TotalItems']?> bản ghi</div>
                <ul class="pagination">
                    <?php
                    for ($i=1; $i<=$pager['TotalPages']; $i++) {
                        $active = $page==$i?'active':''; 
                        echo '<li class="page-item '.$active.'"><a class="page-link" href="?page='.$i.'">'.$i.'</a></li>';
                    }
                    ?>
                </ul>
            </div>
        </div>
    </section>
</div>

<?php include '../footer.php'?>

<script>
$(document).ready(function() {
    function GetParameterValues(param) {
        var url = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
        for (var i = 0; i < url.length; i++) {
            var urlparam = url[i].split('=');
            if (urlparam[0] == param) return urlparam[1];
        }
    }
    if (GetParameterValues('edit-id') != undefined) {
        document.querySelector("[data-target='#modal-edit']").click();
    }

    // validate % không quá 100 khi nhập
    $('input[name="value"]').on('input', function(){
        const type = $(this).closest('form').find('select[name="type"]').val();
        if(type === 'PERCENT' && $(this).val() > 100){
            $(this).val(100);
        }
    });
});

function removeRow(id) {
    if (confirm('Bạn có chắc chắn muốn xoá không?')) {
        window.location = '?del-id=' + id;
    }
}
</script>
