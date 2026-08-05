<?
include("../def_inc.php");
$mod  = M_SETTING;
$menu = S_ADMIN_DB_INDEX;
include("../header.php");

// 관리자 전용
if (($PERMISSION & PERMISSION_ALL) != PERMISSION_ALL) {
    echo '<p style="color:red;padding:20px;">접근 권한이 없습니다.</p>';
    include('../footer.php');
    exit;
}

// ── 관리할 인덱스 목록 정의 ───────────────────────────
// [테이블, 인덱스명, 컬럼목록, 설명]
$index_defs = [
    // shipping_date_new
    ['shipping_date_new', 'idx_sdn_status_date',       '(status, date)',        '출고완료 기간 조회 (통계 기본 필터)'],
    ['shipping_date_new', 'idx_sdn_status_date_model', '(status, date, model)', '모델별 출고 통계 (커버링 인덱스)'],
    ['shipping_date_new', 'idx_sdn_status_date_mall',  '(status, date, mall)',  '구매처별 출고 통계'],
    // as_parcel_service
    ['as_parcel_service', 'idx_aps_proc_upd',          '(process_state, update_time)', 'AS 출고 상태+날짜 조회'],
    ['as_parcel_service', 'idx_aps_reg_date',          '(reg_date)',                   'AS 접수일 조회'],
];

// ── POST 처리: 인덱스 생성 / 삭제 ────────────────────
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $tbl    = isset($_POST['tbl'])    ? $_POST['tbl']    : '';
    $idx    = isset($_POST['idx'])    ? $_POST['idx']    : '';
    $cols   = isset($_POST['cols'])   ? $_POST['cols']   : '';

    // 허용 목록에 있는지 검증
    $allowed = false;
    foreach ($index_defs as $def) {
        if ($def[0] === $tbl && $def[1] === $idx) { $allowed = true; break; }
    }

    if ($allowed) {
        if ($action === 'create') {
            $sql = "ALTER TABLE `$tbl` ADD INDEX `$idx` $cols";
            if (mysqli_query($db->db_conn, $sql)) {
                $msg = "success:인덱스 <strong>$idx</strong> 생성 완료.";
            } else {
                $msg = "error:생성 실패 — " . mysqli_error($db->db_conn);
            }
        } elseif ($action === 'drop') {
            $sql = "ALTER TABLE `$tbl` DROP INDEX `$idx`";
            if (mysqli_query($db->db_conn, $sql)) {
                $msg = "success:인덱스 <strong>$idx</strong> 삭제 완료.";
            } else {
                $msg = "error:삭제 실패 — " . mysqli_error($db->db_conn);
            }
        }
    } else {
        $msg = "error:허용되지 않은 요청입니다.";
    }
}

// ── 현재 인덱스 존재 여부 조회 ───────────────────────
// INFORMATION_SCHEMA 단일 쿼리로 전체 조회
$exist_set = [];
$tables_in = array_unique(array_column($index_defs, 0));
$t_in_sql  = implode("','", array_map(function($t) use ($db) {
    return mysqli_real_escape_string($db->db_conn, $t);
}, $tables_in));

$rs = mysqli_query($db->db_conn,
    "SELECT TABLE_NAME, INDEX_NAME
     FROM INFORMATION_SCHEMA.STATISTICS
     WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME IN ('$t_in_sql')
     GROUP BY TABLE_NAME, INDEX_NAME");
while ($row = mysqli_fetch_assoc($rs)) {
    $exist_set[$row['TABLE_NAME']][$row['INDEX_NAME']] = true;
}

// ── 테이블별 현재 행 수 조회 ─────────────────────────
$row_counts = [];
foreach ($tables_in as $tbl) {
    $r = mysqli_fetch_row(mysqli_query($db->db_conn,
        "SELECT COUNT(*) FROM `" . mysqli_real_escape_string($db->db_conn, $tbl) . "`"));
    $row_counts[$tbl] = $r ? (int)$r[0] : 0;
}

// 메시지 파싱
$msg_type = $msg_text = '';
if ($msg) {
    list($msg_type, $msg_text) = explode(':', $msg, 2);
}
?>

<style>
.idx-table{width:100%;border-collapse:collapse;font-size:13px;margin-bottom:0;}
.idx-table th{background:#f5f5f5;padding:9px 12px;border:1px solid #ddd;
              text-align:left;white-space:nowrap;}
.idx-table td{padding:9px 12px;border:1px solid #ddd;vertical-align:middle;}
.badge-ok  {display:inline-block;background:#dff0d8;color:#3c763d;border-radius:3px;
            padding:2px 9px;font-size:12px;font-weight:700;}
.badge-no  {display:inline-block;background:#f2dede;color:#a94442;border-radius:3px;
            padding:2px 9px;font-size:12px;font-weight:700;}
.tbl-section{margin-bottom:28px;}
.tbl-section h5{font-size:14px;font-weight:700;color:#333;margin:0 0 10px;
                padding-bottom:8px;border-bottom:2px solid #337ab7;}
.tbl-meta{font-size:12px;color:#888;margin-bottom:10px;}
.explain-box{background:#f8f9fb;border:1px solid #dde3ee;border-radius:4px;
             padding:14px 18px;margin-bottom:24px;font-size:13px;}
.explain-box h6{font-size:13px;font-weight:700;margin:0 0 8px;color:#337ab7;}
.explain-box ul{margin:0;padding-left:18px;color:#555;line-height:1.8;}
</style>

<h4 class="page-header">DB 인덱스 관리</h4>

<? if ($msg_type): ?>
<div class="alert alert-<?= $msg_type === 'success' ? 'success' : 'danger' ?>" style="margin-bottom:16px;">
    <?= $msg_text ?>
</div>
<? endif; ?>

<!-- 설명 -->
<div class="explain-box">
    <h6>인덱스란?</h6>
    <ul>
        <li>DB 테이블에 색인을 달아 조회 속도를 높이는 구조입니다.</li>
        <li>데이터가 많을수록 효과가 크며, <strong>수만 건 이상</strong>이면 10배 이상 빠르게 됩니다.</li>
        <li>인덱스 생성 중에는 잠시 테이블에 락이 걸릴 수 있으므로 <strong>업무 외 시간</strong>에 실행을 권장합니다.</li>
        <li>이미 존재하는 인덱스는 생성 버튼이 비활성화됩니다.</li>
    </ul>
</div>

<?php
// 테이블별로 그룹핑해서 출력
$grouped = [];
foreach ($index_defs as $def) $grouped[$def[0]][] = $def;

foreach ($grouped as $tbl => $defs):
    $rc = isset($row_counts[$tbl]) ? $row_counts[$tbl] : 0;
    $all_ok = true;
    foreach ($defs as $d) {
        if (!isset($exist_set[$d[0]][$d[1]])) { $all_ok = false; break; }
    }
?>
<div class="tbl-section">
    <h5>테이블: <code><?= htmlspecialchars($tbl) ?></code>
        <span style="font-size:12px;font-weight:400;color:#888;margin-left:8px;">
            총 <?= number_format($rc) ?>행
        </span>
        <?php if ($all_ok): ?>
        <span class="badge-ok" style="margin-left:8px;font-size:11px;">전체 적용 완료</span>
        <?php else: ?>
        <span class="badge-no" style="margin-left:8px;font-size:11px;">미적용 인덱스 있음</span>
        <?php endif; ?>
    </h5>

    <table class="idx-table">
        <thead>
        <tr>
            <th style="width:36px;">상태</th>
            <th>인덱스명</th>
            <th>컬럼</th>
            <th>설명</th>
            <th style="width:140px;text-align:center;">작업</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($defs as $def):
            list($dtbl, $didx, $dcols, $ddesc) = $def;
            $exists = isset($exist_set[$dtbl][$didx]);
        ?>
        <tr>
            <td style="text-align:center;">
                <?php if ($exists): ?>
                <span class="glyphicon glyphicon-ok" style="color:#3c763d;font-size:16px;" title="적용됨"></span>
                <?php else: ?>
                <span class="glyphicon glyphicon-remove" style="color:#a94442;font-size:16px;" title="미적용"></span>
                <?php endif; ?>
            </td>
            <td><code><?= htmlspecialchars($didx) ?></code></td>
            <td><code><?= htmlspecialchars($dcols) ?></code></td>
            <td style="color:#555;"><?= htmlspecialchars($ddesc) ?></td>
            <td style="text-align:center;">
                <?php if (!$exists): ?>
                <form method="post" style="display:inline;"
                      onsubmit="return confirm('인덱스 <?= htmlspecialchars($didx) ?>를 생성합니다.\n데이터가 많으면 수초~수십초 소요될 수 있습니다.\n계속하시겠습니까?');">
                    <input type="hidden" name="action" value="create">
                    <input type="hidden" name="tbl"    value="<?= htmlspecialchars($dtbl) ?>">
                    <input type="hidden" name="idx"    value="<?= htmlspecialchars($didx) ?>">
                    <input type="hidden" name="cols"   value="<?= htmlspecialchars($dcols) ?>">
                    <button type="submit" class="btn btn-success btn-xs">생성</button>
                </form>
                <?php else: ?>
                <button class="btn btn-success btn-xs" disabled>생성됨</button>
                &nbsp;
                <form method="post" style="display:inline;"
                      onsubmit="return confirm('인덱스 <?= htmlspecialchars($didx) ?>를 삭제합니다.\n성능이 저하될 수 있습니다. 계속하시겠습니까?');">
                    <input type="hidden" name="action" value="drop">
                    <input type="hidden" name="tbl"    value="<?= htmlspecialchars($dtbl) ?>">
                    <input type="hidden" name="idx"    value="<?= htmlspecialchars($didx) ?>">
                    <input type="hidden" name="cols"   value="<?= htmlspecialchars($dcols) ?>">
                    <button type="submit" class="btn btn-danger btn-xs">삭제</button>
                </form>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <!-- 전체 일괄 생성 버튼 -->
    <?php
    $missing = [];
    foreach ($defs as $d) {
        if (!isset($exist_set[$d[0]][$d[1]])) $missing[] = $d;
    }
    if (!empty($missing)):
    ?>
    <div style="margin-top:10px;">
        <form method="post" id="bulk_<?= $tbl ?>">
            <input type="hidden" name="action" value="create">
            <input type="hidden" name="tbl"    value="">
            <input type="hidden" name="idx"    value="">
            <input type="hidden" name="cols"   value="">
        </form>
        <button class="btn btn-primary btn-sm"
                onclick="bulkCreate('<?= htmlspecialchars($tbl) ?>')">
            미적용 <?= count($missing) ?>개 전체 생성
        </button>
        <span style="font-size:12px;color:#888;margin-left:8px;">
            (순서대로 하나씩 생성됩니다)
        </span>
    </div>
    <?php endif; ?>

</div>
<?php endforeach; ?>

<!-- 현재 테이블 전체 인덱스 현황 -->
<div class="panel panel-default" style="margin-top:10px;">
    <div class="panel-heading" style="font-weight:700;font-size:13px;">
        현재 적용된 전체 인덱스 현황
        <button class="btn btn-default btn-xs pull-right" type="button"
                data-toggle="collapse" data-target="#idx-detail">펼치기 / 접기</button>
    </div>
    <div id="idx-detail" class="collapse">
    <table class="idx-table">
        <thead>
        <tr>
            <th>테이블</th><th>인덱스명</th><th>컬럼</th><th>Cardinality</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $rs = mysqli_query($db->db_conn,
            "SELECT TABLE_NAME, INDEX_NAME, GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX) AS cols,
                    MAX(CARDINALITY) AS card
             FROM INFORMATION_SCHEMA.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME IN ('$t_in_sql')
             GROUP BY TABLE_NAME, INDEX_NAME
             ORDER BY TABLE_NAME, INDEX_NAME");
        while ($row = mysqli_fetch_assoc($rs)):
            $is_managed = false;
            foreach ($index_defs as $d) {
                if ($d[0] === $row['TABLE_NAME'] && $d[1] === $row['INDEX_NAME']) {
                    $is_managed = true; break;
                }
            }
        ?>
        <tr style="<?= $is_managed ? 'background:#f0fff0;' : '' ?>">
            <td><code><?= htmlspecialchars($row['TABLE_NAME']) ?></code></td>
            <td>
                <?php if ($is_managed): ?>
                <span class="badge-ok" style="font-size:11px;">관리됨</span>&nbsp;
                <?php endif; ?>
                <code><?= htmlspecialchars($row['INDEX_NAME']) ?></code>
            </td>
            <td><code>(<?= htmlspecialchars($row['cols']) ?>)</code></td>
            <td style="text-align:right;color:#888;"><?= number_format($row['card']) ?></td>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    </div>
</div>

<script>
// 일괄 생성: 미적용 인덱스를 순서대로 개별 POST
var bulkQueue = {};
<?php foreach ($grouped as $tbl => $defs):
    $missing = [];
    foreach ($defs as $d) {
        if (!isset($exist_set[$d[0]][$d[1]])) $missing[] = $d;
    }
    if (empty($missing)) continue;
?>
bulkQueue['<?= $tbl ?>'] = <?= json_encode(array_values(array_map(function($d){
    return ['tbl'=>$d[0],'idx'=>$d[1],'cols'=>$d[2]];
}, $missing))) ?>;
<?php endforeach; ?>

function bulkCreate(tbl) {
    var q = bulkQueue[tbl];
    if (!q || q.length === 0) return;
    var names = q.map(function(x){ return x.idx; }).join('\n');
    if (!confirm('다음 인덱스를 순서대로 생성합니다:\n' + names + '\n\n계속하시겠습니까?')) return;
    submitNext(tbl, q, 0);
}

function submitNext(tbl, q, i) {
    if (i >= q.length) { location.reload(); return; }
    var item = q[i];
    var form = document.getElementById('bulk_' + tbl);
    form.querySelector('[name=tbl]').value  = item.tbl;
    form.querySelector('[name=idx]').value  = item.idx;
    form.querySelector('[name=cols]').value = item.cols;
    // 다음 항목은 응답 후 처리 (페이지 리로드 후 반복 못하므로 일괄은 1개씩 submit)
    form.submit();
}
</script>

<? include('../footer.php'); ?>
