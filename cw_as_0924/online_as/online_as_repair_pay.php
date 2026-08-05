<?php
include("../common.php");
include("../def_inc.php");

$searchData       = isset($_GET['searchData'])       ? $_GET['searchData']       : '';
$searchValuePhone = isset($_GET['searchValuePhone']) ? $_GET['searchValuePhone'] : '';

$sql = "SELECT * FROM as_parcel_service WHERE reg_num='$searchData' AND customer_phone='$searchValuePhone'";
$result     = $db->result($sql);
$result_cnt = $result ? mysqli_num_rows($result) : 0;

// 이미 발급된 수리비 가상계좌 확인 (P_OID = reg_num + "_R")
$repair_oid = $searchData . "_R";
$row3 = $db->object("TB_INICIS_RETURN", "where P_OID='$repair_oid'");
?>
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>캐치웰 수리비 입금 안내</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<style>
    :root {
        --primary-color: #007AFF;
        --success-color: #34C759;
        --bg-gray: #F8F9FA;
        --text-dark: #212529;
        --text-muted: #6C757D;
        --card-radius: 16px;
    }
    body { background-color: var(--bg-gray); font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; color: var(--text-dark); line-height: 1.5; }
    .container-mobile { max-width: 500px; margin: 0 auto; padding: 20px; }
    .logo-header { text-align: center; padding: 30px 0; }
    .logo-header img { max-width: 140px; height: auto; }
    .main-card { background: #fff; border-radius: var(--card-radius); box-shadow: 0 4px 20px rgba(0,0,0,0.05); padding: 24px; margin-bottom: 24px; position: relative; overflow: hidden; }
    .page-title { font-size: 20px; font-weight: 700; margin-bottom: 24px; text-align: center; color: var(--text-dark); }
    .complete-badge { display: inline-block; background-color: #E8F9EE; color: var(--success-color); font-size: 13px; font-weight: 700; padding: 6px 14px; border-radius: 50px; margin-bottom: 12px; }
    .vbank-info-table { width: 100%; background-color: #fdfdfd; border: 1px solid #f0f0f0; border-radius: 12px; padding: 20px; margin-bottom: 20px; }
    .vbank-row { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 15px; }
    .vbank-row:last-child { margin-bottom: 0; }
    .vbank-label { color: var(--text-muted); }
    .vbank-value { font-weight: 700; text-align: right; }
    .vbank-account { color: var(--primary-color); font-size: 18px; letter-spacing: -0.5px; cursor: pointer; transition: opacity 0.2s; position: relative; }
    .vbank-account:active { opacity: 0.6; }
    .copy-hint { font-size: 11px; font-weight: normal; color: var(--text-muted); margin-top: 4px; }
    #toast-msg { visibility: hidden; min-width: 200px; background-color: #333; color: #fff; text-align: center; border-radius: 8px; padding: 12px; position: fixed; z-index: 1000; left: 50%; bottom: 30px; transform: translateX(-50%); font-size: 14px; box-shadow: 0 4px 12px rgba(0,0,0,0.2); }
    #toast-msg.show { visibility: visible; animation: fadein 0.5s, fadeout 0.5s 2.0s; }
    @keyframes fadein { from {bottom: 0; opacity: 0;} to {bottom: 30px; opacity: 1;} }
    @keyframes fadeout { from {bottom: 30px; opacity: 1;} to {bottom: 0; opacity: 0;} }
    .info-box { background-color: #f1f7ff; border-radius: 12px; padding: 16px; margin-bottom: 24px; }
    .info-box h4 { font-size: 15px; font-weight: 700; color: var(--primary-color); margin-bottom: 8px; }
    .info-box ul { padding-left: 18px; margin-bottom: 0; font-size: 13px; color: #44546a; }
    .info-box li { margin-bottom: 6px; }
    .form-group { margin-bottom: 16px; }
    .form-label { font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px; margin-left: 4px; }
    .form-control-custom { background-color: #f8f9fa; border: 1px solid #eee; border-radius: 10px; padding: 12px 16px; font-size: 15px; color: var(--text-dark); font-weight: 500; width: 100%; }
    .btn-issue { background-color: var(--primary-color); color: #fff; border: none; border-radius: 12px; padding: 16px; font-size: 16px; font-weight: 700; width: 100%; margin-top: 10px; transition: all 0.2s; box-shadow: 0 4px 12px rgba(0,122,255,0.2); }
    .btn-issue:active { transform: scale(0.98); background-color: #0062cc; }
    .empty-state { text-align: center; padding: 40px 20px; color: var(--text-muted); }
    .price-text { color: var(--primary-color); font-weight: 800; }
</style>
<script>
    function on_pay() {
        const myform = document.mobileweb;
        myform.action = "https://mobile.inicis.com/smart/payment/";
        myform.target = "_self";
        myform.submit();
    }
    function copyToClipboard(text) {
        const tempElem = document.createElement('textarea');
        tempElem.value = text;
        document.body.appendChild(tempElem);
        tempElem.select();
        document.execCommand('copy');
        document.body.removeChild(tempElem);
        const toast = document.getElementById("toast-msg");
        toast.className = "show";
        setTimeout(function(){ toast.className = toast.className.replace("show", ""); }, 2500);
    }
</script>
</head>
<body>
<div id="toast-msg">계좌번호가 복사되었습니다.</div>
<div class="container-mobile">
    <header class="logo-header">
        <img src="https://catchwell.com/web/upload/NNEditor/20240315/89675d536c942fee35df45d6d52e920f.png" alt="Catchwell Logo">
    </header>
    <main class="main-card">
        <?php if ($row3 && $row3->P_OID == $repair_oid): ?>
            <!-- 이미 발급된 가상계좌 표시 -->
            <div class="text-center">
                <span class="complete-badge">계좌발급 완료</span>
                <h3 class="page-title" style="margin-bottom: 30px;">수리비 입금 정보</h3>
            </div>
            <div class="vbank-info-table">
                <div class="vbank-row">
                    <span class="vbank-label">접수번호</span>
                    <span class="vbank-value"><?php echo $searchData; ?></span>
                </div>
                <div class="vbank-row">
                    <span class="vbank-label">은행명</span>
                    <span class="vbank-value"><?php echo $row3->P_FN_NM; ?></span>
                </div>
                <div class="vbank-row" style="flex-direction: column; align-items: flex-start;">
                    <span class="vbank-label" style="margin-bottom: 2px;">가상계좌번호</span>
                    <span class="vbank-value vbank-account" onclick="copyToClipboard('<?php echo $row3->P_VACT_NUM; ?>')">
                        <?php echo $row3->P_VACT_NUM; ?>
                        <div class="copy-hint">(번호를 누르면 복사됩니다)</div>
                    </span>
                </div>
                <hr style="border-top: 1px dashed #ddd; margin: 15px 0;">
                <div class="vbank-row">
                    <span class="vbank-label">입금하실 금액</span>
                    <span class="vbank-value price-text" style="font-size: 20px;"><?php echo number_format($row3->P_AMT); ?>원</span>
                </div>
            </div>
            <div class="info-box" style="background-color: #fff9f0;">
                <h4 style="color: #d48806;">※ 입금 전 주의사항</h4>
                <ul style="color: #855d10;">
                    <li>반드시 위에 안내된 금액과 동일한 금액을 입금하셔야 합니다.</li>
                    <li>입금이 확인되면 수리 작업이 자동으로 시작됩니다.</li>
                </ul>
            </div>
        <?php else: ?>
            <!-- 수리비 가상계좌 발급 -->
            <h3 class="page-title">수리비 가상계좌 발급</h3>
            <div class="info-box">
                <h4>※ 안내사항</h4>
                <ul>
                    <li>견적서에 안내된 수리비를 아래 가상계좌로 입금해 주세요.</li>
                    <li>입금 확인 후 수리 작업이 진행됩니다.</li>
                </ul>
            </div>
            <?php if ($result_cnt == 0): ?>
                <div class="empty-state">
                    <p>조회된 접수 결과가 없습니다.</p>
                </div>
            <?php else:
                mysqli_data_seek($result, 0);
                $row_as = mysqli_fetch_array($result);
                $repair_price = (int)$row_as['price'];
            ?>
                <?php if ($repair_price <= 0): ?>
                    <div class="empty-state">
                        <p>수리비가 설정되지 않았습니다.<br>고객센터(070-7777-6752)로 문의해 주세요.</p>
                    </div>
                <?php else: ?>
                    <form name="mobileweb" method="post" accept-charset="euc-kr">
                        <input type="hidden" name="P_INI_PAYMENT" value="VBANK">
                        <input type="hidden" name="P_MID" value="CAEcatca07">
                        <input type="hidden" name="P_GOODS" value="수리비">
                        <input type="hidden" name="P_EMAIL" value="">
                        <input type="hidden" name="P_NEXT_URL" value="https://csadmin.catchwell.com/cw_as_0924/pg_m/INImobile_mo_repair_return.php">
                        <input type="hidden" name="P_NOTI_URL" value="https://csadmin.catchwell.com/cw_as_0924/pg_m/mx_rnoti.php">
                        <input type="hidden" name="P_CHARSET" value="utf8">
                        <input type="hidden" name="P_RESERVED" value="vbank_receipt=N&centerCd=Y">

                        <div class="form-group">
                            <label class="form-label">접수번호</label>
                            <input type="text" name="P_OID" value="<?php echo $repair_oid; ?>" class="form-control-custom" readonly>
                        </div>
                        <div class="form-group">
                            <label class="form-label">수리비 금액</label>
                            <input type="text" name="P_AMT" value="<?php echo $repair_price; ?>" class="form-control-custom price-text" readonly>
                        </div>
                        <div class="form-group">
                            <label class="form-label">고객명</label>
                            <input type="text" name="P_UNAME" value="<?php echo $row_as['customer_name']; ?>" class="form-control-custom" readonly>
                        </div>
                        <div class="form-group">
                            <label class="form-label">연락처</label>
                            <input type="text" name="P_MOBILE" value="<?php echo $row_as['customer_phone']; ?>" class="form-control-custom" readonly>
                        </div>
                        <button type="button" onclick="on_pay()" class="btn-issue">가상계좌 발급하기</button>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>
    </main>
    <footer class="text-center pb-4">
        <p style="font-size: 12px; color: #adb5bd;">© Catchwell. All rights reserved.</p>
    </footer>
</div>
</body>
</html>
