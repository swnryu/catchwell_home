<?php
/**
 * 미처리 자동 전환 크론 스크립트
 * [케이스1] 접수중(state=0) 1달 이상 경과 → 미처리(state=8)
 * [케이스2] 접수완료(state=1) 20일 이상 경과 + 송장미조회(return_track_status=0) → 미처리(state=8)
 *
 * 시놀로지 작업 스케줄러:
 *   명령: /usr/local/bin/php74 /volume1/web/cw_as_265/online_as/online_as_unprocessed_cron.php >> /volume1/web/cw_as_265/online_as/unprocessed_cron.log 2>&1
 *   주기: 매일 1회 (예: 오전 2시)
 */

if (php_sapi_name() !== 'cli') {
    die('CLI only.' . PHP_EOL);
}

$root = dirname(__DIR__);
$_SERVER['DOCUMENT_ROOT'] = dirname($root);
$_SERVER['HTTPS']          = 'off';
$_SERVER['HTTP_HOST']      = 'localhost';

require_once $root . '/config.php';
require_once $root . '/lib/basic_class.php';
require_once $root . '/def_inc.php';

$db   = new mysqli_dbConnect($DB_HOST, $DB_NAME, $DB_USER, $DB_PWD, $DB_PORT);
$conn = $db->db_conn;

$log = function($msg) {
    echo '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL;
};

$log('=== 미처리 자동 전환 크론 시작 ===');

$total_done = 0;
$total_err  = 0;

function doTransfer($conn, $rs, $reason, $log, &$cnt_done, &$cnt_err) {
    $total = mysqli_num_rows($rs);
    $log("대상({$reason}): {$total}건");
    while ($row = mysqli_fetch_assoc($rs)) {
        $idx     = (int)$row['idx'];
        $reg_num = $row['reg_num'];
        $prev    = (int)$row['process_state'];
        $ok = mysqli_query($conn,
            "UPDATE as_parcel_service SET process_state=" . ST_UNPROCESSED . " WHERE idx={$idx}");
        if ($ok) {
            $reg_esc = mysqli_real_escape_string($conn, $reg_num);
            mysqli_query($conn,
                "INSERT INTO as_process_history (as_idx, reg_num, prev_state, new_state, changed_by, changed_at)
                 VALUES ({$idx}, '{$reg_esc}', {$prev}, " . ST_UNPROCESSED . ", '{$reason}', NOW())");
            $log("  전환 idx={$idx} {$reg_num}");
            $cnt_done++;
        } else {
            $log("ERR  idx={$idx} 업데이트 실패");
            $cnt_err++;
        }
    }
}

// ── 케이스1: 접수중(0) 1달 이상 ──────────────────────────
$rs1 = mysqli_query($conn,
    "SELECT idx, reg_num, process_state
     FROM as_parcel_service
     WHERE process_state=" . ST_REGISTERING . "
       AND reg_date < DATE_SUB(NOW(), INTERVAL 1 MONTH)
     ORDER BY idx ASC");
doTransfer($conn, $rs1, '자동전환(1달초과)', $log, $total_done, $total_err);

// ── 케이스2: 접수완료(1) 20일 이상 + 송장미조회 ──────────
$rs2 = mysqli_query($conn,
    "SELECT idx, reg_num, process_state
     FROM as_parcel_service
     WHERE process_state=" . ST_REG_DONE . "
       AND return_track_status = 0
       AND reg_date < DATE_SUB(NOW(), INTERVAL 20 DAY)
     ORDER BY idx ASC");
doTransfer($conn, $rs2, '자동전환(20일미조회)', $log, $total_done, $total_err);

$log("=== 완료: 전환 {$total_done}건 / 오류 {$total_err}건 ===");
