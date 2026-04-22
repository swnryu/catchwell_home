<?php
error_reporting(E_ALL);
include("../def_inc.php");

$mod = M_MAIN;
$menu = S_MAIN;

include("../header.php");

$message = ''; // 메시지를 저장할 변수

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customerPhone = $_POST['customerPhone'];

    require_once("../kakao/CKakaoNotificationTalkEx.php");
    $notiMsg = new CKakaoNotificationTalkEx();
    
    // 발송 후 true가 반환되면 메시지 저장
    if ($notiMsg->NotiMsg_picture_get($customerPhone, 1)) {
        $message = "카카오 알림톡을 발송하였습니다.";
    }
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>카카오 알림톡 발송</title>
    <style>
        .notice-box {
            border: 2px solid #000;
            padding: 15px;
            margin-bottom: 20px;
            background-color: #f9f9f9;
            font-size: 16px;
            line-height: 1.5;
            width: 40%; /* 네모 박스의 가로 크기 */
            max-width: 400px; /* 최대 가로 크기 */
            float: left; /* 왼쪽에 배치 */
            margin-right: 20px; /* 네모 박스와 폼 사이 간격 */
        }

        input[type="text"], button {
            width: 40%; /* 네모 박스와 동일한 가로 크기 */
            padding: 10px; /* 여백을 넓혀서 클릭이나 입력이 더 편리하게 만듦 */
            font-size: 18px; /* 폰트 크기 키우기 */
            margin-bottom: 15px; /* 버튼과 입력창 간의 간격 */
            display: block; /* 버튼과 입력창을 각각 블록처럼 설정 */
        }

        button {
            background-color: #4CAF50; /* 버튼 색상 */
            color: white; /* 버튼 텍스트 색상 */
            border: none;
            cursor: pointer; /* 마우스 커서를 포인터로 변경 */
        }

        button:hover {
            background-color: #45a049; /* 버튼에 마우스를 올렸을 때 색상 변경 */
        }

        .popup-message {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #4CAF50;
            color: white;
            padding: 15px;
            border-radius: 5px;
            font-size: 18px;
            display: none; /* 기본적으로 숨김 처리 */
            z-index: 1000;
        }
    </style>
    <script>
        // 페이지 로드 후 팝업 표시 및 자동 숨기기
        window.onload = function() {
            <?php if ($message): ?>
                // 팝업 메시지 표시
                var popup = document.getElementById('popupMessage');
                popup.style.display = 'block';

                // 3초 후에 팝업 숨기기
                setTimeout(function() {
                    popup.style.display = 'none';
                }, 3000); // 3000ms = 3초
            <?php endif; ?>
        };
    </script>
</head>
<body>
    <h1>카카오 알림톡 발송</h1>
    
    <!-- 카카오 알림톡 발송 전에 고지할 내용 (네모 박스 안에) -->
    <div class="notice-box">
        <p>안녕하세요, 캐치웰 C/S 담당자입니다.<br>
        문의 주신 내용에 대해 검토를 진행하기 위해 문제가 발생한 사진을 보내주시면 감사하겠습니다.<br>
        다만, 해당 채팅방은 사진 수신 전용으로, 채팅 상담은 어려운 점 양해 부탁드립니다.</p>
    </div>

    <!-- 전화번호 입력 폼 -->
    <form action="" method="POST">
        <label for="customerPhone">전화번호:</label>
        <input type="text" id="customerPhone" name="customerPhone" required>
        <button type="submit">발송</button>
    </form>

    <!-- 팝업 메시지 -->
    <div id="popupMessage" class="popup-message">
        <?php echo $message; ?>
    </div>
</body>
</html>

<?php
include('../footer.php');
?>
