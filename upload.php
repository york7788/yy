<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

try {
    // 记录请求信息
    error_log("Received upload request");
    error_log("POST data: " . print_r($_POST, true));

    if (!isset($_POST['image']) || !isset($_POST['type'])) {
        throw new Exception('Missing required parameters');
    }

    $uploadDir = 'uploads/';
    
    // 检查并创建上传目录
    if (!file_exists($uploadDir)) {
        if (!mkdir($uploadDir, 0777, true)) {
            error_log("Failed to create directory: " . $uploadDir);
            throw new Exception('Failed to create upload directory');
        }
        error_log("Created directory: " . $uploadDir);
    }

    // 检查目录权限
    if (!is_writable($uploadDir)) {
        error_log("Directory not writable: " . $uploadDir);
        throw new Exception('Upload directory is not writable');
    }

    $base64Data = $_POST['image'];
    
    // 生成文件名
    $filename = uniqid() . '.jpg';
    $filepath = $uploadDir . $filename;
    error_log("Attempting to save file: " . $filepath);

    // 解码并保存图片
    $imageData = base64_decode($base64Data);
    if ($imageData === false) {
        error_log("Failed to decode base64 data");
        throw new Exception('Invalid base64 data');
    }

    $bytesWritten = file_put_contents($filepath, $imageData);
    if ($bytesWritten === false) {
        error_log("Failed to write file: " . $filepath);
        error_log("PHP error: " . error_get_last()['message']);
        throw new Exception('Failed to save image');
    }
    error_log("Successfully wrote " . $bytesWritten . " bytes to " . $filepath);

    // 构建URL
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    $baseUrl = dirname($_SERVER['REQUEST_URI']);
    $imageUrl = $protocol . $host . $baseUrl . '/' . $filepath;
    
    error_log("Generated URL: " . $imageUrl);

    $response = [
        'success' => true,
        'url' => $imageUrl,
        'filepath' => $filepath,
        'filesize' => $bytesWritten
    ];
    
    error_log("Sending response: " . json_encode($response));
    echo json_encode($response);

} catch (Exception $e) {
    error_log("Error occurred: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'details' => error_get_last()
    ]);
}
?> 