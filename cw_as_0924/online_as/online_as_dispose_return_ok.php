<?
include("../common.php");
include("../def_inc.php");
include("../common_lib.php");

$reg_num          = isset($_POST['reg_num']) ? $_POST['reg_num'] : '';
$searchValuePhone = isset($_POST['searchValuePhone']) ? $_POST['searchValuePhone'] : '';
$action           = isset($_POST['action']) ? $_POST['action'] : '';

// cw_as(고객용 견적 페이지, 별도 서버 경로)로 절대 URL 리다이렉트
$redirect_url = "https://csadmin.catchwell.com/cw_as/online_as/online_as_estimate.php?searchData=" . urlencode($reg_num) . "&searchValuePhone=" . urlencode($searchValuePhone);

if ($reg_num === '' || $searchValuePhone === '' || ($action !== 'dispose' && $action !== 'return')) {
    header("Location: " . $redirect_url);
    exit;
}

$reg_num_esc = mysqli_real_escape_string($db->db_conn, $reg_num);
$phone_esc   = mysqli_real_escape_string($db->db_conn, $searchValuePhone);

$row = $db->object("as_parcel_service", "where reg_num='$reg_num_esc' and customer_phone='$phone_esc'");

if (!$row) {
    header("Location: " . $redirect_url);
    exit;
}

$new_state  = ($action === 'dispose') ? ST_DISPOSAL_REQUESTED : ST_RETURN_REQUESTED;
$prev_state = (int)$row->process_state;

// 이미 폐기/반송 신청된 건은 중복 처리하지 않음 (알림 중복 발송 방지)
if ($prev_state === ST_DISPOSAL_REQUESTED || $prev_state === ST_RETURN_REQUESTED) {
    header("Location: " . $redirect_url);
    exit;
}

$update_sql = "UPDATE as_parcel_service SET process_state = $new_state WHERE reg_num = '$reg_num_esc'";
if ($db->result($update_sql)) {
    $changed_by = ($action === 'dispose') ? '고객(폐기신청)' : '고객(반송신청)';
    $db->insert("as_process_history", "as_idx={$row->idx}, reg_num='$reg_num_esc', prev_state=$prev_state, new_state=$new_state, changed_by='$changed_by', changed_at=now()");

    $label = ($action === 'dispose') ? '폐기 신청' : '반송 신청';
    $flow_msg = "[$label]\n"
              . "▸ 접수번호: {$reg_num}\n"
              . "▸ 고객명: {$row->customer_name}\n"
              . "▸ 연락처: {$row->customer_phone}\n"
              . "▸ 모델명: {$row->product_name}\n"
              . "▸ 시간: " . date("Y-m-d H:i:s");
    sendFlowMessage(4186691, $flow_msg);
}

header("Location: " . $redirect_url);
exit;
?>
