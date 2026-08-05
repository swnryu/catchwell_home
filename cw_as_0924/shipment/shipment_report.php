<?
include("../def_inc.php");
$mod  = M_STATS;
$menu = S_STATS_REPORT;
include("../header.php");

$table = "shipping_date_new";

// ── 리포트 대상 모델 ───────────────────────────────────
$report_models = [
    'CX_PRO_N_세트',
    'CX_PRO_N_물걸레키트세트',
	'VC234',
	'CV6_PLUS',
];

// ── 뷰 전환 (weekly / monthly) ────────────────────────
$view = (isset($_GET['view']) && $_GET['view'] === 'monthly') ? 'monthly' : 'weekly';

// ── IN 조건 ───────────────────────────────────────────
$escaped = [];
foreach ($report_models as $m) $escaped[] = mysqli_real_escape_string($db->db_conn, $m);
$in_list = implode("','", $escaped);
$in_cond = "model IN ('$in_list')";

// ══════════════════════════════════════════════════════
//  주간 기준 계산
// ══════════════════════════════════════════════════════
$wk_offset = isset($_GET['week_offset']) ? (int)$_GET['week_offset'] : 0;
if ($wk_offset > 0)  $wk_offset = 0;
if ($wk_offset < -52) $wk_offset = -52;

$day_n        = (int)date('N');
$base_monday  = date('Y-m-d', strtotime('-'.($day_n - 1).' days') + $wk_offset * 7 * 86400);
$base_sunday  = date('Y-m-d', strtotime($base_monday) + 6 * 86400);
if ($base_sunday > date('Y-m-d')) $base_sunday = date('Y-m-d');

$wk_from      = $base_monday;
$wk_to        = $base_sunday;
$prev_wk_from = date('Y-m-d', strtotime($wk_from) - 7 * 86400);
$prev_wk_to   = date('Y-m-d', strtotime($wk_from) - 86400);

// ══════════════════════════════════════════════════════
//  월별 기준 계산
// ══════════════════════════════════════════════════════
$mo_offset = isset($_GET['month_offset']) ? (int)$_GET['month_offset'] : 0;
if ($mo_offset > 0)  $mo_offset = 0;
if ($mo_offset < -36) $mo_offset = -36;

$mo_base      = date('Y-m-01', strtotime(date('Y-m-01')." $mo_offset month"));
$mo_from      = $mo_base;
$mo_to        = date('Y-m-t', strtotime($mo_base));
if ($mo_to > date('Y-m-d')) $mo_to = date('Y-m-d');

$prev_mo_from = date('Y-m-01', strtotime($mo_base.' -1 month'));
$prev_mo_to   = date('Y-m-t',  strtotime($mo_base.' -1 month'));
$ly_mo_from   = date('Y-m-01', strtotime($mo_base.' -1 year'));
$ly_mo_to     = date('Y-m-t',  strtotime($mo_base.' -1 year'));

// 이번달 기준 월명
$mo_label     = date('Y년 n월', strtotime($mo_from));

// ── 이스케이프 ─────────────────────────────────────────
$wf   = mysqli_real_escape_string($db->db_conn, $wk_from);
$wt   = mysqli_real_escape_string($db->db_conn, $wk_to);
$pwf  = mysqli_real_escape_string($db->db_conn, $prev_wk_from);
$pwt  = mysqli_real_escape_string($db->db_conn, $prev_wk_to);
$mf   = mysqli_real_escape_string($db->db_conn, $mo_from);
$mt   = mysqli_real_escape_string($db->db_conn, $mo_to);
$pmf  = mysqli_real_escape_string($db->db_conn, $prev_mo_from);
$pmt  = mysqli_real_escape_string($db->db_conn, $prev_mo_to);
$lymf = mysqli_real_escape_string($db->db_conn, $ly_mo_from);
$lymt = mysqli_real_escape_string($db->db_conn, $ly_mo_to);

// ══════════════════════════════════════════════════════
//  공통: 요약 KPI (1 쿼리)
// ══════════════════════════════════════════════════════
$summary = [];
foreach ($report_models as $m) {
    $summary[$m] = ['wk'=>0,'pwk'=>0,'mo'=>0,'pmo'=>0,'lymo'=>0];
}
$rs = mysqli_query($db->db_conn,
    "SELECT model,
            SUM(status=1 AND date BETWEEN '$wf'  AND '$wt' ) AS wk,
            SUM(status=1 AND date BETWEEN '$pwf' AND '$pwt') AS pwk,
            SUM(status=1 AND date BETWEEN '$mf'  AND '$mt' ) AS mo,
            SUM(status=1 AND date BETWEEN '$pmf' AND '$pmt') AS pmo,
            SUM(status=1 AND date BETWEEN '$lymf' AND '$lymt') AS lymo
     FROM $table WHERE $in_cond GROUP BY model");
while ($row = mysqli_fetch_assoc($rs)) {
    if (isset($summary[$row['model']])) {
        $summary[$row['model']] = [
            'wk'   => (int)$row['wk'],
            'pwk'  => (int)$row['pwk'],
            'mo'   => (int)$row['mo'],
            'pmo'  => (int)$row['pmo'],
            'lymo' => (int)$row['lymo'],
        ];
    }
}

// ══════════════════════════════════════════════════════
//  주간 뷰 전용 쿼리
// ══════════════════════════════════════════════════════
if ($view === 'weekly') {

    // 최근 8주 추이
    $ewf = mysqli_real_escape_string($db->db_conn,
           date('Y-m-d', strtotime($wk_from) - 7 * 7 * 86400));
    $weekly_raw = [];
    $rs = mysqli_query($db->db_conn,
        "SELECT model, YEARWEEK(date,1) as wk, MIN(date) as wk_start, COUNT(*) as cnt
         FROM $table WHERE status=1 AND date BETWEEN '$ewf' AND '$wt' AND $in_cond
         GROUP BY model, wk ORDER BY model, wk");
    while ($row = mysqli_fetch_assoc($rs)) {
        $weekly_raw[$row['model']][] = [
            'label' => date('n/j', strtotime($row['wk_start'])).'주',
            'y'     => (int)$row['cnt'],
        ];
    }

    // 이번주·전주 일별 비교
    $day_label   = ['월','화','수','목','금','토','일'];
    $wk_day_raw  = [];
    $rs = mysqli_query($db->db_conn,
        "SELECT model, date, COUNT(*) as cnt FROM $table
         WHERE status=1 AND date BETWEEN '$pwf' AND '$wt' AND $in_cond
         GROUP BY model, date ORDER BY model, date");
    while ($row = mysqli_fetch_assoc($rs)) {
        $wk_day_raw[$row['model']][$row['date']] = (int)$row['cnt'];
    }
    $wk_compare = [];
    foreach ($report_models as $m) {
        $tw = $pw = [];
        for ($i = 0; $i < 7; $i++) {
            $td = date('Y-m-d', strtotime($wk_from)      + $i * 86400);
            $pd = date('Y-m-d', strtotime($prev_wk_from) + $i * 86400);
            $lbl = $day_label[$i];
            $tw[] = ['label' => $lbl, 'y' => isset($wk_day_raw[$m][$td]) ? $wk_day_raw[$m][$td] : 0];
            $pw[] = ['label' => $lbl, 'y' => isset($wk_day_raw[$m][$pd]) ? $wk_day_raw[$m][$pd] : 0];
        }
        $wk_compare[$m] = ['this' => $tw, 'prev' => $pw];
    }

    // 이번달 일별 추이 (이번 주가 속한 달 기준)
    $cur_mo_from = date('Y-m-01', strtotime($wk_to));
    $cur_mo_to   = $wk_to;
    $cmf = mysqli_real_escape_string($db->db_conn, $cur_mo_from);
    $cmt = mysqli_real_escape_string($db->db_conn, $cur_mo_to);
    $mo_day_raw  = [];
    $rs = mysqli_query($db->db_conn,
        "SELECT model, date, COUNT(*) as cnt FROM $table
         WHERE status=1 AND date BETWEEN '$cmf' AND '$cmt' AND $in_cond
         GROUP BY model, date ORDER BY model, date");
    while ($row = mysqli_fetch_assoc($rs)) {
        $mo_day_raw[$row['model']][$row['date']] = (int)$row['cnt'];
    }
    $mo_daily = [];
    foreach ($report_models as $m) {
        $arr = [];
        for ($ts = strtotime($cur_mo_from), $te = strtotime($cur_mo_to); $ts <= $te; $ts += 86400) {
            $d = date('Y-m-d', $ts);
            $arr[] = ['label' => date('j', $ts).'일', 'y' => isset($mo_day_raw[$m][$d]) ? $mo_day_raw[$m][$d] : 0];
        }
        $mo_daily[$m] = $arr;
    }

    // 이번주 구매처별
    $wk_mall_raw = [];
    $rs = mysqli_query($db->db_conn,
        "SELECT model, mall, COUNT(*) as cnt FROM $table
         WHERE status=1 AND date BETWEEN '$wf' AND '$wt' AND $in_cond AND mall!=''
         GROUP BY model, mall ORDER BY model, cnt DESC");
    while ($row = mysqli_fetch_assoc($rs)) {
        $wk_mall_raw[$row['model']][] = ['label' => $row['mall'], 'y' => (int)$row['cnt']];
    }
}

// ══════════════════════════════════════════════════════
//  월별 뷰 전용 쿼리
// ══════════════════════════════════════════════════════
if ($view === 'monthly') {

    // 최근 12개월 월별 추이
    $yr12_from = mysqli_real_escape_string($db->db_conn,
        date('Y-m-01', strtotime($mo_from.' -11 month')));
    $monthly_raw = [];
    $rs = mysqli_query($db->db_conn,
        "SELECT model, DATE_FORMAT(date,'%Y-%m') as ym, COUNT(*) as cnt
         FROM $table
         WHERE status=1 AND date BETWEEN '$yr12_from' AND '$mt' AND $in_cond
         GROUP BY model, ym ORDER BY model, ym");
    while ($row = mysqli_fetch_assoc($rs)) {
        $monthly_raw[$row['model']][] = ['label' => $row['ym'], 'y' => (int)$row['cnt']];
    }

    // 이번달·전월 주차별 비교 (CEIL(DAY/7) 방식)
    $wn_raw = [];
    $rs = mysqli_query($db->db_conn,
        "SELECT model,
                IF(date BETWEEN '$mf' AND '$mt','this','prev') AS period,
                CEIL(DAY(date)/7) AS wn,
                COUNT(*) AS cnt
         FROM $table
         WHERE status=1
           AND (date BETWEEN '$mf' AND '$mt' OR date BETWEEN '$pmf' AND '$pmt')
           AND $in_cond
         GROUP BY model, period, wn ORDER BY model, period, wn");
    while ($row = mysqli_fetch_assoc($rs)) {
        $wn_raw[$row['model']][$row['period']][(int)$row['wn']] = (int)$row['cnt'];
    }
    $mo_wk_compare = [];
    foreach ($report_models as $m) {
        $tw = $pw = [];
        for ($wn = 1; $wn <= 5; $wn++) {
            $lbl = $wn.'주차';
            $tw[] = ['label' => $lbl, 'y' => isset($wn_raw[$m]['this'][$wn]) ? $wn_raw[$m]['this'][$wn] : 0];
            $pw[] = ['label' => $lbl, 'y' => isset($wn_raw[$m]['prev'][$wn]) ? $wn_raw[$m]['prev'][$wn] : 0];
        }
        $mo_wk_compare[$m] = ['this' => $tw, 'prev' => $pw];
    }

    // 이번달 일별 추이
    $mo_day_raw = [];
    $rs = mysqli_query($db->db_conn,
        "SELECT model, date, COUNT(*) as cnt FROM $table
         WHERE status=1 AND date BETWEEN '$mf' AND '$mt' AND $in_cond
         GROUP BY model, date ORDER BY model, date");
    while ($row = mysqli_fetch_assoc($rs)) {
        $mo_day_raw[$row['model']][$row['date']] = (int)$row['cnt'];
    }
    $mo_daily = [];
    foreach ($report_models as $m) {
        $arr = [];
        for ($ts = strtotime($mo_from), $te = strtotime($mo_to); $ts <= $te; $ts += 86400) {
            $d = date('Y-m-d', $ts);
            $arr[] = ['label' => date('j', $ts).'일', 'y' => isset($mo_day_raw[$m][$d]) ? $mo_day_raw[$m][$d] : 0];
        }
        $mo_daily[$m] = $arr;
    }

    // 이번달 구매처별
    $mo_mall_raw = [];
    $rs = mysqli_query($db->db_conn,
        "SELECT model, mall, COUNT(*) as cnt FROM $table
         WHERE status=1 AND date BETWEEN '$mf' AND '$mt' AND $in_cond AND mall!=''
         GROUP BY model, mall ORDER BY model, cnt DESC");
    while ($row = mysqli_fetch_assoc($rs)) {
        $mo_mall_raw[$row['model']][] = ['label' => $row['mall'], 'y' => (int)$row['cnt']];
    }
}
?>

<style>
.report-wrap{font-family:'맑은 고딕','Apple SD Gothic Neo',sans-serif;}
.report-header{background:#1a3a6e;color:#fff;padding:18px 24px;border-radius:4px;margin-bottom:16px;}
.report-header h3{margin:0 0 4px;font-size:19px;font-weight:700;}
.report-header .meta{font-size:12px;opacity:.75;}

/* 탭 */
.report-tabs{display:flex;gap:0;margin-bottom:20px;border-bottom:2px solid #1a3a6e;}
.report-tabs a{padding:9px 28px;font-size:14px;font-weight:700;color:#666;
               text-decoration:none;border:1px solid #ddd;border-bottom:none;
               background:#f8f9fb;border-radius:4px 4px 0 0;margin-right:4px;}
.report-tabs a.active{background:#1a3a6e;color:#fff;border-color:#1a3a6e;}

/* 네비게이션 */
.period-nav{display:flex;align-items:center;gap:8px;margin-bottom:20px;}
.period-nav .period-label{font-size:14px;font-weight:700;color:#333;padding:0 12px;}

/* 모델 섹션 */
.model-section{border:2px solid #1a3a6e;border-radius:6px;margin-bottom:28px;overflow:hidden;}
.model-section-head{background:#1a3a6e;color:#fff;padding:11px 20px;font-size:15px;font-weight:700;}
.model-section-body{padding:18px 20px;}

/* KPI */
.kpi-row{display:flex;gap:10px;margin-bottom:18px;flex-wrap:wrap;}
.kpi{background:#f8f9fb;border:1px solid #dde3ee;border-radius:4px;padding:13px 16px;flex:1;min-width:110px;}
.kpi .kpi-lbl{font-size:11px;color:#888;margin-bottom:3px;}
.kpi .kpi-num{font-size:26px;font-weight:700;line-height:1;color:#1a3a6e;}
.kpi .kpi-sub{font-size:10px;color:#aaa;margin-top:3px;}
.kpi.hl{background:#1a3a6e;border-color:#1a3a6e;}
.kpi.hl .kpi-lbl{color:rgba(255,255,255,.65);}
.kpi.hl .kpi-num,.kpi.hl .kpi-sub{color:#fff;}
.kpi.hl .kpi-sub{opacity:.6;}
.kpi.up   .kpi-num{color:#27ae60;}
.kpi.down .kpi-num{color:#e74c3c;}
.kpi.flat .kpi-num{color:#888;}

/* 차트 */
.chart-row{display:flex;gap:14px;margin-bottom:14px;}
.chart-card{background:#fff;border:1px solid #dde3ee;border-radius:4px;padding:13px 14px;flex:1;}
.chart-card h6{font-size:12px;font-weight:700;color:#555;margin:0 0 8px;
               padding-bottom:7px;border-bottom:1px solid #eee;}
.no-data{color:#bbb;font-size:12px;text-align:center;padding:30px 0;}

/* 배지 */
.mall-badge{display:inline-block;background:#eef2fb;color:#2c5dbc;border-radius:3px;
            padding:2px 8px;margin:2px 3px;font-size:12px;border:1px solid #d0d9f0;}
.mall-badge strong{color:#1a3a6e;margin-left:3px;}
.mall-badge em{color:#aaa;font-style:normal;}

.print-btn{position:fixed;bottom:28px;right:28px;z-index:999;}
@media print{
    .sidebar,.navbar,.period-nav,.report-tabs,.print-btn{display:none!important;}
    .col-sm-9{width:100%!important;margin-left:0!important;}
    .model-section{page-break-inside:avoid;}
    .report-header,.model-section-head,.kpi.hl{-webkit-print-color-adjust:exact;print-color-adjust:exact;}
}
</style>

<div class="report-wrap">

<button class="btn btn-default btn-sm print-btn" onclick="window.print()">
    <span class="glyphicon glyphicon-print"></span> 인쇄 / PDF
</button>

<!-- 리포트 헤더 -->
<div class="report-header">
    <h3>CXPRO N 출고 리포트</h3>
    <div class="meta">생성일: <?= date('Y-m-d H:i') ?> &nbsp;|&nbsp;
        대상 모델: <?= implode(', ', array_map('htmlspecialchars', $report_models)) ?>
    </div>
</div>

<!-- 탭 -->
<div class="report-tabs">
    <a href="?view=weekly&week_offset=<?= $wk_offset ?>"
       class="<?= $view==='weekly' ? 'active' : '' ?>">주간 리포트</a>
    <a href="?view=monthly&month_offset=<?= $mo_offset ?>"
       class="<?= $view==='monthly' ? 'active' : '' ?>">월별 리포트</a>
</div>


<?php if ($view === 'weekly'): ?>
<!-- ════════════════════════════════════════════════════
     주간 리포트
     ════════════════════════════════════════════════════ -->

<div class="period-nav">
    <a href="?view=weekly&week_offset=<?= $wk_offset - 1 ?>" class="btn btn-default btn-sm">&laquo; 전주</a>
    <span class="period-label"><?= $wk_from ?> (월) ~ <?= $wk_to ?></span>
    <? if ($wk_offset < 0): ?>
    <a href="?view=weekly&week_offset=<?= $wk_offset + 1 ?>" class="btn btn-default btn-sm">다음주 &raquo;</a>
    <? endif; ?>
    <a href="?view=weekly&week_offset=0" class="btn btn-primary btn-sm" style="margin-left:4px;">이번주</a>
</div>

<?php foreach ($report_models as $mi => $model):
    $s  = $summary[$model];
    $wdc = $s['wk'] - $s['pwk'];
    $wdp = $s['pwk'] > 0 ? round($wdc / $s['pwk'] * 100, 1) : 0;
    $mdc = $s['mo'] - $s['pmo'];
    $mdp = $s['pmo'] > 0 ? round($mdc / $s['pmo'] * 100, 1) : 0;
    $wc  = $wdc > 0 ? 'up' : ($wdc < 0 ? 'down' : 'flat');
    $mc  = $mdc > 0 ? 'up' : ($mdc < 0 ? 'down' : 'flat');

    $pfx      = 'w'.$mi;
    $compare  = $wk_compare[$model];
    $w_data   = isset($weekly_raw[$model]) ? $weekly_raw[$model] : [];
    $mo_d     = $mo_daily[$model];
    $mall_d   = isset($wk_mall_raw[$model]) ? $wk_mall_raw[$model] : [];
    $cur_mo_label = date('Y년 n월', strtotime($wk_to));
?>
<div class="model-section">
    <div class="model-section-head"><?= htmlspecialchars($model) ?></div>
    <div class="model-section-body">

        <div class="kpi-row">
            <div class="kpi hl">
                <div class="kpi-lbl">이번 주 출고</div>
                <div class="kpi-num"><?= number_format($s['wk']) ?></div>
                <div class="kpi-sub"><?= $wk_from ?> ~ <?= $wk_to ?></div>
            </div>
            <div class="kpi">
                <div class="kpi-lbl">전주 출고</div>
                <div class="kpi-num"><?= number_format($s['pwk']) ?></div>
                <div class="kpi-sub"><?= $prev_wk_from ?> ~ <?= $prev_wk_to ?></div>
            </div>
            <div class="kpi <?= $wc ?>">
                <div class="kpi-lbl">전주 대비</div>
                <div class="kpi-num"><?= ($wdc>=0?'+':'').$wdp ?>%</div>
                <div class="kpi-sub"><?= ($wdc>=0?'+':'').number_format($wdc) ?>건</div>
            </div>
            <div class="kpi hl">
                <div class="kpi-lbl">이번달 누적</div>
                <div class="kpi-num"><?= number_format($s['mo']) ?></div>
                <div class="kpi-sub"><?= $mo_from ?> ~ <?= $mo_to ?></div>
            </div>
            <div class="kpi">
                <div class="kpi-lbl">전월 실적</div>
                <div class="kpi-num"><?= number_format($s['pmo']) ?></div>
                <div class="kpi-sub"><?= $prev_mo_from ?> ~ <?= $prev_mo_to ?></div>
            </div>
            <div class="kpi <?= $mc ?>">
                <div class="kpi-lbl">전월 대비</div>
                <div class="kpi-num"><?= ($mdc>=0?'+':'').$mdp ?>%</div>
                <div class="kpi-sub"><?= ($mdc>=0?'+':'').number_format($mdc) ?>건</div>
            </div>
        </div>

        <div class="chart-row">
            <div class="chart-card" style="flex:1.4;">
                <h6>이번 주 vs 전주 — 요일별 비교</h6>
                <div id="<?= $pfx ?>_cmp" style="height:220px;"></div>
            </div>
            <div class="chart-card" style="flex:1;">
                <h6>최근 8주 주간 추이</h6>
                <div id="<?= $pfx ?>_wk" style="height:220px;"></div>
            </div>
        </div>

        <div class="chart-row">
            <div class="chart-card" style="flex:1.4;">
                <h6>이번달 일별 추이 (<?= $cur_mo_label ?>)</h6>
                <div id="<?= $pfx ?>_mod" style="height:200px;"></div>
            </div>
            <div class="chart-card" style="flex:1;">
                <h6>이번 주 구매처별 분포</h6>
                <? if (empty($mall_d)): ?>
                <p class="no-data">이번 주 데이터 없음</p>
                <? else: ?>
                <div id="<?= $pfx ?>_mall" style="height:200px;"></div>
                <? endif; ?>
            </div>
        </div>

        <? if (!empty($mall_d)):
            $mt_sum = array_sum(array_column($mall_d, 'y')); ?>
        <div style="font-size:12px;margin-top:4px;">
            <strong style="color:#555;">구매처별 (이번주):</strong>
            <? foreach ($mall_d as $md):
                $p = $mt_sum > 0 ? round($md['y']/$mt_sum*100,0) : 0; ?>
            <span class="mall-badge"><?= htmlspecialchars($md['label']) ?>
                <strong><?= $md['y'] ?>건</strong><em>(<?= $p ?>%)</em></span>
            <? endforeach; ?>
        </div>
        <? endif; ?>

    </div>
</div>

<script>
(function(){
    var cmp  = <?= json_encode($compare,  JSON_UNESCAPED_UNICODE|JSON_NUMERIC_CHECK) ?>;
    var wkly = <?= json_encode($w_data,   JSON_UNESCAPED_UNICODE|JSON_NUMERIC_CHECK) ?>;
    var modd = <?= json_encode($mo_d,     JSON_UNESCAPED_UNICODE|JSON_NUMERIC_CHECK) ?>;
    var mall = <?= json_encode($mall_d,   JSON_UNESCAPED_UNICODE|JSON_NUMERIC_CHECK) ?>;
    window.addEventListener('load', function(){
        new CanvasJS.Chart("<?= $pfx ?>_cmp", {
            animationEnabled:true, toolTip:{shared:true},
            legend:{verticalAlign:"top",fontSize:12},
            axisY:{title:"건수",gridThickness:1,minimum:0},
            data:[
                {type:"column",name:"이번주",showInLegend:true,color:"#1a3a6e",
                 indexLabel:"{y}",indexLabelFontSize:10,indexLabelPlacement:"outside",dataPoints:cmp.this},
                {type:"column",name:"전주",showInLegend:true,color:"#a8b8d8",
                 indexLabel:"{y}",indexLabelFontSize:10,indexLabelPlacement:"outside",dataPoints:cmp.prev}
            ]
        }).render();

        if (wkly.length > 0) {
            new CanvasJS.Chart("<?= $pfx ?>_wk", {
                animationEnabled:true,
                axisX:{labelAngle:-30,labelFontSize:11},
                axisY:{title:"건수",gridThickness:1,minimum:0},
                data:[{type:"column",color:"#3d6ac5",
                       indexLabel:"{y}",indexLabelFontSize:10,indexLabelPlacement:"outside",
                       dataPoints:wkly}]
            }).render();
        } else {
            document.getElementById("<?= $pfx ?>_wk").innerHTML = '<p class="no-data">데이터 없음</p>';
        }

        new CanvasJS.Chart("<?= $pfx ?>_mod", {
            animationEnabled:true,
            axisX:{labelFontSize:10,interval:Math.max(1,Math.floor(modd.length/8))},
            axisY:{gridThickness:1,minimum:0},
            data:[{type:"area",color:"#3d6ac5",fillOpacity:.12,markerSize:4,dataPoints:modd}]
        }).render();

        if (mall.length > 0) {
            new CanvasJS.Chart("<?= $pfx ?>_mall", {
                animationEnabled:true,
                data:[{type:"doughnut",indexLabel:"{label}\n{y}건",indexLabelFontSize:11,dataPoints:mall}]
            }).render();
        }
    });
})();
</script>
<?php endforeach; ?>


<?php else: ?>
<!-- ════════════════════════════════════════════════════
     월별 리포트
     ════════════════════════════════════════════════════ -->

<div class="period-nav">
    <a href="?view=monthly&month_offset=<?= $mo_offset - 1 ?>" class="btn btn-default btn-sm">&laquo; 전월</a>
    <span class="period-label"><?= $mo_label ?> &nbsp;(<?= $mo_from ?> ~ <?= $mo_to ?>)</span>
    <? if ($mo_offset < 0): ?>
    <a href="?view=monthly&month_offset=<?= $mo_offset + 1 ?>" class="btn btn-default btn-sm">다음달 &raquo;</a>
    <? endif; ?>
    <a href="?view=monthly&month_offset=0" class="btn btn-primary btn-sm" style="margin-left:4px;">이번달</a>
</div>

<?php foreach ($report_models as $mi => $model):
    $s   = $summary[$model];
    $mdc = $s['mo'] - $s['pmo'];
    $mdp = $s['pmo']  > 0 ? round($mdc / $s['pmo']  * 100, 1) : 0;
    $ldc = $s['mo'] - $s['lymo'];
    $ldp = $s['lymo'] > 0 ? round($ldc / $s['lymo'] * 100, 1) : 0;
    $mc  = $mdc > 0 ? 'up' : ($mdc < 0 ? 'down' : 'flat');
    $lc  = $ldc > 0 ? 'up' : ($ldc < 0 ? 'down' : 'flat');

    $pfx     = 'mo'.$mi;
    $m12_d   = isset($monthly_raw[$model])    ? $monthly_raw[$model]    : [];
    $wc_d    = $mo_wk_compare[$model];
    $mo_d    = $mo_daily[$model];
    $mall_d  = isset($mo_mall_raw[$model])    ? $mo_mall_raw[$model]    : [];
?>
<div class="model-section">
    <div class="model-section-head"><?= htmlspecialchars($model) ?></div>
    <div class="model-section-body">

        <div class="kpi-row">
            <div class="kpi hl">
                <div class="kpi-lbl">이번달 출고</div>
                <div class="kpi-num"><?= number_format($s['mo']) ?></div>
                <div class="kpi-sub"><?= $mo_from ?> ~ <?= $mo_to ?></div>
            </div>
            <div class="kpi">
                <div class="kpi-lbl">전월 실적</div>
                <div class="kpi-num"><?= number_format($s['pmo']) ?></div>
                <div class="kpi-sub"><?= $prev_mo_from ?> ~ <?= $prev_mo_to ?></div>
            </div>
            <div class="kpi <?= $mc ?>">
                <div class="kpi-lbl">전월 대비</div>
                <div class="kpi-num"><?= ($mdc>=0?'+':'').$mdp ?>%</div>
                <div class="kpi-sub"><?= ($mdc>=0?'+':'').number_format($mdc) ?>건</div>
            </div>
            <div class="kpi">
                <div class="kpi-lbl">전년 동월</div>
                <div class="kpi-num"><?= number_format($s['lymo']) ?></div>
                <div class="kpi-sub"><?= $ly_mo_from ?> ~ <?= $ly_mo_to ?></div>
            </div>
            <div class="kpi <?= $lc ?>">
                <div class="kpi-lbl">전년 동월 대비</div>
                <div class="kpi-num"><?= ($ldc>=0?'+':'').$ldp ?>%</div>
                <div class="kpi-sub"><?= ($ldc>=0?'+':'').number_format($ldc) ?>건</div>
            </div>
            <div class="kpi hl">
                <div class="kpi-lbl">일 평균 출고</div>
                <?php
                    $mo_days = max(1, (int)((strtotime($mo_to)-strtotime($mo_from))/86400)+1);
                    $avg = $mo_days > 0 ? round($s['mo']/$mo_days,1) : 0;
                ?>
                <div class="kpi-num"><?= $avg ?></div>
                <div class="kpi-sub">건/일 (<?= $mo_days ?>일 기준)</div>
            </div>
        </div>

        <!-- 주차별 비교 + 최근 12개월 -->
        <div class="chart-row">
            <div class="chart-card" style="flex:1;">
                <h6>이번달 vs 전월 — 주차별 비교</h6>
                <div id="<?= $pfx ?>_wkc" style="height:220px;"></div>
            </div>
            <div class="chart-card" style="flex:1.4;">
                <h6>최근 12개월 월별 추이</h6>
                <div id="<?= $pfx ?>_m12" style="height:220px;"></div>
            </div>
        </div>

        <!-- 이번달 일별 + 구매처별 -->
        <div class="chart-row">
            <div class="chart-card" style="flex:1.4;">
                <h6>이번달 일별 출고 추이 (<?= $mo_label ?>)</h6>
                <div id="<?= $pfx ?>_day" style="height:200px;"></div>
            </div>
            <div class="chart-card" style="flex:1;">
                <h6>이번달 구매처별 분포</h6>
                <? if (empty($mall_d)): ?>
                <p class="no-data">이번달 데이터 없음</p>
                <? else: ?>
                <div id="<?= $pfx ?>_mall" style="height:200px;"></div>
                <? endif; ?>
            </div>
        </div>

        <? if (!empty($mall_d)):
            $mt_sum = array_sum(array_column($mall_d, 'y')); ?>
        <div style="font-size:12px;margin-top:4px;">
            <strong style="color:#555;">구매처별 (이번달):</strong>
            <? foreach ($mall_d as $md):
                $p = $mt_sum > 0 ? round($md['y']/$mt_sum*100,0) : 0; ?>
            <span class="mall-badge"><?= htmlspecialchars($md['label']) ?>
                <strong><?= $md['y'] ?>건</strong><em>(<?= $p ?>%)</em></span>
            <? endforeach; ?>
        </div>
        <? endif; ?>

    </div>
</div>

<script>
(function(){
    var wkc  = <?= json_encode($wc_d,   JSON_UNESCAPED_UNICODE|JSON_NUMERIC_CHECK) ?>;
    var m12  = <?= json_encode($m12_d,  JSON_UNESCAPED_UNICODE|JSON_NUMERIC_CHECK) ?>;
    var dayd = <?= json_encode($mo_d,   JSON_UNESCAPED_UNICODE|JSON_NUMERIC_CHECK) ?>;
    var mall = <?= json_encode($mall_d, JSON_UNESCAPED_UNICODE|JSON_NUMERIC_CHECK) ?>;
    window.addEventListener('load', function(){

        // 주차별 비교
        new CanvasJS.Chart("<?= $pfx ?>_wkc", {
            animationEnabled:true, toolTip:{shared:true},
            legend:{verticalAlign:"top",fontSize:12},
            axisY:{title:"건수",gridThickness:1,minimum:0},
            data:[
                {type:"column",name:"이번달",showInLegend:true,color:"#1a3a6e",
                 indexLabel:"{y}",indexLabelFontSize:10,indexLabelPlacement:"outside",dataPoints:wkc.this},
                {type:"column",name:"전월",showInLegend:true,color:"#a8b8d8",
                 indexLabel:"{y}",indexLabelFontSize:10,indexLabelPlacement:"outside",dataPoints:wkc.prev}
            ]
        }).render();

        // 최근 12개월
        if (m12.length > 0) {
            new CanvasJS.Chart("<?= $pfx ?>_m12", {
                animationEnabled:true,
                axisX:{labelAngle:-30,labelFontSize:11},
                axisY:{title:"건수",gridThickness:1,minimum:0},
                data:[{type:"column",color:"#3d6ac5",
                       indexLabel:"{y}",indexLabelFontSize:10,indexLabelPlacement:"outside",
                       dataPoints:m12}]
            }).render();
        } else {
            document.getElementById("<?= $pfx ?>_m12").innerHTML = '<p class="no-data">데이터 없음</p>';
        }

        // 일별
        new CanvasJS.Chart("<?= $pfx ?>_day", {
            animationEnabled:true,
            axisX:{labelFontSize:10,interval:Math.max(1,Math.floor(dayd.length/8))},
            axisY:{gridThickness:1,minimum:0},
            data:[{type:"area",color:"#3d6ac5",fillOpacity:.12,markerSize:4,dataPoints:dayd}]
        }).render();

        // 구매처
        if (mall.length > 0) {
            new CanvasJS.Chart("<?= $pfx ?>_mall", {
                animationEnabled:true,
                data:[{type:"doughnut",indexLabel:"{label}\n{y}건",indexLabelFontSize:11,dataPoints:mall}]
            }).render();
        }
    });
})();
</script>
<?php endforeach; ?>

<?php endif; ?>

</div><!-- /report-wrap -->
<? include('../footer.php'); ?>
