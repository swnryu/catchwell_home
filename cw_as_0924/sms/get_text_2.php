<?php
// 텍스트 파일 경로
$file_path = $_SERVER['DOCUMENT_ROOT'] . '/cw_as/sms/sms_log_2.txt';
$text = file_get_contents($file_path);
echo $text; 
?>

