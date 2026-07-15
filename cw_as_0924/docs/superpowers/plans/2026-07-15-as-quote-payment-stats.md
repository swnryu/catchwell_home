# AS 견적/입금 통계 메뉴 Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** 통계 메뉴에 "AS 견적/입금 통계" 페이지를 추가해, 견적발행 건수·금액과 수리비 입금 여부·입금액을 일/주/월별로 볼 수 있게 한다.

**Architecture:** 신규 컬럼이나 기존 로직 수정 없이, 이미 기록되고 있는 `as_process_history`(`new_state=2`=견적완료, `new_state=9`=수리비입금완료)를 조회 전용으로 조인해 집계하는 순수 신규 페이지 1개 + 메뉴 등록. 기존 `online_as_stats.php`와 동일한 Bootstrap+CanvasJS 서버사이드 렌더링 패턴을 그대로 따른다.

**Tech Stack:** PHP 5.6 (procedural, mysqli), MySQL/MariaDB, CanvasJS (전역 스크립트, header.php에서 이미 로드됨), Bootstrap 3.

## Global Constraints

- PHP 5.6 문법만 사용한다: `??`, 화살표 함수(`fn()`), typed properties 등 7.0+ 문법 금지. 삼항 연산은 `isset($x) ? $x : $default` 형태로 작성한다.
- 신규 컬럼 추가, `ALTER TABLE`, `online_as_edit_ok.php` 등 기존 접수/견적/입금 처리 로직 수정은 **하지 않는다** (설계 문서 `docs/superpowers/specs/2026-07-15-as-quote-payment-stats-design.md` §2 확정 사항).
- `TB_INICIS_RETURN`은 동일 `P_OID`에 중복 행이 실제로 존재하므로, 조인 시 반드시 `P_OID`로 `GROUP BY`한 서브쿼리를 거친다 (설계 문서 §4.4, 운영 DB에서 실측 확인됨).
- 취소 건(`process_state` IN (5, 99))은 전체 집계에서 제외한다.
- 이 통계는 `as_process_history` 기록이 시작된 2026-05-22 이후 발행된 견적부터 정확하다 — 페이지에 안내 문구 표시.
- 로컬 검증은 이미 떠 있는 Docker 컨테이너(`cw_php`, `cw_as_0924` 볼륨 마운트, 운영 DB 211.54.90.200:3307에 직접 연결)로 한다. 배포는 사용자가 명시적으로 요청할 때만 `deploy.ps1`로 진행한다 (이번 계획에는 배포 단계를 포함하지 않는다).

---

### Task 1: 통계 메뉴 등록 (`S_STATS_QUOTE`)

**Files:**
- Modify: `def_inc.php:43-46` (메뉴 상수 블록)
- Modify: `header.php:369-381` (통계 사이드바)

**Interfaces:**
- Produces: 메뉴 상수 `S_STATS_QUOTE` (문자열 `"sub_stats_quote"`) — Task 2의 `online_as_quote_stats.php`가 `$menu = S_STATS_QUOTE;`로 소비한다.

- [ ] **Step 1: `def_inc.php`에 메뉴 상수 추가**

`def_inc.php:43-46` 현재:
```php
	define("S_STATS_AS",       "sub_stats_as");
	define("S_STATS_SHIPMENT", "sub_stats_shipment");
	define("S_STATS_REPORT",   "sub_stats_report");
	define("S_STATS_QR",       "sub_stats_qr");
```

다음으로 교체:
```php
	define("S_STATS_AS",       "sub_stats_as");
	define("S_STATS_QUOTE",    "sub_stats_quote");
	define("S_STATS_SHIPMENT", "sub_stats_shipment");
	define("S_STATS_REPORT",   "sub_stats_report");
	define("S_STATS_QR",       "sub_stats_qr");
```

- [ ] **Step 2: `header.php` 통계 사이드바에 링크 추가**

`header.php:369-373` 현재:
```php
				<?if( $mod == M_STATS ){?>
					<div class="panel-heading"><h3 class="panel-title">통계</h3></div>
					<? if (($PERMISSION & PERMISSION_ALL) == PERMISSION_ALL) { ?>
					<a href="<?=$site_url?>/online_as/online_as_stats.php" class="list-group-item <?if($menu==S_STATS_AS){?>active<?}?>">AS 통계</a>
					<? } ?>
```

다음으로 교체:
```php
				<?if( $mod == M_STATS ){?>
					<div class="panel-heading"><h3 class="panel-title">통계</h3></div>
					<? if (($PERMISSION & PERMISSION_ALL) == PERMISSION_ALL) { ?>
					<a href="<?=$site_url?>/online_as/online_as_stats.php" class="list-group-item <?if($menu==S_STATS_AS){?>active<?}?>">AS 통계</a>
					<a href="<?=$site_url?>/online_as/online_as_quote_stats.php" class="list-group-item <?if($menu==S_STATS_QUOTE){?>active<?}?>">AS 견적/입금 통계</a>
					<? } ?>
```

- [ ] **Step 3: PHP 문법 검사**

Run: `docker exec cw_php php -l /var/www/html/cw_as_0924/def_inc.php && docker exec cw_php php -l /var/www/html/cw_as_0924/header.php`
Expected: 두 파일 모두 `No syntax errors detected`

- [ ] **Step 4: 커밋**

```bash
git add def_inc.php header.php
git commit -m "feat: AS 견적/입금 통계 메뉴 항목 추가"
```

---

### Task 2: `online_as_quote_stats.php` 신규 페이지

**Files:**
- Create: `online_as/online_as_quote_stats.php`

**Interfaces:**
- Consumes: `M_STATS`, `S_STATS_QUOTE` (Task 1에서 정의), `$db->db_conn` (mysqli 연결, `common.php`를 통해 `header.php`가 이미 include), `mysqli_query`/`mysqli_fetch_assoc` (전역 함수, 기존 `online_as_stats.php`와 동일 패턴).
- Produces: 없음 (터미널 페이지).

- [ ] **Step 1: 파일 생성 — 데이터 조회 + 버킷팅 (PHP 섹션)**

`online_as/online_as_quote_stats.php` 새로 생성:

```php
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
```

클로저(`function($ts){...}`)는 PHP 5.3+ 정식 문법이라 5.6에서 그대로 동작한다.

- [ ] **Step 2: PHP 문법 검사**

Run: `docker exec cw_php php -l /var/www/html/cw_as_0924/online_as/online_as_quote_stats.php`
Expected: `No syntax errors detected`

- [ ] **Step 3: HTML/CSS 섹션 추가 (요약 카드 + 안내 문구)**

Step 1 코드 블록(`?>`로 끝남) 바로 다음에 이어서 추가:

```php
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
```

- [ ] **Step 4: 탭 버튼 + 일/주/월 차트+상세테이블 블록 추가**

Step 3 다음에 이어서 추가 (일/주/월 세 블록을 반복 구조로 작성; 각 블록은 차트 1개 + 상세 테이블 1개):

```php
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
```

- [ ] **Step 5: JS — 차트 렌더링 + 탭 전환**

Step 4 다음에 이어서 추가:

```php
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
```

- [ ] **Step 6: PHP 문법 검사 (전체 파일)**

Run: `docker exec cw_php php -l /var/www/html/cw_as_0924/online_as/online_as_quote_stats.php`
Expected: `No syntax errors detected`

- [ ] **Step 7: 커밋**

```bash
git add online_as/online_as_quote_stats.php
git commit -m "feat: AS 견적/입금 통계 페이지 추가"
```

---

### Task 3: 로컬 검증

**Files:** 없음 (검증 전용 태스크)

**Interfaces:**
- Consumes: Task 1, Task 2의 산출물

- [ ] **Step 1: SQL 결과와 페이지 집계가 일치하는지 대조**

Run (읽기 전용, 이번 달 견적발행 건수/금액을 직접 계산):
```bash
docker exec cw_db mysql -h 211.54.90.200 -P 3307 -u cwadmin -pCatchwell1! cw_as -e "
SELECT COUNT(*) as cnt, SUM(a.price) as amt
FROM as_parcel_service a
JOIN (SELECT as_idx, MIN(changed_at) as issued_at FROM as_process_history WHERE new_state=2 GROUP BY as_idx) q ON q.as_idx=a.idx
WHERE a.process_state NOT IN (5,99) AND DATE_FORMAT(q.issued_at,'%Y-%m') = DATE_FORMAT(NOW(),'%Y-%m');
"
```
Expected: 이 값이 브라우저로 연 페이지의 "이번 달 견적발행" 카드 숫자와 정확히 일치해야 한다.

- [ ] **Step 2: 브라우저로 페이지 확인**

사용자가 평소 로컬 검증에 쓰는 방식대로 `http://localhost/cw_as_0924/online_as/online_as_quote_stats.php` (또는 기존에 쓰던 로컬 접속 경로)로 로그인 후 접속하여 확인:
- "통계" 사이드바에 "AS 견적/입금 통계" 링크가 보이는가
- 요약 카드 4개(견적발행/입금완료/미입금/입금율) 숫자가 Step 1과 일치하는가
- 일/주/월 탭 전환 시 차트와 상세 테이블이 함께 바뀌는가
- 상세 테이블의 접수번호 클릭 시 `online_as_edit.php`로 정상 이동하는가
- 입금 컬럼이 이니시스 결제(TB_INICIS_RETURN 존재)/수동 상태변경(TB_INICIS_RETURN 없음) 두 경우 모두에서 합리적인 금액을 보여주는가 (idx 83773 `260704-011`처럼 이니시스 결제 건으로 교차 확인 가능)

Expected: 위 항목이 모두 육안으로 정상 확인됨. 문제가 있으면 해당 단계로 돌아가 코드를 수정하고 Step 1의 SQL로 재대조한다.

- [ ] **Step 3: 완료 보고**

이상 없으면 사용자에게 로컬 확인 결과를 요약해 보고한다. 배포는 사용자가 "배포해줘"라고 명시적으로 요청할 때만 `deploy.ps1`로 진행한다 (이 계획의 범위 밖).

---

## Self-Review Notes

- **스펙 커버리지:** 설계 문서 §1(메뉴)=Task 1, §2~§3(데이터/집계 로직)=Task 2 Step 1, §4.1(요약카드)=Task 2 Step 3, §4.2(탭+차트)=Task 2 Step 4~5, §4.3(상세테이블)=Task 2 Step 4, §4.4(쿼리+중복 제거)=Task 2 Step 1, §5(엣지케이스)=Task 2 Step 1(취소 제외)+안내문구(Step 3). 모두 커버됨.
- **플레이스홀더 스캔:** 없음 — 모든 스텝에 실행 가능한 완전한 코드 포함.
- **타입/이름 일관성:** `$quotes` 배열의 키(`idx`, `reg_num`, `price`, `issued_at`, `customer_name`, `pic_name`, `paid`, `paid_at`, `paid_amt`)가 Step 1(생성)부터 Step 4(`qs_render_detail_table`에서 소비)까지 동일하게 사용됨을 확인. `qs_build_buckets`가 반환하는 버킷 배열의 키(`cnt`, `amt`, `paid_cnt`, `paid_amt`)도 요약 카드(Step 3)와 차트 데이터 조립(Step 1 후반)에서 동일하게 사용됨을 확인.
