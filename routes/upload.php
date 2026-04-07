<?php

use Tabi\SDK\TabiClient;
use Wasa\Env;

header('Content-Type: application/json');

$apiKey = Env::get('TABI_API_KEY');
if (!$apiKey) {
    http_response_code(500);
    echo json_encode(['error' => 'TABI_API_KEY not set']);
    exit;
}

if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'No file provided or upload error']);
    exit;
}

$maxSize = 16 * 1024 * 1024; // 16 MB
if ($_FILES['file']['size'] > $maxSize) {
    $sizeMB = number_format($_FILES['file']['size'] / (1024 * 1024), 1);
    http_response_code(413);
    echo json_encode(['error' => "File too large ({$sizeMB} MB). Maximum is 16 MB."]);
    exit;
}

$to          = $_POST['to'] ?? '';
$messageType = $_POST['messageType'] ?? 'image';
$content     = $_POST['content'] ?? "Sent via Wasa ({$messageType})";
$channelId   = $_POST['channelId'] ?? Env::get('TABI_CHANNEL_ID');

$rawBytes = file_get_contents($_FILES['file']['tmp_name']);
$mime     = $_FILES['file']['type'] ?: 'application/octet-stream';
$dataUrl  = 'data:' . $mime . ';base64,' . base64_encode($rawBytes);

try {
    $tabi = new TabiClient($apiKey, Env::get('TABI_BASE_URL', 'https://api.c36.online/api/v1'));
    $result = $tabi->messages()->send($channelId, [
        'to'          => $to,
        'content'     => $content,
        'messageType' => $messageType,
        'mediaUrl'    => $dataUrl,
    ]);
    echo json_encode(['ok' => true, 'data' => $result]);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
