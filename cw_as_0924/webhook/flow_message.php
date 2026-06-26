<?php
$_root = dirname(__DIR__);
require_once $_root . '/def_inc.php';
require_once $_root . '/common_lib.php';
require_once __DIR__ . '/_auth.php';

header('Content-Type: application/json');

$_chatId   = isset($_GET['chatId'])   ? (int)$_GET['chatId']   : 0;
$_contents = isset($_GET['contents']) ? trim($_GET['contents']) : '';

if ($_chatId === 0 || $_contents === '') {
    http_response_code(400);
    echo json_encode(array('success' => false, 'error' => 'chatId and contents are required'));
    exit;
}

sendFlowMessage($_chatId, $_contents);

echo json_encode(array('success' => true));
