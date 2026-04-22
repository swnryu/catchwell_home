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
    <h4 class="page-header">택배조회</h4>
    <form method="post" action="">
        <img src="https://trace.cjlogistics.com/web/img/cjkxlogo.gif" class="logo">
        <label for="certNum1">CJ 택배조회 :</label>
        <input type="text" id="certNum1" name="certNum1" value="<?php echo isset($_POST['certNum1']) ? htmlspecialchars($_POST['certNum1']) : ''; ?>">
        <input type="submit" name="submit1" value="조회">
        <br><br>
        <img src="https://www.epost.go.kr/np2assets/images/e1/logo.gif" class="logo">
        <label for="certNum2">우체국 택배조회 :</label>
        <input type="text" id="certNum2" name="certNum2" value="<?php echo isset($_POST['certNum2']) ? htmlspecialchars($_POST['certNum2']) : ''; ?>">
        <input type="submit" name="submit2" value="조회">
    </form>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST['submit1'])) {
            $certNum1 = !empty($_POST["certNum1"]) ? htmlspecialchars($_POST["certNum1"]) : "NULL";
            $url1 = "https://trace.cjlogistics.com/web/detail.jsp?slipno=" . $certNum1;
            echo "<script>openWindow('$url1');</script>";
        }

        if (isset($_POST['submit2'])) {
            $certNum2 = !empty($_POST["certNum2"]) ? htmlspecialchars($_POST["certNum2"]) : "NULL";
            $url2 = "https://service.epost.go.kr/trace.RetrieveDomRigiTraceList.comm?displayHeader=N&sid1=" . $certNum2;
            echo "<script>openWindow('$url2');</script>";
        }
    }
    ?>
</body>
</html>

<?php include('../footer.php'); ?>
