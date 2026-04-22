<?
error_reporting(E_ALL);
include("../def_inc.php");
include("cancellation_def.php");

$mod	= M_MAIN;
$menu	= S_MAIN;

include("../header.php");

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>문자메시지</title>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
$(document).ready(function(){
    // 페이지가 로드되면 매초마다 업데이트 함수를 호출합니다.
    setInterval(updateText, 1000);

    function updateText() {
        // AJAX를 사용하여 서버에 요청하여 업데이트된 텍스트를 가져옵니다.
        $.ajax({
            url: "get_text_1.php", // 업데이트된 텍스트를 가져올 PHP 파일 경로
            type: "GET",
            success: function(data){
                $('#text_box').val(data);
            },
            error: function(xhr, status, error) {
                // 오류가 발생하면 콘솔에 오류 메시지를 출력합니다.
                console.error("Error fetching data:", error);
            }
        });
		$.ajax({
            url: "get_text_2.php", // 업데이트된 텍스트를 가져올 PHP 파일 경로
            type: "GET",
            success: function(data){
                $('#text_box_1').val(data);
            },
            error: function(xhr, status, error) {
                // 오류가 발생하면 콘솔에 오류 메시지를 출력합니다.
                console.error("Error fetching data:", error);
            }
        });
    }
});
</script>
</head>
<body>

<h4 class="page-header">업무용 문자 수신함</h4>

<div style="display: flex; flex-direction: column;">
    <div style="margin-bottom: 20px;">
        <h5 style="font-weight:bold; margin-top:0px; margin-bottom: 5px;">1번회선 010-4001-8720 수신함</h5>
        <textarea id="text_box" rows="10" cols="60"></textarea>
    </div>
    
    <div>
        <h5 style="font-weight:bold; margin-top:0px; margin-bottom: 5px;">2번회선 010-4618-8720 수신함</h5>
        <textarea id="text_box_1" rows="10" cols="60"></textarea>
    </div>
</div>
<!--
<h5 style="font-weight:bold; margin-top:0px; ">POST주소</h5>
POST KEY : sender
<p>1번회선 https://csadmin.catchwell.com/cw_as/sms/sms_receve_1.php</p>
<p>2번회선 https://csadmin.catchwell.com/cw_as/sms/sms_receve_2.php</p>
-->
</body>
</html>

<? include('../footer.php');?>