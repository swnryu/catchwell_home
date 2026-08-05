<?php
session_start();
include("../common.php");
include("../def_inc.php");

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['ADMIN_USERID'])) {
    echo json_encode(array('ok' => false, 'msg' => '로그인 필요'));
    exit;
}

$photo_idx = isset($_POST['photo_idx']) ? (int)$_POST['photo_idx'] : 0;
if (!$photo_idx) {
    echo json_encode(array('ok' => false, 'msg' => '잘못된 요청'));
    exit;
}

$row = $db->object("as_repair_photos", "where idx='$photo_idx'");
if (!$row) {
    echo json_encode(array('ok' => false, 'msg' => '존재하지 않는 사진'));
    exit;
}

$file_path = $_SERVER['DOCUMENT_ROOT'] . '/cw_as/online_as/files/repair/' . $row->file_name;
if (file_exists($file_path)) {
    @unlink($file_path);
}

$db->delete("as_repair_photos", "where idx='$photo_idx'");

echo json_encode(array('ok' => true));
