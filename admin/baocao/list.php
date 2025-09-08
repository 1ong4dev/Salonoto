<?php include '../header.php' ?>
<?php include '../sidebar.php' ?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <h1 class="m-0">Báo cáo tổng hợp</h1>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">

      <!-- Tabs -->
      <ul class="nav nav-tabs" id="reportTabs" role="tablist">
        <li class="nav-item"><a class="nav-link active" id="sales-tab" data-toggle="tab" href="#sales" role="tab">Doanh thu bán hàng</a></li>
        <li class="nav-item"><a class="nav-link" id="service-tab" data-toggle="tab" href="#service" role="tab">Dịch vụ</a></li>
        <li class="nav-item"><a class="nav-link" id="product-tab" data-toggle="tab" href="#product" role="tab">Sản phẩm bán chạy</a></li>
        <li class="nav-item"><a class="nav-link" id="import-tab" data-toggle="tab" href="#import" role="tab">Nhập hàng</a></li>
        <li class="nav-item"><a class="nav-link" id="stock-tab" data-toggle="tab" href="#stock" role="tab">Tồn kho</a></li>
      </ul>

      <div class="tab-content mt-3">
        <!-- Doanh thu bán hàng -->
        <div class="tab-pane fade show active" id="sales" role="tabpanel">
          <?php
          // Thống kê doanh thu và đơn hàng
          $sqlSales = "
            SELECT DATE_FORMAT(NgayTT, '%Y-%m') AS Thang, SUM(TongTien) AS DoanhThu, COUNT(*) AS SoDon
            FROM thanhtoan
            WHERE TrangThaiTT = 'HoanTat'
            GROUP BY DATE_FORMAT(NgayTT, '%Y-%m')
            ORDER BY Thang ASC
          ";
          $sales = Database::GetData($sqlSales);

          // Tổng số đơn hàng
          $tongDon = Database::GetData("SELECT COUNT(*) AS c FROM thanhtoan WHERE TrangThaiTT='HoanTat'")[0]['c'] ?? 0;

          // Đơn hàng hôm nay
          $donNgay = Database::GetData("SELECT COUNT(*) AS c FROM thanhtoan WHERE TrangThaiTT='HoanTat' AND DATE(NgayTT)=CURDATE()")[0]['c'] ?? 0;

          // Đơn hàng tháng này
          $donThang = Database::GetData("SELECT COUNT(*) AS c FROM thanhtoan WHERE TrangThaiTT='HoanTat' AND MONTH(NgayTT)=MONTH(CURDATE()) AND YEAR(NgayTT)=YEAR(CURDATE())")[0]['c'] ?? 0;

          // Đơn hàng tháng trước
          $donThangTruoc = Database::GetData("SELECT COUNT(*) AS c FROM thanhtoan WHERE TrangThaiTT='HoanTat' AND MONTH(NgayTT)=MONTH(CURDATE()-INTERVAL 1 MONTH) AND YEAR(NgayTT)=YEAR(CURDATE()-INTERVAL 1 MONTH)")[0]['c'] ?? 0;

          // Doanh thu hôm nay
          $dtNgay = Database::GetData("SELECT SUM(TongTien) AS t FROM thanhtoan WHERE TrangThaiTT='HoanTat' AND DATE(NgayTT)=CURDATE()")[0]['t'] ?? 0;

          // Doanh thu tháng này
          $dtThang = Database::GetData("SELECT SUM(TongTien) AS t FROM thanhtoan WHERE TrangThaiTT='HoanTat' AND MONTH(NgayTT)=MONTH(CURDATE()) AND YEAR(NgayTT)=YEAR(CURDATE())")[0]['t'] ?? 0;

          // Doanh thu tháng trước
          $dtThangTruoc = Database::GetData("SELECT SUM(TongTien) AS t FROM thanhtoan WHERE TrangThaiTT='HoanTat' AND MONTH(NgayTT)=MONTH(CURDATE()-INTERVAL 1 MONTH) AND YEAR(NgayTT)=YEAR(CURDATE()-INTERVAL 1 MONTH)")[0]['t'] ?? 0;

          // % thay đổi
          $percentOrder = $donThangTruoc > 0 ? round((($donThang - $donThangTruoc) / $donThangTruoc) * 100, 1) : 0;
          $percentRevenue = $dtThangTruoc > 0 ? round((($dtThang - $dtThangTruoc) / $dtThangTruoc) * 100, 1) : 0;

          // Hàm hiển thị mũi tên rích rắc SVG
         
function zigzagArrow($percent) {
  if ($percent > 0) {
    return "<span class='text-success'>
      <svg width='500' height='60' xmlns='http://www.w3.org/2000/svg'>
        <polyline points='5,55 60,30 120,45 180,20 240,40 300,10 360,35 420,15 480,1'
                  stroke='green' stroke-width='3' fill='none'/>
      </svg>
      +{$percent}%
    </span>";
  } elseif ($percent < 0) {
    return "<span class='text-danger'>
      <svg width='500' height='60' xmlns='http://www.w3.org/2000/svg'>
        <polyline points='5,5 60,30 120,15 180,40 240,20 300,55 360,25 420,45 480,70'
                  stroke='red' stroke-width='3' fill='none'/>
      </svg>
      {$percent}%
    </span>";
  } else {
    return "<span class='text-muted'>0%</span>";
  }
}


          ?>
          
          <div class="row">
            <!-- Bảng trái: Đơn hàng -->
            <div class="col-md-6">
              <div class="card border-primary">
                <div class="card-header bg-primary text-white">Tình hình đơn hàng</div>
                <div class="card-body">
                  <p><b>Tổng số đơn:</b> <?=$tongDon?></p>
                  <p><b>Đơn hôm nay:</b> <?=$donNgay?></p>
                  <p><b>Đơn tháng này:</b> <?=$donThang?></p>
                  <p><b>So với tháng trước:</b> <?=zigzagArrow($percentOrder)?></p>
                </div>
              </div>
            </div>

            <!-- Bảng phải: Doanh thu -->
            <div class="col-md-6">
              <div class="card border-success">
                <div class="card-header bg-success text-white">Tình hình doanh thu</div>
                <div class="card-body">
                  <p><b>Doanh thu hôm nay:</b> <?=Helper::Currency($dtNgay)?></p>
                  <p><b>Doanh thu tháng này:</b> <?=Helper::Currency($dtThang)?></p>
                  <p><b>So với tháng trước:</b> <?=zigzagArrow($percentRevenue)?></p>
                </div>
              </div>
            </div>
          </div>

          <!-- Biểu đồ doanh thu -->
          <h5 class="mt-4">Biểu đồ cột doanh thu theo tháng</h5>
          <canvas id="chartSales" height="100"></canvas>
        </div>

        <!-- Dịch vụ -->
        <div class="tab-pane fade" id="service" role="tabpanel">
          <?php
          $services = Database::GetData("
            SELECT dv.TenDichVu, COUNT(ddv.MaDatDichVu) AS SoLuong
            FROM datdichvu ddv
            JOIN dichvu dv ON ddv.MaDichVu = dv.MaDichVu
            WHERE ddv.TrangThai = 'DaHoanThanh'
            GROUP BY dv.TenDichVu
            ORDER BY SoLuong DESC
          ");
          ?>
          <table class="table table-bordered table-striped">
            <thead class="table-warning"><tr><th>Dịch vụ</th><th>Số lần đặt</th></tr></thead>
            <tbody>
              <?php foreach($services as $s): ?>
                <tr><td><?=$s['TenDichVu']?></td><td><?=$s['SoLuong']?></td></tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <canvas id="chartService" height="100"></canvas>
        </div>

        <!-- Sản phẩm bán chạy -->
        <div class="tab-pane fade" id="product" role="tabpanel">
          <?php
          $products = Database::GetData("
            SELECT sp.TenSP, SUM(ct.SL) AS TongBan
            FROM chitietdondathang ct
            JOIN sanpham sp ON ct.MaSP = sp.MaSP
            JOIN dondathang dh ON ct.MaDonDatHang = dh.MaDonDatHang
            JOIN thanhtoan tt ON dh.MaDonDatHang = tt.MaDonDatHang
            WHERE tt.TrangThaiTT = 'HoanTat'
            GROUP BY sp.TenSP
            ORDER BY TongBan DESC
            LIMIT 10
          ");
          ?>
          <table class="table table-bordered table-striped">
            <thead class="table-info"><tr><th>Sản phẩm</th><th>Số lượng bán</th></tr></thead>
            <tbody>
              <?php foreach($products as $p): ?>
                <tr><td><?=$p['TenSP']?></td><td><?=$p['TongBan']?></td></tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <canvas id="chartProduct" height="100"></canvas>
        </div>

        <!-- Nhập hàng -->
        <div class="tab-pane fade" id="import" role="tabpanel">
          <?php
          $imports = Database::GetData("
            SELECT sp.TenSP, SUM(nh.SL) AS TongSL, SUM(nh.SL*nh.GiaNhap) AS TongTien
            FROM nhaphang nh
            JOIN sanpham sp ON nh.MaSP = sp.MaSP
            GROUP BY sp.TenSP
            ORDER BY TongSL DESC
            LIMIT 10
          ");
          ?>
          <table class="table table-bordered table-striped">
            <thead class="table-secondary"><tr><th>Sản phẩm</th><th>Tổng số lượng nhập</th><th>Tổng tiền nhập</th></tr></thead>
            <tbody>
              <?php foreach($imports as $i): ?>
                <tr><td><?=$i['TenSP']?></td><td><?=$i['TongSL']?></td><td><?=Helper::Currency($i['TongTien'])?></td></tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <canvas id="chartImport" height="100"></canvas>
        </div>

        <!-- Tồn kho -->
        <div class="tab-pane fade" id="stock" role="tabpanel">
          <?php
          $stocks = Database::GetData("
            SELECT sp.MaSP, sp.TenSP, k.SLTon, 
                   GROUP_CONCAT(DISTINCT nc.TenNCC SEPARATOR ', ') AS NhaCungCap
            FROM SanPham sp
            LEFT JOIN Kho k ON sp.MaSP = k.MaSP
            LEFT JOIN NhapHang nh ON sp.MaSP = nh.MaSP
            LEFT JOIN NhaCungCap nc ON nh.MaNCC = nc.MaNCC
            GROUP BY sp.MaSP, sp.TenSP, k.SLTon
            ORDER BY sp.TenSP ASC
          ");
          ?>
          <table class="table table-bordered table-striped">
            <thead class="table-dark text-white"><tr><th>Mã SP</th><th>Tên SP</th><th>Số lượng tồn</th><th>Nhà cung cấp</th></tr></thead>
            <tbody>
              <?php if($stocks): foreach($stocks as $st): ?>
                <tr>
                  <td><?=$st['MaSP']?></td>
                  <td><?=$st['TenSP']?></td>
                  <td><?=$st['SLTon']?></td>
                  <td><?=$st['NhaCungCap']?></td>
                </tr>
              <?php endforeach; else: ?>
                <tr><td colspan="4" class="text-center">Không có dữ liệu</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
          <canvas id="chartStock" height="100"></canvas>
        </div>
      </div>
    </div>
  </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Biểu đồ doanh thu
new Chart(document.getElementById('chartSales').getContext('2d'), {
  type: 'bar',
  data: {
    labels: [<?php foreach($sales as $r) echo "'".$r['Thang']."',"; ?>],
    datasets: [{
      label: 'Doanh thu',
      data: [<?php foreach($sales as $r) echo $r['DoanhThu'].","; ?>],
      backgroundColor: 'rgba(54, 162, 235, 0.6)'
    }]
  },
  options: {responsive: true, plugins: {legend:{display:false}}, scales:{y:{beginAtZero:true}} }
});

// Dịch vụ
new Chart(document.getElementById('chartService').getContext('2d'), {
  type: 'bar',
  data: {
    labels: [<?php foreach($services as $s) echo "'".$s['TenDichVu']."',"; ?>],
    datasets: [{ label: 'Số lần đặt', data: [<?php foreach($services as $s) echo $s['SoLuong'].","; ?>], backgroundColor: 'rgba(75, 192, 192, 0.6)' }]
  },
  options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
});

// Sản phẩm bán chạy
new Chart(document.getElementById('chartProduct').getContext('2d'), {
  type: 'bar',
  data: {
    labels: [<?php foreach($products as $p) echo "'".$p['TenSP']."',"; ?>],
    datasets: [{ label: 'Số lượng bán', data: [<?php foreach($products as $p) echo $p['TongBan'].","; ?>], backgroundColor: 'rgba(255, 99, 132, 0.6)' }]
  },
  options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
});

// Nhập hàng
new Chart(document.getElementById('chartImport').getContext('2d'), {
  type: 'bar',
  data: {
    labels: [<?php foreach($imports as $i) echo "'".$i['TenSP']."',"; ?>],
    datasets: [
      { label: 'Số lượng nhập', data: [<?php foreach($imports as $i) echo $i['TongSL'].","; ?>], backgroundColor: 'rgba(54, 162, 235, 0.6)' },
      { label: 'Tổng tiền nhập', data: [<?php foreach($imports as $i) echo $i['TongTien'].","; ?>], backgroundColor: 'rgba(255, 206, 86, 0.6)' }
    ]
  },
  options: { responsive: true, scales: { y: { beginAtZero: true } } }
});

// Tồn kho
new Chart(document.getElementById('chartStock').getContext('2d'), {
  type: 'bar',
  data: {
    labels: [<?php foreach($stocks as $st) echo "'".$st['TenSP']."',"; ?>],
    datasets: [{ label: 'Số lượng tồn', data: [<?php foreach($stocks as $st) echo $st['SLTon'].","; ?>], backgroundColor: 'rgba(153, 102, 255, 0.6)' }]
  },
  options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
});
</script>

<?php include '../footer.php' ?>
