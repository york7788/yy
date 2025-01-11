<?php
header('Content-Type: application/json');

// 获取 webhook 参数
$webhook = isset($_GET['webhook']) ? $_GET['webhook'] : null;

if (!$webhook) {
    echo json_encode([
        'errcode' => 1,
        'errmsg' => '未指定 webhook 地址'
    ]);
    exit;
}

// 获取请求内容
$content = file_get_contents('php://input');
$data = json_decode($content, true);

if (!$data) {
    echo json_encode([
        'errcode' => 1,
        'errmsg' => '无效的请求数据'
    ]);
    exit;
}

// 发送请求到指定的 webhook
$ch = curl_init($webhook);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($httpCode !== 200) {
    echo json_encode([
        'errcode' => 1,
        'errmsg' => '请求失败：' . curl_error($ch)
    ]);
} else {
    echo $response;
}

curl_close($ch);
?> 