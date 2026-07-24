<?php
error_reporting(E_ALL);

include("../common.php");

$reg_num          = isset($_POST['reg_num']) ? $_POST['reg_num'] : '';
$searchValuePhone = isset($_POST['searchValuePhone']) ? $_POST['searchValuePhone'] : '';
$action           = isset($_POST['action']) ? $_POST['action'] : '';

$redirect_url = "online_as_estimate.php?searchData=" . urlencode($reg_num) . "&searchValuePhone=" . urlencode($searchValuePhone);

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

$new_state  = ($action === 'dispose') ? 10 : 11; // ST_DISPOSAL_REQUESTED / ST_RETURN_REQUESTED
$prev_state = (int)$row->process_state;

// 이미 폐기/반송 신청된 건은 중복 처리하지 않음 (알림 중복 발송 방지)
if ($prev_state === 10 || $prev_state === 11) {
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
    _sendFlowMessageDisposeReturn(4186691, $flow_msg);
}

header("Location: " . $redirect_url);
exit;

// cw_as의 common_lib.php(2020년 버전)에는 sendFlowMessage()가 없어 자체 포함 함수 사용
// (cw_as_0924/pg_m/mx_rnoti.php의 _sendFlowMessageInline() 패턴과 동일)
function _sendFlowMessageDisposeReturn($chatId, $contents) {
    if (empty($chatId) || empty($contents)) return;

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL            => 'https://api.flow.team/v1/chats/' . (int)$chatId . '/messages',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING       => '',
        CURLOPT_MAXREDIRS      => 10,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST  => 'POST',
        CURLOPT_POSTFIELDS     => 'registerId=' . urlencode('swryu@catchwell.com') . '&contents=' . urlencode($contents),
        CURLOPT_HTTPHEADER     => array(
            'Content-Type: application/x-www-form-urlencoded',
            'x-flow-api-key: 20251203050955646-6ab56428-4e53-469e-b564-420e2ce4c4c9',
        ),
    ));
    curl_exec($curl);
    curl_close($curl);
}
?>
