<?php

header('Content-Type:text/html; charset=utf-8');


    //step1. 요청을 위한 파라미터 설정
    $key = "t9SRNHxMH4nzQGXN";
	$iv = "6Nh2sJZRpbdz2t==";
    $mid = "CAEcatc44b";
	$type = "inquiry";
	$timestamp = date("YmdHis");
	$clientIp = "192.0.0.0";
	
	$postdata = array();
	$postdata["mid"] = $mid;
	$postdata["type"] = $type;
    $postdata["timestamp"] = $timestamp;
	$postdata["clientIp"] = $clientIp;
	
	//// Data 상세
    $detail = array();
	$detail["tid"] = "INIMX_VBNKCAEcatc44b20241127101851344901";

	$postdata["data"] = $detail;
	
	$details = str_replace('\\/', '/', json_encode($detail, JSON_UNESCAPED_UNICODE));

	//// Hash Encryption
	$plainTxt = $key.$mid.$type.$timestamp.$details;
    $hashData = hash("sha512", $plainTxt);

	$postdata["hashData"] = $hashData;

	echo "plainTxt : ".$plainTxt."<br/><br/>"; 
	echo "hashData : ".$hashData."<br/><br/>"; 


	$post_data = json_encode($postdata, JSON_UNESCAPED_UNICODE);
	
	echo "**** 요청전문 **** <br/>" ;
	echo str_replace(',', ',<br>', $post_data)."<br/><br/>" ;
	
	
	//step2. 요청전문 POST 전송
	
    $url = "https://iniapi.inicis.com/v2/pg/inquiry";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json;charset=utf-8'));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
     
    $response = curl_exec($ch);
    curl_close($ch);
	
	
    //step3. 결과출력
	
    echo "**** 응답전문 **** <br/>" ;
	echo str_replace(',', ',<br>', $response)."<br><br>";
    // JSON 데이터를 PHP 배열로 변환
	$response_data = json_decode($response, true);

	// 각각의 값들을 변수에 저장
	$resultCode = $response_data['resultCode'];
	$resultMsg = $response_data['resultMsg'];
	$tid = $response_data['tid'];
	$mid = $response_data['mid'];
	$oid = $response_data['oid'];
	$price = $response_data['price'];
	$goodsName = $response_data['goodsName'];
	$paymethod = $response_data['paymethod'];
	$approvedDate = $response_data['approvedDate'];
	$approvedTime = $response_data['approvedTime'];
	$buyerName = $response_data['buyerName'];
	$buyerTel = $response_data['buyerTel'];
	$buyerMail = $response_data['buyerMail'];
	$transactionStatus = $response_data['transactionStatus'];
	$availablePartCancel = $response_data['availablePartCancel'];

	// 가상계좌 정보
	$accountNumber = $response_data['vacctInfo']['accountNumber'];
	$bankName = $response_data['vacctInfo']['bankName'];
	$bankCode = $response_data['vacctInfo']['bankCode'];
	$accountName = $response_data['vacctInfo']['accountName'];
	$senderName = $response_data['vacctInfo']['senderName'];
	$validDate = $response_data['vacctInfo']['validDate'];
	$depositStatus = $response_data['vacctInfo']['depositStatus'];
	$depositBankName = $response_data['vacctInfo']['depositBankName'];
	$depositBankCode = $response_data['vacctInfo']['depositBankCode'];
	$depositAmount = $response_data['vacctInfo']['depositAmount'];
	$depositName = $response_data['vacctInfo']['depositName'];
	$depositDate = $response_data['vacctInfo']['depositDate'];
	$depositTime = $response_data['vacctInfo']['depositTime'];
	
	// 화면에 출력
	echo "Result Code: $resultCode<br>";
	echo "Result Message: $resultMsg<br>";
	echo "TID: $tid<br>";
	echo "MID: $mid<br>";
	echo "OID: $oid<br>";
	echo "Price: $price<br>";
	echo "Goods Name: $goodsName<br>";
	echo "Pay Method: $paymethod<br>";
	echo "Approved Date: $approvedDate<br>";
	echo "Approved Time: $approvedTime<br>";
	echo "Buyer Name: $buyerName<br>";
	echo "Buyer Tel: $buyerTel<br>";
	echo "Buyer Mail: $buyerMail<br>";
	echo "Transaction Status: $transactionStatus<br>";
	echo "Available Part Cancel: " . ($availablePartCancel ? "True" : "False") . "<br>";

	// 가상계좌 정보
	echo "Account Number: $accountNumber<br>";
	echo "Bank Name: $bankName<br>";
	echo "Bank Code: $bankCode<br>";
	echo "Account Name: $accountName<br>";
	echo "Sender Name: $senderName<br>";
	echo "Valid Date: $validDate<br>";
	echo "Deposit Status: $depositStatus<br>";
	echo "Deposit Bank Name: $depositBankName<br>";
	echo "Deposit Bank Code: $depositBankCode<br>";
	echo "Deposit Amount: $depositAmount<br>";
	echo "Deposit Name: $depositName<br>";
	echo "Deposit Date: $depositDate<br>";
	echo "Deposit Time: $depositTime<br>";
?>