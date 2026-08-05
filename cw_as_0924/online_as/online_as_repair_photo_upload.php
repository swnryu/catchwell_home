<?php
session_start();
include("../common.php");
include("../def_inc.php");

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['ADMIN_USERID'])) {
    echo json_encode(array('ok' => false, 'msg' => '로그인 필요'));
    exit;
}

$as_idx  = isset($_POST['as_idx'])  ? (int)$_POST['as_idx']        : 0;
$reg_num = isset($_POST['reg_num']) ? trim($_POST['reg_num'])       : '';

if (!$as_idx || $reg_num === '') {
    echo json_encode(array('ok' => false, 'msg' => '잘못된 요청'));
    exit;
}

if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
    $err = isset($_FILES['photo']) ? $_FILES['photo']['error'] : -1;
    echo json_encode(array('ok' => false, 'msg' => '파일 업로드 오류(' . $err . ')'));
    exit;
}

if ($_FILES['photo']['size'] > 1024 * 1024 * 10) {
    echo json_encode(array('ok' => false, 'msg' => '10MB 이하만 업로드 가능합니다'));
    exit;
}

$orig = strtolower(basename($_FILES['photo']['name']));
$ext  = pathinfo($orig, PATHINFO_EXTENSION);
$ext_ok = array('jpg', 'jpeg', 'png', 'gif', 'heic', 'heif', 'webp');
if (!in_array($ext, $ext_ok)) {
    echo json_encode(array('ok' => false, 'msg' => '이미지 파일만 업로드 가능합니다'));
    exit;
}

$save_dir = $_SERVER['DOCUMENT_ROOT'] . '/cw_as/online_as/files/repair/';
if (!is_dir($save_dir)) {
    mkdir($save_dir, 0755, true);
}

$file_name = $reg_num . '_' . time() . '_' . rand(100, 999) . '.' . $ext;
$save_path = $save_dir . $file_name;

if (!move_uploaded_file($_FILES['photo']['tmp_name'], $save_path)) {
    echo json_encode(array('ok' => false, 'msg' => '파일 저장 실패'));
    exit;
}

$create_sql = "CREATE TABLE IF NOT EXISTS as_repair_photos (
    idx       INT AUTO_INCREMENT PRIMARY KEY,
    as_idx    INT         NOT NULL,
    reg_num   VARCHAR(20) NOT NULL,
    file_name VARCHAR(200) NOT NULL,
    reg_date  DATETIME DEFAULT NOW()
)";
mysqli_query($db->db_conn, $create_sql);

$as_idx_s  = mysqli_real_escape_string($db->db_conn, $as_idx);
$reg_num_s = mysqli_real_escape_string($db->db_conn, $reg_num);
$file_s    = mysqli_real_escape_string($db->db_conn, $file_name);
$db->insert("as_repair_photos", "as_idx='$as_idx_s', reg_num='$reg_num_s', file_name='$file_s', reg_date=NOW()");
$photo_idx = mysqli_insert_id($db->db_conn);

echo json_encode(array(
    'ok'        => true,
    'idx'       => $photo_idx,
    'file_name' => $file_name
));
