<?php
// いいね処理用スクリプト

header('Content-Type: application/json');

$id = $_POST['id'] ?? null;

if (!$id) {
    http_response_code(400);
    echo json_encode(['error' => 'ID is required']);
    exit;
}

$filePath = __DIR__ . '/data/storage.json';

if (!file_exists($filePath)) {
    http_response_code(404);
    echo json_encode(['error' => 'No posts found']);
    exit;
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

$found = false;
foreach ($posts as &$post) {
    if ($post['id'] === $id) {
        $post['likes'] += 1;
        $found = true;
        break;
    }
}
unset($post);

if (!$found) {
    http_response_code(404);
    flock($fp, LOCK_UN);
    fclose($fp);
    echo json_encode(['error' => 'Post not found']);
    exit;
}

rewind($fp);
ftruncate($fp, 0);
fwrite($fp, json_encode($posts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
flock($fp, LOCK_UN);
fclose($fp);

echo json_encode(['success' => true, 'id' => $id]);
