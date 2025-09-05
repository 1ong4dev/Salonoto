-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th9 05, 2025 lúc 05:45 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `doansalon`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chitietdondathang`
--

CREATE TABLE `chitietdondathang` (
  `MaChiTietDonDatHang` int(11) NOT NULL,
  `MaSP` int(11) NOT NULL,
  `MaDonDatHang` varchar(10) NOT NULL,
  `SL` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `chitietdondathang`
--

INSERT INTO `chitietdondathang` (`MaChiTietDonDatHang`, `MaSP`, `MaDonDatHang`, `SL`) VALUES
(4, 1, 'DH875913', 1),
(5, 7, 'DH377579', 1),
(6, 1, 'DH999127', 1),
(7, 1, 'DH441131', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `datdichvu`
--

CREATE TABLE `datdichvu` (
  `MaDatDichVu` int(11) NOT NULL,
  `TenTaiKhoan` varchar(30) NOT NULL,
  `MaDichVu` int(11) NOT NULL,
  `DongXe` varchar(50) NOT NULL,
  `BienSoXe` varchar(10) NOT NULL,
  `NgayDat` datetime DEFAULT current_timestamp(),
  `NgayHen` datetime NOT NULL,
  `TrangThai` enum('ChoXuLy','XacNhan','DaHoanThanh','Huy','TuChoi') NOT NULL DEFAULT 'ChoXuLy',
  `GhiChu` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `datdichvu`
--

INSERT INTO `datdichvu` (`MaDatDichVu`, `TenTaiKhoan`, `MaDichVu`, `DongXe`, `BienSoXe`, `NgayDat`, `NgayHen`, `TrangThai`, `GhiChu`) VALUES
(1, 'Admin', 1, 'Sedan', '15R1-00037', '2025-08-26 13:17:08', '2025-08-28 08:00:00', 'DaHoanThanh', 'Sạch'),
(2, 'a', 6, 'Bán tải', '15R1-00037', '2025-09-05 04:48:25', '2025-09-10 08:00:00', 'TuChoi', 'Đúng giờ '),
(3, 'a', 12, 'Bán tải', '15R1-00037', '2025-09-05 04:48:48', '2025-09-08 08:30:00', 'DaHoanThanh', 'Không có');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `dichvu`
--

CREATE TABLE `dichvu` (
  `MaDichVu` int(11) NOT NULL,
  `TenDichVu` varchar(255) NOT NULL,
  `TrangThai` enum('HoatDong','KhongHoatDong') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `dichvu`
--

INSERT INTO `dichvu` (`MaDichVu`, `TenDichVu`, `TrangThai`) VALUES
(1, 'CHĂM SÓC THƯỜNG XUYÊN', 'HoatDong'),
(4, 'CHĂM SÓC NỘI THẤT bằng máy hơi nước nóng', 'HoatDong'),
(5, 'CHĂM SÓC MÁY', 'HoatDong'),
(6, 'CHĂM SÓC SƠN toàn diện', 'HoatDong'),
(7, 'Phủ Ceramic bảo vệ sơn công nghệ Nhật Bản', 'HoatDong'),
(8, 'Dán PPF bảo vệ bề mặt xe', 'HoatDong'),
(9, 'LÀM SẠCH BỀ MẶT SƠN: Tẩy nhựa cây, Nhựa đường, Phân chim', 'HoatDong'),
(10, 'TẨY BỤI SƠN CÔNG NGHIỆP', 'HoatDong'),
(11, 'CHĂM SÓC KÍNH', 'HoatDong'),
(12, 'CHĂM SÓC VÀNH', 'HoatDong'),
(13, 'ĐIỀU HÒA Ô TÔ', 'HoatDong'),
(14, 'GẦM XE', 'HoatDong'),
(15, 'NHỰA NGOÀI', 'HoatDong'),
(16, 'NHỰA NGOÀI', 'HoatDong');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `dondathang`
--

CREATE TABLE `dondathang` (
  `MaDonDatHang` varchar(10) NOT NULL,
  `TongTien` decimal(15,2) DEFAULT NULL,
  `TrangThai` enum('ChuaThanhToan','DangGiaoHang','DaHoanThanh','Huy','HoanHang') NOT NULL DEFAULT 'ChuaThanhToan',
  `CreatedAt` datetime DEFAULT current_timestamp(),
  `TenTaiKhoan` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `dondathang`
--

INSERT INTO `dondathang` (`MaDonDatHang`, `TongTien`, `TrangThai`, `CreatedAt`, `TenTaiKhoan`) VALUES
('DH377579', 3400000.00, 'Huy', '2025-08-26 03:40:26', 'a'),
('DH441131', 19500000.00, 'DaHoanThanh', '2025-08-26 13:07:54', 'a'),
('DH875913', 19500000.00, 'Huy', '2025-08-26 03:25:43', 'a'),
('DH999127', 19500000.00, 'HoanHang', '2025-08-26 03:48:16', 'a');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `giohang`
--

CREATE TABLE `giohang` (
  `MaSP` int(11) NOT NULL,
  `TenTaiKhoan` varchar(30) NOT NULL,
  `SL` int(11) DEFAULT 1,
  `UpdatedAt` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `kho`
--

CREATE TABLE `kho` (
  `MaSP` int(11) NOT NULL,
  `SLTon` int(11) DEFAULT 0,
  `LastUpdated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `kho`
--

INSERT INTO `kho` (`MaSP`, `SLTon`, `LastUpdated`) VALUES
(1, 9, '2025-08-28 20:05:42'),
(7, 6, '2025-08-23 19:41:07');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `loaisp`
--

CREATE TABLE `loaisp` (
  `MaLoaiSP` int(11) NOT NULL,
  `TenLoaiSP` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `loaisp`
--

INSERT INTO `loaisp` (`MaLoaiSP`, `TenLoaiSP`) VALUES
(7, 'Ca lăng - mặt ca lăng '),
(4, 'Cảm biến'),
(3, 'Camera hành trình'),
(8, 'Lazang - Mâm xe'),
(6, 'Loa '),
(9, 'Lọc không khí'),
(1, 'Màn hình'),
(5, 'Nước hoa hoa - sáp thơm xe hơi'),
(2, 'Đèn xe');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nhacungcap`
--

CREATE TABLE `nhacungcap` (
  `MaNCC` int(11) NOT NULL,
  `TenNCC` varchar(100) NOT NULL,
  `SDT` varchar(15) NOT NULL,
  `Fax` varchar(13) DEFAULT NULL,
  `DiaChi` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `nhacungcap`
--

INSERT INTO `nhacungcap` (`MaNCC`, `TenNCC`, `SDT`, `Fax`, `DiaChi`) VALUES
(1, 'GOTECH TRADING AND PRODUCTION., JSC', '0942283336', '0109149600', 'Số 31 lô E2/D21 khu ĐTM Cầu Giấy, đường Tôn Thất Thuyết, , Thành phố Hà Nội, Việt Nam');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nhaphang`
--

CREATE TABLE `nhaphang` (
  `MaNhap` int(11) NOT NULL,
  `MaSP` int(11) NOT NULL,
  `SL` int(11) NOT NULL,
  `TGNhap` datetime DEFAULT current_timestamp(),
  `MaNCC` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `nhaphang`
--

INSERT INTO `nhaphang` (`MaNhap`, `MaSP`, `SL`, `TGNhap`, `MaNCC`) VALUES
(1, 1, 5, '2025-08-24 02:33:58', 1),
(3, 7, 2, '2025-08-24 02:36:41', 0),
(4, 7, 2, '2025-08-24 02:38:49', 0),
(5, 7, 2, '2025-08-24 02:41:07', 0),
(6, 1, 1, '2025-08-24 02:46:03', 1),
(7, 1, 1, '2025-08-24 02:46:33', 1),
(8, 1, 1, '2025-08-26 01:07:42', 1),
(9, 1, 2, '2025-08-26 13:11:20', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `quyen`
--

CREATE TABLE `quyen` (
  `MaQuyen` int(11) NOT NULL,
  `TenQuyen` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `quyen`
--

INSERT INTO `quyen` (`MaQuyen`, `TenQuyen`) VALUES
(3, 'Khách hàng'),
(2, 'Nhân viên'),
(1, 'Quản trị viên');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `sanpham`
--

CREATE TABLE `sanpham` (
  `MaSP` int(11) NOT NULL,
  `TenSP` varchar(100) NOT NULL,
  `ThongSoSanPham` text NOT NULL,
  `Gia` decimal(15,2) NOT NULL,
  `HinhAnh` varchar(255) NOT NULL,
  `UpdatedAt` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `MaLoaiSP` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `sanpham`
--

INSERT INTO `sanpham` (`MaSP`, `TenSP`, `ThongSoSanPham`, `Gia`, `HinhAnh`, `UpdatedAt`, `MaLoaiSP`) VALUES
(1, 'Màn Hình Gotech 2K GT 13i 360', 'Thiết bị chạy hệ điều hành Android 12, trang bị CPU 8 nhân tốc độ 2.0GHz, RAM 4GB, ROM 64GB và GPU Mali G52 cho hiệu năng mạnh mẽ. Màn hình QLED 2K sắc nét, độ phân giải 2000x1200. Hỗ trợ kết nối 4G, phát WiFi, Bluetooth, FM/AM Radio, tích hợp cảm biến áp suất lốp, kết nối hệ thống loa trên xe và xử lý âm thanh số DSP 32 kênh. Kho ứng dụng CH Play sẵn sàng phục vụ nhu cầu giải trí và tiện ích thông minh.', 19500000.00, '/Salonoto/assets/img/sanpham/a8.jpg', '2025-08-24 02:23:43', 1),
(7, 'Camera Hành Trình VietMap C61 Pro', 'Camera hành trình sử dụng cảm biến Sony Starvis với độ phân giải 2880x2160 (4K UHD), cho hình ảnh sắc nét vượt trội. Góc quay rộng 170º kết hợp khẩu độ F2.0 giúp ghi hình rõ nét cả ban đêm. Hỗ trợ kết nối WiFi 2.4G, tích hợp GPS, hệ thống cảnh báo giao thông và cảm biến G-Sensor đảm bảo an toàn và tiện ích tối đa cho người lái.', 3400000.00, '/Salonoto/assets/img/sanpham/a14.png', '2025-08-24 02:36:30', 3),
(8, 'Nước hoa GRASSE', 'Nước hoa ô tô Grasse Bullsone có 4 mùi thơm để lựa chọn, mỗi hương thơm đều tạo ra ra một không gian thư giãn và thoải mái riêng. Phù hợp sở thích và các mục đích sử dụng khác nhau.', 350000.00, '/Salonoto/assets/img/sanpham/a1.jpg', '2025-09-04 03:17:17', 5),
(9, 'Ca lăng Maybach cho Mercedes E200 Exclusive', 'E200 Exclusive toát lên vẻ sang trọng, lịch lãm với bộ lưới tản nhiệt gồm các nan ngang kim loại và logo ngôi sao 3 cánh nhỏ hướng lên trên giống với dòng sedan đầu bảng S-Class.', 1500000.00, '/Salonoto/assets/img/sanpham/a11.png', '2025-09-04 03:17:49', 7),
(10, 'Loa Sub ô tô Audison APBX 10DS', 'Loa Audison APBX 10DS có kích thước khá lớn 472x334x120mm và nặng tới 8.63kg nên nó phù hợp lắp đặt ở những chiếc xe hơi có khoang cốp rộng.Ngoài ra, công suất của APBX 10DS dao động từ 400W - 800W, cho phép tần số âm thanh được khuếch đại tốt ngay cả trong không gian rộng, nâng trải nghiệm âm thanh của bạn lên tầm cao mới.', 8500000.00, '/Salonoto/assets/img/sanpham/a12.png', '2025-09-04 03:18:20', 6),
(11, 'Cảm biến lùi 4 mắt cho xe ô tô', 'Thiết bị gồm 4 mắt cảm biến hồng ngoại, hiển thị khoảng cách 0–2.5m trên đồng hồ kích thước 96x27x20mm. Hoạt động ở điện áp 12V (9–16V), tần số siêu âm 40KHz, chịu nhiệt từ -30 đến +70°C. Tích hợp chuông cảnh báo tiện lợi.', 350000.00, '/Salonoto/assets/img/sanpham/a13.png', '2025-09-04 03:18:44', 4),
(12, 'Màn hình ô tô Santek X800 ', 'Camera hành trình sử dụng cảm biến Sony Starvis với độ phân giải 2880x2160 (4K UHD), cho hình ảnh sắc nét vượt trội. Góc quay rộng 170º kết hợp khẩu độ F2.0 giúp ghi hình rõ nét cả ban đêm. Hỗ trợ kết nối WiFi 2.4G, tích hợp GPS, hệ thống cảnh báo giao thông và cảm biến G-Sensor đảm bảo an toàn và tiện ích tối đa cho người lái.', 10500000.00, '/Salonoto/assets/img/sanpham/a6.jpg', '2025-09-04 03:20:04', 1),
(13, 'Đèn Bi Pha Aozoom X - Led Pro Domax', 'Đèn có nhiệt độ màu 6000K, cho ánh sáng trắng sáng rõ. Công suất 54W (cos) và 60W (pha), kích thước gọn 3 inch, chiếu xa đến 700m. Hoạt động ổn định trong nhiệt độ từ -40 đến 105°C, đạt chuẩn chống nước IP65 và tuổi thọ lên đến 50.000 giờ.', 9800000.00, '/Salonoto/assets/img/sanpham/a15.png', '2025-09-04 03:20:33', 2),
(14, 'Màn Hình Zestech Z18', 'Thiết bị sử dụng chip UIS8581 với CPU Octa-core ARM Cortex A55, tốc độ 1.6GHz, RAM 2GB và ROM 32GB, chạy hệ điều hành Android 10. Màn hình cảm ứng IPS Full HD kích thước 9 hoặc 10 inch, độ phân giải 1280x720px, phủ kính cường lực 2.5D. Hỗ trợ kết nối WiFi, Bluetooth và SIM 4G, sản xuất năm 2024, đáp ứng tốt nhu cầu giải trí và điều khiển thông minh trên xe.', 4900000.00, '/Salonoto/assets/img/sanpham/a7.jpg', '2025-09-04 03:21:04', 1),
(15, 'LAZANG DAEWOO GENTRA 14 INCH PCD 4X100', 'Dòng lazang độ cho lazang xe Gentra của hãng Rapidash có thông số 14 inch, PCD 4X100, màu đen đỏ được làm từ hợp kim nhôm cao cấp và sơn tĩnh điện cực kỳ bền đẹp.', 58000000.00, '/Salonoto/assets/img/sanpham/a10.png', '2025-09-04 03:21:42', 8),
(16, 'Máy lọc không khí ô tô Hàn Quốc Allo A600 (APS-600)', '- Máy lọc không khi thương hiệu Allo Korea Hàn Quốc  Allo là thương hiệu phụ kiện ô tô nổi tiếng tại Hàn Quốc  - Máy lọc không khí Allo A600 với thiết kế đẹp hiện đại  - Máy lọc không khí dựa trên sinh ra ION âm và lọc không khí với quạt gió và màng lọc Hepa  - Nồng độ ion âm của máy lên đến 5 triệu đơn vị /cm3 giúp lọc không khí khử mùi, khử khuẩn  - Màng lọc 3 lớp : lớp cotton, lớp than hoạt tính, lớp màng lọc Hepa giúp lọc sách tới 99.97% bụi mịn và vi khuẩn  - Quạt gió 2 tốc độ thay đổi được, chế độ quạt turbo mạnh mẽ.  - 312 lỗ xung quanh máy giúp lấy không khí từ các hướng  - Độ ồn của máy nhỏ ở mức 30dp đến 35dp  - Nguồn điện sử dụng điện USB, có thể dùng trên xe ô tô hay ở nhà, văn phòng  - Kích thước sản phẩm 70x70x186 mm', 950000.00, '/Salonoto/assets/img/sanpham/a10.jpg', '2025-09-05 01:18:11', 9);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `sliders`
--

CREATE TABLE `sliders` (
  `SliderID` int(11) NOT NULL,
  `SliderName` varchar(255) DEFAULT NULL,
  `Description` varchar(255) DEFAULT NULL,
  `Thumbnail` varchar(255) DEFAULT NULL,
  `Status` tinyint(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `sliders`
--

INSERT INTO `sliders` (`SliderID`, `SliderName`, `Description`, `Thumbnail`, `Status`) VALUES
(1, 'Slider 1', 'Mô tả slide 1', '/Salon/assets/img/sliders/s1.png', 1),
(2, 'Slider 2', 'Mô tả slide 2', '/Salon/assets/img/sliders/s2.png', 1),
(3, 'Slider 3', 'Mô tả slide 3', '/Salon/assets/img/sliders/s3.png', 1),
(4, 'Slider 4', 'Mô tả slide 4', '/Salon/assets/img/sliders/s4.png', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `thanhtoan`
--

CREATE TABLE `thanhtoan` (
  `MaTT` int(11) NOT NULL,
  `TenTaiKhoan` varchar(30) NOT NULL,
  `TongTien` decimal(15,2) NOT NULL,
  `PhuongThucTT` varchar(50) NOT NULL,
  `TrangThaiTT` enum('ChoXuLy','HoanTat','Huy') DEFAULT 'ChoXuLy',
  `MaGiaoDich` varchar(100) DEFAULT NULL,
  `MaDonDatHang` varchar(50) DEFAULT NULL,
  `NgayTT` datetime DEFAULT current_timestamp(),
  `UpdatedAt` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `GhiChu` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `thanhtoan`
--

INSERT INTO `thanhtoan` (`MaTT`, `TenTaiKhoan`, `TongTien`, `PhuongThucTT`, `TrangThaiTT`, `MaGiaoDich`, `MaDonDatHang`, `NgayTT`, `UpdatedAt`, `GhiChu`) VALUES
(3, 'a', 19500000.00, 'ChuyenKhoan', 'Huy', 'GD1720655', 'DH875913', '2025-08-26 03:25:43', '2025-08-26 03:49:29', ''),
(4, 'a', 3400000.00, 'COD', 'Huy', 'GD9141514', 'DH377579', '2025-08-26 03:40:26', '2025-08-26 03:49:29', ''),
(5, 'a', 19500000.00, 'Momo', 'HoanTat', 'GD1203987', 'DH999127', '2025-08-26 03:48:16', '2025-08-29 03:03:57', ''),
(6, 'a', 19500000.00, 'COD', 'HoanTat', 'GD4853086', 'DH441131', '2025-08-26 13:07:54', '2025-08-26 13:09:18', '');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `TenTaiKhoan` varchar(30) NOT NULL,
  `TenDayDu` varchar(50) NOT NULL,
  `MatKhau` varchar(100) NOT NULL,
  `SDT` varchar(15) NOT NULL,
  `DiaChi` varchar(100) NOT NULL,
  `Avatar` varchar(255) NOT NULL,
  `TrangThai` tinyint(4) DEFAULT 1,
  `CreatedAt` datetime DEFAULT current_timestamp(),
  `MaQuyen` int(11) DEFAULT NULL,
  `Email` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`TenTaiKhoan`, `TenDayDu`, `MatKhau`, `SDT`, `DiaChi`, `Avatar`, `TrangThai`, `CreatedAt`, `MaQuyen`, `Email`) VALUES
('a', 'Nguyễn Văn A', '$2y$10$lkFnHraEzpGrXtWrvULOjeDotFtAXWM5GiZqn80BZMtEkGVMQOnNm', '0123456788', 'Thành Tô, Hải An, TP. Hải Phòng', '/Salonoto/assets/img/a.png', 1, '2025-08-26 01:04:39', 3, 'user1@gmail.com'),
('Admin', 'Vũ Minh Hiếu', '$2y$10$TkhZIvgByID1VobpBQS.8eV7iDGHX7eBQVnmXk/6a0s.ZTCJ04Ewa', '0904482072', 'TP. Hải Phòng', '/Salonoto/assets/img/hacker.png', 1, '2025-08-26 01:03:01', 1, 'hieuvm.0101@gmail.com');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `chitietdondathang`
--
ALTER TABLE `chitietdondathang`
  ADD PRIMARY KEY (`MaChiTietDonDatHang`),
  ADD KEY `MaSP` (`MaSP`),
  ADD KEY `MaDonDatHang` (`MaDonDatHang`);

--
-- Chỉ mục cho bảng `datdichvu`
--
ALTER TABLE `datdichvu`
  ADD PRIMARY KEY (`MaDatDichVu`),
  ADD KEY `TenTaiKhoan` (`TenTaiKhoan`),
  ADD KEY `MaDichVu` (`MaDichVu`);

--
-- Chỉ mục cho bảng `dichvu`
--
ALTER TABLE `dichvu`
  ADD PRIMARY KEY (`MaDichVu`);

--
-- Chỉ mục cho bảng `dondathang`
--
ALTER TABLE `dondathang`
  ADD PRIMARY KEY (`MaDonDatHang`),
  ADD KEY `TenTaiKhoan` (`TenTaiKhoan`);

--
-- Chỉ mục cho bảng `giohang`
--
ALTER TABLE `giohang`
  ADD PRIMARY KEY (`MaSP`,`TenTaiKhoan`),
  ADD KEY `TenTaiKhoan` (`TenTaiKhoan`);

--
-- Chỉ mục cho bảng `kho`
--
ALTER TABLE `kho`
  ADD PRIMARY KEY (`MaSP`);

--
-- Chỉ mục cho bảng `loaisp`
--
ALTER TABLE `loaisp`
  ADD PRIMARY KEY (`MaLoaiSP`),
  ADD UNIQUE KEY `TenLoaiSP` (`TenLoaiSP`);

--
-- Chỉ mục cho bảng `nhacungcap`
--
ALTER TABLE `nhacungcap`
  ADD PRIMARY KEY (`MaNCC`);

--
-- Chỉ mục cho bảng `nhaphang`
--
ALTER TABLE `nhaphang`
  ADD PRIMARY KEY (`MaNhap`),
  ADD KEY `MaSP` (`MaSP`);

--
-- Chỉ mục cho bảng `quyen`
--
ALTER TABLE `quyen`
  ADD PRIMARY KEY (`MaQuyen`),
  ADD UNIQUE KEY `TenQuyen` (`TenQuyen`);

--
-- Chỉ mục cho bảng `sanpham`
--
ALTER TABLE `sanpham`
  ADD PRIMARY KEY (`MaSP`),
  ADD UNIQUE KEY `TenSP` (`TenSP`),
  ADD KEY `MaLoaiSP` (`MaLoaiSP`);

--
-- Chỉ mục cho bảng `sliders`
--
ALTER TABLE `sliders`
  ADD PRIMARY KEY (`SliderID`);

--
-- Chỉ mục cho bảng `thanhtoan`
--
ALTER TABLE `thanhtoan`
  ADD PRIMARY KEY (`MaTT`),
  ADD KEY `TenTaiKhoan` (`TenTaiKhoan`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`TenTaiKhoan`),
  ADD KEY `MaQuyen` (`MaQuyen`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `chitietdondathang`
--
ALTER TABLE `chitietdondathang`
  MODIFY `MaChiTietDonDatHang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT cho bảng `datdichvu`
--
ALTER TABLE `datdichvu`
  MODIFY `MaDatDichVu` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `dichvu`
--
ALTER TABLE `dichvu`
  MODIFY `MaDichVu` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT cho bảng `loaisp`
--
ALTER TABLE `loaisp`
  MODIFY `MaLoaiSP` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT cho bảng `nhacungcap`
--
ALTER TABLE `nhacungcap`
  MODIFY `MaNCC` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `nhaphang`
--
ALTER TABLE `nhaphang`
  MODIFY `MaNhap` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT cho bảng `quyen`
--
ALTER TABLE `quyen`
  MODIFY `MaQuyen` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `sanpham`
--
ALTER TABLE `sanpham`
  MODIFY `MaSP` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT cho bảng `sliders`
--
ALTER TABLE `sliders`
  MODIFY `SliderID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `thanhtoan`
--
ALTER TABLE `thanhtoan`
  MODIFY `MaTT` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `chitietdondathang`
--
ALTER TABLE `chitietdondathang`
  ADD CONSTRAINT `chitietdondathang_ibfk_1` FOREIGN KEY (`MaSP`) REFERENCES `sanpham` (`MaSP`),
  ADD CONSTRAINT `chitietdondathang_ibfk_2` FOREIGN KEY (`MaDonDatHang`) REFERENCES `dondathang` (`MaDonDatHang`);

--
-- Các ràng buộc cho bảng `datdichvu`
--
ALTER TABLE `datdichvu`
  ADD CONSTRAINT `datdichvu_ibfk_1` FOREIGN KEY (`TenTaiKhoan`) REFERENCES `users` (`TenTaiKhoan`),
  ADD CONSTRAINT `datdichvu_ibfk_2` FOREIGN KEY (`MaDichVu`) REFERENCES `dichvu` (`MaDichVu`);

--
-- Các ràng buộc cho bảng `dondathang`
--
ALTER TABLE `dondathang`
  ADD CONSTRAINT `dondathang_ibfk_1` FOREIGN KEY (`TenTaiKhoan`) REFERENCES `users` (`TenTaiKhoan`);

--
-- Các ràng buộc cho bảng `giohang`
--
ALTER TABLE `giohang`
  ADD CONSTRAINT `giohang_ibfk_1` FOREIGN KEY (`MaSP`) REFERENCES `sanpham` (`MaSP`),
  ADD CONSTRAINT `giohang_ibfk_2` FOREIGN KEY (`TenTaiKhoan`) REFERENCES `users` (`TenTaiKhoan`);

--
-- Các ràng buộc cho bảng `kho`
--
ALTER TABLE `kho`
  ADD CONSTRAINT `kho_ibfk_1` FOREIGN KEY (`MaSP`) REFERENCES `sanpham` (`MaSP`);

--
-- Các ràng buộc cho bảng `nhaphang`
--
ALTER TABLE `nhaphang`
  ADD CONSTRAINT `nhaphang_ibfk_1` FOREIGN KEY (`MaSP`) REFERENCES `sanpham` (`MaSP`);

--
-- Các ràng buộc cho bảng `sanpham`
--
ALTER TABLE `sanpham`
  ADD CONSTRAINT `sanpham_ibfk_1` FOREIGN KEY (`MaLoaiSP`) REFERENCES `loaisp` (`MaLoaiSP`);

--
-- Các ràng buộc cho bảng `thanhtoan`
--
ALTER TABLE `thanhtoan`
  ADD CONSTRAINT `thanhtoan_ibfk_1` FOREIGN KEY (`TenTaiKhoan`) REFERENCES `users` (`TenTaiKhoan`);

--
-- Các ràng buộc cho bảng `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`MaQuyen`) REFERENCES `quyen` (`MaQuyen`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
