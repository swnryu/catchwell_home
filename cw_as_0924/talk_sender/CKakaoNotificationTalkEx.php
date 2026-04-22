<?php

//header("Content-Type:text/html;charset=utf-8");

/** Error reporting */
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

require ("CKakaoNotificationTalk.php");
include("kakao_bizppurio.php");//20221117 함수추가 

//include_once ("../common.php");

class CKakaoNotificationTalkEx {
    

    function notiMsg($db, $customerName, $customerPhone, $regNumber, $modelName, $brokenType, $adminMemo, $parcelNo, $return_url)
    {
        //$DEBUG = 0;
        

    
        //apistore --------------------------------------------------------------------------------------------------------------------------------------------
        /*
        //const
        $customerCenter = "070-7777-6752";
        $parcelCo = "CJ대한통운";
        $keyCode = "MTE1NDQtMTU2ODY5NzgyMTU4MS1kYTEwOWZjMi01MmFmLTQ1YTEtOTA5Zi1jMjUyYWY2NWExMWE=";
        $clientID = "catchwell";
        $defaultCallback = "07077776752";
        $noti = new CKakaoNotificationTalk( $keyCode, $clientID, $defaultCallback );

        $talkMsg_applicationDone = "안녕하세요. 캐치웰입니다.\n\n".$customerName." 고객님의 A/S 접수가 완료되었습니다.
        \n* 접수번호 : ".$regNumber."\n* 제품명 : ".$modelName."\n* 고장증상 : ".$brokenType.
        "\n\n소중한 고객님의 제품, 빠른 시일내에 처리될 수 있도록 최선을 다하겠습니다.\n\n관련 문의사항은 고객센터\n(".$customerCenter.")를 이용해 주세요.\n";
        
        $talkMsg_fixDone = "안녕하세요. 캐치웰입니다.\n\n".$customerName." 고객님이 접수하신 제품의 AS가 완료되어 금일 발송처리 되었습니다.
        \n* 접수번호 : ".$regNumber."\n* 제품명 : ".$modelName."\n* 고장증상 : ".$brokenType."\n* A/S내역 : ".$adminMemo.
        "\n\n* 택배사 : ".$parcelCo."\n* 송장번호 : ".$parcelNo.
        "\n\n관련 문의사항은 고객센터\n(".$customerCenter.")를 이용해 주세요.";
        
        $smsMsg_applicationDone = "[캐치웰] ".$customerName." 님의 A/S 접수가 완료되었습니다. \n\n■ 접수번호 : ".$regNumber;
        $smsMsg_fixDone = "[캐치웰] 고객님의 A/S가 완료되어 금일 발송되었습니다. \n\n■ 송장번호 : ".$regNumber."(CJ대한통운)";
        
        $asLink = "http://backup.catchwell.com/cw_as/online_as/online_as_customer_search.php?searchBy=searchbyRegisterNo&searchData=".$regNumber;
        $homapageLink_m = "https://m.catchwell.com";
        $homapageLink = "https://www.catchwell.com";
        
        $msgBody_applicationDone = [
            "phone" => $customerPhone,         // 수신자 전화번호
            "callback" => $defaultCallback,   // 발신자 전화번호 
            "reqdate" => "",                  // 예약발송 기능("20191225100000" 형식), 비워두면 즉시발송
            "msg" => $talkMsg_applicationDone,
            "template_code" => "CAPI007",     // 템플릿으로 등록 승인된 템플릿의 코드값
            "failed_type" => "SMS",
            "failed_subject" => "[캐치웰]",
            "failed_msg" => $smsMsg_applicationDone,
            "btn_types" => "웹링크,웹링크",         // 카카오 알림톡 버튼타입     
            "btn_txts" => "AS 조회,홈페이지",       // 카카오 알림톡 버튼이름
            "btn_urls1" => $asLink.",".$homapageLink_m,
            "btn_urls2" => $asLink.",".$homapageLink
        ];
        
        $msgBody_fixDone = [
            "phone" => $customerPhone,         // 수신자 전화번호
            "callback" => $defaultCallback,   // 발신자 전화번호 
            "reqdate" => "",                  // 예약발송 기능("20191225100000" 형식), 비워두면 즉시발송
            "msg" => $talkMsg_fixDone,
            "template_code" => "CAPI008",     // 템플릿으로 등록 승인된 템플릿의 코드값
            "failed_type" => "SMS",
            "failed_subject" => "[캐치웰]",
            "failed_msg" => $smsMsg_fixDone,
            "btn_types" => "웹링크,배송조회",         // 카카오 알림톡 버튼타입     
            "btn_txts" => "AS 조회,배송조회",       // 카카오 알림톡 버튼이름
            "btn_urls1" => $asLink,
            "btn_urls2" => $asLink
        ];
         if( $parcelNo == "" )
        {
            // 접수완료 카톡알림
            $send_from = "접수";
            $response = $noti->postMessage( $msgBody_applicationDone );
            $resultMsg = $noti->getPostMsgResultText( $response->body->result_code );
        }
        else
        {
            // 출고완료 카톡알림
            $send_from = "출고";
            $response = $noti->postMessage( $msgBody_fixDone );
            $resultMsg = $noti->getPostMsgResultText( $response->body->result_code );
        }
        */

        if( $parcelNo == "" )
        {
            // 접수완료 카톡알림 //CAPI007
            //const
            $customerCenter = "070-7777-6752";
            $parcelCo = "CJ대한통운";
            
            $send_from = "접수";
            $defaultCallback = "07077776752";
            $DEBUG = 0;

            $asLink = "http://backup.catchwell.com/cw_as/online_as/online_as_customer_search.php?searchBy=searchbyRegisterNo&searchData=".$regNumber;     

            $headers[0]="Accept:application/json"; 
            $headers[1]="Content-Type:application/json"; 
            $headers[2]="Authorization:Bearer";
            $url = 'https://api.bizppurio.com/v3/message';
            //추가
            $token = getToken();
            //print_r( $token);
            $headers1 = array('Accept:application/json', 'Content-Type:application/json', 'Authorization:Bearer '. $token);
        
            //CAPI007-------------------------------------------------------
            $button_list1 = array("name"=>"AS 조회", "type"=>"WL", "url_pc" =>$asLink, "url_mobile"=>$asLink);
            $button_list2 = array("name"=>"홈페이지", "type"=>"WL", "url_pc" =>"https://www.catchwell.com", "url_mobile"=>"https://m.catchwell.com");
            $buttons = array($button_list1,$button_list2);
        
            $at_CAPI007["message"] = "안녕하세요. 캐치웰입니다.\n\n"
            .$customerName." 고객님의 A/S 접수가 완료되었습니다.\n\n".
            "* 접수번호 : ".$regNumber."\n".
            "* 제품명 : ".$modelName."\n".
            "* 고장증상 : ".$brokenType."\n".
            "소중한 고객님의 제품, 빠른 시일내에 처리될 수 있도록 최선을 다하겠습니다.\n\n".
            "관련 문의사항은 고객센터( $customerCenter )를 이용해 주세요.";
        
            $at_CAPI007["senderkey"] = "44970513f96ceef0a7b532fa874d4697ca936dce";
            $at_CAPI007["templatecode"] = "CAPI007";
            $at_CAPI007["button"] = $buttons;
        
            $content_CAPI007 = array("at" => $at_CAPI007);
        
            $data_CAPI007 = array( );
            $data_CAPI007["account"] = "catchwellota";//비즈뿌리오계정
            $data_CAPI007["refkey"] = "00000";//고객사에서 부여한 키  임의의 값을 넣으라고 했음
            $data_CAPI007["type"] = "at";//메세지데이터타입
            $data_CAPI007["from"] = $defaultCallback;//발신번호
            $data_CAPI007["to"] = $customerPhone;//수신번호
            $data_CAPI007["content"] = $content_CAPI007;//메시지데이터
        
            $json_data = json_encode($data_CAPI007, JSON_UNESCAPED_SLASHES);
            if($DEBUG)
            {
            echo '<pre>';
            print_r($data_CAPI007);
            print_r($json_data);
            print_r(json_decode($json_data));
            echo '</pre>';
            }
            $Response_CAPI007 = httpsPost($url, $json_data, $headers1);
        
            $Ret_data       = (json_decode($Response_CAPI007));
            $code           = $Ret_data->code;// 결과 코드 code 1000이면 성공
            $description    = $Ret_data->description;//결과 메세지 success 출력
            $refkey_value   = $Ret_data->refkey;//고객사에서 부여한 키
            $messagekey     = $Ret_data->messagekey;//메세지키 고객문의 및 리포트 재요청 기준키 
        
            if ($DEBUG)
            {
                echo '<pre>';
                print_r( "-------------------code------------" );
                echo '<br>';
                print_r($code);// 결과 코드 code 1000이면 성공
                echo '<br>';
                print_r($description); //결과 메세지 success 출력
                echo '<br>';
                print_r($refkey_value); //고객사에서 부여한 키
                echo '<br>';
                print_r($messagekey);//메세지키 
                echo '<br>';
                echo '</pre>';
            }
        
            //$response = $noti->postMessage( $msgBody_applicationDone );
            //$resultMsg = $noti->getPostMsgResultText( $response->body->result_code );
            $resultMsg = $description;
            $send_cmid = $messagekey;//"2022111714032455420102";
        }
        else
        {
            $DEBUG = 0;
            //const
            $customerCenter = "070-7777-6752";
            $parcelCo = "CJ대한통운";
    
            // 출고완료 카톡알림 //CAPI008
            $send_from = "출고";

            $defaultCallback = "07077776752";
            $DEBUG = 0;

            $headers[0]="Accept:application/json"; 
            $headers[1]="Content-Type:application/json"; 
            $headers[2]="Authorization:Bearer";
            $url = 'https://api.bizppurio.com/v3/message';
            //추가
            $token = getToken();
            //print_r( $token);
            $headers1 = array('Accept:application/json', 'Content-Type:application/json', 'Authorization:Bearer '. $token);

            //CAPI008-------------------------------------------------------
            $button_list1_CAPI008 = array("name"=>"AS 조회", "type"=>"WL", "url_pc" =>"http://backup.catchwell.com/cw_as/online_as/online_as_customer_search.php?searchBy=searchbyRegisterNo&searchData=".$regNumber, "url_mobile"=>"http://backup.catchwell.com/cw_as/online_as/online_as_customer_search.php?searchBy=searchbyRegisterNo&searchData=".$regNumber);
            $button_list2_CAPI008 = array("name"=>"배송조회", "type"=>"DS");
            $buttons_CAPI008 = array($button_list1_CAPI008,$button_list2_CAPI008);

            $at_CAPI008["message"] = "안녕하세요. 캐치웰입니다.\n\n"
            .$customerName." 고객님이 접수하신 제품의 AS가 완료되어 금일 발송처리 되었습니다.\n\n".
            "* 접수번호 : ".$regNumber."\n".
            "* 제품명 : ".$modelName."\n".
            "* 고장증상 : ".$brokenType."\n".
            "* A/S내역 : ".$adminMemo."\n\n".
            "* 택배사 : ".$parcelCo."\n".
            "* 송장번호 : ".$parcelNo."\n\n".
            "관련 문의사항은 고객센터( $customerCenter )를 이용해 주세요.";

            $at_CAPI008["senderkey"] = "44970513f96ceef0a7b532fa874d4697ca936dce";
            $at_CAPI008["templatecode"] = "CAPI008";
            $at_CAPI008["button"] = $buttons_CAPI008;

            $content_CAPI008 = array("at" => $at_CAPI008);

            $data_CAPI008 = array( );
            $data_CAPI008["account"] = "catchwellota";//비즈뿌리오계정
            $data_CAPI008["refkey"] = "00000";//고객사에서 부여한 키  임의의 값을 넣으라고 했음
            $data_CAPI008["type"] = "at";//메세지데이터타입
            $data_CAPI008["from"] = $defaultCallback;//발신번호
            $data_CAPI008["to"] = $customerPhone;//수신번호
            $data_CAPI008["content"] = $content_CAPI008;//메시지데이터

            $json_data = json_encode($data_CAPI008, JSON_UNESCAPED_SLASHES);
            if($DEBUG)
            {
            echo '<pre>';
            print_r($data_CAPI008);
            print_r($json_data);
            print_r(json_decode($json_data));
            echo '</pre>';
            }
            $Response_CAPI008 = httpsPost($url, $json_data, $headers1);

            $Ret_data1       = (json_decode($Response_CAPI008));
            $code1           = $Ret_data1->code;// 결과 코드 code 1000이면 성공
            $description1    = $Ret_data1->description;//결과 메세지 success 출력
            $refkey_value1   = $Ret_data1->refkey;//고객사에서 부여한 키
            $messagekey1     = $Ret_data1->messagekey;//메세지키 고객문의 및 리포트 재요청 기준키 

            if ($DEBUG)
            {
                echo '<pre>';
                print_r( "-------------------code1------------" );
                echo '<br>';
                print_r($code1);// 결과 코드 code 1000이면 성공
                echo '<br>';
                print_r($description1); //결과 메세지 success 출력
                echo '<br>';
                print_r($refkey_value1); //고객사에서 부여한 키
                echo '<br>';
                print_r($messagekey1);//메세지키 
                echo '<br>';
                echo '</pre>';
            }

            //$response = $noti->postMessage( $msgBody_fixDone );
            //$resultMsg = $noti->getPostMsgResultText( $response->body->result_code );
            $resultMsg = $description1;
            $send_cmid = $messagekey1;//"2022111714032455420102";


        }
       
        //echo "<script>alert('{$resultMsg}'); history.back(-1);</script>";
        //echo "<script>location.replace('$return_url');</script>";
        //----------------------------------------------------------------------------------------------------------------------------
        
        // add cmid value to TB_REPORT_KAKAO(database)
        /*
        $send_date = $response->headers['Date'];
        $send_cmid = $response->body->cmid;
        */
        $send_date = date("Y-m-d H:i:s");
        //$send_cmid = "2022111714032455420102";
        //{"PHONE":"01011112222","RSLT":"0","CALLBACK":"0232894122","MSG_RSLT":"00","STATUS":"3","CMID":"2017052411064978"}

        /////
        $db_name = "TB_REPORT_KAKAO";
       
        if( $db->insert($db_name,
            "	SENDING='$send_from',
                DATE='$send_date',
                CMID='$send_cmid',
                STATUS='',
                REG_NO='$regNumber',
                PHONE='',
                CALLBACK='',
                RSLT='',
                MSG_RSLT=''
            "
        )) 
        {
            //echo "TB_REPORT_KAKAO successfully insert!!";
        }
        else
        {
            //echo mysqli_error( $db );
        }

        return true;
    }


    //상품출고 알림톡 20210223
    /*
        [캐치웰] 주문상품 발송 안내

        고객님이 주문하신 상품이 금일 발송처리 되었습니다.

        * 주문번호 : #{orderNumber}
        * 상품명 : #{modelName}
        * 택배사 : #{parcelCo}
        * 송장번호 : #{trackingNo}

        AS 및 기타 문의 사항은 고객센터(#{customerCenter})나 홈페이지를 이용해 주세요.

        홈페이지 신규 회원 가입시 드리는 20% 할인 쿠폰으로 다양한 혜택을 누리세요.
        이용해 주셔서 감사합니다. 
    */
    function shipmentNotiMsg($db, $customerName, $customerPhone, $orderNumber, $modelName, $trackingNo)
    {
        $DEBUG = 0;
        //const
        $customerCenter = "070-7777-6752";
        $parcelCo = "CJ대한통운";
        $defaultCallback = "07077776752";

        //20221117 bizppurio 알림톡 변경-----------------------------------------------------------------------------------------------
        $headers[0]="Accept:application/json"; 
        $headers[1]="Content-Type:application/json"; 
        $headers[2]="Authorization:Bearer";
        $url = 'https://api.bizppurio.com/v3/message';
        //추가
        $token = getToken();
        //print_r( $token);
        $headers1 = array('Accept:application/json', 'Content-Type:application/json', 'Authorization:Bearer '. $token);
        //CAPI011-------------------------------------------------------
        $button_list1 = array("name"=>"배송조회", "type"=>"WL", "url_pc" =>"http://nplus.doortodoor.co.kr/web/detail.jsp?slipno=".$trackingNo, "url_mobile"=>"http://nplus.doortodoor.co.kr/web/detail.jsp?slipno=".$trackingNo);
        $button_list2 = array("name"=>"홈페이지", "type"=>"WL", "url_pc" =>"https://www.catchwell.com", "url_mobile"=>"https://m.catchwell.com");  
        $buttons = array($button_list1,$button_list2);

        $at["message"] = "[캐치웰] 주문상품 발송 안내\n\n".
        "고객님이 주문하신 상품이 금일 발송처리 되었습니다.\n\n".
        "* 주문번호 : ".$orderNumber."\n".
        "* 상품명 : ".$modelName."\n".
        "* 택배사 : ".$parcelCo."\n".
        "* 송장번호 : ".$trackingNo."\n".
        "AS 및 기타 문의 사항은 고객센터( $customerCenter )나 홈페이지를 이용해 주세요.\n\n".
        "홈페이지 신규 회원 가입시 드리는 20% 할인 쿠폰으로 다양한 혜택을 누리세요.\n".
        "이용해 주셔서 감사합니다.\n";

        $at["senderkey"] = "44970513f96ceef0a7b532fa874d4697ca936dce";
        $at["templatecode"] = "CAPI011_11";//20%할인 미적용으로 중단
        $at["button"] = $buttons;
        $content = array("at" => $at);

        $data = array( );
        $data["account"] = "catchwellota";//비즈뿌리오계정
        $data["refkey"] = "00000";//고객사에서 부여한 키  임의의 값을 넣으라고 했음
        $data["type"] = "at";//메세지데이터타입
        $data["from"] = $defaultCallback;//발신번호
        $data["to"] = $customerPhone;//수신번호
        $data["content"] = $content;//메시지데이터

        $json_data = json_encode($data, JSON_UNESCAPED_SLASHES);
        if($DEBUG)
        {
        echo '<pre>';
        print_r($data);
        print_r($json_data);
        //print_r(urldecode($json_data));
        print_r(json_decode($json_data));
        echo '</pre>';
        }
        $Response = httpsPost($url, $json_data, $headers1);

        $Ret_data = (json_decode($Response));
        $code = $Ret_data->code;// 결과 코드 code 1000이면 성공
        $description = $Ret_data->description;//결과 메세지 success 출력
        $refkey_value = $Ret_data->refkey;//고객사에서 부여한 키
        $messagekey = $Ret_data->messagekey;//메세지키 고객문의 및 리포트 재요청 기준키 

        if ($DEBUG)
        {
            echo '<pre>';
            print_r( "-------------------code------------" );
            echo '<br>';
            echo '<br>';
            print_r($code);// 결과 코드 code 1000이면 성공
            echo '<br>';
            print_r($description); //결과 메세지 success 출력
            echo '<br>';
            print_r($refkey_value); //고객사에서 부여한 키
            echo '<br>';
            print_r($messagekey);//메세지키 
            echo '<br>';
            echo '</pre>';
        }

        //----------------------------------------------------------------------------------------------------------------------------
        /*
        $keyCode = "MTE1NDQtMTU2ODY5NzgyMTU4MS1kYTEwOWZjMi01MmFmLTQ1YTEtOTA5Zi1jMjUyYWY2NWExMWE=";
        $clientID = "catchwell";
        $defaultCallback = "07077776752";

        $noti = new CKakaoNotificationTalk( $keyCode, $clientID, $defaultCallback );
        $talkMsg = "[캐치웰] 주문상품 발송 안내\n\n"."고객님이 주문하신 상품이 금일 발송처리 되었습니다.\n\n* 주문번호 : ".$orderNumber."\n* 상품명 : ".$modelName."\n* 택배사 : ".$parcelCo."\n* 송장번호 : ".$trackingNo."\n\nAS 및 기타 문의 사항은 고객센터($customerCenter)나 홈페이지를 이용해 주세요.\n\n홈페이지 신규 회원 가입시 드리는 20% 할인 쿠폰으로 다양한 혜택을 누리세요.\n이용해 주셔서 감사합니다.\n";
        $failed_msg = "[캐치웰] 주문 상품 발송 안내\n\n■ 송장번호 : ".$trackingNo."(".$parcelCo.")"; //SMS 전송
        
        $deliveryLink_m = "http://nplus.doortodoor.co.kr/web/detail.jsp?slipno=".$trackingNo;
        $deliveryLink = "http://nplus.doortodoor.co.kr/web/detail.jsp?slipno=".$trackingNo;

        $homapageLink_m = "https://m.catchwell.com";
        $homapageLink = "https://www.catchwell.com";

        $msgBody_shipment = [
            "phone" => $customerPhone,         // 수신자 전화번호
            "callback" => $defaultCallback,   // 발신자 전화번호 
            "reqdate" => "",                  // 예약발송 기능("20191225100000" 형식), 비워두면 즉시발송
            "msg" => $talkMsg,
            "template_code" => "CAPI011",     // 템플릿으로 등록 승인된 템플릿의 코드값
            "failed_type" => "SMS",
            "failed_subject" => "[캐치웰]",
            "failed_msg" => $failed_msg,
            "btn_types" => "웹링크,웹링크",         // 카카오 알림톡 버튼타입     
            "btn_txts" => "배송조회,홈페이지",       // 카카오 알림톡 버튼이름
            "btn_urls1" => $deliveryLink_m.",".$homapageLink_m,
            "btn_urls2" => $deliveryLink.",".$homapageLink
        ];
         // 출고완료 카톡알림
        $send_from = "상품발송";
        $response = $noti->postMessage( $msgBody_shipment );
        $resultMsg = $noti->getPostMsgResultText( $response->body->result_code );
        */

        // 출고완료 카톡알림
        $send_from = "상품발송";
        //$response = $noti->postMessage( $msgBody_shipment );
        //$resultMsg = $noti->getPostMsgResultText( $response->body->result_code );
        $resultMsg = $description;
        $send_cmid = $messagekey;//"2022111714032455420102";
        
 
        //echo "<script>alert('{$resultMsg}'); history.back(-1);</script>";//이미주석
        //echo "<script>location.replace('$return_url');</script>";//이미주석
        
        
        // add cmid value to TB_REPORT_KAKAO(database)
       //$send_date = $response->headers['Date'];
        //$send_cmid = $response->body->cmid;
        $send_date = date("Y-m-d H:i:s");
        //$send_cmid = "2022111714032455420102";

        /////
        $db_name = "TB_SHIPMENT_KAKAO";
       
        if( $db->insert($db_name,
        "	SENDING='$send_from',
            DATE='$send_date',
            CMID='$send_cmid',
            STATUS='',
            TID_NO='$trackingNo',
            PHONE='',
            CALLBACK='',
            RSLT='',
            MSG_RSLT=''
        "
        )) 
        {
            //echo "TB_REPORT_KAKAO successfully insert!!";
        }
        else
        {
            //echo mysqli_error( $db );
        }

        return true;
    }    




    //이벤트 상품출고 알림톡 20210702
    /*
        [캐치웰] 사은품 발송 안내
        #{customerName} 고객님이 응모하신 #{eventName} 이벤트 사은품이 금일 발송 처리 되었습니다.

        ■ 상품명 : #{modelName}
        ■ 택배사 : #{parcelCo}
        ■ 송장번호 : #{trackingNo}

        ※ AS 및 기타 문의 사항은 고객센터(070-7777-6752)나 캐치웰 홈페이지를 이용해 주세요.

        ※ 홈페이지 회원에게만 드리는 다양한 할인 혜택도 함께 누리세요.

        ※ 항상 캐치웰을 이용해 주셔서 감사합니다.
    */
    function eventShipmentNotiMsg($db, $idx, $customerName, $customerPhone, $eventName, $modelName, $trackingNo)
    {
        $DEBUG = 0;
        //const
        $customerCenter = "070-7777-6752";
        $parcelCo = "CJ대한통운";
        //20221117 bizppurio 알림톡 변경------------------------------------------------------------------------------------------------
        $defaultCallback = "07077776752";
        $headers[0]="Accept:application/json"; 
        $headers[1]="Content-Type:application/json"; 
        $headers[2]="Authorization:Bearer";
        $url = 'https://api.bizppurio.com/v3/message';
        //추가
        $token = getToken();
        //print_r( $token);

        $headers1 = array('Accept:application/json', 'Content-Type:application/json', 'Authorization:Bearer '. $token);

        $talkMsg = "[캐치웰] 사은품 발송 안내\n" . $customerName . " 고객님이 응모하신 " . $eventName. " 이벤트 사은품이 금일 발송 처리 되었습니다.\n\n■ 상품명 : " . $modelName . "\n■ 택배사 : " . $parcelCo . "\n■ 송장번호 : ".$trackingNo . "\n\n※ AS 및 기타 문의 사항은 고객센터(070-7777-6752)나 캐치웰 홈페이지를 이용해 주세요.\n\n※ 홈페이지 회원에게만 드리는 다양한 할인 혜택도 함께 누리세요.\n\n※ 항상 캐치웰을 이용해 주셔서 감사합니다.\n";
        $failed_msg = "[캐치웰]이벤트 사은품 발송 안내\n송장번호 : ".$trackingNo."(".$parcelCo.")"; //SMS 전송
                
        $deliveryLink_m = "http://nplus.doortodoor.co.kr/web/detail.jsp?slipno=".$trackingNo;
        $deliveryLink = "http://nplus.doortodoor.co.kr/web/detail.jsp?slipno=".$trackingNo;
        $homapageLink_m = "https://www.catchwell.com";
        $homapageLink = "https://www.catchwell.com";

        $button_list1 = array("name"=>"배송조회", "type"=>"WL", "url_pc" => $deliveryLink, "url_mobile"=>$deliveryLink_m);
        $button_list2 = array("name"=>"캐치웰 홈페이지", "type"=>"WL", "url_pc" =>$homapageLink, "url_mobile"=>$homapageLink_m);
        $buttons = array($button_list1,$button_list2);

        $at["message"] = $talkMsg;
        $at["senderkey"] = "44970513f96ceef0a7b532fa874d4697ca936dce";
        $at["templatecode"] = "CAPI013";
        $at["button"] = $buttons;
        $content = array("at" => $at);

        $data = array( );
        $data["account"] = "catchwellota";//비즈뿌리오계정
        $data["refkey"] = "00000";//고객사에서 부여한 키  임의의 값을 넣으라고 했음
        $data["type"] = "at";//메세지데이터타입
        $data["from"] = $defaultCallback;//발신번호
        $data["to"] = $customerPhone;//수신번호
        $data["content"] = $content;//메시지데이터

        $json_data = json_encode($data, JSON_UNESCAPED_SLASHES);
        if($DEBUG)
        {
        echo '<pre>';
        print_r($data);
        print_r($json_data);
        //print_r(urldecode($json_data));
        print_r(json_decode($json_data));
        echo '</pre>';
        }
        $Response = httpsPost($url, $json_data, $headers1);

        $Ret_data = (json_decode($Response));
        $code = $Ret_data->code;// 결과 코드 code 1000이면 성공
        $description = $Ret_data->description;//결과 메세지 success 출력
        $refkey_value = $Ret_data->refkey;//고객사에서 부여한 키
        $messagekey = $Ret_data->messagekey;//메세지키 고객문의 및 리포트 재요청 기준키 

        if ($DEBUG)
        {
            echo '<pre>';
            print_r( "-------------------code------------" );
            echo '<br>';
            echo '<br>';
            print_r($code);// 결과 코드 code 1000이면 성공
            echo '<br>';
            print_r($description); //결과 메세지 success 출력
            echo '<br>';
            print_r($refkey_value); //고객사에서 부여한 키
            echo '<br>';
            print_r($messagekey);//메세지키 
            echo '<br>';
            echo '</pre>';
        }

        //----------------------------------------------------------------------------------------------------------------------------
        /*
        $keyCode = "MTE1NDQtMTU2ODY5NzgyMTU4MS1kYTEwOWZjMi01MmFmLTQ1YTEtOTA5Zi1jMjUyYWY2NWExMWE=";
        $clientID = "catchwell";
        $defaultCallback = "07077776752";
        $noti = new CKakaoNotificationTalk( $keyCode, $clientID, $defaultCallback );
        
        $talkMsg = "[캐치웰] 사은품 발송 안내\n" . $customerName . " 고객님이 응모하신 " . $eventName. " 이벤트 사은품이 금일 발송 처리 되었습니다.\n\n■ 상품명 : " . $modelName . "\n■ 택배사 : " . $parcelCo . "\n■ 송장번호 : ".$trackingNo . "\n\n※ AS 및 기타 문의 사항은 고객센터(070-7777-6752)나 캐치웰 홈페이지를 이용해 주세요.\n\n※ 홈페이지 회원에게만 드리는 다양한 할인 혜택도 함께 누리세요.\n\n※ 항상 캐치웰을 이용해 주셔서 감사합니다.\n";

        $failed_msg = "[캐치웰]이벤트 사은품 발송 안내\n송장번호 : ".$trackingNo."(".$parcelCo.")"; //SMS 전송
        
        $deliveryLink_m = "http://nplus.doortodoor.co.kr/web/detail.jsp?slipno=".$trackingNo;
        $deliveryLink = "http://nplus.doortodoor.co.kr/web/detail.jsp?slipno=".$trackingNo;

        $homapageLink_m = "https://www.catchwell.com";
        $homapageLink = "https://www.catchwell.com";

        $msgBody_shipment = [
            "phone" => $customerPhone,         // 수신자 전화번호
            "callback" => $defaultCallback,   // 발신자 전화번호 
            "reqdate" => "",                  // 예약발송 기능("20191225100000" 형식), 비워두면 즉시발송
            "msg" => $talkMsg,
            "template_code" => "CAPI013",     // 템플릿으로 등록 승인된 템플릿의 코드값
            "failed_type" => "SMS",
            "failed_subject" => "[캐치웰]",
            "failed_msg" => $failed_msg,
            "btn_types" => "웹링크,웹링크",         // 카카오 알림톡 버튼타입     
            "btn_txts" => "배송조회,캐치웰 홈페이지",       // 카카오 알림톡 버튼이름
            "btn_urls1" => $deliveryLink_m.",".$homapageLink_m,
            "btn_urls2" => $deliveryLink.",".$homapageLink
        ];
         // 출고완료 카톡알림
        $response = $noti->postMessage( $msgBody_shipment );
        $resultMsg = $noti->getPostMsgResultText( $response->body->result_code );
        */
 
        // 출고완료 카톡알림
        //$response = $noti->postMessage( $msgBody_shipment );
        //$resultMsg = $noti->getPostMsgResultText( $response->body->result_code );
        $resultMsg = $description;
        $send_cmid = $messagekey;//"2022111714032455420102";
      
 
        //echo "<script>alert('{$resultMsg}'); history.back(-1);</script>";
        //echo "<script>location.replace('$return_url');</script>";
        
        
        // add cmid value to TB_REPORT_KAKAO(database)
        //$send_date = $response->headers['Date'];
        //$send_cmid = $response->body->cmid;
        
        $send_date = date("Y-m-d H:i:s");
        //$send_cmid = "2022111714032455420102";

        /////
        $db_name = "TB_EVENT_KAKAO";
       
        if( $db->insert($db_name,
        "	SENDING='사은품발송',
            DATE='$send_date',
            CMID='$send_cmid',
            STATUS='$trackingNo',
            OID_NO='$oid',
            PHONE='$customerPhone',
            CALLBACK='',
            RSLT='',
            MSG_RSLT=''
        "
        )) 
        {
            //echo "TB_REPORT_KAKAO successfully insert!!";
        }
        else
        {
            //echo mysqli_error( $db );
        }

        return true;
    }    
    
    
    //20211216
    //CAPI014
    //ORDER_상품출고알림3
     /*
        [캐치웰] 주문상품 배송 안내

        안녕하세요. 고객님, 주문하신 상품의 배송이 시작되었습니다.

        ■ 배송출발일 : #{yyyymmdd}
        ■ 주문번호 : #{orderNumber}
        ■ 택배사 : CJ대한통운
        ■ 운송장 : #{trackingNo}

        ※ 상품의 도착예정일은 택배사 또는 지역 사정에 따라 일부 변경될 수 있습니다.

        [품질 보증기간]
        - 구매일로부터 2년 (배터리 1년)

        [유상 A/S]
        - 제품 파손 및 소모성 부품의 수명이 다한 경우

        ▶ A/S 접수 방법
        - 아래 링크로 접속하시면 A/S 접수가 가능합니다.
        https://catchwell.com/catchwell/AS/as_info.html

        ▶ 소모품 구매 방법
        - 아래 링크로 접속하시면 구매가 가능합니다.
        https://catchwell.com/category/소모품/89/

    */

    function shipmentNotiMsg_CAPI014($db, $customerPhone, $orderNumber, $trackingNo)
    {
        $DEBUG = 0;
        //const
        $customerCenter = "070-7777-6752";
        $parcelCo = "CJ대한통운";
        $defaultCallback = "07077776752";

        //20221117 bizppurio 알림톡 변경-----------------------------------------------------------------------------------------------
        $headers[0]="Accept:application/json"; 
        $headers[1]="Content-Type:application/json"; 
        $headers[2]="Authorization:Bearer";
        $url = 'https://api.bizppurio.com/v3/message';
        //추가
        $token = getToken();
        //print_r( $token);
        $headers1 = array('Accept:application/json', 'Content-Type:application/json', 'Authorization:Bearer '. $token);

        $button_list1 = array("name"=>"배송조회", "type"=>"WL", "url_pc" =>"http://nplus.doortodoor.co.kr/web/detail.jsp?slipno=".$trackingNo, "url_mobile"=>"http://nplus.doortodoor.co.kr/web/detail.jsp?slipno=".$trackingNo);
        $button_list2 = array("name"=>"A/S 접수", "type"=>"WL", "url_pc" =>"https://www.catchwell.com/catchwell/AS/as_info.html", "url_mobile"=>"https://m.catchwell.com/catchwell/AS/as_info.html");
        $button_list3 = array("name"=>"소모품 구매", "type"=>"WL", "url_pc" =>"https://catchwell.com/category/%EC%86%8C%EB%AA%A8%ED%92%88/89/", "url_mobile"=>"https://m.catchwell.com/product/list_thumb.html?cate_no=89");
      
        $buttons = array($button_list1,$button_list2,$button_list3);

        $at["message"] = "[캐치웰] 주문상품 배송 안내\n\n".
        "안녕하세요. 고객님, 주문하신 상품의 배송이 시작되었습니다.\n\n".

        "■ 배송출발일 : ".date('Y/m/d')."\n".
        "■ 주문번호 : ".$orderNumber."\n".
        "■ 택배사 : ".$parcelCo."\n".
        "■ 운송장 : ".$trackingNo."\n\n".
        "※ 상품의 도착예정일은 택배사 또는 지역 사정에 따라 일부 변경될 수 있습니다.\n\n".
        "[품질 보증기간]\n".
        "- 구매일로부터 2년 (배터리 1년)\n\n".
        "[유상 A/S]\n".
        "- 제품 파손 및 소모성 부품의 수명이 다한 경우\n\n".
        "▶ A/S 접수 방법\n".
        "- 아래 링크로 접속하시면 A/S 접수가 가능합니다.\n".
        "https://catchwell.com/catchwell/AS/as_info.html\n".
        "▶ 소모품 구매 방법\n".
        "- 아래 링크로 접속하시면 구매가 가능합니다.\n".
        "https://catchwell.com/category/소모품/89/\n";

        $at["senderkey"] = "44970513f96ceef0a7b532fa874d4697ca936dce";
        $at["templatecode"] = "CAPI014";
        $at["button"] = $buttons;

        $content = array("at" => $at);

        $data = array( );
        $data["account"] = "catchwellota";//비즈뿌리오계정
        $data["refkey"] = "00000";//고객사에서 부여한 키  임의의 값을 넣으라고 했음
        $data["type"] = "at";//메세지데이터타입
        $data["from"] = $defaultCallback;//발신번호
        $data["to"] = $customerPhone;//수신번호
        $data["content"] = $content;//메시지데이터

        $json_data = json_encode($data, JSON_UNESCAPED_SLASHES);
        if($DEBUG)
        {
        echo '<pre>';
        print_r($data);
        print_r($json_data);
       
        print_r(json_decode($json_data));
        echo '</pre>';
        }
        $Response = httpsPost($url, $json_data, $headers1);

        $Ret_data = (json_decode($Response));
        $code = $Ret_data->code;// 결과 코드 code 1000이면 성공
        $description = $Ret_data->description;//결과 메세지 success 출력
        $refkey_value = $Ret_data->refkey;//고객사에서 부여한 키
        $messagekey = $Ret_data->messagekey;//메세지키 고객문의 및 리포트 재요청 기준키 

        if ($DEBUG)
        {
            echo '<pre>';
            print_r( "-------------------code------------" );
            echo '<br>';
            echo '<br>';
            print_r($code);// 결과 코드 code 1000이면 성공
            echo '<br>';
            print_r($description); //결과 메세지 success 출력
            echo '<br>';
            print_r($refkey_value); //고객사에서 부여한 키
            echo '<br>';
            print_r($messagekey);//메세지키 
            echo '<br>';
            echo '</pre>';
        }
 
        //----------------------------------------------------------------------------------------------------------------------------
        /*
        $keyCode = "MTE1NDQtMTU2ODY5NzgyMTU4MS1kYTEwOWZjMi01MmFmLTQ1YTEtOTA5Zi1jMjUyYWY2NWExMWE=";
        $clientID = "catchwell";
        $defaultCallback = "07077776752";

        $noti = new CKakaoNotificationTalk( $keyCode, $clientID, $defaultCallback );
       
        $talkMsg = "[캐치웰] 주문상품 배송 안내\n\n안녕하세요. 고객님, 주문하신 상품의 배송이 시작되었습니다.\n\n■ 배송출발일 : ".date('Y/m/d')."\n■ 주문번호 : ".$orderNumber."\n■ 택배사 : ".$parcelCo."\n■ 운송장 : ".$trackingNo."\n\n※ 상품의 도착예정일은 택배사 또는 지역 사정에 따라 일부 변경될 수 있습니다.\n\n[품질 보증기간]\n- 구매일로부터 2년 (배터리 1년)\n\n[유상 A/S]\n- 제품 파손 및 소모성 부품의 수명이 다한 경우\n\n▶ A/S 접수 방법\n- 아래 링크로 접속하시면 A/S 접수가 가능합니다.\nhttps://catchwell.com/catchwell/AS/as_info.html\n\n▶ 소모품 구매 방법\n- 아래 링크로 접속하시면 구매가 가능합니다.\nhttps://catchwell.com/category/소모품/89/\n\n";

        $failed_msg = "[캐치웰] 주문 상품 배송 안내\n\n■ 송장번호 : ".$trackingNo."(".$parcelCo.")"; //SMS 전송
        
        $deliveryLink_m = "http://nplus.doortodoor.co.kr/web/detail.jsp?slipno=".$trackingNo;
        $deliveryLink = "http://nplus.doortodoor.co.kr/web/detail.jsp?slipno=".$trackingNo;

        $asLink_m = "https://m.catchwell.com/catchwell/AS/as_info.html";
        $asLink = "https://www.catchwell.com/catchwell/AS/as_info.html";

        $mallLink_m = "https://m.catchwell.com/product/list_thumb.html?cate_no=89";
        $mallLink = "https://catchwell.com/category/%EC%86%8C%EB%AA%A8%ED%92%88/89/";

        $msgBody_shipment = [
            "phone" => $customerPhone,         // 수신자 전화번호
            "callback" => $defaultCallback,   // 발신자 전화번호 
            "reqdate" => "",                  // 예약발송 기능("20191225100000" 형식), 비워두면 즉시발송
            "msg" => $talkMsg,
            "template_code" => "CAPI014",     // 템플릿으로 등록 승인된 템플릿의 코드값
            "failed_type" => "SMS",
            "failed_subject" => "[캐치웰]",
            "failed_msg" => $failed_msg,
            "btn_types" => "웹링크,웹링크,웹링크",   // 카카오 알림톡 버튼타입     
            "btn_txts" => "배송조회,A/S 접수,소모품 구매",       // 카카오 알림톡 버튼이름
            "btn_urls1" => $deliveryLink_m.",".$asLink_m.",".$mallLink_m,
            "btn_urls2" => $deliveryLink.",".$asLink.",".$mallLink
        ];
        // 출고완료 카톡알림
        $send_from = "상품발송";
        $response = $noti->postMessage( $msgBody_shipment );
        $resultMsg = $noti->getPostMsgResultText( $response->body->result_code );
        */

        // 출고완료 카톡알림
        $send_from = "상품발송";
        //$response = $noti->postMessage( $msgBody_shipment );
        //$resultMsg = $noti->getPostMsgResultText( $response->body->result_code );
        $resultMsg = $description;
        $send_cmid = $messagekey;//"2022111714032455420102";
        
        //echo "<script>alert('{$resultMsg}'); history.back(-1);</script>";
        //echo "<script>location.replace('$return_url');</script>";
       
        //----------------------------------------------------------------------------------------------------------------------------
        // add cmid value to TB_REPORT_KAKAO(database)
        //$send_date = $response->headers['Date'];
        //$send_cmid = $response->body->cmid;
        $send_date = date("Y-m-d H:i:s");
        //$send_cmid = "2022111714032455420102";
        
        /////
        $db_name = "TB_SHIPMENT_KAKAO";
       
        if( $db->insert($db_name,
        "	SENDING='$send_from',
            DATE='$send_date',
            CMID='$send_cmid',
            STATUS='',
            TID_NO='$trackingNo',
            PHONE='',
            CALLBACK='',
            RSLT='',
            MSG_RSLT=''
        "
        )) 
        {
            //echo "TB_REPORT_KAKAO successfully insert!!";
        }
        else
        {
            //echo mysqli_error( $db );
        }

        return true;        
    }
	function shipmentNotiMsg_estimate($db, $customerPhone, $regNumber, $customerName, $modelName, $brokenType, $checkresult)
    {
		$defaultCallback = "07077776752";
        $DEBUG = 0;
        //const
        $homapageLink_m = "https://m.catchwell.com";
        $homapageLink = "https://www.catchwell.com";
		$estimate_link = "https://csadmin.catchwell.com/cw_as/online_as/online_as_estimate.php?searchData=" . $regNumber . "&searchValuePhone=" . $customerPhone;

        //20221117 bizppurio 알림톡 변경-----------------------------------------------------------------------------------------------
        $headers[0]="Accept:application/json"; 
        $headers[1]="Content-Type:application/json"; 
        $headers[2]="Authorization:Bearer";
        $url = 'https://api.bizppurio.com/v3/message';
        //추가
        $token = getToken();
        //print_r( $token);
        $headers1 = array('Accept:application/json', 'Content-Type:application/json', 'Authorization:Bearer '. $token);

        $button_list1 = array("name"=>"견적서 확인하기", "type"=>"WL", "url_pc" =>$estimate_link, "url_mobile"=>$estimate_link);
        $button_list2 = array("name"=>"홈페이지", "type"=>"WL", "url_pc" =>$homapageLink, "url_mobile"=>$homapageLink_m);
      
        $buttons = array($button_list1,$button_list2);

        $at["message"] = "안녕하세요. 캐치웰입니다.\n\n"
            .$customerName."님께서 맡겨주신 A/S 건이 도착하여 점검을 마쳤습니다.\n".
            "점검 결과, 아래와 같이 수리가 필요하며 소정의 비용이 발생하게 되었습니다.\n\n".
            "* 접수번호  : ".$regNumber."\n".
            "* 제품명  : ".$modelName."\n".
            "* 고장증상  : ".$brokenType."\n".
            "* 점검결과 : ".$checkresult."\n\n".
            "견적서를 확인후 수리여부를 선택해 주시기 바랍니다.";

        $at["senderkey"] = "44970513f96ceef0a7b532fa874d4697ca936dce";
        $at["templatecode"] = "bizp_2024092711051835514785083";
        $at["button"] = $buttons;

        $content = array("at" => $at);

        $data = array( );
        $data["account"] = "catchwellota";//비즈뿌리오계정
        $data["refkey"] = "00000";//고객사에서 부여한 키  임의의 값을 넣으라고 했음
        $data["type"] = "at";//메세지데이터타입
        $data["from"] = $defaultCallback;//발신번호
        $data["to"] = $customerPhone;//수신번호
        $data["content"] = $content;//메시지데이터

        $json_data = json_encode($data, JSON_UNESCAPED_SLASHES);
        if($DEBUG)
        {
        echo '<pre>';
        print_r($data);
        print_r($json_data);
       
        print_r(json_decode($json_data));
        echo '</pre>';
        }
        $Response = httpsPost($url, $json_data, $headers1);

        $Ret_data = (json_decode($Response));
        $code = $Ret_data->code;// 결과 코드 code 1000이면 성공
        $description = $Ret_data->description;//결과 메세지 success 출력
        $refkey_value = $Ret_data->refkey;//고객사에서 부여한 키
        $messagekey = $Ret_data->messagekey;//메세지키 고객문의 및 리포트 재요청 기준키 

        if ($DEBUG)
        {
            echo '<pre>';
            print_r( "-------------------code------------" );
            echo '<br>';
            echo '<br>';
            print_r($code);// 결과 코드 code 1000이면 성공
            echo '<br>';
            print_r($description); //결과 메세지 success 출력
            echo '<br>';
            print_r($refkey_value); //고객사에서 부여한 키
            echo '<br>';
            print_r($messagekey);//메세지키 
            echo '<br>';
            echo '</pre>';
        }
 
        
        $send_from = "견적서 발송";
       
        $resultMsg = $description;
        $send_cmid = $messagekey;//"2022111714032455420102";
        
     
        $send_date = date("Y-m-d H:i:s");
   
        $db_name = "TB_SHIPMENT_KAKAO";
       
        if( $db->insert($db_name,
        "	SENDING='$send_from',
            DATE='$send_date',
            CMID='$send_cmid',
            STATUS='',
            TID_NO='$regNumber',
            PHONE='$customerPhone',
            CALLBACK='',
            RSLT='',
            MSG_RSLT=''
        "
        )) 
        {
            //echo "TB_REPORT_KAKAO successfully insert!!";
        }
        else
        {
            //echo mysqli_error( $db );
        }

        return true;        
    }
	
	function NotiMsg_inicis_ok($db, $customerPhone, $regNumber)
    {
		$defaultCallback = "07077776752";
        $DEBUG = 0;
        //const
        $homapageLink_m = "https://m.catchwell.com";
        $homapageLink = "https://www.catchwell.com";
		$estimate_link = "http://backup.catchwell.com/cw_as/online_as/online_as_customer_search.php?searchBy=searchbyRegisterNo&searchData=".$regNumber;

        //20221117 bizppurio 알림톡 변경-----------------------------------------------------------------------------------------------
        $headers[0]="Accept:application/json"; 
        $headers[1]="Content-Type:application/json"; 
        $headers[2]="Authorization:Bearer";
        $url = 'https://api.bizppurio.com/v3/message';
        //추가
        $token = getToken();
        //print_r( $token);
        $headers1 = array('Accept:application/json', 'Content-Type:application/json', 'Authorization:Bearer '. $token);

        $button_list1 = array("name"=>"AS 조회", "type"=>"WL", "url_pc" =>$estimate_link, "url_mobile"=>$estimate_link);
        $button_list2 = array("name"=>"홈페이지", "type"=>"WL", "url_pc" =>$homapageLink, "url_mobile"=>$homapageLink_m);
      
        $buttons = array($button_list1,$button_list2);

        $at["message"] = "안녕하세요 캐치웰입니다.\n\n".
            "제품 수거를 위한 택배비 입금이 확인되었습니다.\n".
            "소중한 고객님의 제품, 빠른 시일내에 처리될 수 있도록 최선을 다하겠습니다.\n\n".
            "관련 문의사항은 고객센터 070 7777 6752 를 이용해 주세요.";

        $at["senderkey"] = "44970513f96ceef0a7b532fa874d4697ca936dce";
        $at["templatecode"] = "bizp_2025010911080092092967846";
        $at["button"] = $buttons;

        $content = array("at" => $at);

        $data = array( );
        $data["account"] = "catchwellota";//비즈뿌리오계정
        $data["refkey"] = "00000";//고객사에서 부여한 키  임의의 값을 넣으라고 했음
        $data["type"] = "at";//메세지데이터타입
        $data["from"] = $defaultCallback;//발신번호
        $data["to"] = $customerPhone;//수신번호
        $data["content"] = $content;//메시지데이터

        $json_data = json_encode($data, JSON_UNESCAPED_SLASHES);
        if($DEBUG)
        {
        echo '<pre>';
        print_r($data);
        print_r($json_data);
       
        print_r(json_decode($json_data));
        echo '</pre>';
        }
        $Response = httpsPost($url, $json_data, $headers1);

        $Ret_data = (json_decode($Response));
        $code = $Ret_data->code;// 결과 코드 code 1000이면 성공
        $description = $Ret_data->description;//결과 메세지 success 출력
        $refkey_value = $Ret_data->refkey;//고객사에서 부여한 키
        $messagekey = $Ret_data->messagekey;//메세지키 고객문의 및 리포트 재요청 기준키 

        if ($DEBUG)
        {
            echo '<pre>';
            print_r( "-------------------code------------" );
            echo '<br>';
            echo '<br>';
            print_r($code);// 결과 코드 code 1000이면 성공
            echo '<br>';
            print_r($description); //결과 메세지 success 출력
            echo '<br>';
            print_r($refkey_value); //고객사에서 부여한 키
            echo '<br>';
            print_r($messagekey);//메세지키 
            echo '<br>';
            echo '</pre>';
        }
 
        
        $send_from = "견적서 발송";
       
        $resultMsg = $description;
        $send_cmid = $messagekey;//"2022111714032455420102";
        
     
        $send_date = date("Y-m-d H:i:s");
   
        $db_name = "TB_SHIPMENT_KAKAO";
       
        if( $db->insert($db_name,
        "	SENDING='$send_from',
            DATE='$send_date',
            CMID='$send_cmid',
            STATUS='',
            TID_NO='$regNumber',
            PHONE='$customerPhone',
            CALLBACK='',
            RSLT='',
            MSG_RSLT=''
        "
        )) 
        {
            //echo "TB_REPORT_KAKAO successfully insert!!";
        }
        else
        {
            //echo mysqli_error( $db );
        }

        return true;        
    }
	//고객사진을 전송받기 위한 알림톡 발송
	function NotiMsg_picture_get($customerPhone, $TempleteCode)
    {
		$defaultCallback = "07077776752";
        $DEBUG = 0;

        //20221117 bizppurio 알림톡 변경-----------------------------------------------------------------------------------------------
        $headers[0]="Accept:application/json"; 
        $headers[1]="Content-Type:application/json"; 
        $headers[2]="Authorization:Bearer";
        $url = 'https://api.bizppurio.com/v3/message';
        //추가
        $token = getToken();
        //print_r( $token);
        $headers1 = array('Accept:application/json', 'Content-Type:application/json', 'Authorization:Bearer '. $token);

        $at["message"] = "안녕하세요, 캐치웰 C/S 담당자입니다.\n".
            "문의 주신 내용에 대해 검토를 진행하기 위해 문제가 발생한 사진을 보내주시면 감사하겠습니다.\n".
            "다만, 해당 채팅방은 사진 수신 전용으로, 채팅 상담은 어려운 점 양해 부탁드립니다.";

        $at["senderkey"] = "c13c5f26eb2cbfaa679cf4fe97abe2e49b080241";
        $at["templatecode"] = "bizp_2025042116492619361639091";

        $content = array("at" => $at);

        $data = array( );
        $data["account"] = "catchwellota";//비즈뿌리오계정
        $data["refkey"] = "00000";//고객사에서 부여한 키  임의의 값을 넣으라고 했음
        $data["type"] = "at";//메세지데이터타입
        $data["from"] = $defaultCallback;//발신번호
        $data["to"] = $customerPhone;//수신번호
        $data["content"] = $content;//메시지데이터

        $json_data = json_encode($data, JSON_UNESCAPED_SLASHES);
        if($DEBUG)
        {
        echo '<pre>';
        print_r($data);
        print_r($json_data);
       
        print_r(json_decode($json_data));
        echo '</pre>';
        }
        $Response = httpsPost($url, $json_data, $headers1);

        $Ret_data = (json_decode($Response));
        $code = $Ret_data->code;// 결과 코드 code 1000이면 성공
        $description = $Ret_data->description;//결과 메세지 success 출력
        $refkey_value = $Ret_data->refkey;//고객사에서 부여한 키
        $messagekey = $Ret_data->messagekey;//메세지키 고객문의 및 리포트 재요청 기준키 

        if ($DEBUG)
        {
            echo '<pre>';
            print_r( "-------------------code------------" );
            echo '<br>';
            echo '<br>';
            print_r($code);// 결과 코드 code 1000이면 성공
            echo '<br>';
            print_r($description); //결과 메세지 success 출력
            echo '<br>';
            print_r($refkey_value); //고객사에서 부여한 키
            echo '<br>';
            print_r($messagekey);//메세지키 
            echo '<br>';
            echo '</pre>';
        }
 

        return true;        
    }
}

?>