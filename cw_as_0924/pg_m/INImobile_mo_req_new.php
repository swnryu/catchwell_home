<?php
/** Error reporting */
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

include("../common.php");

function showStatus( $state )
{
    switch( $state )
    {
        case 0 : echo "접수중"; break;
        case 1 : echo "접수완료"; break;
        case 2 : echo "수리중"; break;
        case 3 : echo "수리완료"; break;
        case 4 : echo "출고완료"; break;
    }
}

$strSearchCondition = 'reg_num';
$strSearchCondition2 = 'customer_phone';
$searchData = isset($_GET['searchData']) ? $_GET['searchData'] : '';
$searchData2 = isset($_GET['searchValuePhone']) ? $_GET['searchValuePhone'] : '';
$search_sql = "SELECT * FROM as_parcel_service WHERE $strSearchCondition='$searchData' AND $strSearchCondition2='$searchData2' ORDER BY reg_date DESC";

$result = $db->result($search_sql);
$result_cnt = $result ? mysqli_num_rows($result) : 0;

$row3 = $db->object("TB_INICIS_RETURN","where P_OID='$searchData'");
if($row3){
	if($row3->P_OID == $searchData)
	{
		echo "<script>alert('계좌발급이 완료되었습니다.\\n접수번호 : $row3->P_OID\\n입금액 : $row3->P_AMT\\n은행 : $row3->P_FN_NM\\n계좌번호 : $row3->P_VACT_NUM');</script>";
		exit;
	}
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reg_num'])) {
    $update_reg_num = $_POST['reg_num'];
    $update_sql = "UPDATE as_parcel_service SET attached_files = 'YES' WHERE reg_num = '$update_reg_num'";
    if ($db->result($update_sql)) {
        echo "<script>window.open('online_as_estimate_complete.php', '_blank', 'width=500,height=300');</script>";
        exit;
    } else {
        echo "<script>alert('업데이트에 실패하였습니다. 다시 시도해주세요.');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>캐치웰 택배접수시스템</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        :root {
            --primary-color: #007AFF;
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
            margin-bottom: 24px;
            text-align: center;
            color: var(--text-dark);
        }

        .info-box {
            background-color: #f1f7ff;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 24px;
        }

        .info-box h4 {
            font-size: 15px;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
        }

        .info-box ul {
            padding-left: 18px;
            margin-bottom: 0;
            font-size: 13px;
            color: #44546a;
        }

        .info-box li {
            margin-bottom: 6px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-label {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 6px;
            margin-left: 4px;
        }

        .form-control-custom {
            background-color: #f8f9fa;
            border: 1px solid #eee;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 15px;
            color: var(--text-dark);
            font-weight: 500;
            width: 100%;
        }

        .form-control-custom:read-only {
            background-color: #f8f9fa;
            color: #333;
        }

        .btn-issue {
            background-color: var(--primary-color);
            color: #fff;
            border: none;
            border-radius: 12px;
            padding: 16px;
            font-size: 16px;
            font-weight: 700;
            width: 100%;
            margin-top: 10px;
            transition: all 0.2s;
            box-shadow: 0 4px 12px rgba(0, 122, 255, 0.2);
        }

        .btn-issue:active {
            transform: scale(0.98);
            background-color: #0062cc;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-muted);
        }

        .price-text {
            color: var(--primary-color);
            font-weight: 800;
        }
    </style>
    <script>
        function on_pay() {
            const myform = document.mobileweb;
            myform.action = "https://mobile.inicis.com/smart/payment/";
            myform.target = "_self";
            myform.submit();
        }
    </script>
</head>
<body>

<div class="container-mobile">
    <!-- 헤더 로고 -->
    <header class="logo-header">
        <img src="https://catchwell.com/web/upload/NNEditor/20240315/89675d536c942fee35df45d6d52e920f.png" alt="Catchwell Logo">
    </header>

    <main class="main-card">
        <h3 class="page-title">가상계좌 발급</h3>

        <!-- 안내사항 -->
        <div class="info-box">
            <h4>※ 안내사항</h4>
            <ul>
                <li>편도 택배비가 부과됩니다. (발송비용은 캐치웰 부담)</li>
                <li>CJ대한통운을 통해 회수되며 비용은 <span class="price-text">3,500원</span>입니다.</li>
                <li>입금 확인 후 택배기사님이 방문 수거를 진행합니다.</li>
            </ul>
        </div>

        <?php
        if ($result_cnt == 0) {
        ?>
            <div class="empty-state">
                <p>조회된 접수 결과가 없습니다.</p>
            </div>
        <?php
        } else {
            for ($i = 0; $i < $result_cnt; $i++) {
                mysqli_data_seek($result, $i);
                $search_db_row = mysqli_fetch_array($result);
        ?>
            <form name="mobileweb" method="post" accept-charset="euc-kr">
                <!-- Hidden Fields -->
                <input type="hidden" name="P_INI_PAYMENT" value="VBANK">
                <input type="hidden" name="P_MID" value="CAEcatca07">
                <input type="hidden" name="P_GOODS" value="회수 택배비">
                <input type="hidden" name="P_EMAIL" value="">
                <input type="hidden" name="P_NEXT_URL" value="https://csadmin.catchwell.com/cw_as_0924/pg_m/INImobile_mo_return.php">
                <input type="hidden" name="P_NOTI_URL" value="https://csadmin.catchwell.com/cw_as_0924/pg_m/mx_rnoti.php">
                <input type="hidden" name="P_CHARSET" value="utf8">
                <input type="hidden" name="P_RESERVED" value="vbank_receipt=N&centerCd=Y">

                <!-- Visible Input Fields -->
                <div class="form-group">
                    <label class="form-label">접수번호</label>
                    <input type="text" name="P_OID" value="<?php echo $search_db_row['reg_num'] ?>" class="form-control-custom" readonly>
                </div>

                <div class="form-group">
                    <label class="form-label">결제 금액 (택배비)</label>
                    <input type="text" name="P_AMT" value="3500" class="form-control-custom price-text" readonly>
                </div>

                <div class="form-group">
                    <label class="form-label">고객명</label>
                    <input type="text" name="P_UNAME" value="<?php echo $search_db_row['customer_name'] ?>" class="form-control-custom" readonly>
                </div>

                <div class="form-group">
                    <label class="form-label">연락처</label>
                    <input type="text" name="P_MOBILE" value="<?php echo $search_db_row['customer_phone'] ?>" class="form-control-custom" readonly>
                </div>

                <button type="button" onclick="on_pay()" class="btn-issue">가상계좌 발급하기</button>
            </form>
        <?php
            }
        }
        ?>
    </main>

    <footer class="text-center pb-4">
        <p style="font-size: 12px; color: #adb5bd;">© Catchwell. All rights reserved.</p>
    </footer>
</div>

</body>
</html>