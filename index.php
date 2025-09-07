 <?php include 'header.php'?>
<div class="slider-area">
    <!-- Slider -->
    <div class="block-slider block-slider4">
        <ul class="" id="bxslider-home4">
                <?php
                        $sql = 'SELECT * FROM sliders WHERE Status = 1';
                        $sliders = Database::GetData($sql);
                            if ($sliders) {
                                foreach ($sliders as $slider) {
                                    echo '<li>
                                            <img src="' . $slider['Thumbnail'] . '" alt="Slide">
                                            <!-- <div class="caption-group">
                                                <h2 class="caption title">' . $slider['SliderName'] . '</h2>
                                                <h4 class="caption subtitle">' . $slider['Description'] . '</h4>
                                            </div> -->
                                          </li>';
                                }
                            }
                ?>
        </ul>
    </div>
    <!-- ./Slider -->
</div> <!-- End slider area -->

<div class="promo-area">
    <div class="zigzag-bottom"></div>
    <div class="container">
        <div class="row">
            <div class="col-md-3 col-sm-6">
                <div class="single-promo promo1">
                    <i class="fas fa-sync"></i>
                    <p>Hoàn trả 30 ngày</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="single-promo promo2">
                    <i class="fas fa-truck"></i>
                    <p>Vận chuyển nhanh</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="single-promo promo3">
                    <i class="fas fa-lock"></i>
                    <p>Thanh toán an toàn</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="single-promo promo4">
                    <i class="fas fa-gift"></i>
                    <p>Quà tặng khuyến mãi</p>
                </div>
            </div>
        </div>
    </div>
</div> <!-- End promo area -->

<div class="maincontent-area">
    <div class="zigzag-bottom"></div>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="latest-product">
                    <h2 class="section-title">Sản phẩm mới nhất</h2>
                    <div class="product-carousel">
                        <?php
                            $sql = 'SELECT * FROM SanPham ORDER BY UpdatedAt LIMIT 5';
                            $SanPham = Database::GetData($sql);
                            foreach ($SanPham as $sp) {
                            ?>
                        <div class="single-product">
                            <div class="product-f-image">
                                <img src="<?=$sp['HinhAnh']?>" alt="">
                                <div class="product-hover">
                                    <?php if (isset($_SESSION['MaQuyen']) && $_SESSION['MaQuyen'] == 3) {?>
                                    <a href="<?='/Salonoto/cart.php?id=' . $sp['MaSP']?>" class="add-to-cart-link"><i class="fa fa-shopping-cart"></i> Thêm vào giỏ</a>
                                    <?php }?>
                                    <a href="<?='/Salonoto/details.php?id=' . $sp['MaSP']?>" class="view-details-link"><i class="fa fa-link"></i> Chi tiết</a>
                                </div>
                            </div>
                            <h2><a href="<?='/Salonoto/details.php?id=' . $sp['MaSP']?>"><?=$sp['TenSP']?></a></h2>
                            <div class="product-carousel-price">
                                <ins><?=Helper::Currency($sp['Gia'])?></ins>
                            </div>
                        </div>
                        <?php }?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> <!-- End main content area -->
<?php include 'footer.php'?>