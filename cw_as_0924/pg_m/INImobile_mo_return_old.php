<?php

	include("../common.php");
    require_once('libs/properties.php');
    $prop = new properties();
 
     $P_STATUS    = $_REQUEST["P_STATUS"];
     $P_RMESG1    = $_REQUEST["P_RMESG1"];
     $P_TID       = $_REQUEST["P_TID"];
     $P_REQ_URL   = $_REQUEST["P_REQ_URL"];
     $P_NOTI      = $_REQUEST["P_NOTI"];
     $P_AMT       = $_REQUEST["P_AMT"];
  
   if ($_REQUEST["P_STATUS"] === "00") {             // 인증이 P_STATUS===00 일 경우만 승인 요청
 
        $id_merchant = substr($P_TID,'10','10');     // P_TID 내 MID 구분
        $data = array(
        
         'P_MID' => $id_merchant,         // P_MID
         'P_TID' => $P_TID                // P_TID

        );

        //##########################################################################
		// 승인요청 API url (authUrl) 리스트 는 properties 에 세팅하여 사용합니다.
		// idc_name 으로 수신 받은 센터 네임을 properties 에서 include 하여 승인요청하시면 됩니다.
		//##########################################################################
        $idc_name 	= $_REQUEST["idc_name"];
        $P_REQ_URL  = $prop->getAuthUrl($idc_name); 
 
        if (strcmp($P_REQ_URL, $_REQUEST["P_REQ_URL"]) == 0) {
        
            // curl 통신 시작 
        
            $ch = curl_init();                                                //curl 초기화
            curl_setopt($ch, CURLOPT_URL, $_REQUEST["P_REQ_URL"]);            //URL 지정하기
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                   //요청 결과를 문자열로 반환 
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);                     //connection timeout 10초 
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);                      //원격 서버의 인증서가 유효한지 검사 안함
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));    //POST 로 $data 를 보냄
            curl_setopt($ch, CURLOPT_POST, 1);                                //true시 post 전송 
    
    
            $response = curl_exec($ch);
            curl_close($ch);

            parse_str($response, $out);
			$P_STATUS = $out['P_STATUS'];
			$P_RMESG1 = $out['P_RMESG1'];
			$P_TID = $out['P_TID'];
			$P_UNAME = $out['P_UNAME'];
			$P_OID = $out['P_OID'];
			$P_AMT = $out['P_AMT'];
			$P_AUTH_DT = $out['P_AUTH_DT'];
			$P_FN_NM = $out['P_FN_NM'];
			$P_VACT_NUM = $out['P_VACT_NUM'];
			$P_VACT_NAME = $out['P_VACT_NAME'];
					
			
			$sql = "INSERT INTO TB_INICIS_RETURN (
            P_STATUS, P_RMESG1, P_TID, P_UNAME, P_OID, P_AMT, P_AUTH_DT, P_FN_NM, P_VACT_NUM, P_VACT_NAME
			) VALUES (
				'$P_STATUS', 
				'$P_RMESG1', 
				'$P_TID', 
				'$P_UNAME', 
				'$P_OID', 
				'$P_AMT', 
				'$P_AUTH_DT', 
				'$P_FN_NM', 
				'$P_VACT_NUM', 
				'$P_VACT_NAME'
			)";
			$result = $db->result($sql);
        }
    }
?>
<!DOCTYPE html>
<html lang="ko">

    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport"
            content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <title>KG이니시스 결제샘플</title>
        <link rel="stylesheet" href="css/style.css">
		<link rel="stylesheet" href="css/bootstrap.min.css">
		<style>
			main.cont {
			margin: auto;
			padding: 5px;
			padding-top: 20px;
			}
			
			main .card {
			width: 100%;
			background: var(--white);
			border-radius: 20px;
			box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
			padding: 20px;
			padding-top: 0;
			box-sizing: border-box;
			-webkit-user-select: text;
			}
			
			main .card .card_tit h3 {
			font-size: 28px;
			font-weight: 700;
			padding-top: 10px;
			-webkit-user-select: none;
			}
			
			main.cont section .card_desc {
			border: 6px solid var(--gray01);
			border-radius: 10px;
			padding: 20px 10px 20px 10px;
			box-sizing: border-box;
			margin: 10px 0;
			}
			
			main.cont section .card_desc ul {
			margin-top: 4px;
			margin-bottom: 4px;
			font-size: 24px;
			}
			.card_desc ul {
			padding-left: 20px;
			font-size: 14px;
			line-height: 0.1;
			}
			
			.logo-container {
				width: 100%;
				text-align: center;
				padding: 10px 0;
			}

			.logo-container img {
				max-width: 120px; /* 로고 최대 너비 */
				height: auto;
			}

			body {
				font-family: Arial, sans-serif;
			}

			.cont {
				padding: 20px;
			}

			.tit h2 {
				font-size: 24px;
				margin-bottom: 10px;
			}

			.tit p {
				font-size: 14px;
				color: #666;
			}
		
			.card_tit h3 {
				font-size: 18px;
				margin-bottom: 15px;
			}

			.card_desc ul {
				padding-left: 20px;
				font-size: 14px;
				line-height: 1.6;
			}

			.input {
				margin-bottom: 15px;
			}

			.input input {
				width: 100%;
				padding: 10px;
				border: 1px solid #ddd;
				border-radius: 5px;
			}

			.btn_solid_pri {
				display: block;
				text-align: center;
				padding: 15px;
				font-size: 16px;
				background-color: #007bff;
				color: #fff;
				border: none;
				border-radius: 5px;
				cursor: pointer;
			}

			.btn_solid_pri:hover {
				background-color: #0056b3;
			}

			@media (max-width: 576px) {
				.tit h2 {
					font-size: 20px;
				}

				.btn_solid_pri {
					font-size: 14px;
					padding: 12px;
				}
			}
		</style>
    </head>

    <body class="wrap">

        <!-- 본문 -->
        <main class="col-12 cont" id="bill-01">
            <!-- 페이지타이틀 -->
            <section class="mb-5">
                <div class="tit text-center">
                    <h2>캐치웰 가상계좌 결제서비스</h2>
                    <p>아래 계좌번호로 수거 택배비를 입금요청 드립니다.</p>
                </div>
            </section>
            <!-- //페이지타이틀 -->


            <!-- 카드CONTENTS -->
            <section class="menu_cont mb-5">
                <div class="card p-4">
                    <div class="card_tit text-center">
                        <h3>가상계좌 안내</h3>
                    </div>

                    <!-- 유의사항 -->
                    <div class="card_desc">
                        <h4>※ 접수내역</h4>
                        <ul>
							<?php 
                            if (strcmp($P_REQ_URL, $_REQUEST["P_REQ_URL"]) == -1) {
                                echo "
                                <label class='col-10 col-sm-2 input param' style='border:none;'></label>
                                <label class='col-10 col-sm-9 reinput'> authUrl check Fail (인증까지만 진행됨, 아래 인증 결과) </label>";
                            }
							?>
                            <li>접수번호</li>
							<ul><?php echo @(in_array($out["P_OID"] , $out) ? $out["P_OID"] : "null" ) ?></ul>
							<li>입금자명</li>
							<ul><?php echo @(in_array($out["P_UNAME"] , $out) ? $out["P_UNAME"] : "null" ) ?></ul>
							<li>입금액</li>
							<ul><?php echo @(in_array($out["P_AMT"] , $out) ? $out["P_AMT"] : "null" ) ?></ul>
							<li>입금은행</li>
							<ul><?php echo @(in_array($out["P_FN_NM"] , $out) ? $out["P_FN_NM"] : "null" ) ?></ul>
							<li>계좌번호</li>
							<ul onclick="copyToClipboard('<?php echo @(in_array($out['P_VACT_NUM'], $out) ? $out['P_VACT_NUM'] : 'null'); ?>')"
								style="color: blue; cursor: pointer; text-decoration: underline;">
								<?php echo @(in_array($out['P_VACT_NUM'], $out) ? $out['P_VACT_NUM'] : 'null'); ?>
							</ul>
							<li>받는곳</li>
							<ul><?php echo @(in_array($out["P_VACT_NAME"] , $out) ? $out["P_VACT_NAME"] : "null" ) ?></ul>
                        </ul>
                    </div>
				
                </div>
            </section>
			
        </main>
		
    </body>
	<!-- JavaScript 추가 -->
	<script>
		function copyToClipboard(text) {
			if (navigator.clipboard && window.isSecureContext) {
				// 클립보드 API를 지원하고 보안 컨텍스트인 경우
				navigator.clipboard.writeText(text).then(() => {
					alert('계좌번호가 복사되었습니다: ' + text);
				});
			} else {
				// 클립보드 API를 지원하지 않는 경우
				let textArea = document.createElement("textarea");
				textArea.value = text;
				textArea.style.position = "fixed"; // 화면에 보이지 않도록 고정
				textArea.style.left = "-9999px";
				document.body.appendChild(textArea);
				textArea.focus();
				textArea.select();
				try {
					document.execCommand('copy');
					alert('계좌번호가 복사되었습니다: ' + text);
				} catch (err) {
					alert('복사에 실패했습니다.');
				}
				document.body.removeChild(textArea);
			}
		}
	</script>
</html>