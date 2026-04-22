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
        case 0 :
            echo "접수중";
            break;
        case 1 :
            echo "접수완료";
            break;
        case 2 :
            echo "수리중";
            break;
        case 3 :
            echo "수리완료";
            break;
        case 4 :
            echo "출고완료";
            break;
    }
}

$strSearchCondition = 'reg_num';
$strSearchCondition2 = 'customer_phone';
$searchData = $_GET['searchData'];
$searchData2 = $_GET['searchValuePhone'];
$search_sql = "SELECT * FROM as_parcel_service WHERE $strSearchCondition='$searchData' AND $strSearchCondition2='$searchData2' ORDER BY reg_date DESC";

$result = $db->result($search_sql);
$result_cnt = mysqli_num_rows($result);

$row3 = $db->object("TB_INICIS_RETURN","where P_OID='$searchData'");
if($row3){
	if($row3->P_OID == $searchData)
	{
		echo "<script>alert('계좌발급이 완료되었습니다.\\n접수번호 : $row3->P_OID\\n입금액 : $row3->P_AMT\\n은행 : $row3->P_FN_NM\\n계좌번호 : $row3->P_VACT_NUM');</script>";
		exit;
	}
}
// 업데이트 버튼이 클릭되었을 때의 처리
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>캐치웰 택배접수시스템</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">

    <style>
		main.cont {
		margin: auto;
		padding: 5px;
		padding-top: 20px;
		}
		
		main .card .card_tit h3 {
		font-size: 28px;
		font-weight: 700;
		padding-top: 10px;
		-webkit-user-select: none;
		}
		
		main.cont section .card_desc {
		border: 6px solid var(--gray01);
		border-radius: 10px;
		padding: 20px 10px 20px 10px;
		box-sizing: border-box;
		margin: 10px 0;
		}
		
		.logo-container {
            width: 100%;
            text-align: center;
            padding: 10px 0;
        }

        .logo-container img {
            max-width: 120px; /* 로고 최대 너비 */
            height: auto;
        }

        body {
            font-family: Arial, sans-serif;
        }

        .cont {
            padding: 20px;
        }

        .tit h2 {
            font-size: 24px;
            margin-bottom: 10px;
        }

        .tit p {
            font-size: 14px;
            color: #666;
        }
	
        .card_tit h3 {
            font-size: 18px;
            margin-bottom: 15px;
        }

        .card_desc ul {
            padding-left: 20px;
            font-size: 14px;
            line-height: 1.6;
        }

        .input {
            margin-bottom: 15px;
        }

        .input input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .btn_solid_pri {
            display: block;
            text-align: center;
            padding: 15px;
            font-size: 16px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn_solid_pri:hover {
            background-color: #0056b3;
        }

        @media (max-width: 576px) {
            .tit h2 {
                font-size: 20px;
            }

            .btn_solid_pri {
                font-size: 14px;
                padding: 12px;
            }
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

<body class="wrap">

    <!-- 본문 -->
    <main class="col-12 cont" id="bill-01">
        <!-- 페이지타이틀 -->
        <section class="mb-5">
            <div class="tit text-center">
				<header class="logo-container">
				<img src="https://catchwell.com/web/upload/NNEditor/20240315/89675d536c942fee35df45d6d52e920f.png" alt="Catchwell Logo">
				</header>
                <!--<h2>수거 택배비 결제</h2>
                <p>제품수거시 발생하는 택배비 3000원을 선결재 합니다.</p>-->
            </div>
        </section>
        <!-- //페이지타이틀 -->

        <!-- 카드CONTENTS -->
        <section class="menu_cont mb-5">
            <div class="card p-4">
                <div class="card_tit text-center">
                    <h3>가상계좌 발급</h3>
                </div>

                <!-- 유의사항 -->
                <div class="card_desc mb-4">
                    <h4>※ 안내사항</h4>
                    <ul>
                        <li>캐치웰은 고객님의 A/S제품 회수를 위해 편도 택배비를 부과하고 있습니다.(발송비용은 캐치웰 부담)</li>
                        <li>캐치웰과 계약된 CJ대한통운 택배사를 이용하여 회수 및 발송되며 회수비용은 3,500원입니다.</li>
                        <li>가상계좌를 발급받아 입금해주시면 택배기사님이 제품 수거를 위해 방문 합니다.</li>
                    </ul>
                </div>
                <!-- //유의사항 -->
				<?php
				if ($result_cnt == 0) {
				?>
					<div class="search-contents">
						<p>조회 결과가 없습니다.</p>
					</div>
				<?php
				} else {
					for ($i = 0; $i < $result_cnt; $i++) {
						mysqli_data_seek($result, $i);
						$search_db_row = mysqli_fetch_array($result);
						$memo = $search_db_row['admin_memo'];
						$memo = str_replace("(V)","",$memo);
						$memo = str_replace("(R)","",$memo);
						$memo = str_replace("(H)","",$memo);
						$memo = str_replace("(S)","",$memo);
						$memo = str_replace("(M)","",$memo);
						$memo = str_replace("[ETC]","",$memo);
				?>
                <form name="mobileweb" method="post" class="mt-4" accept-charset="euc-kr">
                    <div class="row g-3">

						<input type="hidden" name="P_INI_PAYMENT" value="VBANK" class="form-control">

						<input type="hidden" name="P_MID" value="CAEcatca07" class="form-control"><!-- 캐치웰 MID : CAEcatca07, 이니시스 테스트 아이디  : INIpayTest -->

                        <div class="col-12">
                            <label class="form-label">접수번호</label>
                            <input type="text" name="P_OID" value="<?php echo $search_db_row['reg_num'] ?>" class="form-control" readonly>
                        </div>
                        <div class="col-12">
                            <label class="form-label">택배비</label>
                            <input type="text" name="P_AMT" value="3500" class="form-control" readonly>
                        </div>
                            <input type="hidden" name="P_GOODS" value="회수 택배비" class="form-control">
                        <div class="col-12">
                            <label class="form-label">고객명</label>
                            <input type="text" name="P_UNAME" value="<?php echo $search_db_row['customer_name'] ?>" class="form-control" readonly>
                        </div>
                        <div class="col-12">
                            <label class="form-label">연락처</label>
                            <input type="text" name="P_MOBILE" value="<?php echo $search_db_row['customer_phone'] ?>" class="form-control" readonly>
                        </div>

						<input type="hidden" name="P_EMAIL" value="" class="form-control">

                        <input type="hidden" name="P_NEXT_URL" value="https://csadmin.catchwell.com/cw_as_0924/pg_m/INImobile_mo_return.php">
							
                        <input type="hidden" name="P_NOTI_URL" value="https://csadmin.catchwell.com/cw_as_0924/pg_m/mx_rnoti.php">
						
                        <input type="hidden" name="P_CHARSET" value="utf8">

                        <input type="hidden" name="P_RESERVED"
							value="vbank_receipt=N&centerCd=Y" class="form-control">

                    </div>
                </form>
				<button onclick="on_pay()" class="btn_solid_pri col-12 mt-4">가상계좌 발급</button>
				<?php
					}
				}
				?>

                

            </div>
        </section>
    </main>
</body>

</html>
