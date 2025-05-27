<?php
// CORSヘッダーを設定（必要に応じてドメインを限定してください）
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// JSONファイルのパス
$jsonFilePath = __DIR__ . '/data/storage.json';

// ファイルが存在し、読み込み可能か確認
if (!file_exists($jsonFilePath)) {
    http_response_code(404);
    echo json_encode(["error" => "データファイルが見つかりません。"]);
    exit;
}

$jsonData = file_get_contents($jsonFilePath);
if ($jsonData === false) {
    http_response_code(500);
    echo json_encode(["error" => "ファイルの読み込みに失敗しました。"]);
    exit;
}

// JSONをデコード
$posts = json_decode($jsonData, true);

// デコードに失敗した場合
if (!is_array($posts)) {
    http_response_code(500);
    echo json_encode(["error" => "JSONデータのパースに失敗しました。"]);
    exit;
}

// 投稿をいいね数の降順にソート
usort($posts, function ($a, $b) {
    return ($b['likes'] ?? 0) <=> ($a['likes'] ?? 0);
});

// 出力
echo json_encode($posts, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
