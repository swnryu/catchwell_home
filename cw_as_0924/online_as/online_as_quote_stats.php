<?
include("../def_inc.php");
$mod  = M_STATS;
$menu = S_STATS_QUOTE;
include("../header.php");

$FEATURE_START = "2026-05-22"; // as_process_history 기록 시작일 (안내 문구용)

// ── 원본 데이터 조회 (최근 400일, 월별 12개월까지 커버) ──────────
$sql = "SELECT a.idx, a.reg_num, a.price, a.customer_name, a.pic_name,
               q.issued_at, p.paid_at, v.P_AMT as paid_amt_actual
        FROM as_parcel_service a
        JOIN (
            SELECT as_idx, MIN(changed_at) as issued_at
            FROM as_process_history
            WHERE new_state = 2
            GROUP BY as_idx
        ) q ON q.as_idx = a.idx
        LEFT JOIN (
            SELECT as_idx, MIN(changed_at) as paid_at
            FROM as_process_history
            WHERE new_state = 9
            GROUP BY as_idx
        ) p ON p.as_idx = a.idx
        LEFT JOIN (
            SELECT P_OID, MAX(CAST(P_AMT AS UNSIGNED)) as P_AMT
            FROM TB_INICIS_RETURN
            GROUP BY P_OID
        ) v ON v.P_OID = CONCAT(a.reg_num, '_R')
        WHERE a.process_state NOT IN (5, 99)
          AND q.issued_at >= DATE_SUB(NOW(), INTERVAL 400 DAY)
        ORDER BY q.issued_at DESC";

$rs = mysqli_query($db->db_conn, $sql);
$quotes = array();
while ($row = mysqli_fetch_assoc($rs)) {
    $paid = !empty($row['paid_at']);
    $paid_amt = 0;
    if ($paid) {
        $actual = (int)$row['paid_amt_actual'];
        $paid_amt = $actual > 0 ? $actual : (int)$row['price'];
    }
    $quotes[] = array(
        'idx'           => (int)$row['idx'],
        'reg_num'       => $row['reg_num'],
        'price'         => (int)$row['price'],
        'issued_at'     => $row['issued_at'],
        'customer_name' => $row['customer_name'],
        'pic_name'      => $row['pic_name'],
        'paid'          => $paid,
        'paid_at'       => $row['paid_at'],
        'paid_amt'      => $paid_amt,
    );
}

// ── 기간 버킷 유틸 ──────────────────────────────────────────────
function qs_week_start($date_str) {
    $d = new DateTime(date('Y-m-d', strtotime($date_str)));
    $dow = (int)$d->format('N'); // 1=월 ... 7=일
    $d->modify('-'.($dow - 1).' days');
    return $d->format('Y-m-d');
}

function qs_build_buckets($quotes, $bucket_fn, $ordered_keys) {
    $buckets = array();
    foreach ($ordered_keys as $key) {
        $buckets[$key] = array('cnt' => 0, 'amt' => 0, 'paid_cnt' => 0, 'paid_amt' => 0);
    }
    foreach ($quotes as $q) {
        $key = $bucket_fn($q['issued_at']);
        if (!isset($buckets[$key])) continue;
        $buckets[$key]['cnt']++;
        $buckets[$key]['amt'] += $q['price'];
        if ($q['paid']) {
            $buckets[$key]['paid_cnt']++;
            $buckets[$key]['paid_amt'] += $q['paid_amt'];
        }
    }
    return $buckets;
}

function qs_filter_since($quotes, $since_date) {
    $out = array();
    $since_ts = strtotime($since_date);
    foreach ($quotes as $q) {
        if (strtotime($q['issued_at']) >= $since_ts) $out[] = $q;
    }
    return $out;
}

// ── 일별 (최근 30일) ────────────────────────────────────────────
$daily_keys = array();
for ($i = 29; $i >= 0; $i--) $daily_keys[] = date('Y-m-d', strtotime("-{$i} days"));
$daily_buckets = qs_build_buckets($quotes, function($ts){ return date('Y-m-d', strtotime($ts)); }, $daily_keys);

$daily_amt = $daily_paid_amt = $daily_cnt = array();
foreach ($daily_keys as $k) {
    $label = date('n/j', strtotime($k));
    $daily_amt[]      = array('label' => $label, 'y' => $daily_buckets[$k]['amt']);
    $daily_paid_amt[] = array('label' => $label, 'y' => $daily_buckets[$k]['paid_amt']);
    $daily_cnt[]      = array('label' => $label, 'y' => $daily_buckets[$k]['cnt']);
}
$daily_detail = qs_filter_since($quotes, $daily_keys[0]);

// ── 주별 (최근 12주) ────────────────────────────────────────────
$cur_week_start = qs_week_start(date('Y-m-d'));
$weekly_keys = array();
for ($i = 11; $i >= 0; $i--) $weekly_keys[] = date('Y-m-d', strtotime($cur_week_start." -{$i} weeks"));
$weekly_buckets = qs_build_buckets($quotes, function($ts){ return qs_week_start($ts); }, $weekly_keys);

$weekly_amt = $weekly_paid_amt = $weekly_cnt = array();
foreach ($weekly_keys as $k) {
    $label = date('n/j', strtotime($k)).'주';
    $weekly_amt[]      = array('label' => $label, 'y' => $weekly_buckets[$k]['amt']);
    $weekly_paid_amt[] = array('label' => $label, 'y' => $weekly_buckets[$k]['paid_amt']);
    $weekly_cnt[]      = array('label' => $label, 'y' => $weekly_buckets[$k]['cnt']);
}
$weekly_detail = qs_filter_since($quotes, $weekly_keys[0]);

// ── 월별 (최근 12개월) ──────────────────────────────────────────
$monthly_keys = array();
for ($i = 11; $i >= 0; $i--) $monthly_keys[] = date('Y-m', strtotime("-{$i} months"));
$monthly_buckets = qs_build_buckets($quotes, function($ts){ return date('Y-m', strtotime($ts)); }, $monthly_keys);

$monthly_amt = $monthly_paid_amt = $monthly_cnt = array();
foreach ($monthly_keys as $k) {
    $label = date('n월', strtotime($k.'-01'));
    $monthly_amt[]      = array('label' => $label, 'y' => $monthly_buckets[$k]['amt']);
    $monthly_paid_amt[] = array('label' => $label, 'y' => $monthly_buckets[$k]['paid_amt']);
    $monthly_cnt[]      = array('label' => $label, 'y' => $monthly_buckets[$k]['cnt']);
}
$monthly_detail = qs_filter_since($quotes, $monthly_keys[0].'-01');

// ── 이번 달 요약 카드 ───────────────────────────────────────────
$this_month = date('Y-m');
$m = isset($monthly_buckets[$this_month]) ? $monthly_buckets[$this_month] : array('cnt' => 0, 'amt' => 0, 'paid_cnt' => 0, 'paid_amt' => 0);
$m_unpaid_cnt = $m['cnt'] - $m['paid_cnt'];
$m_unpaid_amt = $m['amt'] - $m['paid_amt'];
$m_rate = $m['cnt'] > 0 ? round($m['paid_cnt'] / $m['cnt'] * 100, 1) : 0;
?>
<style>
.stat-card{background:#fff;border:1px solid #ddd;border-radius:4px;padding:20px 24px;margin-bottom:20px;}
.stat-card .num{font-size:32px;font-weight:700;line-height:1.1;margin:6px 0 2px;}
.stat-card .label{font-size:13px;color:#888;}
.stat-card .sub{font-size:12px;color:#aaa;margin-top:4px;}
.stat-card.blue  .num{color:#337ab7;}
.stat-card.green .num{color:#5cb85c;}
.stat-card.red   .num{color:#d9534f;}
.stat-card.purple .num{color:#9b59b6;}
.chart-box{background:#fff;border:1px solid #ddd;border-radius:4px;padding:20px;margin-bottom:20px;}
.chart-box h5{font-size:14px;font-weight:700;color:#555;margin:0 0 16px;padding-bottom:10px;border-bottom:1px solid #eee;}
.qs-tab-btns{margin-bottom:16px;}
.qs-tab-panel{display:none;}
.qs-tab-panel.active{display:block;}
.qs-table{font-size:12px;}
.qs-table td, .qs-table th{vertical-align:middle;}
</style>

<h4 class="page-header">AS 견적/입금 통계</h4>
<p class="text-muted" style="font-size:13px;margin-top:-10px;">
    ※ 이 통계는 <?=$FEATURE_START?> 이후 발행된 견적(견적서발행 처리 이력)부터 집계됩니다.
</p>

<!-- 이번 달 요약 카드 -->
<div class="row">
    <div class="col-sm-2">
        <div class="stat-card blue">
            <div class="label">이번 달 견적발행</div>
            <div class="num"><?=$m['cnt']?></div>
            <div class="sub"><?=number_format($m['amt'])?>원</div>
        </div>
    </div>
    <div class="col-sm-2">
        <div class="stat-card green">
            <div class="label">이번 달 입금완료</div>
            <div class="num"><?=$m['paid_cnt']?></div>
            <div class="sub"><?=number_format($m['paid_amt'])?>원</div>
        </div>
    </div>
    <div class="col-sm-2">
        <div class="stat-card red">
            <div class="label">이번 달 미입금</div>
            <div class="num"><?=$m_unpaid_cnt?></div>
            <div class="sub"><?=number_format($m_unpaid_amt)?>원</div>
        </div>
    </div>
    <div class="col-sm-2">
        <div class="stat-card purple">
            <div class="label">입금율</div>
            <div class="num"><?=$m_rate?>%</div>
            <div class="sub">입금완료 / 견적발행</div>
        </div>
    </div>
</div>

<!-- 기간 탭 -->
<div class="qs-tab-btns">
    <button type="button" class="btn btn-sm btn-primary" id="qs-btn-daily" onclick="qsShowTab('daily')">일별</button>
    <button type="button" class="btn btn-sm btn-default" id="qs-btn-weekly" onclick="qsShowTab('weekly')">주별</button>
    <button type="button" class="btn btn-sm btn-default" id="qs-btn-monthly" onclick="qsShowTab('monthly')">월별</button>
</div>

<?
function qs_render_detail_table($rows) {
    ?>
    <table class="table table-condensed table-bordered table-hover qs-table">
        <thead><tr style="background:#f7f7f7;">
            <th class="text-center" style="width:100px;">접수번호</th>
            <th class="text-center" style="width:130px;">견적일</th>
            <th class="text-center" style="width:90px;">견적금액</th>
            <th class="text-center" style="width:70px;">입금여부</th>
            <th class="text-center" style="width:130px;">입금일</th>
            <th class="text-center" style="width:90px;">입금액</th>
            <th style="width:90px;">고객명</th>
            <th class="text-center" style="width:80px;">담당자</th>
        </tr></thead>
        <tbody>
        <?php if (empty($rows)): ?>
        <tr><td colspan="8" class="text-center text-muted">데이터 없음</td></tr>
        <?php else: foreach ($rows as $r): ?>
        <tr>
            <td class="text-center">
                <a href="online_as_edit.php?idx=<?=$r['idx']?>&from=<?=S_STATS_QUOTE?>"><?=htmlspecialchars($r['reg_num'])?></a>
            </td>
            <td class="text-center"><?=$r['issued_at']?></td>
            <td class="text-center"><?=number_format($r['price'])?>원</td>
            <td class="text-center">
                <?php if ($r['paid']): ?><span style="color:#5cb85c;font-weight:700;">입금</span>
                <?php else: ?><span style="color:#d9534f;">미입금</span><?php endif; ?>
            </td>
            <td class="text-center"><?=$r['paid_at'] ? $r['paid_at'] : '-'?></td>
            <td class="text-center"><?=$r['paid'] ? number_format($r['paid_amt']).'원' : '-'?></td>
            <td><?=htmlspecialchars($r['customer_name'])?></td>
            <td class="text-center"><?=htmlspecialchars($r['pic_name'])?></td>
        </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
    <?
}
?>

<div id="qs-tab-daily" class="qs-tab-panel active">
    <div class="chart-box">
        <h5>일별 견적발행/입금 추이 (최근 30일)</h5>
        <div id="chart_quote_daily" style="height:280px;"></div>
    </div>
    <div class="chart-box">
        <h5>일별 상세 내역</h5>
        <? qs_render_detail_table($daily_detail); ?>
    </div>
</div>

<div id="qs-tab-weekly" class="qs-tab-panel">
    <div class="chart-box">
        <h5>주별 견적발행/입금 추이 (최근 12주)</h5>
        <div id="chart_quote_weekly" style="height:280px;"></div>
    </div>
    <div class="chart-box">
        <h5>주별 상세 내역</h5>
        <? qs_render_detail_table($weekly_detail); ?>
    </div>
</div>

<div id="qs-tab-monthly" class="qs-tab-panel">
    <div class="chart-box">
        <h5>월별 견적발행/입금 추이 (최근 12개월)</h5>
        <div id="chart_quote_monthly" style="height:280px;"></div>
    </div>
    <div class="chart-box">
        <h5>월별 상세 내역</h5>
        <? qs_render_detail_table($monthly_detail); ?>
    </div>
</div>

<script>
var qs_daily_amt      = <?= json_encode($daily_amt,      JSON_NUMERIC_CHECK) ?>;
var qs_daily_paid_amt = <?= json_encode($daily_paid_amt, JSON_NUMERIC_CHECK) ?>;
var qs_daily_cnt      = <?= json_encode($daily_cnt,      JSON_NUMERIC_CHECK) ?>;

var qs_weekly_amt      = <?= json_encode($weekly_amt,      JSON_NUMERIC_CHECK) ?>;
var qs_weekly_paid_amt = <?= json_encode($weekly_paid_amt, JSON_NUMERIC_CHECK) ?>;
var qs_weekly_cnt      = <?= json_encode($weekly_cnt,      JSON_NUMERIC_CHECK) ?>;

var qs_monthly_amt      = <?= json_encode($monthly_amt,      JSON_NUMERIC_CHECK) ?>;
var qs_monthly_paid_amt = <?= json_encode($monthly_paid_amt, JSON_NUMERIC_CHECK) ?>;
var qs_monthly_cnt      = <?= json_encode($monthly_cnt,      JSON_NUMERIC_CHECK) ?>;

var qsChartDaily, qsChartWeekly, qsChartMonthly;

function qs_make_chart(containerId, amtPts, paidAmtPts, cntPts) {
    return new CanvasJS.Chart(containerId, {
        animationEnabled: true,
        legend: { cursor: "pointer", verticalAlign: "top" },
        toolTip: { shared: true },
        axisX: { labelAngle: -30 },
        axisY: { title: "금액(원)", gridThickness: 1, includeZero: true },
        axisY2: { title: "건수", gridThickness: 0, includeZero: true },
        data: [
            { type: "column", name: "견적금액", showInLegend: true, color: "#337ab7", yValueFormatString: "#,##0", dataPoints: amtPts },
            { type: "column", name: "입금액",   showInLegend: true, color: "#5cb85c", yValueFormatString: "#,##0", dataPoints: paidAmtPts },
            { type: "line",   name: "건수",     showInLegend: true, color: "#f0ad4e", axisYType: "secondary", markerSize: 4, dataPoints: cntPts }
        ]
    });
}

function qsShowTab(tab) {
    var tabs = ['daily', 'weekly', 'monthly'];
    for (var i = 0; i < tabs.length; i++) {
        var isActive = (tabs[i] === tab);
        document.getElementById('qs-tab-' + tabs[i]).className = 'qs-tab-panel' + (isActive ? ' active' : '');
        document.getElementById('qs-btn-' + tabs[i]).className = 'btn btn-sm ' + (isActive ? 'btn-primary' : 'btn-default');
    }
    if (tab === 'daily')   qsChartDaily.render();
    if (tab === 'weekly')  qsChartWeekly.render();
    if (tab === 'monthly') qsChartMonthly.render();
}

window.onload = function() {
    qsChartDaily   = qs_make_chart("chart_quote_daily",   qs_daily_amt,   qs_daily_paid_amt,   qs_daily_cnt);
    qsChartWeekly  = qs_make_chart("chart_quote_weekly",  qs_weekly_amt,  qs_weekly_paid_amt,  qs_weekly_cnt);
    qsChartMonthly = qs_make_chart("chart_quote_monthly", qs_monthly_amt, qs_monthly_paid_amt, qs_monthly_cnt);
    qsChartDaily.render();
};
</script>

<? include('../footer.php'); ?>
