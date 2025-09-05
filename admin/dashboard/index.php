<?php include '../header.php'?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<?php include '../sidebar.php'?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Bảng điều khiển</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">
                            <a href="/Salonoto/admin/dashboard/"><i class="fas fa-home"></i></a>
                        </li>
                        <li class="breadcrumb-item active">Bảng điều khiển</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-primary">
                            <h4>Tổng số đơn hàng đã hoàn thành theo năm</h4>
                        </div>
                        <div class="card-body">
                            <canvas id="ordersOfYear"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-primary">
                            <h4>Doanh thu từ đơn hàng đã hoàn thành theo năm</h4>
                        </div>
                        <div class="card-body">
                            <canvas id="moneyOfYear"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- /.content -->

<?php
// Lấy dữ liệu chỉ từ các đơn đã hoàn thành
$sql = "SELECT MONTH(CreatedAt) AS Month, COUNT(*) AS Number, SUM(TongTien) AS Money 
        FROM dondathang 
        WHERE TrangThai='DaHoanThanh'
        GROUP BY MONTH(CreatedAt)";
$datas = Database::GetData($sql);

// Khởi tạo mảng 12 tháng
$ordersOfYearValue = array_fill(0, 12, 0);
$moneyOfYearValue  = array_fill(0, 12, 0);

foreach ($datas as $data) {
    $ordersOfYearValue[$data['Month'] - 1] = $data['Number'];
    $moneyOfYearValue[$data['Month'] - 1]  = $data['Money'];
}
?>

<script>
const data = {
    labels: ["Tháng 1","Tháng 2","Tháng 3","Tháng 4","Tháng 5","Tháng 6",
             "Tháng 7","Tháng 8","Tháng 9","Tháng 10","Tháng 11","Tháng 12"],
    datasets: [{
        label: 'Tổng số đơn hàng',
        data: <?= json_encode($ordersOfYearValue) ?>,
        backgroundColor: 'rgba(54, 162, 235, 0.2)',
        borderColor: 'rgb(54, 162, 235)',
        borderWidth: 1
    }]
};

const data1 = {
    labels: ["Tháng 1","Tháng 2","Tháng 3","Tháng 4","Tháng 5","Tháng 6",
             "Tháng 7","Tháng 8","Tháng 9","Tháng 10","Tháng 11","Tháng 12"],
    datasets: [{
        label: 'Doanh thu',
        data: <?= json_encode($moneyOfYearValue) ?>,
        backgroundColor: 'rgba(255, 159, 64, 0.2)',
        borderColor: 'rgb(255, 159, 64)',
        borderWidth: 1
    }]
};

const configOrders = { type: 'bar', data: data, options: { scales: { y: { beginAtZero: true } } } };
const configMoney  = { type: 'bar', data: data1, options: { scales: { y: { beginAtZero: true } } } };

new Chart(document.getElementById("ordersOfYear"), configOrders);
new Chart(document.getElementById("moneyOfYear"), configMoney);
</script>
</div>

<?php include '../footer.php'?>
