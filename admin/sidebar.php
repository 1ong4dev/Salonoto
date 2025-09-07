<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="/Salonoto/admin/dashboard/" class="brand-link">
        <img src="/Salonoto/assets/img/logo1.png" alt="Salon" style="width: 100%">
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <?php $avatar = empty($_SESSION['Avatar']) ? '/assets/img/user.png' : $_SESSION['Avatar'];?>
                <img src="<?=$avatar?>" class="img-circle elevation-2" alt="User image">
            </div>
            <div class="info">
                <a href="#" class="d-block"><?=$_SESSION['TenDayDu']?></a>
            </div>
        </div>

        <!-- SidebarSearch Form -->
        <div class="form-inline">
            <div class="input-group" data-widget="sidebar-search">
                <input class="form-control form-control-sidebar" type="search" placeholder="Tìm kiếm" aria-label="Search">
                <div class="input-group-append">
                    <button class="btn btn-sidebar">
                        <i class="fas fa-search fa-fw"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2" style="height: calc(100% - 74px)">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <li class="nav-item">
                    <a href="../dashboard/" class="nav-link">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Bảng điều khiển</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../baocaothongke/list.php" class="nav-link">
                        <i class="nav-icon fas fa-chart-bar"></i>
                        <p>Bảng điều khiển</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../loaisp/list.php" class="nav-link">
                        <i class="nav-icon fas fa-clipboard-list"></i>
                        <p>Loại sản phẩm</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../kho/list.php" class="nav-link">
                        <i class="nav-icon fas fa-warehouse"></i>
                        <p>Quản lý kho</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../nhacungcap/list.php" class="nav-link">
                        <i class="nav-icon fas fa-boxes"></i>
                        <p>Nhà cung cấp</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../sanpham/list.php" class="nav-link">
                        <i class="nav-icon fas fa-car"></i>
                        <p>Sản phẩm</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../nhaphang/list.php" class="nav-link">
                        <i class="nav-icon fas fa-arrow-right"></i>
                        <p>Nhập hàng</p>
                    </a>
                </li> 
                <li class="nav-item">
                    <a href="../dichvu/list.php" class="nav-link">
                        <i class="nav-icon fas fa-star"></i>
                        <p>Dịch vụ</p>
                    </a>
                </li> 
                <li class="nav-item">
                    <a href="../dondichvu/list.php" class="nav-link">
                        <i class="nav-icon fas fa-check"></i>
                        <p>Đơn đặt dịch vụ</p>
                    </a>
                </li> 
                <li class="nav-item">
                    <a href="../thanhtoan/list.php" class="nav-link">
                        <i class="nav-icon fas fa-credit-card "></i>
                        <p>Thanh toán</p>
                    </a>
                </li>             
                <li class="nav-item">
                    <a href="../donhang/list.php" class="nav-link">
                        <i class="nav-icon fas fa-truck-loading "></i>
                        <p>Đơn hàng</p>
                    </a>
                </li>
                   <li class="nav-item">
                    <a href="../khuyenmai/list.php" class="nav-link">
                        <i class="nav-icon fas fa-tags "></i>
                        <p>Khuyến Mãi</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../users/list.php" class="nav-link">
                        <i class="nav-icon fas fa-user-circle"></i>
                        <p>Tài khoản</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../sliders/list.php" class="nav-link">
                        <i class="nav-icon fas fa-images"></i>
                        <p>Quảng cáo</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../baocao/list.php" class="nav-link">
                        <i class="nav-icon fas fa-images"></i>
                        <p>Báo Cáo Thống Kê</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../../index.php" class="nav-link">
                        <i class="nav-icon fas fa-sign-out-alt"></i>
                        <p>Trang mua hàng</p>
                    </a>
                </li>
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>
<!-- testt -->