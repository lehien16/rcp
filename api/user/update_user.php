<?php
header('Content-Type: application/json; charset=utf-8');
include "../db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Chỉ chấp nhận POST request'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $mand = $input['MaND'] ?? '';
    $tendn = $input['TenDN'] ?? '';
    $hoten = $input['HoTen'] ?? '';
    $sodt = $input['SoDT'] ?? '';
    $thanhpho = $input['ThanhPho'] ?? '';
    $ngaysinh = $input['NgaySinh'] ?? '';
    $email = $input['Email'] ?? '';
    $matkhau = $input['MatKhau'] ?? '';
    $quyenhan = $input['QuyenHan'] ?? '';
    $avatar = $input['Avatar'] ?? '';
    $matkhau_hientai = $input['MatKhauHienTai'] ?? '';
    
    if (empty($mand) || empty($tendn) || empty($hoten) || empty($email) || empty($quyenhan)) {
        throw new Exception('Vui lòng điền đầy đủ thông tin bắt buộc');
    }
    
    // Kiểm tra mật khẩu hiện tại
    if (empty($matkhau_hientai)) {
        throw new Exception('Vui lòng nhập mật khẩu hiện tại để xác nhận');
    }
    
    // Kiểm tra người dùng tồn tại và mật khẩu hiện tại
    $checkSql = "SELECT MatKhau FROM NguoiDung WHERE MaND = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("s", $mand);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!$user) {
        throw new Exception('Không tìm thấy người dùng với mã: ' . $mand);
    }
    
    // Kiểm tra mật khẩu hiện tại có đúng không
    if ($user['MatKhau'] !== $matkhau_hientai) {
        throw new Exception('Mật khẩu hiện tại không đúng');
    }
    
    if (!empty($matkhau)) {
        $sql = "UPDATE NguoiDung SET TenDN=?, HoTen=?, SoDT=?, ThanhPho=?, NgaySinh=?, Email=?, MatKhau=?, QuyenHan=?, Avatar=? WHERE MaND=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssssss", $tendn, $hoten, $sodt, $thanhpho, $ngaysinh, $email, $matkhau, $quyenhan, $avatar, $mand);
    } else {
        $sql = "UPDATE NguoiDung SET TenDN=?, HoTen=?, SoDT=?, ThanhPho=?, NgaySinh=?, Email=?, QuyenHan=?, Avatar=? WHERE MaND=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssss", $tendn, $hoten, $sodt, $thanhpho, $ngaysinh, $email, $quyenhan, $avatar, $mand);
    }
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Cập nhật người dùng thành công'], JSON_UNESCAPED_UNICODE);
    } else {
        throw new Exception('Lỗi khi cập nhật người dùng: ' . $stmt->error);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>