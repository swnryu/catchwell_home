<html>
<head>
<meta charset="utf-8">
<link rel="stylesheet" type="text/css" href="kakao_style.css">
</head>
<body> 
<?php

require "CKakaoNotificationTalk.php";
    
include("../common.php");
    
/** Error reporting */
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
    
// for KAKAO
$keyCode = "MTE1NDQtMTU2ODY5NzgyMTU4MS1kYTEwOWZjMi01MmFmLTQ1YTEtOTA5Zi1jMjUyYWY2NWExMWE=";
$clientID = "catchwell";
$defaultCallback = "07077776752";
$kakao = new CKakaoNotificationTalk( $keyCode, $clientID, $defaultCallback );


$sql = "SELECT * FROM TB_REPORT_KAKAO";
$result = $db->result( $sql );
    
//var_dump($result);
?>
    
<p><div class="title">카카오 알림톡 전송결과</div>
<!--<div class="refresh">새로고침</div>-->
<table>
    <thead class="head">
    <tr>
        <th class=bolder>접수번호</th>
        <th class=bolder>구분</th>
        <th class=bolder>날짜</th>
        <th class=bolder>CMID</th>
        <th class=bolder>발송상태</th>
        <th class=bolder>수신번호</th>
        <th class=bolder>발신번호</th>
        <th class=bolder>결과</th>
        <th class=bolder>실패메시지</th>
    </tr>
    </thead>
    <tbody id="result">
    <?php
    $idx = 0;
    while( $db_row = mysqli_fetch_array( $result ) ) {
        // result null check
        $rslt = $db_row['RSLT'];
    
        if( empty( $rslt ) )
        {
            // refresh   
            $sending = $db_row['SENDING'];
            $cmid = $db_row['CMID'];
            $regNo = $db_row['REG_NO'];
            $reportBody = [
                "cmid" =>   $cmid   //$response->body->cmid
            ];
            
            $report = $kakao->getReport( $reportBody );
            $date = $report->body->sentdate;
            $status = $kakao->getReportStatusText($report->body->STATUS);
            $phone = $report->body->phone;
            $callback = $report->body->callback;
            $rslt = $kakao->getReportResultText( $report->body->RSLT );
            //var_dump($report);
            if( $report->body->STATUS == '4' )
                $msg_rslt = $kakao->getReportMsgResultText( $report->body->MSG_RSLT );
            else
                $msg_rslt = '';
            
            $update_sql = "UPDATE TB_REPORT_KAKAO SET SENDING='$sending', DATE='$date', STATUS='$status', REG_NO='$regNo', PHONE='$phone', CALLBACK='$callback', RSLT='$rslt', MSG_RSLT='$msg_rslt' WHERE CMID='$cmid' ORDER BY DATE DESC";
            //echo "SQL : ".$update_sql."<br>";
            
            if( !$db->result( $update_sql ) ) {
                echo "DB UPDATE fail.. <br>".mysql_error($db_conn);
            }
        }
        
        $idx++;
    ?>
    <tr>
        <td><?php echo $db_row['REG_NO'] ?></td>
        <td><?php echo $db_row['SENDING'] ?></td>
        <td><?php echo $db_row['DATE'] ?></td>
        <td><?php echo $db_row['CMID'] ?></td>
        <td><?php echo $db_row['STATUS'] ?></td>
        <td><?php echo $db_row['PHONE'] ?></td>
        <td><?php echo $db_row['CALLBACK'] ?></td>
        <td><?php echo $db_row['RSLT'] ?></td>
        <td><?php echo $db_row['MSG_RSLT'] ?></td>
    </tr>    
    <?php
    }    
    ?>
    </tbody>
</table>
</body>
</html>