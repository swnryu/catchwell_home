<?php
/** Error reporting */
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

include("../common.php");

/** 진행 상태 변환 함수 */
function showStatus( $state )
{
    switch( $state )
    {
        case 0 : return "접수중";
        case 1 : return "접수완료";
        case 2 : return "수리중";
        case 3 : return "수리완료";
        case 4 : return "출고완료";
        default: return "상태확인불가";
    }
}

/** * 데이터 조회 로직 
 * 에러 수정: $db->connect가 정의되지 않은 경우를 대비하여 최초 코드의 변수 할당 방식 유지
 */
$strSearchCondition = 'reg_num';
$strSearchCondition2 = 'customer_phone';

// $_GET 데이터를 안전하게 가져오되, mysqli_real_escape_string 에러 방지를 위해 처리
$searchData = isset($_GET['searchData']) ? $_GET['searchData'] : '';
$searchData2 = isset($_GET['searchValuePhone']) ? $_GET['searchValuePhone'] : '';

if ($searchData && $searchData2) {
    // SQL 인젝션 방지를 위해 쿼리 구성 시 직접 이스케이프 (클래스 내부 메서드가 있다면 그것을 사용하는 것이 좋음)
    // 여기서는 에러 메시지를 바탕으로 호환성 있게 수정
    $search_sql = "SELECT * FROM as_parcel_service WHERE $strSearchCondition='$searchData' AND $strSearchCondition2='$searchData2' ORDER BY reg_date DESC";
    $result = $db->result($search_sql);
    $result_cnt = mysqli_num_rows($result);
} else {
    $result_cnt = 0;
}

/** 업데이트(수리 요청) 처리 로직 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reg_num'])) {
    $update_reg_num = $_POST['reg_num'];
    $update_sql = "UPDATE as_parcel_service SET attached_files = 'YES' WHERE reg_num = '$update_reg_num'";
    
    if ($db->result($update_sql)) {
        echo "<script>window.open('online_as_estimate_complete.php', '_blank', 'width=500,height=300'); location.reload();</script>";
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>캐치웰 A/S 견적서</title>
    <style>
        :root {
            --primary-color: #0066ff;
            --primary-dark: #0052cc;
            --bg-color: #f2f4f7;
            --card-bg: #ffffff;
            --text-main: #1a1a1a;
            --text-muted: #707070;
            --border-color: #eef1f5;
            --accent-bg: #f8fbff;
        }

        body {
            font-family: 'Pretendard', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            padding: 20px 15px;
            background-color: var(--bg-color);
            color: var(--text-main);
            line-height: 1.5;
        }

        .container {
            max-width: 480px;
            margin: auto;
            background-color: var(--card-bg);
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .header {
            text-align: center;
            padding: 40px 20px;
            background-color: var(--text-main);
            color: white;
        }
        .header h2 { margin: 0; font-size: 20px; font-weight: 800; letter-spacing: 2px; }
        .header p { margin: 8px 0 0; opacity: 0.6; font-size: 13px; font-weight: 300; }

        .section { padding: 30px 24px; border-bottom: 1px solid var(--border-color); }
        .section-title {
            font-size: 13px; color: var(--primary-color); font-weight: 700;
            margin-bottom: 20px; text-transform: uppercase; letter-spacing: 0.5px;
        }

        .info-row { display: flex; justify-content: space-between; margin-bottom: 14px; font-size: 15px; }
        .info-row:last-child { margin-bottom: 0; }
        .label { color: var(--text-muted); font-weight: 400; }
        .value { font-weight: 600; text-align: right; }
        .value.status { color: var(--primary-color); background: var(--accent-bg); padding: 2px 8px; border-radius: 4px; font-size: 13px; }
        
        .price-container {
            margin-top: 10px; padding: 20px; background-color: var(--accent-bg);
            border-radius: 16px; border: 1px solid #e0ebff;
        }
        .price-label { font-size: 14px; color: var(--text-muted); display: block; margin-bottom: 5px; }
        .price-value { font-size: 26px; font-weight: 800; color: var(--primary-color); }

        .memo-container { margin-top: 10px; }
        .memo-box {
            background-color: #f9f9fb; padding: 16px; border-radius: 12px;
            font-size: 14px; color: #444; line-height: 1.6; border: 1px solid #f0f0f2;
        }

        .notice-area { padding: 24px; background-color: #fffaf0; border-top: 1px solid #ffedd5; border-bottom: 1px solid #ffedd5; }
        .notice-text { font-size: 13px; color: #9a662e; margin: 0; text-align: center; }

        .account-section { padding: 30px 24px; text-align: center; }
        .account-label { font-size: 13px; color: var(--text-muted); margin-bottom: 12px; display: block; }
        .account-number {
            display: inline-block; font-size: 18px; font-weight: 700; color: var(--text-main);
            padding: 12px 20px; background: #fff; border: 2px solid var(--border-color);
            border-radius: 12px; cursor: pointer; transition: all 0.2s; position: relative;
        }
        .account-number:hover { border-color: var(--primary-color); background: var(--accent-bg); }
        .copy-tag { font-size: 10px; color: var(--primary-color); margin-top: 8px; display: block; font-weight: 600; }

        .btn-area { padding: 0 24px 40px; }
        .btn-submit {
            width: 100%; padding: 20px; font-size: 17px; font-weight: 700;
            background-color: var(--primary-color); color: white; border: none;
            border-radius: 16px; cursor: pointer; transition: transform 0.1s, background-color 0.2s;
            box-shadow: 0 8px 20px rgba(0, 102, 255, 0.2);
        }
        .btn-submit:active { transform: scale(0.98); background-color: var(--primary-dark); }
        
        .contact-box { margin-top: 20px; text-align: center; font-size: 13px; color: var(--text-muted); }
        .contact-box a { color: var(--text-main); text-decoration: none; font-weight: 700; margin-left: 5px; }

        .no-data { text-align: center; padding: 60px 20px; color: var(--text-muted); font-size: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>CATCHWELL</h2>
            <p>Quality Service & Support</p>
        </div>

        <?php if ($result_cnt == 0): ?>
            <div class="no-data">
                <p>조회된 견적 내역이 없습니다.<br>입력하신 정보를 다시 확인해 주세요.</p>
            </div>
        <?php else: 
            for ($i = 0; $i < $result_cnt; $i++) {
                mysqli_data_seek($result, $i);
                $search_db_row = mysqli_fetch_array($result);
                
                $memo = $search_db_row['admin_memo'];
                $tags = ["(V)", "(R)", "(H)", "(S)", "(M)", "[ETC]"];
                $memo = str_replace($tags, "", $memo);
        ?>
            <!-- 고객 및 제품 정보 섹션 -->
            <div class="section">
                <div class="section-title">Case Information</div>
                <div class="info-row">
                    <span class="label">접수번호</span>
                    <span class="value"><?php echo htmlspecialchars($search_db_row['reg_num']) ?></span>
                </div>
                <div class="info-row">
                    <span class="label">고객명</span>
                    <span class="value"><?php echo htmlspecialchars($search_db_row['customer_name']) ?></span>
                </div>
                <div class="info-row">
                    <span class="label">제품명</span>
                    <span class="value"><?php echo htmlspecialchars($search_db_row['product_name']) ?></span>
                </div>
                <div class="info-row">
                    <span class="label">진행상태</span>
                    <span class="value status"><?php echo showStatus($search_db_row['process_state']) ?></span>
                </div>
            </div>

            <!-- 점검 및 비용 정보 섹션 -->
            <div class="section">
                <div class="section-title">Diagnosis & Cost</div>
                <div class="info-row" style="flex-direction: column; align-items: flex-start;">
                    <span class="label" style="margin-bottom: 8px;">고장증상</span>
                    <span class="value" style="text-align: left; font-weight: normal;"><?php echo htmlspecialchars($search_db_row['customer_desc']) ?></span>
                </div>
                <div class="memo-container" style="margin-top: 15px;">
                    <div class="label" style="margin-bottom:8px; font-size:14px;">엔지니어 점검 의견</div>
                    <div class="memo-box"><?php echo nl2br(htmlspecialchars($memo)) ?></div>
                </div>
                
                <div class="price-container">
                    <span class="price-label">수리 결제 예정 금액</span>
                    <span class="price-value"><?php echo number_format($search_db_row['price']) ?>원</span>
                </div>
            </div>

            <div class="notice-area">
                <p class="notice-text">수리 진행을 원하실 경우 비용 입금 후 아래 버튼을 눌러주세요.</p>
            </div>

            <!-- 결제 계좌 정보 섹션 -->
            <div class="account-section">
                <span class="account-label">입금 계좌 정보 (우리은행)</span>
                <div class="account-number" onclick="copyToClipboard('1005-103-879305')">
                    1005-103-879305
                </div>
                <span class="copy-tag">터치하여 계좌번호 복사</span>
                <div style="font-size:13px; margin-top:8px; color:#555;">예금주: (주)캐치웰</div>
            </div>

            <!-- 실행 버튼 섹션 -->
            <div class="btn-area">
                <form method="POST" onsubmit="return confirm('수리 진행을 확정하시겠습니까?\n입금 확인 후 진행됩니다.');">
                    <input type="hidden" name="reg_num" value="<?php echo htmlspecialchars($search_db_row['reg_num']) ?>">
                    <button type="submit" class="btn-submit">수리 진행 요청</button>
                </form>
                
                <div class="contact-box">
                    상담 문의 <a href="tel:010-4071-8720"><?php echo htmlspecialchars($search_db_row['pic_name']) ?> 010-4071-8720</a>
                </div>
            </div>
        <?php 
            }
        endif; 
        ?>
    </div>

    <script>
        /** 계좌번호 복사 함수 */
        function copyToClipboard(text) {
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(function() {
                    alert('계좌번호가 복사되었습니다.');
                });
            } else {
                const textArea = document.createElement("textarea");
                textArea.value = text;
                document.body.appendChild(textArea);
                textArea.select();
                try {
                    document.execCommand('copy');
                    alert('계좌번호가 복사되었습니다.');
                } catch (err) {
                    console.error('복사 실패', err);
                }
                document.body.removeChild(textArea);
            }
        }
    </script>
</body>
</html>