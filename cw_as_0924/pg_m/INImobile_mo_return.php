<?php
include("../common.php");
require_once('libs/properties.php');
$prop = new properties();

$P_STATUS    = $_REQUEST["P_STATUS"];
$P_RMESG1    = $_REQUEST["P_RMESG1"];
$P_TID       = $_REQUEST["P_TID"];
$P_REQ_URL   = $_REQUEST["P_REQ_URL"];
$P_NOTI      = $_REQUEST["P_NOTI"];
$P_AMT       = $_REQUEST["P_AMT"];

$out = array(); // 결과 데이터 담을 배열 초기화

if ($_REQUEST["P_STATUS"] === "00") {
    $id_merchant = substr($P_TID,'10','10');
    $data = array(
        'P_MID' => $id_merchant,
        'P_TID' => $P_TID
    );

    $idc_name = $_REQUEST["idc_name"];
    $P_REQ_URL = $prop->getAuthUrl($idc_name); 

    if (strcmp($P_REQ_URL, $_REQUEST["P_REQ_URL"]) == 0) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $_REQUEST["P_REQ_URL"]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_POST, 1);

        $response = curl_exec($ch);
        curl_close($ch);

        parse_str($response, $out);
        
        $P_STATUS = $out['P_STATUS'];
        $P_RMESG1 = $out['P_RMESG1'];
        $P_TID = $out['P_TID'];
        $P_UNAME = $out['P_UNAME'];
        $P_OID = $out['P_OID'];
        $P_AMT = $out['P_AMT'];
        $P_AUTH_DT = $out['P_AUTH_DT'];
        $P_FN_NM = $out['P_FN_NM'];
        $P_VACT_NUM = $out['P_VACT_NUM'];
        $P_VACT_NAME = $out['P_VACT_NAME'];

        $sql = "INSERT INTO TB_INICIS_RETURN (
            P_STATUS, P_RMESG1, P_TID, P_UNAME, P_OID, P_AMT, P_AUTH_DT, P_FN_NM, P_VACT_NUM, P_VACT_NAME
        ) VALUES (
            '$P_STATUS', '$P_RMESG1', '$P_TID', '$P_UNAME', '$P_OID', '$P_AMT', '$P_AUTH_DT', '$P_FN_NM', '$P_VACT_NUM', '$P_VACT_NAME'
        )";
        $db->result($sql);
    }
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>캐치웰 가상계좌 안내</title>
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

        body {
            background-color: var(--bg-gray);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: var(--text-dark);
            line-height: 1.5;
        }

        .container-mobile {
            max-width: 500px;
            margin: 0 auto;
            padding: 20px;
        }

        .logo-header {
            text-align: center;
            padding: 30px 0;
        }

        .logo-header img {
            max-width: 140px;
            height: auto;
        }

        .main-card {
            background: #fff;
            border-radius: var(--card-radius);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            padding: 24px;
            margin-bottom: 24px;
        }

        .page-title {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 8px;
            text-align: center;
        }

        .page-subtitle {
            font-size: 14px;
            color: var(--text-muted);
            text-align: center;
            margin-bottom: 24px;
        }

        .complete-badge {
            display: block;
            width: fit-content;
            margin: 0 auto 12px;
            background-color: #E8F9EE;
            color: var(--success-color);
            font-size: 13px;
            font-weight: 700;
            padding: 6px 14px;
            border-radius: 50px;
        }

        .vbank-info-table {
            width: 100%;
            background-color: #fdfdfd;
            border: 1px solid #f0f0f0;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .vbank-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 15px;
        }

        .vbank-row:last-child { margin-bottom: 0; }
        .vbank-label { color: var(--text-muted); }
        .vbank-value { font-weight: 700; text-align: right; }
        
        .vbank-account-box {
            background: #f8fbff;
            border: 1px solid #e1eeff;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            margin-top: 10px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .vbank-account-box:active {
            background: #edf5ff;
            transform: scale(0.98);
        }

        .vbank-account-number {
            display: block;
            font-size: 20px;
            font-weight: 800;
            color: var(--primary-color);
            margin-bottom: 4px;
            letter-spacing: -0.5px;
        }

        .copy-hint {
            font-size: 11px;
            color: var(--text-muted);
        }

        .info-box {
            background-color: #fff9f0;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 24px;
        }

        .info-box h4 {
            font-size: 14px;
            font-weight: 700;
            color: #d48806;
            margin-bottom: 8px;
        }

        .info-box ul {
            padding-left: 18px;
            margin-bottom: 0;
            font-size: 13px;
            color: #855d10;
        }

        /* Toast Message */
        #toast-msg {
            visibility: hidden;
            min-width: 200px;
            background-color: #333;
            color: #fff;
            text-align: center;
            border-radius: 8px;
            padding: 12px;
            position: fixed;
            z-index: 1000;
            left: 50%;
            bottom: 30px;
            transform: translateX(-50%);
            font-size: 14px;
        }
        #toast-msg.show {
            visibility: visible;
            animation: fadein 0.5s, fadeout 0.5s 2.0s;
        }

        @keyframes fadein { from {bottom: 0; opacity: 0;} to {bottom: 30px; opacity: 1;} }
        @keyframes fadeout { from {bottom: 30px; opacity: 1;} to {bottom: 0; opacity: 0;} }

        .btn-home {
            background-color: var(--primary-color);
            color: #fff;
            border: none;
            border-radius: 12px;
            padding: 16px;
            font-size: 16px;
            font-weight: 700;
            width: 100%;
            text-decoration: none;
            display: block;
            text-align: center;
        }
    </style>
</head>
<body>

<div id="toast-msg">계좌번호가 복사되었습니다.</div>

<div class="container-mobile">
    <header class="logo-header">
        <img src="https://catchwell.com/web/upload/NNEditor/20240315/89675d536c942fee35df45d6d52e920f.png" alt="Catchwell Logo">
    </header>

    <main class="main-card">
        <span class="complete-badge">가상계좌 발급완료</span>
        <h2 class="page-title">입금 안내</h2>
        <p class="page-subtitle">수거 택배비 입금 계좌 정보입니다.</p>

        <?php if ($P_STATUS === "00" && !empty($out)): ?>
            <div class="vbank-info-table">
                <div class="vbank-row">
                    <span class="vbank-label">접수번호</span>
                    <span class="vbank-value"><?php echo htmlspecialchars($out["P_OID"]); ?></span>
                </div>
                <div class="vbank-row">
                    <span class="vbank-label">입금자명</span>
                    <span class="vbank-value"><?php echo htmlspecialchars($out["P_UNAME"]); ?></span>
                </div>
                <div class="vbank-row">
                    <span class="vbank-label">입금은행</span>
                    <span class="vbank-value"><?php echo htmlspecialchars($out["P_FN_NM"]); ?></span>
                </div>
                <div class="vbank-row">
                    <span class="vbank-label">입금금액</span>
                    <span class="vbank-value" style="color:var(--primary-color); font-size:18px;"><?php echo number_format($out["P_AMT"]); ?>원</span>
                </div>
                
                <div class="vbank-account-box" onclick="copyToClipboard('<?php echo $out['P_VACT_NUM']; ?>')">
                    <span class="vbank-account-number"><?php echo htmlspecialchars($out["P_VACT_NUM"]); ?></span>
                    <span class="copy-hint">터치하면 계좌번호가 복사됩니다.</span>
                </div>
            </div>

            <div class="info-box">
                <h4>※ 입금 시 유의사항</h4>
                <ul>
                    <li>반드시 표시된 금액과 <strong>정확히 일치</strong>하게 입금해 주세요.</li>
                    <li>입금이 확인되면 자동으로 수거 접수가 완료됩니다.</li>
                </ul>
            </div>
            
            <!--<a href="" class="btn-home">홈으로 이동</a>-->

        <?php else: ?>
            <div class="text-center py-5">
                <p class="text-danger fw-bold">인증에 실패하였거나 정보가 없습니다.</p>
                <p class="small text-muted"><?php echo htmlspecialchars($P_RMESG1); ?></p>
                <a href="javascript:history.back();" class="btn btn-secondary mt-3">뒤로가기</a>
            </div>
        <?php endif; ?>
    </main>

    <footer class="text-center pb-4">
        <p style="font-size: 12px; color: #adb5bd;">© Catchwell. All rights reserved.</p>
    </footer>
</div>

<script>
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

</body>
</html>