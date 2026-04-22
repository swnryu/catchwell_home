<?php

//*******************************************************************************
// FILE NAME : INIpayResult.php
// DATE : 2009.07
// 이니시스 가상계좌 입금내역 처리demon으로 넘어오는 파라메터를 control 하는 부분 입니다.
//*******************************************************************************
//**********************************************************************************
//이니시스가 전달하는 가상계좌이체의 결과를 수신하여 DB 처리 하는 부분 입니다.
//필요한 파라메터에 대한 DB 작업을 수행하십시오.
//**********************************************************************************

@extract($_GET);
@extract($_POST);
@extract($_SERVER);


//**********************************************************************************
//  이부분에 로그파일 경로를 수정해주세요.	

$INIpayHome = "/home/usr/www/vacct";      // 이니페이 홈디렉터리
//**********************************************************************************


$TEMP_IP = getenv("REMOTE_ADDR");
$PG_IP = substr($TEMP_IP, 0, 10);

if ($PG_IP == "203.238.37" || $PG_IP == "39.115.212" || $PG_IP == "183.109.71") {  //PG에서 보냈는지 IP로 체크
    $msg_id = $msg_id;             //메세지 타입
    $no_tid = $no_tid;             //거래번호
    $no_oid = $no_oid;             //상점 주문번호
    $id_merchant = $id_merchant;   //상점 아이디
    $cd_bank = $cd_bank;           //거래 발생 기관 코드
    $cd_deal = $cd_deal;           //취급 기관 코드
    $dt_trans = $dt_trans;         //거래 일자
    $tm_trans = $tm_trans;         //거래 시간
    $no_msgseq = $no_msgseq;       //전문 일련 번호
    $cd_joinorg = $cd_joinorg;     //제휴 기관 코드

    $dt_transbase = $dt_transbase; //거래 기준 일자
    $no_transeq = $no_transeq;     //거래 일련 번호
    $type_msg = $type_msg;         //거래 구분 코드
    $cl_close = $cl_close;         //마감 구분코드
    $cl_kor = $cl_kor;             //한글 구분 코드
    $no_msgmanage = $no_msgmanage; //전문 관리 번호
    $no_vacct = $no_vacct;         //가상계좌번호
    $amt_input = $amt_input;       //입금금액
    $amt_check = $amt_check;       //미결제 타점권 금액
    $nm_inputbank = $nm_inputbank; //입금 금융기관명
    $nm_input = $nm_input;         //입금 의뢰인
    $dt_inputstd = $dt_inputstd;   //입금 기준 일자
    $dt_calculstd = $dt_calculstd; //정산 기준 일자
    $flg_close = $flg_close;       //마감 전화
    //가상계좌채번시 현금영수증 자동발급신청시에만 전달
    $dt_cshr = $dt_cshr;       //현금영수증 발급일자
    $tm_cshr = $tm_cshr;       //현금영수증 발급시간
    $no_cshr_appl = $no_cshr_appl;  //현금영수증 발급번호
    $no_cshr_tid = $no_cshr_tid;   //현금영수증 발급TID

    $logfile = fopen($INIpayHome . "/log/result.log", "a+");


    fwrite($logfile, "************************************************");
    fwrite($logfile, "ID_MERCHANT : " . $id_merchant . "\r\n");
    fwrite($logfile, "NO_TID : " . $no_tid . "\r\n");
    fwrite($logfile, "NO_OID : " . $no_oid . "\r\n");
    fwrite($logfile, "NO_VACCT : " . $no_vacct . "\r\n");
    fwrite($logfile, "AMT_INPUT : " . $amt_input . "\r\n");
    fwrite($logfile, "NM_INPUTBANK : " . $nm_inputbank . "\r\n");
    fwrite($logfile, "NM_INPUT : " . $nm_input . "\r\n");
    fwrite($logfile, "************************************************");

    /*
      fwrite( $logfile,"전체 결과값"."\r\n");
      fwrite( $logfile, $msg_id."\r\n");
      fwrite( $logfile, $no_tid."\r\n");
      fwrite( $logfile, $no_oid."\r\n");
      fwrite( $logfile, $id_merchant."\r\n");
      fwrite( $logfile, $cd_bank."\r\n");
      fwrite( $logfile, $dt_trans."\r\n");
      fwrite( $logfile, $tm_trans."\r\n");
      fwrite( $logfile, $no_msgseq."\r\n");
      fwrite( $logfile, $type_msg."\r\n");
      fwrite( $logfile, $cl_close."\r\n");
      fwrite( $logfile, $cl_kor."\r\n");
      fwrite( $logfile, $no_msgmanage."\r\n");
      fwrite( $logfile, $no_vacct."\r\n");
      fwrite( $logfile, $amt_input."\r\n");
      fwrite( $logfile, $amt_check."\r\n");
      fwrite( $logfile, $nm_inputbank."\r\n");
      fwrite( $logfile, $nm_input."\r\n");
      fwrite( $logfile, $dt_inputstd."\r\n");
      fwrite( $logfile, $dt_calculstd."\r\n");
      fwrite( $logfile, $flg_close."\r\n");
      fwrite( $logfile, "\r\n");
     */

    fclose($logfile);


//************************************************************************************
    //위에서 상점 데이터베이스에 등록 성공유무에 따라서 성공시에는 "OK"를 이니시스로
    //리턴하셔야합니다. 아래 조건에 데이터베이스 성공시 받는 FLAG 변수를 넣으세요
    //(주의) OK를 리턴하지 않으시면 이니시스 지불 서버는 "OK"를 수신할때까지 계속 재전송을 시도합니다
    //기타 다른 형태의 PRINT( echo )는 하지 않으시기 바랍니다
//      if (데이터베이스 등록 성공 유무 조건변수 = true)
//      {

    echo "OK";                        // 절대로 지우지마세요
//      }
//*************************************************************************************
}
?>
