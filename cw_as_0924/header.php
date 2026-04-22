<? //session_name("CW_AS");
session_start();
include ("common.php");
require ("check_session.php");
include_once ("def_inc.php");

header('Cache-Control:no cache');
session_cache_limiter('private_no_expire');

?>

<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta charset="utf-8">
	
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="">
	<meta name="author" content="">

	<title>Catchwell_CS_Admin</title>

    <link href="<?=$site_url?>/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?=$site_url?>/css/skin/dashboard.css" rel="stylesheet">

    <!-- Just for debugging purposes. Don't actually copy these 2 lines! -->
    <!--[if lt IE 9]><script src="/js/assets/ie8-responsive-file-warning.js"></script><![endif]-->
    <script src="<?=$site_url?>/js/assets/ie-emulation-modes-warning.js"></script>

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script src="<?=$site_url?>/js/bootstrap.min.js"></script>
    <script src="<?=$site_url?>/js/docs.min.js"></script>
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <script src="<?=$site_url?>/js/assets/ie10-viewport-bug-workaround.js"></script>

	 <!-- calendar 
	 ================================================== -->
	<link rel="stylesheet" type="text/css" media="screen" href="<?=$site_url?>/calendar/css/bootstrap-datetimepicker.min.css" />
	<script type="text/javascript" src="<?=$site_url?>/calendar/js/moment.js"></script>
	<script type="text/javascript" src="<?=$site_url?>/calendar/js/bootstrap-datetimepicker.js"></script> 
	
	<!-- ETC JavaScript
	==================================================-->
	<!--<script src="<?=$site_url?>/js/admin.js"></script>-->
	<script src="<?=$site_url?>/js/myadmin.js?ver=20220214"></script>
	<script src="http://dmaps.daum.net/map_js_init/postcode.v2.js"></script>

	<!-- CHART JavaScript
	==================================================-->
	<script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>



	<style type="text/css" >
    .wrap-loading div{ /*로딩 이미지*/
        position: fixed;
        top:50%;
        left:50%;
        margin-left: -21px;
        margin-top: -21px;
    }
    .display-none{ /*감추기*/
        display:none;
	}

	/*20210113*/
	/* Chrome, Safari, Edge, Opera */
	input::-webkit-outer-spin-button,
	input::-webkit-inner-spin-button {
	-webkit-appearance: none;
	margin: 0;
	}
	/* Firefox */
	input[type=number] {
	-moz-appearance: textfield;
	}
	
	</style>
	
</head>
<body>


<!-- TOP NAV BAR -->
<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
	<div class="container-fluid">
		<div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="<?=$site_url?>/main.php"><font color=white>캐치웰</font></a>
		</div>

		<div id="navbar" class="navbar-collapse collapse">
	  
			<ul class="nav navbar-nav navbar-left">
			<!-- 상단메뉴 -->
			<? if (($PERMISSION & PERMISSION_GROUP_CS) == PERMISSION_GROUP_CS) { ?>
			<li class="dropdown">
				<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">AS신청서 관리 <span class="caret"></span></a>
				<ul class="dropdown-menu" role="menu">
					<li><a href="<?=$site_url?>/online_as/online_as_edit.php">신규신청서 등록</a></li>
                    <li><a href="<?=$site_url?>/online_as/online_as.php?state=<?echo ST_REGISTERING;?>">접수중 보기</a></li>
					<li><a href="<?=$site_url?>/online_as/online_as.php?state=<?echo ST_DC;?>">수거 택배비 입금</a></li>
                    <li><a href="<?=$site_url?>/online_as/online_as.php?state=<?echo ST_REG_DONE;?>">접수완료 보기</a></li>
					<li><a href="<?=$site_url?>/online_as/online_as.php?state=<?echo ST_FIXING;?>">견적완료 보기</a></li>
					<li><a href="<?=$site_url?>/online_as/online_as.php?state=<?echo ST_FIX_DONE;?>">수리완료 보기</a></li>

					<li><a href="<?=$site_url?>/online_as/online_as_shipment.php">AS 출고완료</a></li><!--20230707 출고완료 추가 -->
					<li><a href="<?=$site_url?>/online_as/online_as_report.php">AS 전체 검색</a></li><!--20230707 전체검색 -->

					<? if (($PERMISSION & PERMISSION_ALL) == PERMISSION_ALL) { ?>
					<!--li><a href="<?=$site_url?>/online_as/online_as_analysis.php">AS 통계 분석</a></li--> <!--20210128-->
					<? } ?>
					<li><a href="<?=$site_url?>/online_as/online_as_edit_m.php">수리 업무 진행</a></li>
               </ul>
			</li>
			<? } ?>

			<!--20210322-->
			<? if (($PERMISSION & PERMISSION_GROUP_CS) == PERMISSION_GROUP_CS) { ?>
				<li class="dropdown">
				<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">출고/반품/교환 관리 <span class="caret"></span></a>
				<ul class="dropdown-menu" role="menu">
					<li><a href="<?=$site_url?>/cancellation/cancellation_edit.php">반품/교환 신청서 등록</a></li>
					<li><a href="<?=$site_url?>/cancellation/cancellation_list.php">반품/교환 리스트</a></li>
					<li><a href="<?=$site_url?>/cancellation/exchange_list.php">교환출고 요청 리스트</a></li><!-- 20230515 -->
					<!--li><a href="<?=$site_url?>/cancellation/exchange_list.php">교환출고 요청 리스트</a></li--><!-- 20230515 -->
					<li style="margin-top:5px; border-top:1px solid #ccc;"><a href="<?=$site_url?>/internal_orders/cs_internal_orders_edit.php" >사내판매 출고 등록</a></li><!--20211203-->
					<li><a href="<?=$site_url?>/internal_orders/cs_internal_orders_list.php">사내판매 출고 요청</a></li>
					<li><a href="<?=$site_url?>/internal_orders/cs_internal_orders_list.php?shipment=1">사내판매 출고 완료</a></li> <!--20211206-->
				</ul>
			   </li>
			<? } ?>

			<!--20211117-->
			<? if (($PERMISSION & PERMISSION_GROUP_CS) == PERMISSION_GROUP_CS) { ?>
			<li class="dropdown">
			    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">고객센터 콜 관리 <span class="caret"></span></a>
			    <ul class="dropdown-menu" role="menu">
					<li><a href="<?=$site_url?>/cs_inbound/cs_inbound_edit.php">CS 콜 등록</a></li>
					<li><a href="<?=$site_url?>/cs_inbound/cs_inbound_list.php">CS 콜 리스트</a></li>
					<li><a href="<?=$site_url?>/cs_inbound/cs_inbound_admin_callback.php">관리자 전화요청 리스트</a></li>

					<li style="margin-top:5px; border-top:1px solid #ccc;"><a href="<?=$site_url?>/cs_inbound/cs_parts_edit.php" >부품출고 등록</a></li><!--20211203-->
					<li><a href="<?=$site_url?>/cs_inbound/cs_parts_list.php">부품출고 요청 리스트</a></li>
					<li><a href="<?=$site_url?>/cs_inbound/cs_parts_list.php?shipment=1">부품출고 완료 조회</a></li> <!--20211206-->
					<!--li><a href="<?=$site_url?>/cs_inbound/cs_inbound_chart.php">CS 콜 통계 분석</a></li-->
			    </ul>
			</li>
			<? } ?>

<!--20210224
			<? if (($PERMISSION & PERMISSION_ALL) == PERMISSION_ALL) { ?>
			<li><a href="<?=$site_url?>/banner/banner.php">배너 관리</a></li>
			<? } ?>
-->
			<? if (($PERMISSION & PERMISSION_GROUP_SALES) == PERMISSION_GROUP_SALES) { ?>
			<li><a href="<?=$site_url?>/online_event/online_event.php">이벤트 응모 관리</a></li>
			<? } ?>

			<? if (($PERMISSION & PERMISSION_GROUP_SHIPMENT) == PERMISSION_GROUP_SHIPMENT) { ?>
			<li><a href="<?=$site_url?>/shipment/shipment_new.php">출고 관리</a></li>
			<? } ?> <!--20210219-->

	
			<!--20240613-->
			<? if ($PERMISSION > PERMISSION_NONE) { ?>
			<li class="dropdown">
			    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">TOOL <span class="caret"></span></a>
			    <ul class="dropdown-menu" role="menu">
					<li><a href="<?=$site_url?>/talk_sender/talk_sender.php">알림톡 발송</a></li>
					<li><a href="<?=$site_url?>/sms/sms_view.php">메시지</a></li>
					<li><a href="<?=$site_url?>/cert/cert_query.php">인증번호 검색</a></li>
					<li><a href="<?=$site_url?>/tracking/tracking.php">택배조회</a></li>
					<li><a href="http://webhard.catchwell.com/preview/">제품별 상세페이지</a></li>
					<!--li><a href="<?=$site_url?>/cs_inbound/cs_inbound_chart.php">CS 콜 통계 분석</a></li-->
					<!--li><a href="<?=$site_url?>/online_as/online_as_analysis.php">AS 통계 분석</a></li--> <!--20210128-->
			    </ul>
			</li>
			<? } ?>

			<? if ($PERMISSION > PERMISSION_NONE) { ?>
			<li><a href="<?=$site_url?>/setting/setting.php">설정</a></li> <!--20220107-->
			<? } ?>
			</ul>


			<ul class="nav navbar-nav navbar-right">
			
			<li><?if($ADMIN_USERID){?><a href="https://www.catchwell.com" class="navbar-link" target="_blank">홈페이지</a><?}?></li>
            <li><?if($ADMIN_USERID){?><a href="<?=$site_url?>/login_progress.php?logout=1" class="navbar-link"><?echo $ADMIN_NAME;?> 로그아웃</a><?}?></li>
			</ul>

        </div>
	</div>
</nav>`

	

	<div class="col-sm-3 col-md-2 sidebar">
		<div class="row">
            <div class="panel panel-default">

				<?if( $mod == M_MAIN ){?>
				<div class="panel-heading"><h3 class="panel-title">메인 관리메뉴</h3></div>
					<? if (($PERMISSION & PERMISSION_GROUP_CS) == PERMISSION_GROUP_CS) { ?>
						<a href="<?=$site_url?>/online_as/online_as.php" class="list-group-item <?if($menu==S_AS_REGISTERING){?>active<?}?>">AS신청서 관리</a>
					<?}?>

					<!--20210322-->
					<? if (($PERMISSION & PERMISSION_GROUP_CS) == PERMISSION_GROUP_CS) { ?>
						<a href="<?=$site_url?>/cancellation/cancellation_list.php" class="list-group-item <?if($menu==S_CANCELLATION){?>active<?}?>">반품/교환 관리</a>
					<?}?>

					<!--20211117-->
					<? if (($PERMISSION & PERMISSION_GROUP_CS) == PERMISSION_GROUP_CS) { ?>
						<a href="<?=$site_url?>/cs_inbound/cs_inbound_edit.php" class="list-group-item <?if($menu==S_INBOUND_EDIT){?>active<?}?>">고객센터 콜 관리</a>
					<?}?>

					<? if (($PERMISSION & PERMISSION_GROUP_SALES) == PERMISSION_GROUP_SALES) { ?>
						<a href="<?=$site_url?>/online_event/online_event.php" class="list-group-item <?if($menu==S_EVENT){?>active<?}?>">이벤트 응모 관리</a>
					<?}?>

					<? if (($PERMISSION & PERMISSION_GROUP_SHIPMENT) == PERMISSION_GROUP_SHIPMENT) { ?>
						<a href="<?=$site_url?>/shipment/shipment_new.php" class="list-group-item <?if($menu==S_SHIPMENT_NEW){?>active<?}?>">출고 관리</a>
					<?}?>

					<? if ($PERMISSION > PERMISSION_NONE) { ?>
						<a href="<?=$site_url?>/setting/setting.php" class="list-group-item <?if($menu==S_BANNER){?>active<?}?>">설정</a> <!--20220107-->
					<?}?>
				<?}?>


				<?if( $mod == M_AS ){?>
					<div class="panel-heading"><h3 class="panel-title">AS 신청서 관리</h3></div>
					<a href="<?=$site_url?>/online_as/online_as_edit.php" class="list-group-item <?if($menu==S_AS_NEW){?>active<?}?>">신규신청서 등록</a>
					<a href="<?=$site_url?>/online_as/online_as.php?state=<?echo ST_REGISTERING;?>" class="list-group-item <?if($menu==S_AS_REGISTERING){?>active<?}?>">접수중 보기</a>
					<a href="<?=$site_url?>/online_as/online_as.php?state=<?echo ST_DC;?>" class="list-group-item <?if($menu==S_AS_DC){?>active<?}?>">수거 택배비 입금</a>
					<a href="<?=$site_url?>/online_as/online_as.php?state=<?echo ST_REG_DONE;?>" class="list-group-item <?if($menu==S_AS_REGDONE){?>active<?}?>">접수완료 보기</a>
					<a href="<?=$site_url?>/online_as/online_as.php?state=<?echo ST_FIXING;?>" class="list-group-item <?if($menu==S_AS_FIXING){?>active<?}?>">견적완료 보기</a>
					<a href="<?=$site_url?>/online_as/online_as.php?state=<?echo ST_FIX_DONE;?>" class="list-group-item <?if($menu==S_AS_FIXDONE){?>active<?}?>">수리완료 보기</a>

					<a href="<?=$site_url?>/online_as/online_as_shipment.php" class="list-group-item <?if($menu==S_AS_SHIPMENT){?>active<?}?>">AS 출고완료</a><!--20230707-->
					<a href="<?=$site_url?>/online_as/online_as_report.php" class="list-group-item <?if($menu==S_AS_REPORT){?>active<?}?>">AS 전체 검색</a><!--20230707-->

					<a href="<?=$site_url?>/online_as/template/user_manual_as.pdf" class="list-group-item " target="_blank">사용자 매뉴얼</a><!-- 20210803 -->
				<?}?>

				<!--20210322-->
				<?if( $mod == M_CANCELLATION ){?>
					<div class="panel-heading"><h3 class="panel-title">반품/교환 관리</h3></div>
					<a href="<?=$site_url?>/cancellation/cancellation_edit.php" class="list-group-item <?if($menu==S_CANCELLATION_NEW){?>active<?}?>">반품/교환 신청서 등록</a>
					<a href="<?=$site_url?>/cancellation/cancellation_list.php" class="list-group-item <?if($menu==S_CANCELLATION){?>active<?}?>">반품/교환 리스트</a>
					<a href="<?=$site_url?>/cancellation/exchange_list.php" class="list-group-item <?if($menu==S_EXCHANGE){?>active<?}?>">교환출고 요청 리스트</a><!-- 20230515 -->
					<a href="<?=$site_url?>/cancellation/template/user_manual_cancellation.pdf" class="list-group-item " target="_blank">사용자 매뉴얼</a><!-- 20210721 -->

					<div class="panel-heading" style="margin-top:0px;"><h3 class="panel-title">사내판매 출고 관리</h3></div><!-- 20211203 -->
					<a href="<?=$site_url?>/internal_orders/cs_internal_orders_edit.php" class="list-group-item <?if($menu==S_PARTS_EDIT){?>active<?}?>">사내판매 출고 등록</a>
					<a href="<?=$site_url?>/internal_orders/cs_internal_orders_list.php" class="list-group-item <?if($menu==S_PARTS_LIST){?>active<?}?>">사내판매 출고 요청</a>
					<a href="<?=$site_url?>/internal_orders/cs_internal_orders_list.php?shipment=1" class="list-group-item <?if($menu==S_PARTS_SHIPMENT){?>active<?}?>">사내판매 출고 완료</a> <!--20211206-->

				<?}?>

				<!--20211117-->
				<?if( $mod == M_INBOUND ){?>
					<div class="panel-heading"><h3 class="panel-title">고객센터 콜 관리</h3></div>
					<a href="<?=$site_url?>/cs_inbound/cs_inbound_edit.php" class="list-group-item <?if($menu==S_INBOUND_EDIT){?>active<?}?>">CS 콜 등록</a>
					<a href="<?=$site_url?>/cs_inbound/cs_inbound_list.php" class="list-group-item <?if($menu==S_INBOUND_LIST){?>active<?}?>">CS 콜 리스트</a>
					<a href="<?=$site_url?>/cs_inbound/cs_inbound_admin_callback.php" class="list-group-item <?if($menu==S_INBOUND_CALLBACK){?>active<?}?>">관리자 전화요청 리스트</a>
					<a href="<?=$site_url?>/cs_inbound/user_manual_cs_inbound.pdf" class="list-group-item " target="_blank" >사용자 매뉴얼</a>

					<div class="panel-heading" style="margin-top:0px;"><h3 class="panel-title">부품 출고 관리</h3></div><!-- 20211203 -->
					<a href="<?=$site_url?>/cs_inbound/cs_parts_edit.php" class="list-group-item <?if($menu==S_PARTS_EDIT){?>active<?}?>">부품출고 등록</a>
					<a href="<?=$site_url?>/cs_inbound/cs_parts_list.php" class="list-group-item <?if($menu==S_PARTS_LIST){?>active<?}?>">부품출고 요청 리스트</a>
					<a href="<?=$site_url?>/cs_inbound/cs_parts_list.php?shipment=1" class="list-group-item <?if($menu==S_PARTS_SHIPMENT){?>active<?}?>">부품출고 완료 조회</a> <!--20211206-->
					<a href="<?=$site_url?>/cs_inbound/user_manual_cs_parts.pdf" class="list-group-item " target="_blank" >사용자 매뉴얼</a>
				<?}?>

<!--20210224
				<?if( $mod == M_BANNER ){?>
					<div class="panel-heading"><h3 class="panel-title">배너 관리</h3></div>
					<a href="<?=$site_url?>/banner/banner.php" class="list-group-item <?if($menu==S_BANNER){?>active<?}?>">배너 이미지 설정</a>
				<?}?>
-->
				<?if( $mod == M_EVENT ){?>
					<div class="panel-heading"><h3 class="panel-title">이벤트 응모 관리</h3></div>
					<a href="<?=$site_url?>/online_event/online_event.php" class="list-group-item <?if($menu==S_EVENT){?>active<?}?>">포토상품평 이벤트 신청서</a>
					<? if ((($PERMISSION & PERMISSION_LAB) == PERMISSION_LAB) || (($PERMISSION & PERMISSION_GROUP_CS) == PERMISSION_GROUP_CS) || (($PERMISSION & PERMISSION_GROUP_SALES) == PERMISSION_GROUP_SALES)) { ?> <!-- 20210628 -->
					<a href="<?=$site_url?>/online_event/common_online_event.php" class="list-group-item <?if($menu==S_EVENT_COMMON){?>active<?}?>">이벤트 신청서 - 스나이퍼CG72</a>
					<a href="<?=$site_url?>/online_event/online_event_sniper.php" class="list-group-item <?if($menu==S_EVENT_SNIPER){?>active<?}?>">포토상품평 이벤트 신청서 - 스나이퍼</a>
					
					<!-- 20230504 -->
					<a href="<?=$site_url?>/online_event/online_event_gift.php" class="list-group-item <?if($menu==S_EVENT_GIFT){?>active<?}?>">사은품출고요청 - CS</a>
					<a href="<?=$site_url?>/online_event/temp/user_manual_event.pdf" class="list-group-item " target="_blank" >사용자 매뉴얼</a><!-- 20210727 -->
					<? } ?>
				<?}?>

				<?if( $mod == M_SHIPMENT ){?>
					<div class="panel-heading"><h3 class="panel-title">출고 관리</h3></div>
					<a href="<?=$site_url?>/shipment/shipment_new.php" class="list-group-item <?if($menu==S_SHIPMENT_NEW){?>active<?}?>">출고 처리</a>
					<a href="<?=$site_url?>/shipment/shipment.php" class="list-group-item <?if($menu==S_SHIPMENT){?>active<?}?>">출고 완료 전체 조회</a>
					<a href="<?=$site_url?>/shipment/shipment_files.php" class="list-group-item <?if($menu==S_SHIPMENT_FILES){?>active<?}?>">배송리스트 파일 조회</a>
					<a href="<?=$site_url?>/admin/management_delivery_fee.php" class="list-group-item <?if($menu==S_ADMIN_DELIVERY){?>active<?}?>">출고운임 관리</a>
					<a href="<?=$site_url?>/shipment/template/user_manual_shipment.pdf" class="list-group-item " target="_blank">사용자 매뉴얼</a><!-- 20210722 -->
					<!--a href="<?=$site_url?>/shipment/shipment_chart.php" class="list-group-item <?if($menu==S_SHIPMENT_CHART){?>active<?}?>">출고 데이터 차트</a-->
				<?}?> <!--20210219-->

				<?if( $mod == M_SETTING ){?>
					<div class="panel-heading"><h3 class="panel-title">설정</h3></div>
					<a href="<?=$site_url?>/setting/setting.php" class="list-group-item <?if($menu==S_SETTING){?>active<?}?>">관리자 기본 정보</a> <!--20220107-->
					
					<? if (($PERMISSION & PERMISSION_ALL) == PERMISSION_ALL) { ?>
					<div class="panel-heading" style="margin-top:0px;"><h3 class="panel-title">관리자 전용</h3></div>
					<a href="<?=$site_url?>/admin/management_id.php" class="list-group-item <?if($menu==S_ADMIN_ID){?>active<?}?>">관리자용 계정 관리</a>
					<a href="<?=$site_url?>/admin/management_product_category.php" class="list-group-item <?if($menu==S_ADMIN_PRODUCT){?>active<?}?>">제품 카테고리 관리</a>
					<? } ?>
				<?}?>
			<!-- 20240614 시계추가 -->
			<style>
				#clock {
					height: 160px;
					display: flex;
					flex-direction: column; /* 시계 내용을 세로로 배치합니다. */
					justify-content: center;
					align-items: center;
					background-color: #fff;
					border: 1px solid #ccc;
					font-size: 48px;
					font-family: 'Arial', sans-serif;
					color: #333;
					position: relative;
				}
				#ampm {
					position: absolute;
					bottom: 20px;
					right: 20px;
					font-size: 20px;
					color: #666;
				}
				#date {
					font-size: 14px; /* 날짜 텍스트 크기를 24픽셀로 설정합니다. */
					margin-bottom: 0px; /* 날짜와 시간 사이에 간격을 추가합니다. */
					color: #333;
				}
			</style>
			<div id="clock">
				<div id="date"></div>
				<div id="time"></div>
				<div id="ampm"></div>
			</div>

			<script>
				function updateClock() {
					const now = new Date();
					const hours = now.getHours();
					const minutes = now.getMinutes();
					const seconds = now.getSeconds();
					const ampm = hours >= 12 ? 'PM' : 'AM';

					const formattedHours = hours % 12 || 12;
					const formattedMinutes = minutes < 10 ? '0' + minutes : minutes;
					const formattedSeconds = seconds < 10 ? '0' + seconds : seconds;

					const timeString = `${formattedHours}:${formattedMinutes}:${formattedSeconds}`;
					const dateString = now.toLocaleDateString('ko-KR', {
						year: 'numeric',
						month: 'long',
						day: 'numeric',
						weekday: 'long'
					});

					document.getElementById('time').textContent = timeString;
					document.getElementById('ampm').textContent = ampm;
					document.getElementById('date').textContent = dateString;
				}

				setInterval(updateClock, 1000);
				updateClock();
			</script>
			<!-- 20240614 시계추가 요기까지 -->
			
			</div><!-- /.panel panel-default -->
		</div><!-- /.row -->
		<?if (($PERMISSION & PERMISSION_ALL) == PERMISSION_ALL){?><!--a href="<?=$site_url?>/admin/management_id.php" style="text-decoration:none"><h5 style="margin-top:300px;color:white">m</h3></a--><? } ?>
	</div><!-- /.col-sm-3 col-md-2 sidebar -->

	<div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main" ><!-- 테이블 위치 -->







