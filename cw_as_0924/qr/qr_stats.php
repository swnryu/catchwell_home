<?php
include("../def_inc.php");
$mod  = M_STATS;
$menu = S_STATS_QR;
include("../header.php");

error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);

// qr_log_type 테이블은 cw_as_dev DB에 있음
$qr_db = new mysqli_dbConnect($DB_HOST, "cw_as_dev", $DB_USER, $DB_PWD, $DB_PORT);
$qr_conn = $qr_db->db_conn;

$table = "qr_log_type";

// 전체기간 데이터 범위
$r_range = mysqli_fetch_row(mysqli_query($qr_conn, "SELECT DATE(MIN(udate)), DATE(MAX(udate)) FROM $table"));
$min_date = $r_range[0];
$max_date = $r_range[1];

$use_filter = (isset($_GET['date_from']) && $_GET['date_from'] != '');
$date_from  = $use_filter ? $_GET['date_from'] : $min_date;
$date_to    = (isset($_GET['date_to']) && $_GET['date_to'] != '') ? $_GET['date_to'] : $max_date;
$date_to2   = date('Y-m-d', strtotime($date_to . ' +1 day'));

$where_base = "WHERE udate >= '$date_from' AND udate < '$date_to2'";

// ── 요약 ──────────────────────────────────────────────────────────
$r = mysqli_fetch_row(mysqli_query($qr_conn,
    "SELECT COUNT(DISTINCT ip, DATE(udate)), COUNT(*) FROM $table $where_base"));
$total_uniq = (int)$r[0];
$total_raw  = (int)$r[1];

$days = max(1, (int)date_diff(date_create($date_from), date_create($date_to))->format('%a') + 1);
$daily_avg = round($total_uniq / $days, 1);

$this_month = date('Y-m');
$r2 = mysqli_fetch_row(mysqli_query($qr_conn,
    "SELECT COUNT(DISTINCT ip, DATE(udate)) FROM $table WHERE DATE_FORMAT(udate,'%Y-%m')='$this_month'"));
$this_month_cnt = (int)$r2[0];

$prev_month = date('Y-m', strtotime('first day of last month'));
$r3 = mysqli_fetch_row(mysqli_query($qr_conn,
    "SELECT COUNT(DISTINCT ip, DATE(udate)) FROM $table WHERE DATE_FORMAT(udate,'%Y-%m')='$prev_month'"));
$prev_month_cnt = (int)$r3[0];
$mom_diff = $this_month_cnt - $prev_month_cnt;
$mom_sign = $mom_diff >= 0 ? '+' : '';

// ── 월별 트렌드 ───────────────────────────────────────────────────
$monthly = array();
$res = mysqli_query($qr_conn,
    "SELECT DATE_FORMAT(udate,'%Y-%m') as ym, COUNT(DISTINCT ip, DATE(udate)) as cnt
     FROM $table $where_base
     GROUP BY ym ORDER BY ym ASC");
while ($row = mysqli_fetch_assoc($res)) {
    $monthly[] = array('label' => $row['ym'], 'y' => (int)$row['cnt']);
}

// ── 주별 트렌드 (최근 12주) ───────────────────────────────────────
$weekly = array();
$res = mysqli_query($qr_conn,
    "SELECT DATE_FORMAT(MIN(udate),'%m/%d') as wk_label,
            YEARWEEK(udate,1) as yw,
            COUNT(DISTINCT ip, DATE(udate)) as cnt
     FROM $table
     WHERE udate >= DATE_SUB(NOW(), INTERVAL 12 WEEK)
     GROUP BY yw ORDER BY yw ASC");
while ($row = mysqli_fetch_assoc($res)) {
    $weekly[] = array('label' => $row['wk_label'].'주', 'y' => (int)$row['cnt']);
}

// ── 요일별 분포 ───────────────────────────────────────────────────
$weekday_labels = array('일','월','화','수','목','금','토');
$weekday_data   = array_fill(0, 7, 0);
$res = mysqli_query($qr_conn,
    "SELECT DAYOFWEEK(udate) as dow, COUNT(DISTINCT ip, DATE(udate)) as cnt
     FROM $table $where_base GROUP BY dow");
while ($row = mysqli_fetch_assoc($res)) {
    $weekday_data[(int)$row['dow'] - 1] = (int)$row['cnt'];
}
$weekday_points = array();
foreach ($weekday_labels as $i => $lbl) {
    $weekday_points[] = array('label' => $lbl, 'y' => $weekday_data[$i]);
}

// ── 시간대별 분포 ─────────────────────────────────────────────────
$hourly_data = array_fill(0, 24, 0);
$res = mysqli_query($qr_conn,
    "SELECT HOUR(udate) as h, COUNT(DISTINCT ip, DATE(udate)) as cnt
     FROM $table $where_base GROUP BY h");
while ($row = mysqli_fetch_assoc($res)) {
    $hourly_data[(int)$row['h']] = (int)$row['cnt'];
}
$hourly_points = array();
for ($h = 0; $h < 24; $h++) {
    $hourly_points[] = array('label' => $h.'시', 'y' => $hourly_data[$h]);
}

// ── 모델별 분석 ───────────────────────────────────────────────────
$model_rows  = array();
$model_total = 0;
$res = mysqli_query($qr_conn,
    "SELECT contents as model, COUNT(DISTINCT ip, DATE(udate)) as cnt
     FROM $table $where_base AND contents IS NOT NULL AND contents != ''
     GROUP BY contents ORDER BY cnt DESC");
while ($row = mysqli_fetch_assoc($res)) {
    $model_rows[] = array('model' => $row['model'], 'cnt' => (int)$row['cnt']);
    $model_total += (int)$row['cnt'];
}
$model_pie = array();
$model_other = 0;
foreach ($model_rows as $mr) {
    if ($mr['cnt'] < 3) { $model_other += $mr['cnt']; }
    else { $model_pie[] = array('label' => $mr['model'], 'y' => $mr['cnt']); }
}
if ($model_other > 0) { $model_pie[] = array('label' => '기타', 'y' => $model_other); }

// ── 동작별 분석 ───────────────────────────────────────────────────
$action_map = array(
    'mall'        => '소모품 구매',
    'as'          => 'AS접수',
    'manual'      => '사용자 매뉴얼',
    'googleplay'  => '앱 다운로드(Android)',
    'appstore'    => '앱 다운로드(iOS)',
    'youtubelink' => '필터청소방법',
    'qna'         => '고객문의',
    'call'        => 'AS전화연결',
);
$action_rows  = array();
$action_total = 0;
$res = mysqli_query($qr_conn,
    "SELECT comment as action, COUNT(DISTINCT ip) as cnt
     FROM $table $where_base AND comment IS NOT NULL AND comment != ''
     GROUP BY comment ORDER BY cnt DESC");
while ($row = mysqli_fetch_assoc($res)) {
    $key   = $row['action'];
    $label = isset($action_map[$key]) ? $action_map[$key] : $key;
    $action_rows[] = array('action' => $label, 'cnt' => (int)$row['cnt']);
    $action_total += (int)$row['cnt'];
}
$action_pie = array();
foreach ($action_rows as $ar) {
    $action_pie[] = array('label' => $ar['action'], 'y' => $ar['cnt']);
}

// ── 주별 모델별 통계 (최근 12주, 상위 10 모델) ──────────────────────
// 기간 내 상위 10 모델 추출
$wm_top_models = array();
$res = mysqli_query($qr_conn,
    "SELECT contents as model, COUNT(DISTINCT ip, DATE(udate)) as cnt
     FROM $table
     WHERE udate >= DATE_SUB(NOW(), INTERVAL 12 WEEK)
       AND contents IS NOT NULL AND contents != ''
     GROUP BY contents ORDER BY cnt DESC LIMIT 10");
while ($row = mysqli_fetch_assoc($res)) {
    $wm_top_models[] = $row['model'];
}

// 주 목록
$wm_weeks = array(); // ['yw' => ..., 'label' => ...]
$res = mysqli_query($qr_conn,
    "SELECT YEARWEEK(udate,1) as yw, DATE_FORMAT(MIN(udate),'%m/%d') as wk_label
     FROM $table
     WHERE udate >= DATE_SUB(NOW(), INTERVAL 12 WEEK)
     GROUP BY yw ORDER BY yw ASC");
while ($row = mysqli_fetch_assoc($res)) {
    $wm_weeks[] = array('yw' => $row['yw'], 'label' => $row['wk_label']);
}

// 주 × 모델 피벗
$wm_pivot = array(); // $wm_pivot[yw][model] = cnt
$res = mysqli_query($qr_conn,
    "SELECT YEARWEEK(udate,1) as yw, contents as model,
            COUNT(DISTINCT ip, DATE(udate)) as cnt
     FROM $table
     WHERE udate >= DATE_SUB(NOW(), INTERVAL 12 WEEK)
       AND contents IS NOT NULL AND contents != ''
     GROUP BY yw, contents");
while ($row = mysqli_fetch_assoc($res)) {
    $wm_pivot[$row['yw']][$row['model']] = (int)$row['cnt'];
}

// CanvasJS stackedColumn용: 모델별 시리즈 배열
$wm_series = array();
foreach ($wm_top_models as $model) {
    $pts = array();
    foreach ($wm_weeks as $wk) {
        $pts[] = array(
            'label' => $wk['label'],
            'y'     => isset($wm_pivot[$wk['yw']][$model]) ? $wm_pivot[$wk['yw']][$model] : 0,
        );
    }
    $wm_series[] = array('name' => $model, 'dataPoints' => $pts);
}

// ── 최근 30일 일별 ────────────────────────────────────────────────
$daily_points = array();
$res = mysqli_query($qr_conn,
    "SELECT DATE(udate) as d, COUNT(DISTINCT ip) as cnt
     FROM $table
     WHERE udate >= DATE_SUB(NOW(), INTERVAL 30 DAY)
     GROUP BY d ORDER BY d ASC");
while ($row = mysqli_fetch_assoc($res)) {
    $daily_points[] = array('label' => $row['d'], 'y' => (int)$row['cnt']);
}
?>

<h4 class="page-header">QR 코드 접속 통계</h4>

<!-- 기간 필터 -->
<form method="GET" class="form-inline" style="margin-bottom:20px;">
  <div class="form-group">
    <label>기간&nbsp;</label>
    <input type="text" class="form-control input-sm" id="date_from" name="date_from"
           value="<?php echo htmlspecialchars($date_from); ?>" style="width:120px;">
    <span>&nbsp;~&nbsp;</span>
    <input type="text" class="form-control input-sm" id="date_to" name="date_to"
           value="<?php echo htmlspecialchars($date_to); ?>" style="width:120px;">
  </div>
  <button type="submit" class="btn btn-sm btn-primary" style="margin-left:8px;">조회</button>
  <a href="qr_stats.php" class="btn btn-sm btn-default" style="margin-left:4px;">전체기간</a>
  <span style="margin-left:12px;font-size:12px;color:#888;">전체 데이터: <?php echo $min_date; ?> ~ <?php echo $max_date; ?></span>
</form>
<script>
$(function(){
  $('#date_from,#date_to').datetimepicker({ format:'YYYY-MM-DD', locale:'ko' });
});
</script>

<!-- 요약 카드 -->
<div class="row" style="margin-bottom:10px;">
  <div class="col-sm-3">
    <div class="panel panel-primary" style="text-align:center;padding:15px 0 10px;">
      <div style="font-size:12px;color:#aee;">기간 내 총 접속 (중복제거)</div>
      <div style="font-size:30px;font-weight:bold;color:#fff;"><?php echo number_format($total_uniq); ?></div>
      <div style="font-size:11px;color:#cde;"><?php echo $date_from; ?> ~ <?php echo $date_to; ?></div>
    </div>
  </div>
  <div class="col-sm-3">
    <div class="panel panel-default" style="text-align:center;padding:15px 0 10px;">
      <div style="font-size:12px;color:#888;">일 평균 접속</div>
      <div style="font-size:30px;font-weight:bold;"><?php echo $daily_avg; ?></div>
      <div style="font-size:11px;color:#aaa;"><?php echo number_format($days); ?>일 기준</div>
    </div>
  </div>
  <div class="col-sm-3">
    <div class="panel panel-default" style="text-align:center;padding:15px 0 10px;">
      <div style="font-size:12px;color:#888;">이번 달</div>
      <div style="font-size:30px;font-weight:bold;"><?php echo number_format($this_month_cnt); ?></div>
      <div style="font-size:11px;color:#aaa;"><?php echo $this_month; ?></div>
    </div>
  </div>
  <div class="col-sm-3">
    <div class="panel panel-default" style="text-align:center;padding:15px 0 10px;">
      <div style="font-size:12px;color:#888;">전월 대비</div>
      <div style="font-size:30px;font-weight:bold;color:<?php echo $mom_diff >= 0 ? '#5cb85c' : '#d9534f'; ?>;">
        <?php echo $mom_sign . number_format($mom_diff); ?>
      </div>
      <div style="font-size:11px;color:#aaa;"><?php echo $prev_month; ?> <?php echo number_format($prev_month_cnt); ?>건</div>
    </div>
  </div>
</div>

<!-- 최근 30일 일별 추이 -->
<div class="panel panel-default">
  <div class="panel-heading"><strong>최근 30일 일별 접속 추이</strong></div>
  <div class="panel-body" style="padding:10px 15px;">
    <div id="chart_daily" style="height:200px;"></div>
  </div>
</div>

<!-- 월별 / 주별 -->
<div class="row">
  <div class="col-sm-7">
    <div class="panel panel-default">
      <div class="panel-heading"><strong>월별 접속 트렌드</strong> <small style="color:#999;">(기간 필터 적용)</small></div>
      <div class="panel-body" style="padding:10px 15px;">
        <div id="chart_monthly" style="height:240px;"></div>
      </div>
    </div>
  </div>
  <div class="col-sm-5">
    <div class="panel panel-default">
      <div class="panel-heading"><strong>주별 접속</strong> <small style="color:#999;">(최근 12주)</small></div>
      <div class="panel-body" style="padding:10px 15px;">
        <div id="chart_weekly" style="height:240px;"></div>
      </div>
    </div>
  </div>
</div>

<!-- 요일별 / 시간대별 -->
<div class="row">
  <div class="col-sm-4">
    <div class="panel panel-default">
      <div class="panel-heading"><strong>요일별 분포</strong></div>
      <div class="panel-body" style="padding:10px 15px;">
        <div id="chart_weekday" style="height:200px;"></div>
      </div>
    </div>
  </div>
  <div class="col-sm-8">
    <div class="panel panel-default">
      <div class="panel-heading"><strong>시간대별 분포</strong></div>
      <div class="panel-body" style="padding:10px 15px;">
        <div id="chart_hourly" style="height:200px;"></div>
      </div>
    </div>
  </div>
</div>

<!-- 주별 모델별 통계 -->
<div class="panel panel-default">
  <div class="panel-heading"><strong>주별 모델별 접속 통계</strong> <small style="color:#999;">최근 12주 · 상위 10모델 스택</small></div>
  <div class="panel-body" style="padding:10px 15px;">
    <div id="chart_wm_stack" style="height:300px;"></div>
    <div style="overflow-x:auto;margin-top:16px;">
      <table class="table table-condensed table-bordered table-hover" style="font-size:11px;min-width:600px;">
        <thead>
          <tr>
            <th style="white-space:nowrap;">주</th>
            <?php foreach ($wm_top_models as $m): ?>
            <th class="text-center" style="white-space:nowrap;"><?php echo htmlspecialchars($m); ?></th>
            <?php endforeach; ?>
            <th class="text-center">합계</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($wm_weeks as $wk):
          $row_total = 0;
          $row_vals  = array();
          foreach ($wm_top_models as $m) {
              $v = isset($wm_pivot[$wk['yw']][$m]) ? $wm_pivot[$wk['yw']][$m] : 0;
              $row_vals[] = $v;
              $row_total += $v;
          }
        ?>
          <tr>
            <td style="white-space:nowrap;"><?php echo $wk['label']; ?>주</td>
            <?php foreach ($row_vals as $v): ?>
            <td class="text-center"><?php echo $v > 0 ? $v : '-'; ?></td>
            <?php endforeach; ?>
            <td class="text-center"><strong><?php echo $row_total; ?></strong></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- 모델별 / 동작별 -->
<div class="row">
  <div class="col-sm-6">
    <div class="panel panel-default">
      <div class="panel-heading"><strong>모델별 접속 비율</strong> <small style="color:#999;">총 <?php echo number_format($model_total); ?>건</small></div>
      <div class="panel-body" style="padding:10px 15px;">
        <div id="chart_model" style="height:260px;"></div>
        <table class="table table-condensed table-hover" style="margin-top:10px;font-size:12px;">
          <thead><tr><th>모델</th><th class="text-right">접속수</th><th>비율</th></tr></thead>
          <tbody>
          <?php foreach ($model_rows as $mr):
            $pct = $model_total > 0 ? round($mr['cnt'] / $model_total * 100, 1) : 0;
          ?>
          <tr>
            <td><?php echo htmlspecialchars($mr['model']); ?></td>
            <td class="text-right"><?php echo number_format($mr['cnt']); ?></td>
            <td style="min-width:100px;">
              <div class="progress" style="margin:2px 0;height:14px;">
                <div class="progress-bar" style="width:<?php echo $pct; ?>%;line-height:14px;font-size:10px;"><?php echo $pct; ?>%</div>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="col-sm-6">
    <div class="panel panel-default">
      <div class="panel-heading"><strong>동작별 접속 비율</strong> <small style="color:#999;">총 <?php echo number_format($action_total); ?>건</small></div>
      <div class="panel-body" style="padding:10px 15px;">
        <div id="chart_action" style="height:260px;"></div>
        <table class="table table-condensed table-hover" style="margin-top:10px;font-size:12px;">
          <thead><tr><th>동작</th><th class="text-right">접속수</th><th>비율</th></tr></thead>
          <tbody>
          <?php foreach ($action_rows as $ar):
            $pct = $action_total > 0 ? round($ar['cnt'] / $action_total * 100, 1) : 0;
          ?>
          <tr>
            <td><?php echo htmlspecialchars($ar['action']); ?></td>
            <td class="text-right"><?php echo number_format($ar['cnt']); ?></td>
            <td style="min-width:100px;">
              <div class="progress" style="margin:2px 0;height:14px;">
                <div class="progress-bar progress-bar-info" style="width:<?php echo $pct; ?>%;line-height:14px;font-size:10px;"><?php echo $pct; ?>%</div>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
window.onload = function() {

  new CanvasJS.Chart("chart_daily", {
    animationEnabled: true,
    axisX: { labelFontSize:10, labelAngle:-40 },
    axisY: { labelFontSize:11 },
    data: [{ type:"splineArea", color:"rgba(51,122,183,0.55)", markerSize:4,
      dataPoints: <?php echo json_encode($daily_points, JSON_NUMERIC_CHECK); ?>
    }]
  }).render();

  new CanvasJS.Chart("chart_monthly", {
    animationEnabled: true,
    axisX: { labelFontSize:10, labelAngle:-30 },
    axisY: { labelFontSize:11 },
    data: [{ type:"column", color:"#337ab7",
      indexLabel:"{y}", indexLabelFontSize:10,
      dataPoints: <?php echo json_encode($monthly, JSON_NUMERIC_CHECK); ?>
    }]
  }).render();

  new CanvasJS.Chart("chart_weekly", {
    animationEnabled: true,
    axisX: { labelFontSize:10, labelAngle:-30 },
    axisY: { labelFontSize:11 },
    data: [{ type:"column", color:"#5bc0de",
      indexLabel:"{y}", indexLabelFontSize:10,
      dataPoints: <?php echo json_encode($weekly, JSON_NUMERIC_CHECK); ?>
    }]
  }).render();

  new CanvasJS.Chart("chart_weekday", {
    animationEnabled: true,
    axisX: { labelFontSize:12 },
    axisY: { labelFontSize:11 },
    data: [{ type:"column", color:"#f0ad4e",
      indexLabel:"{y}", indexLabelFontSize:10,
      dataPoints: <?php echo json_encode($weekday_points, JSON_NUMERIC_CHECK); ?>
    }]
  }).render();

  new CanvasJS.Chart("chart_hourly", {
    animationEnabled: true,
    axisX: { labelFontSize:10 },
    axisY: { labelFontSize:11 },
    data: [{ type:"column", color:"#5cb85c",
      indexLabel:"{y}", indexLabelFontSize:9,
      dataPoints: <?php echo json_encode($hourly_points, JSON_NUMERIC_CHECK); ?>
    }]
  }).render();

  // 주별 모델별 스택 차트
  var wmSeries = <?php echo json_encode($wm_series, JSON_NUMERIC_CHECK); ?>;
  var wmData = [];
  for (var i = 0; i < wmSeries.length; i++) {
    wmData.push({
      type: "stackedColumn",
      name: wmSeries[i].name,
      showInLegend: true,
      legendText: wmSeries[i].name,
      dataPoints: wmSeries[i].dataPoints
    });
  }
  new CanvasJS.Chart("chart_wm_stack", {
    animationEnabled: true,
    legend: { fontSize: 11, cursor: "pointer" },
    axisX: { labelFontSize: 10, labelAngle: -30 },
    axisY: { labelFontSize: 11 },
    toolTip: { shared: true },
    data: wmData
  }).render();

  new CanvasJS.Chart("chart_model", {
    animationEnabled: true,
    data: [{ type:"pie", startAngle:270,
      indexLabel:"{label} {y}건", indexLabelFontSize:11,
      dataPoints: <?php echo json_encode($model_pie, JSON_NUMERIC_CHECK); ?>
    }]
  }).render();

  new CanvasJS.Chart("chart_action", {
    animationEnabled: true,
    data: [{ type:"pie", startAngle:270,
      indexLabel:"{label} {y}건", indexLabelFontSize:11,
      dataPoints: <?php echo json_encode($action_pie, JSON_NUMERIC_CHECK); ?>
    }]
  }).render();

};
</script>

<?php include('../footer.php'); ?>
