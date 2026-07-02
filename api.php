<?php
/* =====================================================================
   api.php — ukládá data aplikace do souboru data.json vedle sebe.
   Změň si TOKEN níže na stejné heslo, jaké máš v index.html.
   ===================================================================== */

header("Content-Type: application/json; charset=utf-8");
header("Cache-Control: no-store");

$TOKEN = "ZMEN_SI_ME";                 // <-- musí sedět s TOKEN v index.html
$FILE  = __DIR__ . "/data.json";

$method = $_SERVER["REQUEST_METHOD"] ?? "GET";

if ($method === "GET") {
    if (($_GET["token"] ?? "") !== $TOKEN) {
        http_response_code(403);
        echo json_encode(["error" => "forbidden"]);
        exit;
    }
    if (!file_exists($FILE)) { echo "{}"; exit; }
    echo file_get_contents($FILE);
    exit;
}

if ($method === "POST") {
    $raw  = file_get_contents("php://input");
    $body = json_decode($raw, true);
    if (!is_array($body) || ($body["token"] ?? "") !== $TOKEN) {
        http_response_code(403);
        echo json_encode(["error" => "forbidden"]);
        exit;
    }
    if (!array_key_exists("data", $body)) {
        http_response_code(400);
        echo json_encode(["error" => "no data"]);
        exit;
    }
    $json = json_encode($body["data"], JSON_UNESCAPED_UNICODE);
    if (file_put_contents($FILE, $json, LOCK_EX) === false) {
        http_response_code(500);
        echo json_encode(["error" => "write_failed"]);
        exit;
    }
    echo json_encode(["ok" => true]);
    exit;
}

http_response_code(405);
echo json_encode(["error" => "method_not_allowed"]);
