<?php
// 投稿追加用スクリプト

header('Content-Type: application/json');

$text = $_POST['text'] ?? null;

if (!$text || trim($text) === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Text is required']);
    exit;
}

$filePath = __DIR__ . '/data/storage.json';

if (!file_exists($filePath)) {
    file_put_contents($filePath, json_encode([]), LOCK_EX);
}

$fp = fopen($filePath, 'c+');
if (!$fp) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to open file']);
    exit;
}

flock($fp, LOCK_EX);

$contents = stream_get_contents($fp);
$posts = json_decode($contents ?: '[]', true);

if (!is_array($posts)) {
    $posts = [];
}

$newPost = [
    'id' => uniqid(),
    'text' => $text,
    'likes' => 0,
    'timestamp' => date('c'),
];

$posts[] = $newPost;

rewind($fp);
ftruncate($fp, 0);
fwrite($fp, json_encode($posts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
flock($fp, LOCK_UN);
fclose($fp);

echo json_encode(['success' => true, 'post' => $newPost]);
