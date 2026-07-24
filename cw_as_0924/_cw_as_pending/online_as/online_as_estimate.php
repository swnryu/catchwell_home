<?php
error_reporting(E_ALL);
ini_set('display_errors', TRUE);

include("../common.php");

$searchData       = isset($_GET['searchData'])       ? $_GET['searchData']       : '';
$searchValuePhone = isset($_GET['searchValuePhone']) ? $_GET['searchValuePhone'] : '';

$search_sql = "SELECT * FROM as_parcel_service
               WHERE reg_num='$searchData' AND customer_phone='$searchValuePhone'
               ORDER BY reg_date DESC";
$result     = $db->result($search_sql);
$result_cnt = $result ? mysqli_num_rows($result) : 0;

// 이미 발급된 수리비 가상계좌 확인 (P_OID = reg_num + "_R")
$repair_oid = $searchData . "_R";
$vbank      = $db->object("TB_INICIS_RETURN", "where P_OID='$repair_oid'");

$row_as = null;
if ($result_cnt > 0) {
    mysqli_data_seek($result, 0);
    $row_as = mysqli_fetch_array($result);
    // 메모 태그 제거
    $memo = $row_as['admin_memo'];
    foreach (array('(V)','(R)','(H)','(S)','(M)','[ETC]') as $tag) {
        $memo = str_replace($tag, '', $memo);
    }
    $memo = trim($memo);

    $pic_name = isset($row_as['pic_name']) ? $row_as['pic_name'] : '';
    if ($pic_name === '이승환') {
        $engineer_phone = '010-4071-8720';
    } else if ($pic_name === '정승호') {
        $engineer_phone = '070-7777-6752';
    } else {
        $engineer_phone = '070-7777-6752';
    }

    // 수리사진 조회
    $reg_num_s = mysqli_real_escape_string($db->db_conn, $row_as['reg_num']);
    $photos_sql = "SELECT * FROM as_repair_photos WHERE reg_num='$reg_num_s' ORDER BY reg_date ASC";
    $photos_result = @$db->result($photos_sql);
    $repair_photos = array();
    if ($photos_result && mysqli_num_rows($photos_result) > 0) {
        while ($pr = mysqli_fetch_object($photos_result)) {
            $repair_photos[] = $pr;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>캐치웰 A/S 견적서</title>
<style>
/* ── 기본 리셋 ── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
    --blue:    #2563EB;
    --blue-lt: #EFF6FF;
    --green:   #16A34A;
    --green-lt:#F0FDF4;
    --gray-50: #F8FAFC;
    --gray-100:#F1F5F9;
    --gray-200:#E2E8F0;
    --gray-400:#94A3B8;
    --gray-600:#475569;
    --gray-900:#0F172A;
    --red-lt:  #FFF7ED;
    --red:     #C2410C;
    --radius:  16px;
    --shadow:  0 2px 16px rgba(15,23,42,.08);
}

body {
    background: var(--gray-50);
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    color: var(--gray-900);
    min-height: 100vh;
    padding-bottom: 40px;
}

/* ── 레이아웃 ── */
.wrap { max-width: 480px; margin: 0 auto; padding: 0 16px; }

/* ── 헤더 ── */
.top-bar {
    background: #fff;
    border-bottom: 1px solid var(--gray-200);
    padding: 14px 20px;
    text-align: center;
    position: sticky;
    top: 0;
    z-index: 10;
}
.top-bar img { height: 28px; }

/* ── 히어로 배지 ── */
.hero {
    padding: 24px 0 8px;
    text-align: center;
}
.hero .badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: var(--blue-lt);
    color: var(--blue);
    font-size: 13px;
    font-weight: 700;
    padding: 6px 14px;
    border-radius: 999px;
    margin-bottom: 10px;
}
.hero h1 {
    font-size: 22px;
    font-weight: 800;
    color: var(--gray-900);
    line-height: 1.3;
}
.hero p {
    font-size: 13px;
    color: var(--gray-400);
    margin-top: 4px;
}

/* ── 카드 ── */
.card {
    background: #fff;
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    padding: 20px;
    margin-top: 16px;
}
.card-title {
    font-size: 12px;
    font-weight: 700;
    color: var(--gray-400);
    text-transform: uppercase;
    letter-spacing: .06em;
    margin-bottom: 14px;
}

/* ── 견적 정보 행 ── */
.info-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 9px 0;
    border-bottom: 1px solid var(--gray-100);
    gap: 12px;
}
.info-row:last-child { border-bottom: none; }
.info-label {
    font-size: 13px;
    color: var(--gray-400);
    flex-shrink: 0;
    min-width: 72px;
    padding-top: 1px;
}
.info-value {
    font-size: 14px;
    font-weight: 600;
    color: var(--gray-900);
    text-align: right;
    word-break: keep-all;
    line-height: 1.5;
}
.info-value.memo {
    text-align: left;
    font-weight: 500;
    color: var(--gray-600);
    white-space: pre-wrap;
}

/* ── 수리비 강조 ── */
.price-block {
    background: var(--blue-lt);
    border-radius: 12px;
    padding: 18px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 4px;
}
.price-block .lbl { font-size: 14px; color: var(--blue); font-weight: 600; }
.price-block .amt { font-size: 26px; font-weight: 900; color: var(--blue); }
.price-block .unit { font-size: 16px; font-weight: 700; color: var(--blue); }

/* ── 가상계좌 카드 ── */
.vbank-card {
    background: var(--green-lt);
    border: 1.5px solid #BBF7D0;
    border-radius: var(--radius);
    padding: 20px;
    margin-top: 16px;
}
.vbank-card .vc-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    font-weight: 700;
    color: var(--green);
    margin-bottom: 16px;
}
.vbank-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #D1FAE5;
    font-size: 14px;
}
.vbank-row:last-child { border-bottom: none; padding-bottom: 0; }
.vbank-row .lbl { color: var(--gray-600); }
.vbank-row .val { font-weight: 700; color: var(--gray-900); }

.account-box {
    background: #fff;
    border: 1px solid #6EE7B7;
    border-radius: 10px;
    padding: 14px 16px;
    text-align: center;
    cursor: pointer;
    margin-top: 12px;
    transition: background .15s;
    -webkit-tap-highlight-color: transparent;
}
.account-box:active { background: #F0FDF4; }
.account-num {
    display: block;
    font-size: 22px;
    font-weight: 900;
    color: var(--green);
    letter-spacing: -.5px;
    margin-bottom: 4px;
}
.copy-hint { font-size: 11px; color: var(--gray-400); }

.vbank-amt {
    margin-top: 14px;
    text-align: center;
    font-size: 13px;
    color: var(--gray-600);
}
.vbank-amt strong { font-size: 20px; color: var(--green); }

/* ── 발급 버튼 카드 ── */
.issue-card {
    background: #fff;
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    padding: 20px;
    margin-top: 16px;
}
.issue-card .guide {
    font-size: 13px;
    color: var(--gray-600);
    line-height: 1.6;
    margin-bottom: 16px;
}
.btn-issue {
    display: block;
    width: 100%;
    padding: 16px;
    background: var(--blue);
    color: #fff;
    font-size: 16px;
    font-weight: 700;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(37,99,235,.35);
    transition: background .15s, transform .1s;
    -webkit-tap-highlight-color: transparent;
}
.btn-issue:active { background: #1D4ED8; transform: scale(.98); }

/* ── 주의사항 ── */
.notice {
    background: var(--red-lt);
    border-radius: var(--radius);
    padding: 16px 20px;
    margin-top: 16px;
}
.notice .n-title {
    font-size: 13px;
    font-weight: 700;
    color: var(--red);
    margin-bottom: 10px;
}
.notice ul {
    padding-left: 16px;
    font-size: 12px;
    color: #9A3412;
    line-height: 1.7;
}

/* ── 고객센터 ── */
.cs-bar {
    text-align: center;
    margin-top: 20px;
    padding: 14px;
    background: #fff;
    border-radius: var(--radius);
    box-shadow: var(--shadow);
}
.cs-bar p { font-size: 12px; color: var(--gray-400); margin-bottom: 4px; }
.cs-bar a {
    font-size: 18px;
    font-weight: 800;
    color: var(--blue);
    text-decoration: none;
}

/* ── 빈 상태 ── */
.empty { text-align: center; padding: 60px 20px; color: var(--gray-400); font-size: 15px; }

/* ── 토스트 ── */
#toast {
    position: fixed; bottom: 28px; left: 50%; transform: translateX(-50%);
    background: #1E293B; color: #fff; font-size: 13px; font-weight: 600;
    padding: 11px 22px; border-radius: 999px;
    opacity: 0; pointer-events: none;
    transition: opacity .3s;
    white-space: nowrap;
    z-index: 999;
}
#toast.show { opacity: 1; }
</style>
</head>
<body>

<div id="toast">계좌번호가 복사되었습니다 ✓</div>

<!-- 사진 라이트박스 -->
<div id="photo-overlay" onclick="this.style.display='none'"
     style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.9); z-index:9999; cursor:pointer;">
    <img id="photo-overlay-img" src=""
         style="max-width:94%; max-height:90%; position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); border-radius:8px;">
    <span style="position:absolute; top:16px; right:20px; color:#fff; font-size:28px; line-height:1;">×</span>
</div>

<!-- 상단 로고 -->
<header class="top-bar">
    <img src="https://catchwell.com/web/upload/NNEditor/20240315/89675d536c942fee35df45d6d52e920f.png" alt="캐치웰">
</header>

<div class="wrap">

<?php if ($result_cnt === 0 || $row_as === null): ?>
    <div class="empty">조회된 접수 정보가 없습니다.</div>

<?php else:
    $repair_price = (int)$row_as['price'];
?>

    <!-- 히어로 -->
    <div class="hero">
        <div class="badge">
            <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414L8.414 15l-4.121-4.121a1 1 0 011.414-1.414L8.414 12.172l6.879-6.879a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
            A/S 견적서
        </div>
        <h1>수리 견적 안내</h1>
        <p>아래 내용을 확인하신 후 수리비를 입금해 주세요.</p>
    </div>

    <!-- 견적 정보 카드 -->
    <div class="card">
        <div class="card-title">접수 정보</div>
        <div class="info-row">
            <span class="info-label">접수번호</span>
            <span class="info-value"><?php echo htmlspecialchars($row_as['reg_num']); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">고객명</span>
            <span class="info-value"><?php echo htmlspecialchars($row_as['customer_name']); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">제품명</span>
            <span class="info-value"><?php echo htmlspecialchars($row_as['product_name']); ?></span>
        </div>
        <div class="info-row">
            <span class="info-label">고장증상</span>
            <span class="info-value memo"><?php echo nl2br(htmlspecialchars($row_as['customer_desc'])); ?></span>
        </div>
        <?php if ($memo): ?>
        <div class="info-row">
            <span class="info-label">점검결과</span>
            <span class="info-value memo"><?php echo nl2br(htmlspecialchars($memo)); ?></span>
        </div>
        <?php endif; ?>
        <div class="info-row">
            <span class="info-label">담당자</span>
            <span class="info-value">
                <a href="tel:<?php echo preg_replace('/[^0-9]/', '', $engineer_phone); ?>" style="color:var(--blue);font-weight:600;text-decoration:none;"><?php echo $engineer_phone; ?></a>
            </span>
        </div>
    </div>

    <?php if (count($repair_photos) > 0): ?>
    <!-- 수리사진 갤러리 -->
    <div class="card">
        <div class="card-title">수리사진</div>
        <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:8px;">
            <?php foreach ($repair_photos as $rp): ?>
            <div onclick="openPhoto('files/repair/<?php echo htmlspecialchars($rp->file_name); ?>')"
                 style="cursor:pointer; border-radius:8px; overflow:hidden; aspect-ratio:1; background:#f1f5f9;">
                <img src="files/repair/<?php echo htmlspecialchars($rp->file_name); ?>"
                     style="width:100%; height:100%; object-fit:cover; display:block;">
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- 수리비 강조 -->
    <div class="card" style="padding: 16px 20px;">
        <div class="price-block">
            <span class="lbl">수리비용</span>
            <div>
                <span class="amt"><?php echo number_format($repair_price); ?></span>
                <span class="unit">원</span>
            </div>
        </div>
    </div>

<?php if ((int)$row_as['process_state'] === 10): ?>
    <!-- ── 폐기 신청 접수됨 ── -->
    <div class="notice">
        <div class="n-title">폐기 신청이 접수되었습니다</div>
        <ul>
            <li>담당자가 확인 후 처리해 드립니다.</li>
        </ul>
    </div>
<?php elseif ((int)$row_as['process_state'] === 11): ?>
    <!-- ── 반송 신청 접수됨 ── -->
    <div class="notice">
        <div class="n-title">반송 신청이 접수되었습니다</div>
        <ul>
            <li>담당자가 확인 후 처리해 드립니다.</li>
        </ul>
    </div>
<?php else: ?>

<?php if ($vbank && $vbank->P_OID === $repair_oid): ?>
    <!-- ── 이미 발급된 가상계좌 ── -->
    <div class="vbank-card">
        <div class="vc-title">
            <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            가상계좌 발급 완료
        </div>
        <div class="vbank-row">
            <span class="lbl">은행</span>
            <span class="val"><?php echo htmlspecialchars($vbank->P_FN_NM); ?></span>
        </div>
        <div class="account-box" onclick="copyAcct('<?php echo htmlspecialchars($vbank->P_VACT_NUM); ?>')">
            <span class="account-num"><?php echo htmlspecialchars($vbank->P_VACT_NUM); ?></span>
            <span class="copy-hint">터치하면 계좌번호가 복사됩니다</span>
        </div>
        <div class="vbank-amt">
            입금 금액&nbsp;&nbsp;<strong><?php echo number_format($vbank->P_AMT); ?>원</strong>
        </div>
    </div>

    <div class="notice">
        <div class="n-title">※ 입금 시 주의사항</div>
        <ul>
            <li>반드시 안내된 금액과 동일하게 입금해 주세요.</li>
            <li>입금 확인 후 수리 작업이 자동으로 시작됩니다.</li>
        </ul>
    </div>

<?php elseif ($repair_price > 0): ?>
    <!-- ── 가상계좌 발급 폼 ── -->
    <div class="issue-card">
        <p class="guide">수리 진행을 원하시면 가상계좌를 발급받아 수리비를 입금해 주세요.<br>입금 확인 즉시 수리가 시작됩니다.</p>
        <form name="mobileweb" method="post" accept-charset="euc-kr">
            <input type="hidden" name="P_INI_PAYMENT" value="VBANK">
            <input type="hidden" name="P_MID"         value="CAEcatca07">
            <input type="hidden" name="P_GOODS"       value="수리비">
            <input type="hidden" name="P_EMAIL"       value="">
            <input type="hidden" name="P_NEXT_URL"    value="https://csadmin.catchwell.com/cw_as_0924/pg_m/INImobile_mo_repair_return.php">
            <input type="hidden" name="P_NOTI_URL"    value="https://csadmin.catchwell.com/cw_as_0924/pg_m/mx_rnoti.php">
            <input type="hidden" name="P_CHARSET"     value="utf8">
            <input type="hidden" name="P_RESERVED"    value="vbank_receipt=N&centerCd=Y">
            <input type="hidden" name="P_OID"         value="<?php echo htmlspecialchars($repair_oid); ?>">
            <input type="hidden" name="P_AMT"         value="<?php echo $repair_price; ?>">
            <input type="hidden" name="P_UNAME"       value="<?php echo htmlspecialchars($row_as['customer_name']); ?>">
            <input type="hidden" name="P_MOBILE"      value="<?php echo htmlspecialchars($row_as['customer_phone']); ?>">
            <button type="button" class="btn-issue" onclick="onPay()">
                가상계좌 발급하기
            </button>
        </form>
    </div>

    <div class="notice">
        <div class="n-title">※ 안내사항</div>
        <ul>
            <li>가상계좌는 발급 후 7일 이내에 입금해 주세요.</li>
            <li>입금 확인 후 수리가 시작됩니다.</li>
            <li>수리 거부 시 반송 처리됩니다.</li>
        </ul>
    </div>

<?php else: ?>
    <div class="card">
        <p style="font-size:14px; color:var(--gray-600); text-align:center; padding: 10px 0;">
            수리비가 아직 설정되지 않았습니다.<br>고객센터로 문의해 주세요.
        </p>
    </div>
<?php endif; ?>

    <!-- ── 폐기/반송 신청 (가상계좌 발급 여부와 무관하게 항상 노출) ── -->
    <div class="card" style="padding:16px 20px;">
        <p style="font-size:13px; color:var(--gray-600); margin-bottom:12px;">수리를 원하지 않으시면 아래에서 신청해 주세요. 신청 후에는 취소할 수 없습니다.</p>
        <form name="disposeReturnForm" method="post" action="online_as_dispose_return_ok.php">
            <input type="hidden" name="reg_num" value="<?php echo htmlspecialchars($searchData); ?>">
            <input type="hidden" name="searchValuePhone" value="<?php echo htmlspecialchars($searchValuePhone); ?>">
            <input type="hidden" name="action" value="">
            <button type="button" class="btn-issue" style="background:#6b7280; margin-bottom:8px;" onclick="submitDisposeReturn('dispose')">폐기 신청</button>
            <button type="button" class="btn-issue" style="background:#9ca3af;" onclick="submitDisposeReturn('return')">반송 신청</button>
        </form>
    </div>

<?php endif; ?>

    <!-- 담당 엔지니어 -->
    <div class="cs-bar">
        <p>문의사항은 담당 엔지니어에게 연락해 주세요</p>
        <a href="tel:<?php echo preg_replace('/[^0-9]/', '', $engineer_phone); ?>"><?php echo $engineer_phone; ?></a>
    </div>

<?php endif; ?>
</div><!-- /wrap -->

<script>
function submitDisposeReturn(action) {
    var label = (action === 'dispose') ? '폐기' : '반송';
    if (!confirm('정말 ' + label + ' 신청하시겠습니까? 신청 후에는 취소할 수 없습니다.')) { return; }
    var f = document.disposeReturnForm;
    f.elements.action.value = action;
    f.submit();
}

function onPay() {
    var f = document.mobileweb;
    f.action = "https://mobile.inicis.com/smart/payment/";
    f.target = "_self";
    f.submit();
}

function copyAcct(num) {
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(num).then(showToast);
    } else {
        var el = document.createElement('textarea');
        el.value = num;
        el.style.position = 'fixed';
        el.style.opacity = '0';
        document.body.appendChild(el);
        el.focus(); el.select();
        document.execCommand('copy');
        document.body.removeChild(el);
        showToast();
    }
}

function openPhoto(src) {
    document.getElementById('photo-overlay-img').src = src;
    document.getElementById('photo-overlay').style.display = 'block';
}

function showToast() {
    var t = document.getElementById('toast');
    t.classList.add('show');
    setTimeout(function() { t.classList.remove('show'); }, 2200);
}
</script>
</body>
</html>
