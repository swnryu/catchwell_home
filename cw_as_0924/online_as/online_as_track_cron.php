<?php
/**
 * CJ택배 배송 자동 추적 크론 스크립트
 * 실행: php /path/to/cw_as_265/online_as/online_as_track_cron.php
 *
 * 시놀로지 작업 스케줄러 설정:
 *   명령: php /volume1/web/cw_as_265/online_as/online_as_track_cron.php
 *   주기: 1시간마다
 *
 * 사전 조건 (DB ALTER TABLE — 최초 1회 실행):
 *   ALTER TABLE as_parcel_service
 *     ADD COLUMN return_track_status TINYINT NOT NULL DEFAULT 0,
 *     ADD COLUMN return_track_at DATETIME NULL;
 */

if (php_sapi_name() !== 'cli') {
    die('CLI only.' . PHP_EOL);
}

// 경로 설정
$root = dirname(__DIR__);
$_SERVER['DOCUMENT_ROOT'] = dirname($root);
$_SERVER['HTTPS']          = 'off';
$_SERVER['HTTP_HOST']      = 'localhost';

require_once $root . '/config.php';
require_once $root . '/lib/basic_class.php';
require_once $root . '/def_inc.php';

$db    = new mysqli_dbConnect($DB_HOST, $DB_NAME, $DB_USER, $DB_PWD, $DB_PORT);
$conn  = $db->db_conn;

$log = function($msg) {
    echo '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL;
};

$log('=== CJ 배송추적 크론 시작 ===');

// 접수완료(state=1) + 회수송장 있는 건 조회
$rs = mysqli_query($conn,
    "SELECT idx, reg_num, parcel_num, return_track_status, process_state
     FROM as_parcel_service
     WHERE process_state=" . ST_REG_DONE . "
       AND parcel_num IS NOT NULL AND parcel_num != ''
       AND return_track_status < 2");

$total    = mysqli_num_rows($rs);
$cnt_done = 0;
$cnt_skip = 0;
$cnt_err  = 0;

$log("대상: {$total}건");

while ($row = mysqli_fetch_assoc($rs)) {
    $idx       = (int)$row['idx'];
    $reg_num   = $row['reg_num'];
    $parcel_no = preg_replace('/[^0-9]/', '', $row['parcel_num']);

    if (strlen($parcel_no) < 10) {
        $log("SKIP idx={$idx} 송장번호 짧음: {$parcel_no}");
        $cnt_skip++;
        continue;
    }

    // CJ API 조회
    $trackResult = cjTrack($parcel_no);

    if ($trackResult === null) {
        $log("ERR  idx={$idx} API 조회 실패: {$parcel_no}");
        $cnt_err++;
        continue;
    }

    $details   = isset($trackResult['parcelDetailResultMap']['resultList'])
                 ? $trackResult['parcelDetailResultMap']['resultList'] : array();
    $lastEvent = !empty($details) ? $details[count($details) - 1] : null;
    $scanNm    = $lastEvent ? $lastEvent['scanNm'] : '';

    // 상태 결정: 배송완료 → 2, 그 외 이력 있으면 → 1
    $newStatus = 0;
    if ($scanNm === '배송완료') {
        $newStatus = 2;
    } else if (!empty($details)) {
        $newStatus = 1;
    }

    // DB 업데이트
    $prevStatus = (int)$row['return_track_status'];
    $scanEsc    = mysqli_real_escape_string($conn, $scanNm);

    mysqli_query($conn,
        "UPDATE as_parcel_service
         SET return_track_status={$newStatus}, return_track_at=NOW()
         WHERE idx={$idx}");

    $label = ($newStatus === 2) ? '배송완료' : (($newStatus === 1) ? '배송중' : '이력없음');
    $log("    idx={$idx} {$reg_num} {$label} [{$scanNm}]");

    if ($newStatus === 2) {
        $reg_num_esc = mysqli_real_escape_string($conn, $reg_num);
        $prev_state  = (int)$row['process_state'];
        mysqli_query($conn,
            "INSERT INTO as_process_history (as_idx, reg_num, prev_state, new_state, changed_by, changed_at)
             VALUES ({$idx}, '{$reg_num_esc}', {$prev_state}, " . ST_ARRIVED . ", 'CJ배송추적', NOW())");
        $cnt_done++;
    }

    // CJ 서버 부하 방지
    usleep(500000); // 0.5초 대기
}

$log("=== 완료: 입고처리 {$cnt_done}건 / 건너뜀 {$cnt_skip}건 / 오류 {$cnt_err}건 ===");

// ─── CJ API 함수 ───────────────────────────────────────────────────

function cjTrack($invoiceNo) {
    $cookieFile = tempnam(sys_get_temp_dir(), 'cj_cron_');

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
        CURLOPT_POSTFIELDS     => http_build_query(array('_csrf' => $m[1], 'paramInvcNo' => $invoiceNo)),
        CURLOPT_TIMEOUT        => 15,
    ));
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    @unlink($cookieFile);

    if ($httpCode != 200 || !$response) return null;

    $data = json_decode($response, true);
    return $data ? $data : null;
}
