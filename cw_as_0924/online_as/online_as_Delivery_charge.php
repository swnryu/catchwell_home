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

// 업데이트 버튼이 클릭되었을 때의 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>캐치웰 A/S 견적서</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h2 {
            margin: 0;
            color: #007BFF;
        }
        .info {
            margin-bottom: 20px;
        }
        .info p {
            margin: 8px 0;
            font-size: 16px;
        }
        .highlight {
            font-weight: bold;
            color: #007BFF;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
        }
        .footer a {
            display: inline-block;
            margin-top: 10px;
            padding: 10px 20px;
            background-color: #007BFF;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .footer a:hover {
            background-color: #007BFF;
        }
		.footer button {
            padding: 15px 30px; /* 버튼 크기 조절: padding을 늘려서 크기를 키움 */
            font-size: 18px; /* 글자 크기 조절 */
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .footer button:hover {
            background-color: #0056b3;
		}
        .account-link {
            color: #007BFF;
            text-decoration: none;
            font-weight: bold;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>캐치웰 A/S 견적</h2>
        </div>
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
            <div class="info">
                <p><span class="highlight">접수번호:</span> <?php echo $search_db_row['reg_num'] ?></p>
                <p><span class="highlight">고객명:</span> <?php echo $search_db_row['customer_name'] ?></p>
                <p><span class="highlight">진행상태:</span> <?php echo showStatus($search_db_row['process_state']) ?></p>
                <p><span class="highlight">제품명:</span> <?php echo $search_db_row['product_name'] ?></p>
                <p><span class="highlight">고장증상:</span> <?php echo $search_db_row['customer_desc'] ?></p>
                <p><span class="highlight">점검결과:</span> <?php echo $memo ?></p>
                <p><span class="highlight">수리비용:</span> <?php echo number_format($search_db_row['price']) ?>원</p>
                <p><span class="highlight">담당자:</span> <?php echo $search_db_row['pic_name'] ?> <a href="tel:010-4071-8720">010-4071-8720</a></p>
            </div>

            <div class="footer">
                <form method="POST" onsubmit="return confirm('수리 진행을 요청하시겠습니까?');">
                    <p>수리에 동의할 경우 아래 계좌로 수리비용 입금후 수리진행 요청 버튼을 눌러주세요.</br> 상담이 필요할경우 담당자에게 연락부탁드립니다.</p>
                    <input type="hidden" name="reg_num" value="<?php echo $search_db_row['reg_num'] ?>">
                    <button type="submit">수리 진행 요청</button>
                </form>
                <p>수리비용 입금 계좌:</p>
                <p><span class="highlight">은행:</span> 우리은행 (주)캐치웰</p>
                <p>
                    <span class="highlight">계좌번호:</span> 
                    <span class="account-link" onclick="copyToClipboard('1005-103-879305')"><u>1005-103-879305</u></span>
                </p>
                <p><span class="highlight">금액:</span> <?php echo number_format($search_db_row['price']) ?>원</p>
            </div>
        <?php
            }
        }
        ?>
    </div>
    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                alert('계좌번호가 클립보드에 복사되었습니다: ' + text);
            }, function(err) {
                console.error('클립보드 복사에 실패했습니다.', err);
            });
        }
    </script>
</body>
</html>
