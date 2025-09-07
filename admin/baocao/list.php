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
          $sqlSales = "
            SELECT DATE_FORMAT(NgayTT, '%Y-%m') AS Thang, SUM(TongTien) AS DoanhThu, COUNT(*) AS SoDon
            FROM thanhtoan
            WHERE TrangThaiTT = 'HoanTat'
            GROUP BY DATE_FORMAT(NgayTT, '%Y-%m')
            ORDER BY Thang DESC
          ";
          $sales = Database::GetData($sqlSales);
          ?>
          <table class="table table-bordered table-striped">
            <thead class="table-success"><tr><th>Tháng</th><th>Số đơn</th><th>Doanh thu</th></tr></thead>
            <tbody>
              <?php foreach($sales as $r): ?>
                <tr><td><?=$r['Thang']?></td><td><?=$r['SoDon']?></td><td><?=Helper::Currency($r['DoanhThu'])?></td></tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <canvas id="chartSales" height="100"></canvas>
        </div>

        <!-- Dịch vụ -->
        <div class="tab-pane fade" id="service" role="tabpanel">
          <?php
          $sqlService = "
            SELECT dv.TenDichVu, COUNT(ddv.MaDatDichVu) AS SoLuong
            FROM datdichvu ddv
            JOIN dichvu dv ON ddv.MaDichVu = dv.MaDichVu
            WHERE ddv.TrangThai = 'DaHoanThanh'
            GROUP BY dv.TenDichVu
            ORDER BY SoLuong DESC
          ";
          $services = Database::GetData($sqlService);
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
          $sqlProduct = "
            SELECT sp.TenSP, SUM(ct.SL) AS TongBan
            FROM chitietdondathang ct
            JOIN sanpham sp ON ct.MaSP = sp.MaSP
            JOIN dondathang dh ON ct.MaDonDatHang = dh.MaDonDatHang
            JOIN thanhtoan tt ON dh.MaDonDatHang = tt.MaDonDatHang
            WHERE tt.TrangThaiTT = 'HoanTat'
            GROUP BY sp.TenSP
            ORDER BY TongBan DESC
            LIMIT 10
          ";
          $products = Database::GetData($sqlProduct);
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
          $sqlImport = "
            SELECT sp.TenSP, SUM(nh.SL) AS TongSL, SUM(nh.SL*nh.GiaNhap) AS TongTien
            FROM nhaphang nh
            JOIN sanpham sp ON nh.MaSP = sp.MaSP
            GROUP BY sp.TenSP
            ORDER BY TongSL DESC
            LIMIT 10
          ";
          $imports = Database::GetData($sqlImport);
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
          $sqlStock = "
            SELECT sp.MaSP, sp.TenSP, k.SLTon, 
                   GROUP_CONCAT(DISTINCT nc.TenNCC SEPARATOR ', ') AS NhaCungCap
            FROM SanPham sp
            LEFT JOIN Kho k ON sp.MaSP = k.MaSP
            LEFT JOIN NhapHang nh ON sp.MaSP = nh.MaSP
            LEFT JOIN NhaCungCap nc ON nh.MaNCC = nc.MaNCC
            GROUP BY sp.MaSP, sp.TenSP, k.SLTon
            ORDER BY sp.TenSP ASC
          ";
          $stocks = Database::GetData($sqlStock);
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
  // Doanh thu bán hàng
  new Chart(document.getElementById('chartSales').getContext('2d'), {
    type: 'bar',
    data: {
      labels: [<?php foreach($sales as $r) echo "'".$r['Thang']."',"; ?>],
      datasets: [{ label: 'Doanh thu', data: [<?php foreach($sales as $r) echo $r['DoanhThu'].","; ?>], backgroundColor: 'rgba(54, 162, 235, 0.6)' }]
    }
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
