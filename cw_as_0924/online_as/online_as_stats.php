<?
include("../def_inc.php");
$mod  = M_STATS;
$menu = S_STATS_AS;
include("../header.php");

$table = "as_parcel_service";

// ── 요약 카드 ──────────────────────────────────────────
$today_reg   = $db->cnt($table, "where return_track_status=2 AND DATE(return_track_at)=CURDATE()");
$today_ship  = $db->cnt($table, "where process_state=4 AND DATE(update_time)=CURDATE()");
$week_reg    = $db->cnt($table, "where return_track_status=2 AND return_track_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
$week_ship   = $db->cnt($table, "where process_state=4 AND update_time >= DATE_SUB(NOW(), INTERVAL 7 DAY)");

$r = mysqli_fetch_row(mysqli_query($db->db_conn,
    "SELECT ROUND(AVG(DATEDIFF(DATE(h.fix_done_at), DATE(a.return_track_at))),1)
     FROM $table a
     JOIN (SELECT as_idx, MIN(changed_at) as fix_done_at
           FROM as_process_history WHERE new_state=3 GROUP BY as_idx) h ON h.as_idx=a.idx
     WHERE a.return_track_status=2 AND a.return_track_at IS NOT NULL
       AND h.fix_done_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
       AND DATEDIFF(DATE(h.fix_done_at), DATE(a.return_track_at)) BETWEEN 0 AND 30"));
$avg_days = isset($r[0]) ? $r[0] : '-';

// ── 최근 30일 일별 입고완료/출고 ──────────────────────────
$reg_map = [];
$rs = mysqli_query($db->db_conn,
    "SELECT DATE(return_track_at) as d, COUNT(*) as cnt FROM $table
     WHERE return_track_status=2
       AND return_track_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
     GROUP BY DATE(return_track_at) ORDER BY d");
while($row = mysqli_fetch_assoc($rs)) $reg_map[$row['d']] = (int)$row['cnt'];

$ship_map = [];
$rs = mysqli_query($db->db_conn,
    "SELECT DATE(update_time) as d, COUNT(*) as cnt FROM $table
     WHERE process_state=4 AND update_time >= DATE_SUB(NOW(), INTERVAL 30 DAY)
     GROUP BY DATE(update_time) ORDER BY d");
while($row = mysqli_fetch_assoc($rs)) $ship_map[$row['d']] = (int)$row['cnt'];

$daily_reg = $daily_ship = [];
for($i=29; $i>=0; $i--) {
    $d = date('Y-m-d', strtotime("-{$i} days"));
    $daily_reg[]  = ['label' => date('n/j', strtotime($d)), 'y' => isset($reg_map[$d])  ? $reg_map[$d]  : 0];
    $daily_ship[] = ['label' => date('n/j', strtotime($d)), 'y' => isset($ship_map[$d]) ? $ship_map[$d] : 0];
}

// ── 최근 12주 주간 처리량 ──────────────────────────────
$weekly = [];
$rs = mysqli_query($db->db_conn,
    "SELECT YEARWEEK(update_time,1) as wk,
            MIN(DATE(update_time)) as wk_start,
            COUNT(*) as cnt
     FROM $table
     WHERE process_state=4 AND update_time >= DATE_SUB(NOW(), INTERVAL 12 WEEK)
     GROUP BY wk ORDER BY wk");
while($row = mysqli_fetch_assoc($rs)) {
    $weekly[] = ['label' => date('n/j', strtotime($row['wk_start'])).'주', 'y' => (int)$row['cnt']];
}

// ── 처리 기간 분포 ──────────────────────────────────────
$rs = mysqli_query($db->db_conn,
    "SELECT
       CASE
         WHEN DATEDIFF(DATE(h.fix_done_at), DATE(a.return_track_at)) <= 1  THEN '1일 이내'
         WHEN DATEDIFF(DATE(h.fix_done_at), DATE(a.return_track_at)) <= 3  THEN '2~3일'
         WHEN DATEDIFF(DATE(h.fix_done_at), DATE(a.return_track_at)) <= 7  THEN '4~7일'
         WHEN DATEDIFF(DATE(h.fix_done_at), DATE(a.return_track_at)) <= 14 THEN '8~14일'
         ELSE '15일 이상'
       END as period,
       COUNT(*) as cnt
     FROM $table a
     JOIN (SELECT as_idx, MIN(changed_at) as fix_done_at
           FROM as_process_history WHERE new_state=3 GROUP BY as_idx) h ON h.as_idx=a.idx
     WHERE a.return_track_status=2 AND a.return_track_at IS NOT NULL
       AND h.fix_done_at >= DATE_SUB(NOW(), INTERVAL 3 MONTH)
       AND DATEDIFF(DATE(h.fix_done_at), DATE(a.return_track_at)) BETWEEN 0 AND 30
     GROUP BY period");
$period_order = ['1일 이내','2~3일','4~7일','8~14일','15일 이상'];
$period_map = [];
while($row = mysqli_fetch_assoc($rs)) $period_map[$row['period']] = (int)$row['cnt'];
$duration = [];
foreach($period_order as $p) $duration[] = ['label' => $p, 'y' => isset($period_map[$p]) ? $period_map[$p] : 0];

// ── 기간 필터 (담당자/제품별 AS기간 섹션용) ─────────────
$period_filter = isset($_GET['period']) ? (int)$_GET['period'] : 3;
$period_options = [1=>'최근 1개월', 3=>'최근 3개월', 6=>'최근 6개월', 12=>'최근 12개월'];
$period_interval = "DATE_SUB(NOW(), INTERVAL {$period_filter} MONTH)";

// ── 담당자별 AS 기간 (주말 제외 영업일 기준) ───────────────────────────
function count_weekdays($start_date, $end_date) {
    if (!$start_date || !$end_date || $start_date >= $end_date) return 0;
    $d1    = new DateTime($start_date);
    $d2    = new DateTime($end_date);
    $total = (int)$d1->diff($d2)->days;
    $weeks = (int)($total / 7);
    $rem   = $total % 7;
    $days  = $weeks * 5;
    $start_dow = (int)$d1->format('N'); // 1=월 … 7=일
    for ($i = 1; $i <= $rem; $i++) {
        $dow = (($start_dow - 1 + $i) % 7) + 1;
        if ($dow <= 5) $days++;
    }
    return $days;
}

$pic_period = [];
$rs = mysqli_query($db->db_conn,
    "SELECT a.pic_name,
            DATE(a.return_track_at) as start_date,
            DATE(h.fix_done_at) as end_date
     FROM $table a
     JOIN (SELECT as_idx, MIN(changed_at) as fix_done_at
           FROM as_process_history WHERE new_state=3 GROUP BY as_idx) h ON h.as_idx=a.idx
     WHERE a.return_track_status=2 AND a.return_track_at IS NOT NULL
       AND h.fix_done_at >= $period_interval
       AND DATEDIFF(DATE(h.fix_done_at), DATE(a.return_track_at)) BETWEEN 0 AND 30
       AND a.pic_name != ''");
$_pic_data = [];
while ($row = mysqli_fetch_assoc($rs)) {
    $name = $row['pic_name'];
    $wd   = count_weekdays($row['start_date'], $row['end_date']);
    if ($wd === 0) continue;
    if (!isset($_pic_data[$name])) $_pic_data[$name] = ['sum'=>0,'cnt'=>0,'min'=>9999,'max'=>0];
    $_pic_data[$name]['sum'] += $wd;
    $_pic_data[$name]['cnt']++;
    if ($wd < $_pic_data[$name]['min']) $_pic_data[$name]['min'] = $wd;
    if ($wd > $_pic_data[$name]['max']) $_pic_data[$name]['max'] = $wd;
}
foreach ($_pic_data as $name => $d) {
    $pic_period[] = [
        'name'     => $name,
        'cnt'      => $d['cnt'],
        'avg_days' => round($d['sum'] / $d['cnt'], 1),
        'min_days' => $d['min'],
        'max_days' => $d['max'],
    ];
}
usort($pic_period, function($a, $b) { return $a['avg_days'] > $b['avg_days'] ? 1 : -1; });

// ── 제품별 AS 기간 (주말 제외 영업일 기준) ─────────────────────────────
$prod_period = [];
$rs = mysqli_query($db->db_conn,
    "SELECT a.product_name,
            DATE(a.return_track_at) as start_date,
            DATE(h.fix_done_at) as end_date
     FROM $table a
     JOIN (SELECT as_idx, MIN(changed_at) as fix_done_at
           FROM as_process_history WHERE new_state=3 GROUP BY as_idx) h ON h.as_idx=a.idx
     WHERE a.return_track_status=2 AND a.return_track_at IS NOT NULL
       AND h.fix_done_at >= $period_interval
       AND DATEDIFF(DATE(h.fix_done_at), DATE(a.return_track_at)) BETWEEN 0 AND 30");
$_prod_data = [];
while ($row = mysqli_fetch_assoc($rs)) {
    $name = $row['product_name'];
    $wd   = count_weekdays($row['start_date'], $row['end_date']);
    if ($wd === 0) continue;
    if (!isset($_prod_data[$name])) $_prod_data[$name] = ['sum'=>0,'cnt'=>0,'min'=>9999,'max'=>0];
    $_prod_data[$name]['sum'] += $wd;
    $_prod_data[$name]['cnt']++;
    if ($wd < $_prod_data[$name]['min']) $_prod_data[$name]['min'] = $wd;
    if ($wd > $_prod_data[$name]['max']) $_prod_data[$name]['max'] = $wd;
}
foreach ($_prod_data as $name => $d) {
    $prod_period[] = [
        'name'     => $name,
        'cnt'      => $d['cnt'],
        'avg_days' => round($d['sum'] / $d['cnt'], 1),
        'min_days' => $d['min'],
        'max_days' => $d['max'],
    ];
}
usort($prod_period, function($a, $b) { return $b['cnt'] - $a['cnt']; });
$prod_period = array_slice($prod_period, 0, 20);

// ── 30일 이상 장기 처리 내역 ────────────────────────────
$long_cases = [];
$rs = mysqli_query($db->db_conn,
    "SELECT idx, reg_num, reg_date, DATE(update_time) as ship_date,
            DATEDIFF(DATE(update_time), reg_date) as days,
            product_name, pic_name, customer_name
     FROM $table
     WHERE process_state=4
       AND DATEDIFF(DATE(update_time), reg_date) >= 30
     ORDER BY ship_date DESC");
while ($row = mysqli_fetch_assoc($rs)) $long_cases[] = $row;

// ── 상태별 현황 ─────────────────────────────────────────
$state_labels = [1=>'접수완료',2=>'수리중',3=>'수리완료',6=>'택배비입금'];
$rs = mysqli_query($db->db_conn,
    "SELECT process_state, COUNT(*) as cnt FROM $table
     WHERE process_state IN (1,2,3,6) GROUP BY process_state");
$state_points = [];
while($row = mysqli_fetch_assoc($rs)) {
    $state_points[] = ['label' => $state_labels[$row['process_state']], 'y' => (int)$row['cnt']];
}
?>

<style>
.stat-card{background:#fff;border:1px solid #ddd;border-radius:4px;padding:20px 24px;margin-bottom:20px;}
.stat-card .num{font-size:36px;font-weight:700;line-height:1.1;margin:6px 0 2px;}
.stat-card .label{font-size:13px;color:#888;}
.stat-card .sub{font-size:12px;color:#aaa;margin-top:4px;}
.stat-card.blue  .num{color:#337ab7;}
.stat-card.green .num{color:#5cb85c;}
.stat-card.orange .num{color:#f0ad4e;}
.stat-card.red   .num{color:#d9534f;}
.stat-card.teal  .num{color:#5bc0de;}
.stat-card.purple .num{color:#9b59b6;}
.chart-box{background:#fff;border:1px solid #ddd;border-radius:4px;padding:20px;margin-bottom:20px;}
.chart-box h5{font-size:14px;font-weight:700;color:#555;margin:0 0 16px;padding-bottom:10px;border-bottom:1px solid #eee;}
</style>

<h4 class="page-header">AS 통계 대시보드</h4>

<!-- 요약 카드 -->
<div class="row">
    <div class="col-sm-2">
        <div class="stat-card blue">
            <div class="label">오늘 입고완료</div>
            <div class="num"><?= $today_reg ?></div>
            <div class="sub">건</div>
        </div>
    </div>
    <div class="col-sm-2">
        <div class="stat-card green">
            <div class="label">오늘 출고</div>
            <div class="num"><?= $today_ship ?></div>
            <div class="sub">건</div>
        </div>
    </div>
    <div class="col-sm-2">
        <div class="stat-card blue">
            <div class="label">최근 7일 입고완료</div>
            <div class="num"><?= $week_reg ?></div>
            <div class="sub">건</div>
        </div>
    </div>
    <div class="col-sm-2">
        <div class="stat-card green">
            <div class="label">최근 7일 출고</div>
            <div class="num"><?= $week_ship ?></div>
            <div class="sub">건</div>
        </div>
    </div>
    <div class="col-sm-2">
        <div class="stat-card teal">
            <div class="label">평균 처리 기간</div>
            <div class="num"><?= $avg_days ?></div>
            <div class="sub">일 (배송완료→수리완료, 최근 30일)</div>
        </div>
    </div>
</div>

<!-- 최근 30일 일별 추이 -->
<div class="chart-box">
    <h5>최근 30일 일별 입고완료 / 출고 추이</h5>
    <div id="chart_daily" style="height:280px;"></div>
</div>

<!-- 주간 처리량 + 처리 기간 분포 -->
<div class="row">
    <div class="col-sm-7">
        <div class="chart-box">
            <h5>최근 12주 주간 출고량</h5>
            <div id="chart_weekly" style="height:260px;"></div>
        </div>
    </div>
    <div class="col-sm-5">
        <div class="chart-box">
            <h5>처리 기간 분포 (최근 3개월, 배송완료→수리완료 기준)</h5>
            <div id="chart_duration" style="height:260px;"></div>
        </div>
    </div>
</div>

<!-- 상태별 현황 + 제품별 -->
<div class="row">
    <div class="col-sm-5">
        <div class="chart-box">
            <h5>현재 처리 대기 상태별 현황</h5>
            <div id="chart_state" style="height:260px;"></div>
        </div>
    </div>
    <div class="col-sm-7">
        <div class="chart-box">
            <h5>제품별 누적 AS 접수 현황 (출고 완료 기준)</h5>
            <div id="chart_product" style="height:260px;"></div>
        </div>
    </div>
</div>

<!-- 담당자/제품별 AS 기간 분석 -->
<div class="chart-box">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid #eee;">
        <span style="font-size:14px;font-weight:700;color:#555;">AS 기간 분석 (담당자 · 제품별)</span>
        <div>
            <span style="font-size:12px;color:#888;margin-right:8px;">조회 기간:</span>
            <?php foreach($period_options as $v => $label): ?>
            <a href="?period=<?=$v?>" class="btn btn-xs <?= $period_filter==$v ? 'btn-primary' : 'btn-default' ?>" style="margin-right:2px;"><?=$label?></a>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6">
            <p style="font-size:13px;font-weight:700;color:#555;margin:0 0 10px;">담당자별 평균 AS 기간 (영업일 기준, 주말 제외)</p>
            <div id="chart_pic_period" style="height:260px;"></div>
            <table class="table table-condensed table-bordered" style="margin-top:14px;font-size:12px;">
                <thead><tr style="background:#f7f7f7;">
                    <th>담당자</th><th class="text-center">처리건수</th><th class="text-center">평균(영업일)</th><th class="text-center">최단</th><th class="text-center">최장</th>
                </tr></thead>
                <tbody>
                <?php foreach($pic_period as $r): ?>
                <tr>
                    <td><?= htmlspecialchars($r['name']) ?></td>
                    <td class="text-center"><?= $r['cnt'] ?>건</td>
                    <td class="text-center"><strong><?= $r['avg_days'] ?>일</strong></td>
                    <td class="text-center"><?= $r['min_days'] ?>일</td>
                    <td class="text-center"><?= $r['max_days'] ?>일</td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($pic_period)): ?>
                <tr><td colspan="5" class="text-center text-muted">데이터 없음</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="col-sm-6">
            <p style="font-size:13px;font-weight:700;color:#555;margin:0 0 10px;">제품별 평균 AS 기간 (영업일 기준, 주말 제외) <small class="text-muted">최대 20개</small></p>
            <div id="chart_prod_period" style="height:260px;"></div>
            <table class="table table-condensed table-bordered" style="margin-top:14px;font-size:12px;">
                <thead><tr style="background:#f7f7f7;">
                    <th>제품명</th><th class="text-center">처리건수</th><th class="text-center">평균(영업일)</th><th class="text-center">최단</th><th class="text-center">최장</th>
                </tr></thead>
                <tbody>
                <?php foreach($prod_period as $r): ?>
                <tr>
                    <td><?= htmlspecialchars($r['name']) ?></td>
                    <td class="text-center"><?= $r['cnt'] ?>건</td>
                    <td class="text-center"><strong><?= $r['avg_days'] ?>일</strong></td>
                    <td class="text-center"><?= $r['min_days'] ?>일</td>
                    <td class="text-center"><?= $r['max_days'] ?>일</td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($prod_period)): ?>
                <tr><td colspan="5" class="text-center text-muted">데이터 없음</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
var daily_reg   = <?= json_encode($daily_reg,  JSON_NUMERIC_CHECK) ?>;
var daily_ship  = <?= json_encode($daily_ship, JSON_NUMERIC_CHECK) ?>;
var weekly      = <?= json_encode($weekly,     JSON_NUMERIC_CHECK) ?>;
var duration    = <?= json_encode($duration,   JSON_NUMERIC_CHECK) ?>;
var state_pts   = <?= json_encode($state_points, JSON_NUMERIC_CHECK) ?>;
var pic_period_pts  = <?= json_encode(array_map(function($r){ return ['label'=>$r['name'], 'y'=>$r['avg_days']]; }, $pic_period),  JSON_NUMERIC_CHECK) ?>;
var prod_period_pts = <?= json_encode(array_map(function($r){ return ['label'=>$r['name'], 'y'=>$r['avg_days']]; }, $prod_period), JSON_NUMERIC_CHECK) ?>;

<?php
// 제품별 데이터
$rs = mysqli_query($db->db_conn,
    "SELECT product_name, COUNT(*) as cnt FROM $table
     WHERE process_state=4 GROUP BY product_name ORDER BY cnt DESC LIMIT 15");
$product_pts = [];
$total = $db->cnt($table, "where process_state=4");
while($row = mysqli_fetch_assoc($rs)) {
    $pct = round($row['cnt'] / $total * 100, 1);
    if($pct >= 1) $product_pts[] = ['label' => $row['product_name'], 'y' => (int)$row['cnt']];
}
echo "var product_pts = " . json_encode($product_pts, JSON_NUMERIC_CHECK) . ";";
?>

window.onload = function() {

    // 일별 추이
    new CanvasJS.Chart("chart_daily", {
        animationEnabled: true,
        legend: { cursor:"pointer", verticalAlign:"top" },
        toolTip: { shared: true },
        axisX: { labelAngle: -30, interval: 5 },
        axisY: { title: "건수", gridThickness: 1 },
        data: [
            { type:"line", name:"입고완료", showInLegend:true, markerSize:4,
              color:"#337ab7", dataPoints: daily_reg },
            { type:"line", name:"출고", showInLegend:true, markerSize:4,
              color:"#5cb85c", dataPoints: daily_ship }
        ]
    }).render();

    // 주간 처리량
    new CanvasJS.Chart("chart_weekly", {
        animationEnabled: true,
        axisX: { labelAngle: -30 },
        axisY: { title: "출고 건수", gridThickness: 1 },
        data: [{
            type: "column", color:"#337ab7",
            indexLabel: "{y}", indexLabelPlacement:"outside", indexLabelFontSize:11,
            dataPoints: weekly
        }]
    }).render();

    // 처리 기간 분포
    new CanvasJS.Chart("chart_duration", {
        animationEnabled: true,
        axisY: { title: "건수", gridThickness: 1 },
        data: [{
            type: "bar", color:"#5bc0de",
            indexLabel: "{y}건", indexLabelPlacement:"outside", indexLabelFontSize:11,
            dataPoints: duration
        }]
    }).render();

    // 상태별 현황
    new CanvasJS.Chart("chart_state", {
        animationEnabled: true,
        data: [{
            type: "doughnut",
            indexLabel: "{label} {y}건",
            indexLabelFontSize: 12,
            dataPoints: state_pts
        }]
    }).render();

    // 제품별
    new CanvasJS.Chart("chart_product", {
        animationEnabled: true,
        data: [{
            type: "pie",
            startAngle: 240,
            indexLabel: "{label} {y}",
            indexLabelFontSize: 11,
            dataPoints: product_pts
        }]
    }).render();

    // 담당자별 AS 기간
    if(pic_period_pts.length > 0) {
        new CanvasJS.Chart("chart_pic_period", {
            animationEnabled: true,
            axisX: { labelAngle: 0 },
            axisY: { title: "평균 기간(일)", gridThickness: 1 },
            data: [{
                type: "bar", color:"#9b59b6",
                indexLabel: "{y}일", indexLabelPlacement:"outside", indexLabelFontSize:11,
                dataPoints: pic_period_pts
            }]
        }).render();
    }

    // 제품별 AS 기간
    if(prod_period_pts.length > 0) {
        new CanvasJS.Chart("chart_prod_period", {
            animationEnabled: true,
            axisX: { labelAngle: 0 },
            axisY: { title: "평균 기간(일)", gridThickness: 1 },
            data: [{
                type: "bar", color:"#f0ad4e",
                indexLabel: "{y}일", indexLabelPlacement:"outside", indexLabelFontSize:11,
                dataPoints: prod_period_pts
            }]
        }).render();
    }
};
</script>

<!-- 30일 이상 장기 처리 내역 -->
<div class="chart-box">
    <h5>출고 완료 중 처리 기간 30일 이상 내역 <span class="badge" style="background:#d9534f;"><?= count($long_cases) ?>건</span></h5>
    <table class="table table-condensed table-bordered table-hover" style="font-size:12px;">
        <thead><tr style="background:#f7f7f7;">
            <th class="text-center" style="width:60px;">No</th>
            <th class="text-center" style="width:50px;">idx</th>
            <th class="text-center" style="width:110px;">접수번호</th>
            <th class="text-center" style="width:90px;">접수일</th>
            <th class="text-center" style="width:90px;">출고일</th>
            <th class="text-center" style="width:60px;">기간</th>
            <th>제품명</th>
            <th class="text-center" style="width:80px;">담당자</th>
            <th class="text-center" style="width:80px;">고객명</th>
        </tr></thead>
        <tbody>
        <?php foreach($long_cases as $i => $r): ?>
        <?php
            $days = (int)$r['days'];
            $color = $days >= 90 ? '#fdf2f2' : ($days >= 60 ? '#fef9e7' : '');
        ?>
        <tr style="background:<?= $color ?>">
            <td class="text-center"><?= $i+1 ?></td>
            <td class="text-center"><?= $r['idx'] ?></td>
            <td class="text-center"><?= htmlspecialchars($r['reg_num']) ?></td>
            <td class="text-center"><?= $r['reg_date'] ?></td>
            <td class="text-center"><?= $r['ship_date'] ?></td>
            <td class="text-center"><strong style="color:<?= $days>=60?'#d9534f':'#f0ad4e' ?>"><?= $days ?>일</strong></td>
            <td><?= htmlspecialchars($r['product_name']) ?></td>
            <td class="text-center"><?= htmlspecialchars($r['pic_name']) ?></td>
            <td class="text-center"><?= htmlspecialchars($r['customer_name']) ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<? include('../footer.php'); ?>
