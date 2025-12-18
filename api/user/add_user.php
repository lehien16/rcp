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
    
    // Debug: Log dữ liệu nhận được
    error_log("Add User - Received data: " . json_encode($input));
    
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
    
    // Debug: Log các giá trị sau khi parse
    error_log("Add User - QuyenHan value: '" . $quyenhan . "'");
    
    if (empty($mand) || empty($tendn) || empty($hoten) || empty($email) || empty($matkhau) || empty($quyenhan)) {
        throw new Exception('Vui lòng điền đầy đủ thông tin bắt buộc');
    }
    
    // Kiểm tra từng trường riêng biệt để thông báo cụ thể
    $checkMaND = "SELECT COUNT(*) as count FROM NguoiDung WHERE MaND = ?";
    $stmt = $conn->prepare($checkMaND);
    $stmt->bind_param("s", $mand);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->fetch_assoc()['count'] > 0) {
        throw new Exception('Mã người dùng đã tồn tại: ' . $mand);
    }
    
    $checkTenDN = "SELECT COUNT(*) as count FROM NguoiDung WHERE TenDN = ?";
    $stmt = $conn->prepare($checkTenDN);
    $stmt->bind_param("s", $tendn);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->fetch_assoc()['count'] > 0) {
        throw new Exception('Tên đăng nhập đã tồn tại: ' . $tendn);
    }
    
    $checkEmail = "SELECT COUNT(*) as count FROM NguoiDung WHERE Email = ?";
    $stmt = $conn->prepare($checkEmail);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->fetch_assoc()['count'] > 0) {
        throw new Exception('Email đã tồn tại: ' . $email);
    }
    
    $sql = "INSERT INTO NguoiDung (MaND, TenDN, HoTen, SoDT, ThanhPho, NgaySinh, Email, MatKhau, QuyenHan, Avatar) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssssss", $mand, $tendn, $hoten, $sodt, $thanhpho, $ngaysinh, $email, $matkhau, $quyenhan, $avatar);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Thêm người dùng thành công'], JSON_UNESCAPED_UNICODE);
    } else {
        throw new Exception('Lỗi khi thêm người dùng: ' . $stmt->error);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>