<?
include("../def_inc.php");
$mod  = M_AS;
$menu = S_AS_TRACKING;
include("../header.php");

$result = null;
$error  = '';

$postedInvoice = isset($_POST['invoice']) ? $_POST['invoice'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $invoice = preg_replace('/[^0-9]/', '', $postedInvoice);

    if (!$invoice) {
        $error = '운송장번호를 입력해주세요.';
    } else {

        $cookieFile = tempnam(sys_get_temp_dir(), 'cj_cookie_');

        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL            => 'https://www.cjlogistics.com/ko/tool/parcel/tracking',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_COOKIEJAR      => $cookieFile,
            CURLOPT_COOKIEFILE     => $cookieFile,
            CURLOPT_USERAGENT      => 'Mozilla/5.0'
        ));

        $html      = curl_exec($ch);
        $curlError = curl_error($ch);
        curl_close($ch);

        if (!$html) {
            $error = 'CJ 페이지 접속 실패: ' . $curlError;
        } else if (!preg_match('/name="_csrf"\s+value="([^"]+)"/', $html, $match)) {
            $error = 'CSRF 토큰 추출 실패';
        } else {

            $csrf = $match[1];

            $ch = curl_init();
            curl_setopt_array($ch, array(
                CURLOPT_URL            => 'https://www.cjlogistics.com/ko/tool/parcel/tracking-detail',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_COOKIEJAR      => $cookieFile,
                CURLOPT_COOKIEFILE     => $cookieFile,
                CURLOPT_USERAGENT      => 'Mozilla/5.0',
                CURLOPT_HTTPHEADER     => array(
                    'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
                    'X-Requested-With: XMLHttpRequest',
                    'Referer: https://www.cjlogistics.com/ko/tool/parcel/tracking'
                ),
                CURLOPT_POSTFIELDS     => http_build_query(array(
                    '_csrf'       => $csrf,
                    'paramInvcNo' => $invoice
                ))
            ));

            $response  = curl_exec($ch);
            $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($httpCode != 200 || !$response) {
                $error = '배송조회 실패 / HTTP: ' . $httpCode . ' / ' . $curlError;
            } else {
                $data = json_decode($response, true);
                if (!$data) {
                    $error = '배송조회 결과 파싱 실패';
                } else {
                    $result = $data;
                }
            }
        }

        @unlink($cookieFile);
    }
}
?>

<style>
.cj-card { margin-top: 20px; border: 1px solid #e1e1e1; border-radius: 6px; overflow: hidden; }
.cj-card-title { background: #f1f3f5; padding: 12px 16px; font-weight: bold; border-bottom: 1px solid #e1e1e1; }
.cj-info-table { width: 100%; border-collapse: collapse; }
.cj-info-table th, .cj-info-table td { border-bottom: 1px solid #eee; padding: 10px 12px; font-size: 14px; text-align: left; }
.cj-info-table th { width: 150px; background: #fafafa; }
.cj-track-table { width: 100%; border-collapse: collapse; }
.cj-track-table th, .cj-track-table td { border-bottom: 1px solid #eee; padding: 10px 12px; font-size: 13px; text-align: left; }
.cj-track-table th { background: #fafafa; }
.cj-badge { display: inline-block; padding: 3px 8px; border-radius: 20px; background: #eef2ff; color: #2948c7; font-size: 12px; font-weight: bold; white-space: nowrap; }
.cj-badge-complete { background: #e8f7ef; color: #16823a; }
</style>

<h4 class="page-header">택배 조회 (CJ대한통운)</h4>

<form method="post" class="form-inline">
    <div class="input-group" style="width:100%;max-width:440px;">
        <input type="text" name="invoice" class="form-control"
               placeholder="운송장번호를 입력하세요"
               value="<?= htmlspecialchars($postedInvoice, ENT_QUOTES, 'UTF-8') ?>"
               autofocus autocomplete="off">
        <span class="input-group-btn">
            <button type="submit" class="btn btn-primary">조회</button>
        </span>
    </div>
</form>

<?php if ($error) { ?>
    <div class="alert alert-danger" style="margin-top:16px;">
        <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
    </div>
<?php } ?>

<?php if ($result) {
    $info    = isset($result['parcelResultMap']['resultList'][0])       ? $result['parcelResultMap']['resultList'][0]        : array();
    $details = isset($result['parcelDetailResultMap']['resultList'])    ? $result['parcelDetailResultMap']['resultList']     : array();
    $last    = count($details) > 0 ? $details[count($details) - 1] : array();
?>

    <div class="cj-card">
        <div class="cj-card-title">배송 기본정보</div>
        <table class="cj-info-table">
            <tr><th>운송장번호</th><td><?= htmlspecialchars(isset($info['invcNo'])  ? $info['invcNo']  : '', ENT_QUOTES, 'UTF-8') ?></td></tr>
            <tr>
                <th>현재상태</th>
                <td>
                    <?php $lastStatus = isset($last['scanNm']) ? $last['scanNm'] : ''; ?>
                    <span class="<?= ($lastStatus == '배송완료') ? 'cj-badge cj-badge-complete' : 'cj-badge' ?>">
                        <?= htmlspecialchars($lastStatus, ENT_QUOTES, 'UTF-8') ?>
                    </span>
                </td>
            </tr>
            <tr><th>상품명</th>  <td><?= htmlspecialchars(isset($info['itemNm'])  ? $info['itemNm']  : '', ENT_QUOTES, 'UTF-8') ?></td></tr>
            <tr><th>수량</th>    <td><?= htmlspecialchars(isset($info['qty'])     ? $info['qty']     : '', ENT_QUOTES, 'UTF-8') ?></td></tr>
            <tr><th>보내는 분</th><td><?= htmlspecialchars(isset($info['sendrNm']) ? $info['sendrNm'] : '', ENT_QUOTES, 'UTF-8') ?></td></tr>
            <tr><th>받는 분</th> <td><?= htmlspecialchars(isset($info['rcvrNm'])  ? $info['rcvrNm']  : '', ENT_QUOTES, 'UTF-8') ?></td></tr>
        </table>
    </div>

    <div class="cj-card">
        <div class="cj-card-title">배송 진행상황</div>
        <?php if (count($details) > 0) { ?>
            <div class="table-responsive">
                <table class="cj-track-table">
                    <thead><tr><th>시간</th><th>상태</th><th>지점</th><th>내용</th></tr></thead>
                    <tbody>
                    <?php foreach ($details as $row) {
                        $scanNm = isset($row['scanNm']) ? $row['scanNm'] : '';
                    ?>
                    <tr>
                        <td><?= htmlspecialchars(isset($row['dTime'])      ? $row['dTime']      : '', ENT_QUOTES, 'UTF-8') ?></td>
                        <td><span class="<?= ($scanNm == '배송완료') ? 'cj-badge cj-badge-complete' : 'cj-badge' ?>"><?= htmlspecialchars($scanNm, ENT_QUOTES, 'UTF-8') ?></span></td>
                        <td><?= htmlspecialchars(isset($row['regBranNm']) ? $row['regBranNm'] : '', ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars(isset($row['crgNm'])     ? $row['crgNm']     : '', ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } else { ?>
            <p class="text-muted" style="padding:16px;">배송 진행 내역이 없습니다.</p>
        <?php } ?>
    </div>

<?php } ?>

<?php include('../footer.php'); ?>
