<?php
error_reporting(E_ALL);
include("../def_inc.php");
include("cancellation_def.php");

$mod    = M_MAIN;
$menu   = S_MAIN;

include("../header.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>인증 정보 조회</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 20px;
            background-color: #ffffff;
        }
        form {
            margin-bottom: 20px;
        }
        label {
            font-weight: bold;
        }
        input[type="text"] {
            margin-right: 10px;
            padding: 5px;
            width: 100%;
            max-width: 300px;
            margin-bottom: 10px;
        }
        input[type="submit"] {
            padding: 10px 20px;
            background-color: #007BFF;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-bottom: 10px;
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
        }
        .logo {
            display: block;
            margin-bottom: 10px;
        }
    </style>
    <script>
        function openWindow(url) {
            window.open(url, '_blank', 'width=800,height=600');
        }

        document.addEventListener('DOMContentLoaded', (event) => {
            document.getElementById('certNum1').addEventListener('keydown', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    document.querySelector('input[name="submit1"]').click();
                }
            });

            document.getElementById('certNum2').addEventListener('keydown', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    document.querySelector('input[name="submit2"]').click();
                }
            });
        });
    </script>
</head>
<body>
    <h4 class="page-header">제품 인증번호 검색</h4>
    <form method="post" action="">
        <img src="https://safetykorea.kr/resources/img/common/foot_logo.gif" alt="KC 안전 인증 로고" class="logo">
        <label for="certNum1">KC안전인증인증번호 :</label>
        <input type="text" id="certNum1" name="certNum1" value="<?php echo isset($_POST['certNum1']) ? htmlspecialchars($_POST['certNum1']) : ''; ?>">
        <input type="submit" name="submit1" value="조회">
        <br><br>
        <img src="https://www.rra.go.kr/ko/images/popup/pop_logo.jpg" alt="적합성 평가 로고" class="logo">
        <label for="certNum2">적합성평가 인증번호 :</label>
        <input type="text" id="certNum2" name="certNum2" value="<?php echo isset($_POST['certNum2']) ? htmlspecialchars($_POST['certNum2']) : ''; ?>">
        <input type="submit" name="submit2" value="조회">
    </form>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST['submit1'])) {
            $certNum1 = !empty($_POST["certNum1"]) ? htmlspecialchars($_POST["certNum1"]) : "NULL";
            $url1 = "https://safetykorea.kr/search/searchPop?certNum=" . $certNum1;
            echo "<script>openWindow('$url1');</script>";
        }

        if (isset($_POST['submit2'])) {
            $certNum2 = !empty($_POST["certNum2"]) ? htmlspecialchars($_POST["certNum2"]) : "NULL";
            $url2 = "https://www.rra.go.kr/conform/" . $certNum2;
            echo "<script>openWindow('$url2');</script>";
        }
    }
    ?>
</body>
</html>

<?php include('../footer.php'); ?>
