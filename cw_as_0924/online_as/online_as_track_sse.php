<?php
set_time_limit(0);
session_start();
include('../common.php');
require('../check_session.php');
include('../def_inc.php');

if (($PERMISSION & PERMISSION_ALL) != PERMISSION_ALL) {
    http_response_code(403);
    exit;
}

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('X-Accel-Buffering: no');  // nginx 버퍼링 비활성화

@ob_end_clean();
ob_implicit_flush(true);

$conn = $db->db_conn;

function send($msg, $cls = '', $done = false) {
    $payload = json_encode(array('msg' => $msg, 'cls' => $cls, 'done' => $done));
    echo "data: {$payload}\n\n";
    ob_flush(); flush();
}

send('[' . date('H:i:s') . '] === CJ 배송추적 수동 실행 시작 ===', 'start');

$rs = mysqli_query($conn,
    "SELECT idx, reg_num, parcel_num, return_track_status
     FROM as_parcel_service
     WHERE process_state=" . ST_REG_DONE . "
       AND parcel_num IS NOT NULL AND parcel_num != ''
       AND return_track_status < 2");

$total    = mysqli_num_rows($rs);
$cnt_done = 0;
$cnt_skip = 0;
$cnt_err  = 0;

send('[' . date('H:i:s') . '] 대상: ' . $total . '건', 'info');

while ($row = mysqli_fetch_assoc($rs)) {
    $idx       = (int)$row['idx'];
    $reg_num   = $row['reg_num'];
    $parcel_no = preg_replace('/[^0-9]/', '', $row['parcel_num']);

    if (strlen($parcel_no) < 10) {
        send('[' . date('H:i:s') . '] SKIP idx=' . $idx . ' 송장번호 짧음: ' . $parcel_no, 'skip');
        $cnt_skip++;
        continue;
    }

    $trackResult = cjTrackSse($parcel_no);

    if ($trackResult === null) {
        send('[' . date('H:i:s') . '] ERR  idx=' . $idx . ' ' . $reg_num . ' API 조회 실패', 'err');
        $cnt_err++;
        continue;
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

    $label = ($newStatus === 2) ? '배송완료' : (($newStatus === 1) ? '배송중' : '이력없음');
    $cls   = ($newStatus === 2) ? 'done' : (($newStatus === 1) ? 'mid' : '');
    send('[' . date('H:i:s') . ']     idx=' . $idx . ' ' . $reg_num . ' ' . $label . ' [' . $scanNm . ']', $cls);
    if ($newStatus === 2) $cnt_done++;

    usleep(500000);
}

send('[' . date('H:i:s') . '] === 완료: 배송완료 ' . $cnt_done . '건 / 건너뜀 ' . $cnt_skip . '건 / 오류 ' . $cnt_err . '건 ===', 'end', true);

function cjTrackSse($invoiceNo) {
    $cookieFile = tempnam(sys_get_temp_dir(), 'cj_sse_');

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
