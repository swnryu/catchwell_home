<?php
session_start();
include('../common.php');
require('../check_session.php');
include('../def_inc.php');

if (($PERMISSION & PERMISSION_ALL) != PERMISSION_ALL) {
    http_response_code(403);
    echo json_encode(array('ok' => false, 'msg' => '권한 없음'));
    exit;
}

header('Content-Type: application/json; charset=utf-8');

$conn   = $db->db_conn;
$action = isset($_POST['action']) ? $_POST['action'] : '';

// ── 대상 idx 목록 반환 ──────────────────────────────────────
if ($action === 'list') {
    $rs = mysqli_query($conn,
        "SELECT idx FROM as_parcel_service
         WHERE process_state=" . ST_REG_DONE . "
           AND parcel_num IS NOT NULL AND parcel_num != ''
           AND return_track_status < 2
         ORDER BY idx ASC");

    $idxs = array();
    while ($row = mysqli_fetch_assoc($rs)) {
        $idxs[] = (int)$row['idx'];
    }
    echo json_encode(array('ok' => true, 'idxs' => $idxs));
    exit;
}

// ── 단건 처리 ──────────────────────────────────────────────
if ($action === 'process') {
    $idx = isset($_POST['idx']) ? (int)$_POST['idx'] : 0;
    if ($idx <= 0) {
        echo json_encode(array('err' => true, 'msg' => '잘못된 idx'));
        exit;
    }

    $row = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT idx, reg_num, parcel_num, return_track_status, process_state
         FROM as_parcel_service WHERE idx={$idx} LIMIT 1"));

    if (!$row) {
        echo json_encode(array('err' => true, 'msg' => '레코드 없음'));
        exit;
    }

    $parcel_no = preg_replace('/[^0-9]/', '', $row['parcel_num']);

    if (strlen($parcel_no) < 10) {
        echo json_encode(array('skip' => true, 'msg' => '송장번호 짧음: ' . $parcel_no));
        exit;
    }

    $trackResult = cjTrack($parcel_no);

    if ($trackResult === null) {
        echo json_encode(array('err' => true, 'msg' => $row['reg_num'] . ' API 조회 실패'));
        exit;
    }

    $details   = isset($trackResult['parcelDetailResultMap']['resultList'])
                 ? $trackResult['parcelDetailResultMap']['resultList'] : array();
    $lastEvent = !empty($details) ? $details[count($details) - 1] : null;
    $scanNm    = $lastEvent ? $lastEvent['scanNm'] : '';

    $newStatus = 0;
    if ($scanNm === '배송완료') {
        $newStatus = 2;
    } elseif (!empty($details)) {
        $newStatus = 1;
    }

    mysqli_query($conn,
        "UPDATE as_parcel_service
         SET return_track_status={$newStatus}, return_track_at=NOW()
         WHERE idx={$idx}");

    if ($newStatus === 2) {
        $reg_num_esc = mysqli_real_escape_string($conn, $row['reg_num']);
        $prev_state  = (int)$row['process_state'];
        mysqli_query($conn,
            "INSERT INTO as_process_history (as_idx, reg_num, prev_state, new_state, changed_by, changed_at)
             VALUES ({$idx}, '{$reg_num_esc}', {$prev_state}, " . ST_ARRIVED . ", 'CJ배송추적', NOW())");
    }

    $label = ($newStatus === 2) ? '배송완료' : (($newStatus === 1) ? '배송중' : '이력없음');

    echo json_encode(array(
        'ok'      => true,
        'reg_num' => $row['reg_num'],
        'status'  => $newStatus,
        'label'   => $label,
        'scanNm'  => $scanNm,
    ));
    exit;
}

echo json_encode(array('ok' => false, 'msg' => '알 수 없는 action'));

// ── CJ API ────────────────────────────────────────────────
function cjTrack($invoiceNo) {
    $cookieFile = tempnam(sys_get_temp_dir(), 'cj_chunk_');

    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_URL            => 'https://www.cjlogistics.com/ko/tool/parcel/tracking',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_COOKIEJAR      => $cookieFile,
        CURLOPT_COOKIEFILE     => $cookieFile,
        CURLOPT_USERAGENT      => 'Mozilla/5.0',
        CURLOPT_TIMEOUT        => 15,
    ));
    $html = curl_exec($ch);
    curl_close($ch);

    if (!$html || !preg_match('/name="_csrf"\s+value="([^"]+)"/', $html, $m)) {
        @unlink($cookieFile);
        return null;
    }

    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_URL            => 'https://www.cjlogistics.com/ko/tool/parcel/tracking-detail',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_COOKIEJAR      => $cookieFile,
        CURLOPT_COOKIEFILE     => $cookieFile,
        CURLOPT_USERAGENT      => 'Mozilla/5.0',
        CURLOPT_HTTPHEADER     => array(
            'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
            'X-Requested-With: XMLHttpRequest',
            'Referer: https://www.cjlogistics.com/ko/tool/parcel/tracking'
        ),
        CURLOPT_POSTFIELDS => http_build_query(array('_csrf' => $m[1], 'paramInvcNo' => $invoiceNo)),
        CURLOPT_TIMEOUT    => 15,
    ));
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    @unlink($cookieFile);

    if ($httpCode != 200 || !$response) return null;
    $data = json_decode($response, true);
    return $data ? $data : null;
}
