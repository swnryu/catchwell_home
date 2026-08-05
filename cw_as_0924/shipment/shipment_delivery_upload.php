<?php
include("../def_inc.php");
$mod  = M_SHIPMENT;
$menu = S_SHIPMENT_DELIVERY_UPLOAD;
include("../header.php");

$msg     = '';
$msgType = '';

// ── 파일 업로드 처리 ──────────────────────────────────────────
if (!empty($_FILES['xlsfile']['size'])) {

    $uploadDir   = __DIR__ . '/files/';
    $origName    = $_FILES['xlsfile']['name'];
    $ext         = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

    // 선택된 날짜 (기본: 오늘)
    $targetDate  = isset($_POST['target_date']) && $_POST['target_date']
                   ? $_POST['target_date'] : date('Y-m-d');
    $targetDate  = preg_replace('/[^0-9\-]/', '', $targetDate);

    if (!in_array($ext, array('xlsx', 'xls'))) {
        $msg = '엑셀 파일(.xlsx/.xls)만 업로드 가능합니다.';
        $msgType = 'danger';
    } elseif ($_FILES['xlsfile']['size'] > 1024 * 1024 * 20) {
        $msg = '20MB 이하 파일만 업로드 가능합니다.';
        $msgType = 'danger';
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $targetDate)) {
        $msg = '날짜 형식이 올바르지 않습니다.';
        $msgType = 'danger';
    } else {
        $savePath = $uploadDir . $origName;
        if (!move_uploaded_file($_FILES['xlsfile']['tmp_name'], $savePath)) {
            $msg = '파일 저장 실패.';
            $msgType = 'danger';
        } else {
            require_once "../PHPExcel/Classes/PHPExcel.php";
            require_once "../PHPExcel/Classes/PHPExcel/IOFactory.php";

            try {
                $reader = PHPExcel_IOFactory::createReaderForFile($savePath);
                $reader->setReadDataOnly(true);
                $objExcel   = $reader->load($savePath);
                $sheet      = $objExcel->getActiveSheet();
                $highestRow = $sheet->getHighestRow();

                // 년월
                $ym      = substr($targetDate, 0, 7);
                $curYear = substr($targetDate, 0, 4);

                // 같은 날짜 기존 데이터 삭제
                $db->result("DELETE d FROM shipment_delivery d
                             INNER JOIN shipment_upload_history h ON h.idx = d.upload_id
                             WHERE h.ship_date = '$targetDate'");
                $db->result("DELETE FROM shipment_upload_history WHERE ship_date = '$targetDate'");

                // 업로드 이력 등록
                $db->insert('shipment_upload_history',
                    "file_name='" . mysqli_real_escape_string($db->db_conn, $origName) . "'," .
                    "ym='$ym', ship_date='$targetDate', uploaded_by='$ADMIN_NAME', created_at=NOW()"
                );
                $uploadId = $db->lastIdx();

                // 셀 값 취득 헬퍼
                $getVal = function($col, $row) use ($sheet) {
                    $cell = $sheet->getCellByColumnAndRow($col - 1, $row);
                    $v    = $cell->getValue();
                    if ($v === null) return '';
                    if (is_numeric($v) && PHPExcel_Shared_Date::isDateTime($cell)) {
                        return date('m월 d일', PHPExcel_Shared_Date::ExcelToPHP((float)$v));
                    }
                    return trim((string)$v);
                };

                $inserted = 0;
                $skipped  = 0;
                $filtered = 0;

                for ($r = 2; $r <= $highestRow; $r++) {
                    $g = function($col) use ($getVal, $r) { return $getVal($col, $r); };

                    $model = $g(2);
                    if ($model === '') { $skipped++; continue; }

                    // 발주날짜 파싱
                    $shipDate = null;
                    $dateRaw  = $g(1);
                    if (preg_match('/(\d+)월\s*(\d+)일/', $dateRaw, $dm)) {
                        $shipDate = $curYear . '-' . str_pad($dm[1], 2, '0', STR_PAD_LEFT)
                                  . '-' . str_pad($dm[2], 2, '0', STR_PAD_LEFT);
                    } elseif (is_numeric($dateRaw) && $dateRaw > 40000) {
                        $shipDate = date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP((float)$dateRaw));
                    }
                    if (!$shipDate) { $skipped++; continue; }

                    // 선택한 날짜와 다른 행은 건너뜀
                    if ($shipDate !== $targetDate) { $filtered++; continue; }

                    $modelBase = explode('_', $model)[0];

                    $toInt = function($v) {
                        $v = preg_replace('/[^0-9\-]/', '', $v);
                        return ($v === '' || $v === '-') ? null : (int)$v;
                    };

                    // 주문일시 열16
                    $orderDt = null;
                    $dtRaw   = $getVal(16, $r);
                    if (preg_match('/\d{4}-\d{2}-\d{2}/', $dtRaw)) {
                        $orderDt = "'" . mysqli_real_escape_string($db->db_conn, $dtRaw) . "'";
                    } elseif (is_numeric($dtRaw) && $dtRaw > 40000) {
                        $orderDt = "'" . date('Y-m-d H:i:s', PHPExcel_Shared_Date::ExcelToPHP((float)$dtRaw)) . "'";
                    }

                    $esc = function($v) use ($db) {
                        return "'" . mysqli_real_escape_string($db->db_conn, $v) . "'";
                    };
                    $escInt = function($v) use ($toInt) {
                        $n = $toInt($v);
                        return ($n === null) ? 'NULL' : $n;
                    };

                    $sql = "INSERT INTO shipment_delivery (
                        upload_id, ship_date, model, model_base,
                        accessory_info, accessory_name, qty, order_no, channel,
                        recipient, tracking_no, phone, mobile, address, delivery_msg,
                        sabangnet_no, order_datetime, orderer_name, orderer_phone1, orderer_phone2,
                        sale_price, payment_amt, orderer_id,
                        commission_sale, commission_payment, commission_rate_sale, commission_rate_payment,
                        created_at
                    ) VALUES (
                        $uploadId, '$shipDate', {$esc($model)}, {$esc($modelBase)},
                        {$esc($g(3))}, {$esc($g(4))}, " . max(1, (int)$g(5)) . ", {$esc($g(7))}, {$esc($g(8))},
                        {$esc($g(9))}, {$esc($g(10))}, {$esc($g(11))}, {$esc($g(12))}, {$esc($g(13))}, {$esc($g(14))},
                        {$esc($g(15))}, " . ($orderDt ? $orderDt : 'NULL') . ", {$esc($g(17))}, {$esc($g(18))}, {$esc($g(19))},
                        {$escInt($g(20))}, {$escInt($g(21))}, {$esc($g(22))},
                        {$escInt($g(23))}, {$escInt($g(24))}, {$esc($g(25))}, {$esc($g(26))},
                        NOW()
                    )";

                    if ($db->result($sql)) {
                        $inserted++;
                    } else {
                        $skipped++;
                    }
                }

                $db->result("UPDATE shipment_upload_history SET total_rows=$inserted WHERE idx=$uploadId");

                $msg     = "{$targetDate} 배송 데이터 <strong>{$inserted}건</strong> 저장 완료."
                         . " (다른 날짜: {$filtered}건 제외, 스킵: {$skipped}건)";
                $msgType = 'success';

            } catch (Exception $e) {
                $msg     = '파일 처리 오류: ' . htmlspecialchars($e->getMessage());
                $msgType = 'danger';
            }
        }
    }
}

// 업로드 이력 목록 (날짜별)
$histories = array();
$res = mysqli_query($db->db_conn,
    "SELECT h.*, COUNT(d.idx) AS row_cnt
     FROM shipment_upload_history h
     LEFT JOIN shipment_delivery d ON d.upload_id = h.idx
     GROUP BY h.idx
     ORDER BY h.ship_date DESC, h.created_at DESC
     LIMIT 30");
while ($row = mysqli_fetch_object($res)) $histories[] = $row;
?>

<h4 class="page-header">배송리스트 업로드</h4>

<?php if ($msg): ?>
<div class="alert alert-<?= $msgType ?>"><?= $msg ?></div>
<?php endif; ?>

<div class="panel panel-default">
    <div class="panel-heading"><strong>엑셀 파일 업로드</strong></div>
    <div class="panel-body">
        <form method="post" enctype="multipart/form-data">
            <div class="row">
                <div class="col-sm-3">
                    <label class="control-label">가져올 날짜</label>
                    <input type="date" name="target_date" class="form-control"
                           value="<?= date('Y-m-d') ?>" required>
                    <p class="help-block" style="font-size:11px;">
                        해당 날짜의 데이터만 가져옵니다
                    </p>
                </div>
                <div class="col-sm-6">
                    <label class="control-label">배송리스트 파일 (.xlsx / .xls)</label>
                    <input type="file" name="xlsfile" accept=".xlsx,.xls" required
                           class="form-control" style="padding:4px;">
                    <p class="help-block" style="font-size:11px;">
                        월별 누적 파일에서 선택한 날짜 행만 추출합니다. 동일 날짜 재업로드 시 교체됩니다.
                    </p>
                </div>
                <div class="col-sm-3" style="padding-top:25px;">
                    <button type="submit" class="btn btn-primary btn-block">
                        <span class="glyphicon glyphicon-upload"></span> 업로드
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading"><strong>업로드 이력</strong> <small class="text-muted">최근 30건</small></div>
    <table class="table table-bordered table-hover table-condensed" style="margin:0;">
        <thead>
            <tr style="background:#f5f5f5;">
                <th>배송일자</th>
                <th>파일명</th>
                <th style="text-align:right;">저장건수</th>
                <th>업로더</th>
                <th>업로드일시</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($histories)): ?>
        <tr><td colspan="6" class="text-center text-muted">업로드 이력이 없습니다.</td></tr>
        <?php else: ?>
        <?php foreach ($histories as $h): ?>
        <tr>
            <td><strong><?= htmlspecialchars($h->ship_date) ?></strong></td>
            <td style="font-size:11px;color:#666;"><?= htmlspecialchars($h->file_name) ?></td>
            <td style="text-align:right;"><?= number_format($h->row_cnt) ?>건</td>
            <td><?= htmlspecialchars($h->uploaded_by) ?></td>
            <td style="font-size:11px;"><?= htmlspecialchars($h->created_at) ?></td>
            <td>
                <a href="shipment_delivery_dashboard.php?ym=<?= urlencode($h->ym) ?>"
                   class="btn btn-xs btn-info">대시보드</a>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include('../footer.php'); ?>
