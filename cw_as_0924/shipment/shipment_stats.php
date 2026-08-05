<?
include("../def_inc.php");
$mod  = M_STATS;
$menu = S_STATS_SHIPMENT;
include("../header.php");

$table = "shipping_date_new";

// ── 인덱스 보장 (최초 1회만 실행되도록 존재 확인 후 추가) ─────
// 직접 실행하거나, DB 관리자에게 아래 SQL을 요청할 것:
// ALTER TABLE shipping_date_new ADD INDEX IF NOT EXISTS idx_status_date       (status, date);
// ALTER TABLE shipping_date_new ADD INDEX IF NOT EXISTS idx_status_date_model (status, date, model);
// ALTER TABLE shipping_date_new ADD INDEX IF NOT EXISTS idx_status_date_mall  (status, date, mall);

// ── 기간 파라미터 처리 ─────────────────────────────────
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-01');
$date_to   = isset($_GET['date_to'])   ? $_GET['date_to']   : date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_from)) $date_from = date('Y-m-01');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_to))   $date_to   = date('Y-m-d');
if ($date_from > $date_to) { $t=$date_from; $date_from=$date_to; $date_to=$t; }

$period_days = (int)((strtotime($date_to) - strtotime($date_from)) / 86400) + 1;
$prev_from   = date('Y-m-d', strtotime($date_from) - $period_days * 86400);
$prev_to     = date('Y-m-d', strtotime($date_from) - 86400);

$df = mysqli_real_escape_string($db->db_conn, $date_from);
$dt = mysqli_real_escape_string($db->db_conn, $date_to);
$pf = mysqli_real_escape_string($db->db_conn, $prev_from);
$pt = mysqli_real_escape_string($db->db_conn, $prev_to);

// ══════════════════════════════════════════════════════
// 1. 요약 카드 — COUNT 5개 → 단일 쿼리로 통합
// ══════════════════════════════════════════════════════
$row = mysqli_fetch_assoc(mysqli_query($db->db_conn,
    "SELECT
       SUM(status=1 AND date BETWEEN '$df' AND '$dt')               AS period_ship,
       SUM(status=1 AND date BETWEEN '$pf' AND '$pt')               AS prev_ship,
       SUM(status=1 AND date = CURDATE())                           AS today_ship,
       SUM(status=0)                                                AS pending,
       SUM(status=1)                                                AS total_ship
     FROM $table"));
$period_ship = (int)$row['period_ship'];
$prev_ship   = (int)$row['prev_ship'];
$today_ship  = (int)$row['today_ship'];
$pending     = (int)$row['pending'];
$total_ship  = (int)$row['total_ship'];

$diff_cnt = $period_ship - $prev_ship;
$diff_pct = $prev_ship > 0 ? round($diff_cnt / $prev_ship * 100, 1) : 0;

// ══════════════════════════════════════════════════════
// 2. 일별·주간·월별 — GROUP BY 3쿼리 (변경 없음, 인덱스로 빨라짐)
// ══════════════════════════════════════════════════════
$ship_map = [];
$rs = mysqli_query($db->db_conn,
    "SELECT date, COUNT(*) as cnt FROM $table
     WHERE status=1 AND date BETWEEN '$df' AND '$dt'
     GROUP BY date ORDER BY date");
while ($row = mysqli_fetch_assoc($rs)) $ship_map[$row['date']] = (int)$row['cnt'];

$daily_ship = [];
$ts = strtotime($date_from);
$ts_end = strtotime($date_to);
while ($ts <= $ts_end) {
    $d = date('Y-m-d', $ts);
    $daily_ship[] = ['label' => date('n/j', $ts), 'y' => isset($ship_map[$d]) ? $ship_map[$d] : 0];
    $ts += 86400;
}

$weekly = [];
$rs = mysqli_query($db->db_conn,
    "SELECT YEARWEEK(date,1) as wk, MIN(date) as wk_start, COUNT(*) as cnt
     FROM $table
     WHERE status=1 AND date BETWEEN '$df' AND '$dt'
     GROUP BY wk ORDER BY wk");
while ($row = mysqli_fetch_assoc($rs)) {
    $weekly[] = ['label' => date('n/j', strtotime($row['wk_start'])).'주', 'y' => (int)$row['cnt']];
}

$monthly = [];
$rs = mysqli_query($db->db_conn,
    "SELECT DATE_FORMAT(date,'%Y-%m') as ym, COUNT(*) as cnt
     FROM $table
     WHERE status=1 AND date BETWEEN '$df' AND '$dt'
     GROUP BY ym ORDER BY ym");
while ($row = mysqli_fetch_assoc($rs)) {
    $monthly[] = ['label' => $row['ym'], 'y' => (int)$row['cnt']];
}

// ══════════════════════════════════════════════════════
// 3. 모델별 상세 — N+1 제거: 모델수×2 쿼리 → 2쿼리로 통합
// ══════════════════════════════════════════════════════

// 3-a. 모델 순위 (1 쿼리)
$model_rows = [];
$rs = mysqli_query($db->db_conn,
    "SELECT model, COUNT(*) as cnt FROM $table
     WHERE status=1 AND date BETWEEN '$df' AND '$dt' AND model != ''
     GROUP BY model ORDER BY cnt DESC");
while ($row = mysqli_fetch_assoc($rs)) {
    $model_rows[] = ['model' => $row['model'], 'cnt' => (int)$row['cnt']];
}
$model_total = array_sum(array_column($model_rows, 'cnt'));

// 3-b. 모델×구매처 분포 — 모델 수에 무관하게 단 1쿼리
$model_mall_raw = [];
$rs = mysqli_query($db->db_conn,
    "SELECT model, mall, COUNT(*) as cnt FROM $table
     WHERE status=1 AND date BETWEEN '$df' AND '$dt' AND model!='' AND mall!=''
     GROUP BY model, mall ORDER BY model, cnt DESC");
while ($row = mysqli_fetch_assoc($rs)) {
    $model_mall_raw[$row['model']][] = ['label' => $row['mall'], 'y' => (int)$row['cnt']];
}

// 3-c. 모델×일별 추이 — 단 1쿼리
$model_day_raw = [];
$rs = mysqli_query($db->db_conn,
    "SELECT model, date, COUNT(*) as cnt FROM $table
     WHERE status=1 AND date BETWEEN '$df' AND '$dt' AND model!=''
     GROUP BY model, date ORDER BY model, date");
while ($row = mysqli_fetch_assoc($rs)) {
    $model_day_raw[$row['model']][$row['date']] = (int)$row['cnt'];
}

// PHP에서 날짜 채우기 (날짜 라벨 배열 재사용)
$date_labels = [];
$ts = strtotime($date_from);
$ts_end = strtotime($date_to);
while ($ts <= $ts_end) {
    $date_labels[] = ['d' => date('Y-m-d', $ts), 'l' => date('n/j', $ts)];
    $ts += 86400;
}

$model_detail = [];
foreach ($model_rows as $mr) {
    $m = $mr['model'];
    $mall_sub = isset($model_mall_raw[$m]) ? array_slice($model_mall_raw[$m], 0, 10) : [];
    $day_map  = isset($model_day_raw[$m]) ? $model_day_raw[$m] : [];
    $day_sub  = [];
    foreach ($date_labels as $dl) {
        $day_sub[] = ['label' => $dl['l'], 'y' => isset($day_map[$dl['d']]) ? $day_map[$dl['d']] : 0];
    }
    $model_detail[$m] = ['mall' => $mall_sub, 'daily' => $day_sub];
}

// ══════════════════════════════════════════════════════
// 4. 구매처별 현황 (1 쿼리)
// ══════════════════════════════════════════════════════
$mall_pts = [];
$rs = mysqli_query($db->db_conn,
    "SELECT mall, COUNT(*) as cnt FROM $table
     WHERE status=1 AND date BETWEEN '$df' AND '$dt' AND mall!=''
     GROUP BY mall ORDER BY cnt DESC LIMIT 15");
while ($row = mysqli_fetch_assoc($rs)) {
    $mall_pts[] = ['label' => $row['mall'], 'y' => (int)$row['cnt']];
}
$mall_total = array_sum(array_column($mall_pts, 'y'));
?>

<style>
.filter-box{background:#fff;border:1px solid #ddd;border-radius:4px;padding:16px 20px;margin-bottom:20px;}
.filter-box .quick-btns .btn{margin-right:4px;margin-bottom:4px;}
.stat-card{background:#fff;border:1px solid #ddd;border-radius:4px;padding:16px 20px;margin-bottom:16px;}
.stat-card .num{font-size:32px;font-weight:700;line-height:1.1;margin:4px 0 2px;}
.stat-card .lbl{font-size:12px;color:#888;}
.stat-card .sub{font-size:11px;color:#aaa;margin-top:3px;}
.stat-card.blue  .num{color:#337ab7;}
.stat-card.green .num{color:#5cb85c;}
.stat-card.orange .num{color:#f0ad4e;}
.stat-card.red   .num{color:#d9534f;}
.stat-card.teal  .num{color:#5bc0de;}
.stat-card.purple .num{color:#9b59b6;}
.chart-box{background:#fff;border:1px solid #ddd;border-radius:4px;padding:20px;margin-bottom:20px;}
.chart-box h5{font-size:14px;font-weight:700;color:#555;margin:0 0 14px;padding-bottom:8px;border-bottom:1px solid #eee;}
.model-table{width:100%;border-collapse:collapse;font-size:13px;}
.model-table th{background:#f5f5f5;padding:9px 10px;border:1px solid #ddd;text-align:center;white-space:nowrap;}
.model-table td{padding:8px 10px;border:1px solid #ddd;vertical-align:middle;}
.model-table tbody tr:hover > td{background:#f9f9f9;cursor:pointer;}
.model-table .rank{text-align:center;font-weight:700;color:#888;width:42px;}
.model-table .cnt-cell{text-align:right;font-weight:700;width:80px;}
.model-table .pct-cell{text-align:right;color:#888;width:60px;}
.model-table .bar-cell{width:160px;}
.model-bar-wrap{background:#eee;border-radius:3px;height:10px;overflow:hidden;}
.model-bar-fill{height:10px;border-radius:3px;background:#337ab7;transition:width .3s;}
.toggle-cell{text-align:center;width:42px;color:#337ab7;}
.detail-row td{padding:0!important;border-top:none!important;}
.detail-panel{display:none;padding:16px;background:#f9fbff;border-top:2px solid #337ab7;}
.detail-panel.open{display:block;}
.detail-title{font-size:12px;font-weight:700;color:#555;margin-bottom:8px;}
.mall-badge{display:inline-block;background:#e8f0fe;color:#2c5dbc;border-radius:3px;padding:3px 8px;margin:3px 4px 3px 0;font-size:12px;}
.mall-badge span{font-weight:700;color:#1a3a8a;margin-left:4px;}
</style>

<h4 class="page-header">출고완료 통계 대시보드</h4>

<!-- ── 기간 설정 ── -->
<div class="filter-box">
<form method="get" id="filter-form" class="form-inline">
    <label style="font-weight:700;margin-right:10px;">조회 기간</label>
    <input type="text" id="inp_from" name="date_from" class="form-control input-sm"
           value="<?= htmlspecialchars($date_from) ?>" style="width:110px;" readonly>
    <span style="margin:0 6px;">~</span>
    <input type="text" id="inp_to" name="date_to" class="form-control input-sm"
           value="<?= htmlspecialchars($date_to) ?>" style="width:110px;" readonly>
    <button type="submit" class="btn btn-primary btn-sm" style="margin-left:10px;">조회</button>
    <div class="quick-btns" style="display:inline-block;margin-left:16px;">
        <button type="button" class="btn btn-default btn-xs qbtn" data-range="today">오늘</button>
        <button type="button" class="btn btn-default btn-xs qbtn" data-range="thisweek">이번주</button>
        <button type="button" class="btn btn-default btn-xs qbtn" data-range="thismonth">이번달</button>
        <button type="button" class="btn btn-default btn-xs qbtn" data-range="lastmonth">지난달</button>
        <button type="button" class="btn btn-default btn-xs qbtn" data-range="30d">최근 30일</button>
        <button type="button" class="btn btn-default btn-xs qbtn" data-range="90d">최근 90일</button>
        <button type="button" class="btn btn-default btn-xs qbtn" data-range="thisyear">올해</button>
    </div>
</form>
<div style="margin-top:8px;font-size:12px;color:#888;">
    조회 기간: <strong><?= htmlspecialchars($date_from) ?> ~ <?= htmlspecialchars($date_to) ?></strong>
    (<?= $period_days ?>일) &nbsp;|&nbsp;
    비교 기간: <?= htmlspecialchars($prev_from) ?> ~ <?= htmlspecialchars($prev_to) ?>
</div>
</div>

<!-- ── 요약 카드 ── -->
<div class="row">
    <div class="col-sm-2">
        <div class="stat-card green">
            <div class="lbl">기간 출고</div>
            <div class="num"><?= number_format($period_ship) ?></div>
            <div class="sub">건</div>
        </div>
    </div>
    <div class="col-sm-2">
        <div class="stat-card <?= $diff_cnt >= 0 ? 'green' : 'red' ?>">
            <div class="lbl">전기간 대비</div>
            <div class="num"><?= ($diff_cnt >= 0 ? '+' : '') . $diff_pct ?>%</div>
            <div class="sub">전기간 <?= number_format($prev_ship) ?>건 (<?= ($diff_cnt >= 0 ? '+' : '') . number_format($diff_cnt) ?>)</div>
        </div>
    </div>
    <div class="col-sm-2">
        <div class="stat-card blue">
            <div class="lbl">오늘 출고</div>
            <div class="num"><?= $today_ship ?></div>
            <div class="sub">건 (오늘 기준)</div>
        </div>
    </div>
    <div class="col-sm-2">
        <div class="stat-card teal">
            <div class="lbl">일 평균 출고</div>
            <div class="num"><?= $period_days > 0 ? round($period_ship / $period_days, 1) : 0 ?></div>
            <div class="sub">건/일</div>
        </div>
    </div>
    <div class="col-sm-2">
        <div class="stat-card orange">
            <div class="lbl">현재 출고 대기</div>
            <div class="num"><?= number_format($pending) ?></div>
            <div class="sub">건</div>
        </div>
    </div>
    <div class="col-sm-2">
        <div class="stat-card purple">
            <div class="lbl">누적 출고 (전체)</div>
            <div class="num"><?= number_format($total_ship) ?></div>
            <div class="sub">건</div>
        </div>
    </div>
</div>

<!-- ── 일별 출고 추이 ── -->
<div class="chart-box">
    <h5>일별 출고 추이 &nbsp;<small style="font-weight:400;color:#aaa;"><?= $date_from ?> ~ <?= $date_to ?></small></h5>
    <div id="chart_daily" style="height:260px;"></div>
</div>

<!-- ── 주간 + 월별 ── -->
<div class="row">
    <div class="col-sm-6">
        <div class="chart-box">
            <h5>주간 출고량</h5>
            <div id="chart_weekly" style="height:240px;"></div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="chart-box">
            <h5>월별 출고량</h5>
            <div id="chart_monthly" style="height:240px;"></div>
        </div>
    </div>
</div>

<!-- ── 제품별 상세 ── -->
<div class="chart-box">
    <h5>제품별 출고 상세 &nbsp;<small style="font-weight:400;color:#aaa;">모델명 클릭 시 구매처·일별 추이 확인</small></h5>
    <? if (empty($model_rows)): ?>
    <p style="color:#aaa;text-align:center;padding:30px 0;">해당 기간에 출고 데이터가 없습니다.</p>
    <? else: ?>
    <table class="model-table">
        <thead>
        <tr>
            <th class="rank">순위</th>
            <th>모델명</th>
            <th class="cnt-cell">출고건수</th>
            <th class="pct-cell">비율</th>
            <th class="bar-cell">비율 그래프</th>
            <th class="toggle-cell"></th>
        </tr>
        </thead>
        <tbody>
        <? foreach ($model_rows as $i => $mr):
            $pct = $model_total > 0 ? round($mr['cnt'] / $model_total * 100, 1) : 0;
            $detail_json = json_encode($model_detail[$mr['model']], JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
        ?>
        <tr class="model-row" data-idx="<?= $i ?>" data-detail='<?= htmlspecialchars($detail_json, ENT_QUOTES) ?>'>
            <td class="rank"><?= $i+1 ?></td>
            <td style="font-weight:600;"><?= htmlspecialchars($mr['model']) ?></td>
            <td class="cnt-cell"><?= number_format($mr['cnt']) ?></td>
            <td class="pct-cell"><?= $pct ?>%</td>
            <td class="bar-cell">
                <div class="model-bar-wrap">
                    <div class="model-bar-fill" style="width:<?= $pct ?>%"></div>
                </div>
            </td>
            <td class="toggle-cell"><span class="toggle-icon">▼</span></td>
        </tr>
        <tr class="detail-row" id="detail_<?= $i ?>">
            <td colspan="6">
                <div class="detail-panel" id="panel_<?= $i ?>">
                    <div class="row">
                        <div class="col-sm-5">
                            <div class="detail-title">구매처별 출고</div>
                            <div id="mall_chart_<?= $i ?>" style="height:160px;"></div>
                        </div>
                        <div class="col-sm-7">
                            <div class="detail-title">일별 출고 추이</div>
                            <div id="day_chart_<?= $i ?>" style="height:160px;"></div>
                        </div>
                    </div>
                    <div style="margin-top:10px;">
                    <? foreach ($model_detail[$mr['model']]['mall'] as $ms): ?>
                        <span class="mall-badge"><?= htmlspecialchars($ms['label']) ?><span><?= $ms['y'] ?>건</span></span>
                    <? endforeach; ?>
                    </div>
                </div>
            </td>
        </tr>
        <? endforeach; ?>
        </tbody>
        <tfoot>
        <tr style="background:#f5f5f5;font-weight:700;">
            <td colspan="2" style="padding:8px 10px;border:1px solid #ddd;text-align:right;">합계</td>
            <td class="cnt-cell" style="border:1px solid #ddd;"><?= number_format($model_total) ?></td>
            <td colspan="3" style="border:1px solid #ddd;"></td>
        </tr>
        </tfoot>
    </table>
    <? endif; ?>
</div>

<!-- ── 구매처별 현황 ── -->
<div class="row">
    <div class="col-sm-6">
        <div class="chart-box">
            <h5>구매처별 출고 현황</h5>
            <div id="chart_mall" style="height:300px;"></div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="chart-box">
            <h5>구매처별 출고 순위</h5>
            <table style="width:100%;font-size:13px;border-collapse:collapse;">
                <thead>
                <tr>
                    <th style="background:#f5f5f5;padding:7px 10px;border:1px solid #ddd;width:40px;text-align:center;">순위</th>
                    <th style="background:#f5f5f5;padding:7px 10px;border:1px solid #ddd;">구매처</th>
                    <th style="background:#f5f5f5;padding:7px 10px;border:1px solid #ddd;text-align:right;width:80px;">건수</th>
                    <th style="background:#f5f5f5;padding:7px 10px;border:1px solid #ddd;text-align:right;width:60px;">비율</th>
                </tr>
                </thead>
                <tbody>
                <? foreach ($mall_pts as $mi => $mp):
                    $mp_pct = $mall_total > 0 ? round($mp['y'] / $mall_total * 100, 1) : 0;
                ?>
                <tr>
                    <td style="padding:6px 10px;border:1px solid #ddd;text-align:center;font-weight:700;color:#888;"><?= $mi+1 ?></td>
                    <td style="padding:6px 10px;border:1px solid #ddd;"><?= htmlspecialchars($mp['label']) ?></td>
                    <td style="padding:6px 10px;border:1px solid #ddd;text-align:right;font-weight:700;"><?= number_format($mp['y']) ?></td>
                    <td style="padding:6px 10px;border:1px solid #ddd;text-align:right;color:#888;"><?= $mp_pct ?>%</td>
                </tr>
                <? endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function fmt(d){ return d.toISOString().slice(0,10); }
function setRange(f,t){ document.getElementById('inp_from').value=f; document.getElementById('inp_to').value=t; }

document.querySelectorAll('.qbtn').forEach(function(btn){
    btn.addEventListener('click', function(){
        var r = this.dataset.range;
        var now = new Date(), y=now.getFullYear(), m=now.getMonth(), d=now.getDate();
        if(r==='today'){
            setRange(fmt(now), fmt(now));
        } else if(r==='thisweek'){
            var day=now.getDay()||7; var mon=new Date(now); mon.setDate(d-day+1);
            setRange(fmt(mon), fmt(now));
        } else if(r==='thismonth'){
            setRange(y+'-'+('0'+(m+1)).slice(-2)+'-01', fmt(now));
        } else if(r==='lastmonth'){
            setRange(fmt(new Date(y,m-1,1)), fmt(new Date(y,m,0)));
        } else if(r==='30d'){
            var f=new Date(now); f.setDate(d-29); setRange(fmt(f), fmt(now));
        } else if(r==='90d'){
            var f=new Date(now); f.setDate(d-89); setRange(fmt(f), fmt(now));
        } else if(r==='thisyear'){
            setRange(y+'-01-01', fmt(now));
        }
        document.getElementById('filter-form').submit();
    });
});

$('#inp_from').datetimepicker({ format:'YYYY-MM-DD', locale:'ko' });
$('#inp_to').datetimepicker({ format:'YYYY-MM-DD', locale:'ko' });

var daily_ship = <?= json_encode($daily_ship, JSON_NUMERIC_CHECK) ?>;
var weekly     = <?= json_encode($weekly,     JSON_NUMERIC_CHECK) ?>;
var monthly    = <?= json_encode($monthly,    JSON_NUMERIC_CHECK) ?>;
var mall_pts   = <?= json_encode($mall_pts,   JSON_NUMERIC_CHECK) ?>;

var modelDetails = {};
<?php foreach ($model_rows as $i => $mr): ?>
modelDetails[<?= $i ?>] = <?= json_encode($model_detail[$mr['model']], JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK) ?>;
<?php endforeach; ?>

window.onload = function() {
    new CanvasJS.Chart("chart_daily", {
        animationEnabled: true,
        toolTip: { shared: true },
        axisX: { labelAngle: -45, interval: Math.max(1, Math.floor(daily_ship.length/10)) },
        axisY: { title: "건수", gridThickness: 1, minimum: 0 },
        data: [{ type:"area", color:"#5cb85c", fillOpacity:0.15,
                 markerSize: daily_ship.length<=31 ? 4 : 0, dataPoints: daily_ship }]
    }).render();

    new CanvasJS.Chart("chart_weekly", {
        animationEnabled: true,
        axisX: { labelAngle: -30 },
        axisY: { title: "건수", gridThickness: 1, minimum: 0 },
        data: [{ type:"column", color:"#337ab7",
                 indexLabel:"{y}", indexLabelPlacement:"outside", indexLabelFontSize:11,
                 dataPoints: weekly }]
    }).render();

    new CanvasJS.Chart("chart_monthly", {
        animationEnabled: true,
        axisX: { labelAngle: -30 },
        axisY: { title: "건수", gridThickness: 1, minimum: 0 },
        data: [{ type:"column", color:"#5bc0de",
                 indexLabel:"{y}", indexLabelPlacement:"outside", indexLabelFontSize:11,
                 dataPoints: monthly }]
    }).render();

    if (mall_pts.length > 0) {
        new CanvasJS.Chart("chart_mall", {
            animationEnabled: true,
            data: [{ type:"doughnut", indexLabel:"{label} {y}건", indexLabelFontSize:11, dataPoints: mall_pts }]
        }).render();
    }
};

var openIdx = -1;
var drawnCharts = {};

document.querySelectorAll('.model-row').forEach(function(row){
    row.addEventListener('click', function(){
        var idx = parseInt(this.dataset.idx);
        var panel = document.getElementById('panel_'+idx);

        if (openIdx === idx) {
            panel.classList.remove('open');
            this.querySelector('.toggle-icon').textContent = '▼';
            openIdx = -1;
            return;
        }
        if (openIdx >= 0) {
            document.getElementById('panel_'+openIdx).classList.remove('open');
            var prev = document.querySelector('[data-idx="'+openIdx+'"]');
            if (prev) prev.querySelector('.toggle-icon').textContent = '▼';
        }

        panel.classList.add('open');
        this.querySelector('.toggle-icon').textContent = '▲';
        openIdx = idx;

        if (!drawnCharts[idx]) {
            drawnCharts[idx] = true;
            var det = modelDetails[idx];

            if (det.mall && det.mall.length > 0) {
                new CanvasJS.Chart('mall_chart_'+idx, {
                    animationEnabled: true,
                    axisX: { labelFontSize: 11 },
                    axisY: { gridThickness: 1, minimum: 0 },
                    data: [{ type:"bar", color:"#f0ad4e",
                             indexLabel:"{y}", indexLabelFontSize:10, indexLabelPlacement:"outside",
                             dataPoints: det.mall }]
                }).render();
            } else {
                document.getElementById('mall_chart_'+idx).innerHTML =
                    '<p style="color:#aaa;text-align:center;padding:20px 0;font-size:12px;">구매처 데이터 없음</p>';
            }

            if (det.daily && det.daily.length > 0) {
                var iv = Math.max(1, Math.floor(det.daily.length/8));
                new CanvasJS.Chart('day_chart_'+idx, {
                    animationEnabled: true,
                    axisX: { labelAngle:-45, labelFontSize:10, interval:iv },
                    axisY: { gridThickness:1, minimum:0, labelFontSize:10 },
                    data: [{ type:"column", color:"#337ab7", dataPoints: det.daily }]
                }).render();
            }
        }
    });
});
</script>

<? include('../footer.php'); ?>
