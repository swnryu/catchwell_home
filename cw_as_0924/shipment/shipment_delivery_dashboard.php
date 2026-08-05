<?php
include("../def_inc.php");
$mod  = M_SHIPMENT;
$menu = S_SHIPMENT_DELIVERY_DASHBOARD;
include("../header.php");

// ── 뷰 타입 → 기본 기간 계산 ─────────────────────────────────
$viewType = isset($_GET['view']) ? $_GET['view'] : 'monthly';
if (!in_array($viewType, array('daily','weekly','monthly'))) $viewType = 'monthly';

$today     = date('Y-m-d');
$dowN      = (int)date('N');               // 1=월 ~ 7=일
$weekStart = date('Y-m-d', strtotime('-' . ($dowN - 1) . ' days'));
$monthStart = date('Y-m-01');

$viewDefault = array(
    'daily'   => array($today,      $today),
    'weekly'  => array($weekStart,  $today),
    'monthly' => array($monthStart, $today),
);

$dateFrom = (isset($_GET['date_from']) && $_GET['date_from'])
    ? preg_replace('/[^0-9\-]/', '', $_GET['date_from'])
    : $viewDefault[$viewType][0];
$dateTo   = (isset($_GET['date_to'])   && $_GET['date_to'])
    ? preg_replace('/[^0-9\-]/', '', $_GET['date_to'])
    : $viewDefault[$viewType][1];

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom)) $dateFrom = $viewDefault[$viewType][0];
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo))   $dateTo   = $viewDefault[$viewType][1];
if ($dateFrom > $dateTo) { $t = $dateFrom; $dateFrom = $dateTo; $dateTo = $t; }

$dateCond = "ship_date BETWEEN '$dateFrom' AND '$dateTo'";

// DB 전체 기간 (전체보기 링크용)
$rangeRow  = mysqli_fetch_row(mysqli_query($db->db_conn,
    "SELECT MIN(ship_date), MAX(ship_date) FROM shipment_delivery"));
$dbMinDate = ($rangeRow && $rangeRow[0]) ? $rangeRow[0] : $monthStart;
$dbMaxDate = ($rangeRow && $rangeRow[1]) ? $rangeRow[1] : $today;

if (empty($rangeRow[0])) {
    echo '<div class="alert alert-info" style="margin-top:20px;">업로드된 배송 데이터가 없습니다. <a href="shipment_delivery_upload.php">업로드 하기</a></div>';
    include('../footer.php'); exit;
}

// ── 요약 집계 ──────────────────────────────────────────────────
$total = (int)mysqli_fetch_row(mysqli_query($db->db_conn,
    "SELECT COUNT(*) FROM shipment_delivery WHERE $dateCond"))[0];

$modelCnt = (int)mysqli_fetch_row(mysqli_query($db->db_conn,
    "SELECT COUNT(DISTINCT model_base) FROM shipment_delivery WHERE $dateCond"))[0];

$channelCnt = (int)mysqli_fetch_row(mysqli_query($db->db_conn,
    "SELECT COUNT(DISTINCT channel) FROM shipment_delivery WHERE $dateCond"))[0];

$paySum = (int)mysqli_fetch_row(mysqli_query($db->db_conn,
    "SELECT IFNULL(SUM(payment_amt),0) FROM shipment_delivery
     WHERE $dateCond AND payment_amt > 0"))[0];

$avgPay = (int)mysqli_fetch_row(mysqli_query($db->db_conn,
    "SELECT ROUND(IFNULL(AVG(NULLIF(payment_amt,0)),0))
     FROM shipment_delivery WHERE $dateCond AND payment_amt > 0"))[0];

$uploadedDayCnt = (int)mysqli_fetch_row(mysqli_query($db->db_conn,
    "SELECT COUNT(DISTINCT ship_date) FROM shipment_delivery WHERE $dateCond"))[0];
$dailyAvg = $uploadedDayCnt > 0 ? round($total / $uploadedDayCnt) : 0;

// ── 일별 출고+매출 (항상 일 단위) ────────────────────────────
$trendData = array();
$res = mysqli_query($db->db_conn,
    "SELECT ship_date, COUNT(*) AS cnt, IFNULL(SUM(payment_amt),0) AS revenue
     FROM shipment_delivery WHERE $dateCond
     GROUP BY ship_date ORDER BY ship_date ASC");
while ($r = mysqli_fetch_object($res)) $trendData[] = $r;

// ── 요일별 패턴 ─────────────────────────────────────────────────
$dowLabels = array(1=>'일',2=>'월',3=>'화',4=>'수',5=>'목',6=>'금',7=>'토');
$dowCounts = array(1=>0,2=>0,3=>0,4=>0,5=>0,6=>0,7=>0);
$res = mysqli_query($db->db_conn,
    "SELECT DAYOFWEEK(ship_date) AS dow, COUNT(*) AS cnt
     FROM shipment_delivery WHERE $dateCond GROUP BY DAYOFWEEK(ship_date)");
while ($r = mysqli_fetch_object($res)) {
    if (isset($dowCounts[(int)$r->dow])) $dowCounts[(int)$r->dow] = (int)$r->cnt;
}

// ── 모델별 출고+매출 ─────────────────────────────────────────
$modelData = array();
$res = mysqli_query($db->db_conn,
    "SELECT model_base, COUNT(*) AS cnt,
            IFNULL(SUM(payment_amt),0) AS revenue,
            ROUND(IFNULL(AVG(NULLIF(payment_amt,0)),0)) AS avg_price
     FROM shipment_delivery WHERE $dateCond AND model_base != ''
     GROUP BY model_base ORDER BY cnt DESC LIMIT 15");
while ($r = mysqli_fetch_object($res)) $modelData[] = $r;

// ── 채널별 출고+매출 ─────────────────────────────────────────
$channelData = array();
$res = mysqli_query($db->db_conn,
    "SELECT channel, COUNT(*) AS cnt,
            IFNULL(SUM(payment_amt),0) AS revenue,
            ROUND(IFNULL(AVG(NULLIF(payment_amt,0)),0)) AS avg_price
     FROM shipment_delivery WHERE $dateCond AND channel != ''
     GROUP BY channel ORDER BY cnt DESC LIMIT 12");
while ($r = mysqli_fetch_object($res)) $channelData[] = $r;

// ── 채널별 매출 bar용 (매출 상위 10, DESC → reverse → CanvasJS ASC 렌더)
$channelRevData = array();
$res = mysqli_query($db->db_conn,
    "SELECT channel, IFNULL(SUM(payment_amt),0) AS revenue
     FROM shipment_delivery WHERE $dateCond AND channel != ''
     GROUP BY channel HAVING revenue > 0 ORDER BY revenue DESC LIMIT 10");
while ($r = mysqli_fetch_object($res)) $channelRevData[] = $r;
$channelRevData = array_reverse($channelRevData); // CanvasJS bar: 아래→위 렌더링

// ── 상세 목록 필터 ─────────────────────────────────────────────
$filterDate  = isset($_GET['date'])    ? preg_replace('/[^0-9\-]/', '', $_GET['date']) : '';
$filterModel = isset($_GET['model'])   ? trim($_GET['model'])   : '';
$filterCh    = isset($_GET['channel']) ? trim($_GET['channel']) : '';

$where = "WHERE $dateCond";
if ($filterDate)  $where .= " AND ship_date='"  . mysqli_real_escape_string($db->db_conn, $filterDate)  . "'";
if ($filterModel) $where .= " AND model_base='" . mysqli_real_escape_string($db->db_conn, $filterModel) . "'";
if ($filterCh)    $where .= " AND channel='"    . mysqli_real_escape_string($db->db_conn, $filterCh)    . "'";

$page    = max(1, (int)(isset($_GET['page']) ? $_GET['page'] : 1));
$perPage = 50;
$offset  = ($page - 1) * $perPage;

$listCnt   = (int)mysqli_fetch_row(mysqli_query($db->db_conn,
    "SELECT COUNT(*) FROM shipment_delivery $where"))[0];
$totalPage = max(1, ceil($listCnt / $perPage));

$listRows = array();
$res = mysqli_query($db->db_conn,
    "SELECT ship_date, model, qty, channel, recipient, tracking_no, mobile, payment_amt, orderer_id
     FROM shipment_delivery $where
     ORDER BY ship_date DESC, idx DESC
     LIMIT $perPage OFFSET $offset");
while ($r = mysqli_fetch_object($res)) $listRows[] = $r;

// ── 업로드된 날짜 (기간 내) ───────────────────────────────────
$uploadedDates = array();
$res = mysqli_query($db->db_conn,
    "SELECT ship_date, total_rows FROM shipment_upload_history
     WHERE ship_date BETWEEN '$dateFrom' AND '$dateTo'
       AND ship_date IS NOT NULL ORDER BY ship_date ASC");
while ($r = mysqli_fetch_object($res)) $uploadedDates[] = $r;

$modelDataRev = array_reverse($modelData);
$baseRange = 'view=' . urlencode($viewType)
           . '&date_from=' . urlencode($dateFrom)
           . '&date_to='   . urlencode($dateTo);

$viewLabel = array('daily'=>'오늘', 'weekly'=>'이번주', 'monthly'=>'이번달');
$trendTitle = $viewLabel[$viewType] . ' 출고량 &amp; 매출';
?>

<style>
.stat-card{background:#fff;border:1px solid #ddd;border-radius:4px;padding:12px 14px;text-align:center;margin-bottom:12px;}
.stat-card .num{font-size:22px;font-weight:700;color:#337ab7;line-height:1.2;}
.stat-card .lbl{font-size:11px;color:#999;margin-top:3px;}
.chart-box{background:#fff;border:1px solid #ddd;border-radius:4px;padding:14px;margin-bottom:14px;}
.chart-box h5{font-size:13px;font-weight:700;color:#555;margin:0 0 10px;padding-bottom:6px;border-bottom:1px solid #eee;}
.insight-tbl{font-size:12px;margin:0;width:100%;}
.insight-tbl thead th{background:#f5f5f5;font-weight:600;padding:5px 8px;}
.insight-tbl tbody td{padding:5px 8px;vertical-align:middle;}
.bar-bg{background:#e8edf7;border-radius:2px;height:8px;}
.bar-fill{background:#4e73df;border-radius:2px;height:8px;}
.filter-box{background:#fff;border:1px solid #ddd;border-radius:4px;padding:12px 16px;margin-bottom:14px;}
</style>

<h4 class="page-header">배송 대시보드</h4>

<!-- 조회 조건 -->
<div class="filter-box">
    <form method="get" id="filterForm">
        <input type="hidden" name="view"      id="viewHidden"    value="<?= $viewType ?>">
        <input type="hidden" name="date_from" id="dateFromHidden" value="<?= htmlspecialchars($dateFrom) ?>">
        <input type="hidden" name="date_to"   id="dateToHidden"   value="<?= htmlspecialchars($dateTo) ?>">

        <div style="display:flex;align-items:center;flex-wrap:wrap;gap:8px;margin-bottom:8px;">
            <!-- 뷰 타입 (기간 프리셋) -->
            <div class="btn-group">
                <button type="button" class="btn btn-sm <?= $viewType=='daily'   ? 'btn-primary' : 'btn-default' ?>" onclick="setView('daily')">일별</button>
                <button type="button" class="btn btn-sm <?= $viewType=='weekly'  ? 'btn-primary' : 'btn-default' ?>" onclick="setView('weekly')">주별</button>
                <button type="button" class="btn btn-sm <?= $viewType=='monthly' ? 'btn-primary' : 'btn-default' ?>" onclick="setView('monthly')">월별</button>
            </div>
            <span style="color:#ccc;">|</span>
            <!-- 기간 직접 지정 -->
            <input type="date" id="inputDateFrom" class="form-control input-sm"
                   value="<?= htmlspecialchars($dateFrom) ?>" style="width:140px;"
                   onchange="syncDates()">
            <span style="color:#888;">~</span>
            <input type="date" id="inputDateTo" class="form-control input-sm"
                   value="<?= htmlspecialchars($dateTo) ?>" style="width:140px;"
                   onchange="syncDates()">
            <button type="submit" class="btn btn-sm btn-primary">조회</button>
            <span style="color:#ccc;">|</span>
            <a href="shipment_delivery_upload.php" class="btn btn-sm btn-default">
                <span class="glyphicon glyphicon-upload"></span> 업로드
            </a>
        </div>
        <!-- 전체보기 -->
        <div>
            <span style="font-size:11px;color:#aaa;margin-right:6px;">전체 기간</span>
            <a href="?view=<?= $viewType ?>&date_from=<?= $dbMinDate ?>&date_to=<?= $dbMaxDate ?>"
               class="btn btn-xs <?= ($dateFrom==$dbMinDate && $dateTo==$dbMaxDate) ? 'btn-info' : 'btn-default' ?>">
               <?= $dbMinDate ?> ~ <?= $dbMaxDate ?>
            </a>
        </div>
    </form>
</div>

<!-- 요약 카드 -->
<div class="row">
    <div class="col-xs-12" style="margin-bottom:6px;font-size:12px;color:#888;padding-left:15px;">
        <strong style="color:#337ab7;"><?= $viewLabel[$viewType] ?></strong>
        &nbsp;<?= htmlspecialchars($dateFrom) ?><?= ($dateFrom !== $dateTo ? ' ~ '.$dateTo : '') ?>
        &nbsp;|&nbsp; 데이터 <?= $uploadedDayCnt ?>일치
    </div>
    <div class="col-xs-6 col-sm-2">
        <div class="stat-card">
            <div class="num"><?= number_format($total) ?></div>
            <div class="lbl">총 출고</div>
        </div>
    </div>
    <div class="col-xs-6 col-sm-2">
        <div class="stat-card">
            <div class="num" style="color:#5cb85c;"><?= number_format($dailyAvg) ?></div>
            <div class="lbl">일 평균 출고</div>
        </div>
    </div>
    <div class="col-xs-6 col-sm-2">
        <div class="stat-card">
            <div class="num" style="color:#f0ad4e;"><?= $modelCnt ?></div>
            <div class="lbl">모델 종류</div>
        </div>
    </div>
    <div class="col-xs-6 col-sm-2">
        <div class="stat-card">
            <div class="num" style="color:#9b59b6;"><?= $channelCnt ?></div>
            <div class="lbl">판매 채널</div>
        </div>
    </div>
    <div class="col-xs-6 col-sm-2">
        <div class="stat-card">
            <div class="num" style="font-size:17px;color:#e74c3c;">
                <?= $paySum > 0 ? number_format($paySum).'원' : '-' ?>
            </div>
            <div class="lbl">결제금액 합계</div>
        </div>
    </div>
    <div class="col-xs-6 col-sm-2">
        <div class="stat-card">
            <div class="num" style="font-size:17px;color:#e67e22;">
                <?= $avgPay > 0 ? number_format($avgPay).'원' : '-' ?>
            </div>
            <div class="lbl">평균 결제금액</div>
        </div>
    </div>
</div>

<!-- 추이 차트 -->
<div class="chart-box">
    <h5><?= $trendTitle ?></h5>
    <?php if (empty($trendData)): ?>
    <p style="text-align:center;color:#ccc;padding:60px 0;">해당 기간에 데이터가 없습니다.</p>
    <?php else: ?>
    <div id="trendChart" style="height:260px;"></div>
    <?php endif; ?>
</div>

<div class="row">
    <div class="col-sm-7">
        <div class="chart-box">
            <h5>모델별 출고량 (상위 15)</h5>
            <div id="modelChart" style="height:310px;"></div>
        </div>
    </div>
    <div class="col-sm-5">
        <div class="chart-box">
            <h5>채널별 출고 비중</h5>
            <div id="channelChart" style="height:310px;"></div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-sm-4">
        <div class="chart-box">
            <h5>요일별 출고 패턴</h5>
            <div id="dowChart" style="height:200px;"></div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="chart-box">
            <h5>채널별 매출 (상위 10)</h5>
            <div id="channelRevChart" style="height:200px;"></div>
        </div>
    </div>
</div>

<!-- 모델·채널 상세 테이블 -->
<div class="row">
    <div class="col-sm-7">
        <div class="chart-box" style="padding:0;">
            <div style="padding:9px 14px;border-bottom:1px solid #eee;font-weight:700;font-size:13px;color:#555;">모델별 상세</div>
            <?php $modelMaxCnt = !empty($modelData) ? (int)$modelData[0]->cnt : 1; ?>
            <table class="insight-tbl table table-condensed table-hover">
                <thead>
                    <tr>
                        <th style="width:24px;">#</th><th>모델</th>
                        <th style="text-align:right;">출고수</th>
                        <th style="width:80px;"></th>
                        <th style="text-align:right;">매출합계</th>
                        <th style="text-align:right;">평균단가</th>
                    </tr>
                </thead>
                <tbody>
                <?php $rank=1; foreach ($modelData as $m): ?>
                <tr>
                    <td style="color:#bbb;"><?= $rank++ ?></td>
                    <td><a href="?<?= $baseRange ?>&model=<?= urlencode($m->model_base) ?>"><?= htmlspecialchars($m->model_base) ?></a></td>
                    <td style="text-align:right;font-weight:600;"><?= number_format($m->cnt) ?></td>
                    <td><div class="bar-bg"><div class="bar-fill" style="width:<?= round($m->cnt/$modelMaxCnt*100) ?>%;"></div></div></td>
                    <td style="text-align:right;color:#e74c3c;"><?= $m->revenue > 0 ? number_format($m->revenue) : '-' ?></td>
                    <td style="text-align:right;color:#888;font-size:11px;"><?= $m->avg_price > 0 ? number_format($m->avg_price) : '-' ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-sm-5">
        <div class="chart-box" style="padding:0;">
            <div style="padding:9px 14px;border-bottom:1px solid #eee;font-weight:700;font-size:13px;color:#555;">채널별 상세</div>
            <table class="insight-tbl table table-condensed table-hover">
                <thead>
                    <tr>
                        <th style="width:24px;">#</th><th>채널</th>
                        <th style="text-align:right;">출고수</th>
                        <th style="text-align:right;">비중</th>
                        <th style="text-align:right;">매출합계</th>
                    </tr>
                </thead>
                <tbody>
                <?php $rank=1; foreach ($channelData as $c): ?>
                <tr>
                    <td style="color:#bbb;"><?= $rank++ ?></td>
                    <td><a href="?<?= $baseRange ?>&channel=<?= urlencode($c->channel) ?>"><?= htmlspecialchars($c->channel) ?></a></td>
                    <td style="text-align:right;font-weight:600;"><?= number_format($c->cnt) ?></td>
                    <td style="text-align:right;color:#888;"><?= $total > 0 ? round($c->cnt/$total*100,1).'%' : '-' ?></td>
                    <td style="text-align:right;color:#e74c3c;"><?= $c->revenue > 0 ? number_format($c->revenue) : '-' ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- 업로드된 날짜 -->
<?php if (!empty($uploadedDates)): ?>
<div style="margin-bottom:14px;padding:10px 14px;background:#f8f9fa;border:1px solid #e0e0e0;border-radius:4px;font-size:12px;">
    <strong>업로드된 날짜:</strong>&nbsp;
    <?php foreach ($uploadedDates as $ud): ?>
    <a href="?<?= $baseRange ?>&date=<?= $ud->ship_date ?>"
       style="display:inline-block;margin:2px 4px;padding:2px 8px;background:<?= ($filterDate==$ud->ship_date?'#1a5276':'#337ab7') ?>;color:#fff;border-radius:3px;text-decoration:none;">
        <?= date('m/d', strtotime($ud->ship_date)) ?>
        <span style="opacity:.8;">(<?= number_format($ud->total_rows) ?>건)</span>
    </a>
    <?php endforeach; ?>
    <?php if ($filterDate): ?>
    &nbsp;<a href="?<?= $baseRange ?>" style="color:#999;">전체보기</a>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- 상세 목록 -->
<div class="panel panel-default">
    <div class="panel-heading">
        <strong>출고 상세</strong>
        <span class="text-muted" style="font-size:12px;margin-left:8px;">총 <?= number_format($listCnt) ?>건</span>
    </div>
    <div class="panel-body" style="padding-bottom:5px;">
        <form method="get" class="form-inline">
            <input type="hidden" name="view"      value="<?= htmlspecialchars($viewType) ?>">
            <input type="hidden" name="date_from" value="<?= htmlspecialchars($dateFrom) ?>">
            <input type="hidden" name="date_to"   value="<?= htmlspecialchars($dateTo) ?>">
            <input type="text" name="date" class="form-control input-sm"
                   placeholder="날짜 (2026-05-27)" value="<?= htmlspecialchars($filterDate) ?>"
                   style="width:140px;">&nbsp;
            <input type="text" name="model" class="form-control input-sm"
                   placeholder="모델 (HC501)" value="<?= htmlspecialchars($filterModel) ?>"
                   style="width:120px;">&nbsp;
            <input type="text" name="channel" class="form-control input-sm"
                   placeholder="채널" value="<?= htmlspecialchars($filterCh) ?>"
                   style="width:140px;">&nbsp;
            <button class="btn btn-sm btn-primary" type="submit">검색</button>
            <a href="?<?= $baseRange ?>" class="btn btn-sm btn-default">초기화</a>
        </form>
    </div>
    <table class="table table-bordered table-hover table-condensed" style="margin:0;font-size:12px;">
        <thead>
            <tr style="background:#f5f5f5;">
                <th>날짜</th><th>모델</th><th>수량</th><th>채널</th>
                <th>수령자</th><th>송장번호</th><th>연락처</th>
                <th>결제금액</th><th>주문자ID</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($listRows)): ?>
        <tr><td colspan="9" class="text-center text-muted">데이터 없음</td></tr>
        <?php else: ?>
        <?php foreach ($listRows as $row): ?>
        <tr>
            <td><?= htmlspecialchars($row->ship_date) ?></td>
            <td><?= htmlspecialchars($row->model) ?></td>
            <td style="text-align:center;"><?= (int)$row->qty ?></td>
            <td><?= htmlspecialchars($row->channel) ?></td>
            <td><?= htmlspecialchars($row->recipient) ?></td>
            <td><?= htmlspecialchars($row->tracking_no) ?></td>
            <td><?= htmlspecialchars($row->mobile) ?></td>
            <td style="text-align:right;"><?= ($row->payment_amt > 0) ? number_format($row->payment_amt) : '-' ?></td>
            <td><?= htmlspecialchars($row->orderer_id) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
    <?php if ($totalPage > 1): ?>
    <div style="padding:10px;text-align:center;">
        <ul class="pagination pagination-sm" style="margin:0;">
        <?php
        $baseQ = http_build_query(array_filter(array(
            'view' => $viewType, 'date_from' => $dateFrom, 'date_to' => $dateTo,
            'date' => $filterDate, 'model' => $filterModel, 'channel' => $filterCh
        )));
        for ($p = max(1, $page-4); $p <= min($totalPage, $page+4); $p++):
        ?>
        <li class="<?= ($p==$page?'active':'') ?>">
            <a href="?<?= $baseQ ?>&page=<?= $p ?>"><?= $p ?></a>
        </li>
        <?php endfor; ?>
        </ul>
    </div>
    <?php endif; ?>
</div>

<script>
// 뷰별 기본 기간
var viewPresets = {
    daily:   {from:"<?= $today ?>",      to:"<?= $today ?>"},
    weekly:  {from:"<?= $weekStart ?>",  to:"<?= $today ?>"},
    monthly: {from:"<?= $monthStart ?>", to:"<?= $today ?>"}
};

// 뷰 버튼 클릭: 기간 프리셋 적용 후 제출
function setView(v) {
    document.getElementById('viewHidden').value    = v;
    document.getElementById('dateFromHidden').value = viewPresets[v].from;
    document.getElementById('dateToHidden').value   = viewPresets[v].to;
    document.getElementById('filterForm').submit();
}

// 날짜 직접 변경 시 hidden input 동기화
function syncDates() {
    document.getElementById('dateFromHidden').value = document.getElementById('inputDateFrom').value;
    document.getElementById('dateToHidden').value   = document.getElementById('inputDateTo').value;
}

// ── 차트 데이터 ──────────────────────────────────────────────
var trendQtyPts = [<?php foreach ($trendData as $d): ?>
    {label:"<?= date('m/d', strtotime($d->ship_date)) ?>",y:<?= (int)$d->cnt ?>},
<?php endforeach; ?>];
var trendRevPts = [<?php foreach ($trendData as $d): ?>
    {label:"<?= date('m/d', strtotime($d->ship_date)) ?>",y:<?= (int)$d->revenue ?>},
<?php endforeach; ?>];

var modelQtyPts = [<?php foreach ($modelDataRev as $m): ?>
    {label:"<?= htmlspecialchars(addslashes($m->model_base)) ?>",y:<?= (int)$m->cnt ?>},
<?php endforeach; ?>];

var channelPts = [<?php foreach ($channelData as $c): ?>
    {label:"<?= htmlspecialchars(addslashes($c->channel)) ?>",y:<?= (int)$c->cnt ?>},
<?php endforeach; ?>];

var channelRevPts = [<?php foreach ($channelRevData as $c): ?>
    {label:"<?= htmlspecialchars(addslashes($c->channel)) ?>",y:<?= (int)$c->revenue ?>},
<?php endforeach; ?>];

var dowPts = [<?php foreach ($dowCounts as $dow => $cnt): ?>
    {label:"<?= $dowLabels[$dow] ?>",y:<?= (int)$cnt ?>,color:<?= in_array($dow,array(1,7)) ? '"#c0392b"' : '"#4e73df"' ?>},
<?php endforeach; ?>];

window.onload = function() {
    // 추이 차트
    if (trendQtyPts.length > 0) {
        var interval = Math.max(1, Math.floor(trendQtyPts.length / 12));
        new CanvasJS.Chart("trendChart", {
            animationEnabled: true,
            toolTip: {shared:true},
            axisX: {labelFontSize:11, interval:interval},
            axisY: {title:"출고수", titleFontSize:11, labelFontSize:11,
                    gridDashType:"dash", minimum:0},
            axisY2: {title:"매출(원)", titleFontSize:11, labelFontSize:11,
                     gridDashType:"dash", minimum:0, valueFormatString:"#,##0"},
            legend: {fontSize:11, verticalAlign:"top", horizontalAlign:"right"},
            data: [
                {type:"column", name:"출고수", showInLegend:true, color:"#4e73df",
                 yValueFormatString:"#,##0건", dataPoints:trendQtyPts},
                {type:"line",   name:"매출(원)", showInLegend:true, color:"#e74c3c",
                 axisYType:"secondary", markerSize:4, lineThickness:2,
                 yValueFormatString:"#,##0원", dataPoints:trendRevPts}
            ]
        }).render();
    }

    new CanvasJS.Chart("modelChart", {
        animationEnabled: true,
        axisX: {labelFontSize:10},
        axisY: {gridDashType:"dash", labelFontSize:10, minimum:0},
        data: [{type:"bar", color:"#4e73df",
                yValueFormatString:"#,##0건", dataPoints:modelQtyPts}]
    }).render();

    new CanvasJS.Chart("channelChart", {
        animationEnabled: true,
        legend: {fontSize:11, verticalAlign:"bottom", horizontalAlign:"center"},
        data: [{type:"doughnut", showInLegend:true,
                indexLabel:"{y}건", indexLabelFontSize:11,
                toolTipContent:"{label}: {y}건",
                dataPoints:channelPts}]
    }).render();

    new CanvasJS.Chart("dowChart", {
        animationEnabled: true,
        axisX: {labelFontSize:12},
        axisY: {gridDashType:"dash", labelFontSize:11, minimum:0},
        data: [{type:"column", yValueFormatString:"#,##0건", dataPoints:dowPts}]
    }).render();

    if (channelRevPts.length > 0) {
        new CanvasJS.Chart("channelRevChart", {
            animationEnabled: true,
            axisX: {labelFontSize:11},
            axisY: {gridDashType:"dash", labelFontSize:10, minimum:0,
                    valueFormatString:"#,##0"},
            data: [{type:"bar", color:"#e74c3c",
                    yValueFormatString:"#,##0원", dataPoints:channelRevPts}]
        }).render();
    } else {
        document.getElementById('channelRevChart').innerHTML =
            '<p style="text-align:center;color:#ccc;padding:60px 0;">결제금액 데이터 없음</p>';
    }
};
</script>

<?php include('../footer.php'); ?>
