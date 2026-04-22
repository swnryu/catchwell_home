<?php
// 샘플 데이터
$row3 = new stdClass();
$row3->P_OID = "123456789";
$row3->P_AMT = "100,000원";
$row3->P_FN_NM = "국민은행";
$row3->P_VACT_NUM = "1234-5678-9012-34";
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>계좌 발급 완료</title>
    <script>
        function copyToClipboard(text) {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            alert('계좌번호가 클립보드에 복사되었습니다: ' + text);
        }
    </script>
</head>
<body>
    <script>
        alert('계좌발급이 완료되었습니다.\n' +
              '접수번호 : <?php echo $row3->P_OID; ?>\n' +
              '입금액 : <?php echo $row3->P_AMT; ?>\n' +
              '은행 : <?php echo $row3->P_FN_NM; ?>\n' +
              '계좌번호 : <?php echo $row3->P_VACT_NUM; ?>\n(클릭하면 복사됩니다.)');
    </script>
    
    <h2>계좌 발급 완료</h2>
    <p>접수번호: <?php echo $row3->P_OID; ?></p>
    <p>입금액: <?php echo $row3->P_AMT; ?></p>
    <p>은행: <?php echo $row3->P_FN_NM; ?></p>
    <p>계좌번호: 
        <span style="cursor: pointer; color: blue; text-decoration: underline;" 
              onclick="copyToClipboard('<?php echo $row3->P_VACT_NUM; ?>')">
            <?php echo $row3->P_VACT_NUM; ?>
        </span>
    </p>
</body>
</html>