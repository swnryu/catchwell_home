<?php
$_secret = isset($_GET['secret']) ? $_GET['secret'] : '';
if (!hash_equals(WEBHOOK_SECRET, $_secret)) {
    header('Content-Type: application/json');
    http_response_code(403);
    echo json_encode(array('success' => false, 'error' => 'Unauthorized'));
    exit;
}
