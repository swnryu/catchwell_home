<?
error_reporting(E_ALL);
include("../def_inc.php");
$mod	= M_AS;
//$menu	= S_AS_REPORT;
$menu	= S_AS_SHIPMENT;//20230707 출고완료 추가
//define("S_AS_SHIPMENT", "sub_as_shipment");//20230707 출고완료 추가

include("../header.php");

	$table			= "as_parcel_service";
	$listScale		= 20;
	$pageScale		= 10;
	$state_fixdone	= constant('ST_FIX_DONE');

	$search_item = isset($_GET["search_item"]) ? $_GET["search_item"] : "";
	$search_order = isset($_GET["search_order"]) ? $_GET["search_order"] : "";

	$date_from = isset($_GET["date_from"]) ? $_GET["date_from"] : "";
	$date_to = isset($_GET["date_to"]) ? $_GET["date_to"] : "";

//	$date_from = isset($_POST["date_from"]) ? $_POST["date_from"] : date("Y-m-d", strtotime($day." -1 week"));
	if ($date_to == "") {
		$date_to = isset($_POST["date_to"]) ? $_POST["date_to"] : date("Y-m-d", strtotime($day." -0 day"));
	}

	if ($date_from == "") {
        //$date_from = isset($_POST["date_from"]) ? $_POST["date_from"] : date("Y-m-d", strtotime($day." -1 year"));//연도 1년 설정으로인하여 다운로드 문제생김
		//$date_from = isset($_POST["date_from"]) ? $_POST["date_from"] : date("Y-m-d", strtotime($day." -1 week"));//20230707 1주일로 변경
		$date_from = isset($_POST["date_from"]) ? $_POST["date_from"] : date("Y-m-d", strtotime($day." -1 month"));//20230707 1달로 변경(cs신아람씨 요청)
	}

	$date_to2 = date("Y-m-d", strtotime($date_to." +1 day"));

	$startPage = isset($_GET['startPage'])?$_GET['startPage']:0;
	if( !$startPage ) { $startPage = 0; }

	$totalPage = floor($startPage / ($listScale * $pageScale));
	$query	= "select * from $table where update_time between date('$date_from') and date('$date_to2') and (process_state > $state_fixdone) and process_state != 6  ";
	
	if ($search_item == "") {
		$search_item	= isset($_POST["search_item"]) ? $_POST["search_item"] : "";
	}
	if ($search_order == "") {
		$search_order	= isset($_POST["search_order"]) ? $_POST["search_order"] : "";
	}

//echo $date_from."<br>";	
//echo $date_to."<br>";	
//echo $search_item."<br>";	
//echo $search_order."<br>";	

	if($search_order){
		if($search_item){
			$query.=" and $search_item like '%$search_order%'";
		}else{
			$query.=" and (reg_num like '%$search_order%' or customer_name like '%$search_order%' or customer_phone like '%$search_order%' or customer_desc like '%$search_order%' or product_name like '%$search_order%')";
		}
	}

	$query_where = strchr($query, "where"); 
	$query_where = rawurlencode($query_where . " order by idx desc");


	$rs			= mysqli_query($db->db_conn, $query);
	$totalList	= mysqli_num_rows($rs);

	$query = "select * from $table where update_time between date('$date_from') and (date('$date_to2')) and (process_state > $state_fixdone) and process_state != 6 ";
	if($search_order){
		if($search_item){
			$query.=" and $search_item like '%$search_order%'";
		}else{
			$query.=" and (reg_num like '%$search_order%' or customer_name like '%$search_order%' or customer_phone like '%$search_order%' or customer_desc like '%$search_order%' or product_name like '%$search_order%')";
		}
	}

	$query.="  order by idx desc LIMIT $startPage, $listScale";
	$result = mysqli_query($db->db_conn, $query);

	if( $startPage ) { $listNo = $totalList - $startPage; } else { $listNo = $totalList; }

	$param_url = "date_from=".$date_from."&date_to=".$date_to. "&search_item=".$search_item."&search_order=".$search_order;
	
//echo $query."<br>";
//echo $date_to2."<br>";
//echo $param_url."<br>";
?>

	<!--h4 class="page-header">AS 전체 검색</!h4-->
    <h4 class="page-header">AS 출고완료</h4><!--20230630 -->

	<form method="post" name="search_form" class="form-inline" action="<?=$_SERVER['PHP_SELF']?>" >
	<table class="table table-bordered">
	<colgroup>
	<col width="15%">
	<col width="*">
	</colgroup>
	<tbody>
	<tr>
		<th>기간 선택</th>
		<td>
			<div class="input-group datetime" style="width:170px;">
				<input type="text" name="date_from" class="form-control input-sm text-center" placeholder="YYYY-MM-DD" value="<?=$date_from?>"/>
				<span class="input-group-addon">
					<span class="glyphicon glyphicon-calendar"></span>
				</span>
			</div>
			 ~
			<div class="input-group datetime" style="width:170px;">
				<input type="text" name="date_to" class="form-control input-sm text-center" placeholder="YYYY-MM-DD" value="<?=$date_to?>"/>
				<span class="input-group-addon">
					<span class="glyphicon glyphicon-calendar"></span>
				</span>
			</div>
			
		</td>
	</tr>
	<tr>
		<th>검색어</th>
		<td>
			<div class="form-group">
				<div class="input-group-btn">
					<select name="search_item" class="form-control input-sm" onchange="changeSearchOption()">
						<option value="">통합검색</option>
						<option value="reg_num" <?if($search_item=="reg_num"){?>selected<?}?>>접수번호</option>
						<option value="customer_name" <?if($search_item=="customer_name"){?>selected<?}?>>이름</option>
						<option value="customer_phone" <?if($search_item=="customer_phone"){?>selected<?}?>>휴대폰</option>
						<option value="customer_desc" <?if($search_item=="customer_desc"){?>selected<?}?>>불량내용</option>
						<option value="product_name" <?if($search_item=="product_name"){?>selected<?}?>>모델명</option>
					</select>
				</div>
			</div>
			<input type="text" name="search_order" class="form-control input-sm" value="<?=$search_order?>">
		</td>
	</tr>
	<tr>
		<td colspan="2" class="text-center">
			<button type="submit" class="btn btn-primary btn-sm">검색</button>
			<a href="<?=$_SERVER['PHP_SELF']?>" class="btn btn-default btn-sm">초기화</a>
			<a href="online_as_excel_download.php?query_where=<?=$query_where?>" class="btn btn-success btn-sm" style="margin-left:50px;">엑셀 다운로드 ALL</a>
			<!--a href="online_as_excel_report.php?<?=$param_url?>" class="btn btn-info btn-sm" style="margin-left:20px;" data-toggle="tooltip" title="보고서 작성은 5~8초 걸립니다.">보고서 다운로드</a-->
			<? if (($PERMISSION & PERMISSION_GROUP_CS) == PERMISSION_GROUP_CS) { ?>
				<a href="javascript:;" id="report_btn" class="btn btn-info btn-sm ajax-button" data-from="<?=$date_from?>" data-to="<?=$date_to?>" data_item="<?=$search_item?>" data_order="<?=$search_order?>" style="margin-left:20px;" data-toggle="tooltip" title="브라우저의 팝업을 허용하세요.">보고서 다운로드</!a>
				<a href="javascript:;" id="weeklyreport_btn" class="btn btn-warning btn-sm ajax-button-weeklyreport" data-from="<?=$date_from?>" data-to="<?=$date_to?>" data_item="<?=$search_item?>" data_order="<?=$search_order?>" style="margin-left:20px;" data-toggle="tooltip" title="이전 버전의 수리내역과 호환되지 않습니다.">NEW 주간 보고서 다운로드</a><!--20211029-->
				<div class="wrap-loading display-none">
					<div><img src="../images/loading.gif" /><br><br><p style="font-size:14px; color:white; background-color: gray;">&nbsp&nbsp&nbsp보고서 작성중...&nbsp&nbsp&nbsp</p></div>
				</div>
			<? } ?>

		</td>
	</tr>
	</tbody>
	</table>
	</form>


	<div class="table-responsive">
	<table class="table table-bordered table-hover">
	<colgroup>
	<col width="5%">
	<col width="8%">
	<col width="7%">
	<col width="10%">
	<col width="10%">
	<!--col width="10%"-->
	<col width="*">
	<col width="17%">
	<col width="6%"> <!--20220103-->
	<col width="10%">
	<col width="8%">
	<col width="5%">
	</colgroup>
	<thead>
	<tr>
		<th>NO</th>
		<th>접수번호</th>
		<th>상 태</th>
		<th>이 름</th>
		<th>모델명</th>
		<!--th>불량 유형</th-->
		<th>불량 내용</th>
		<th>조치 사항</th>
		<th>담당자명</th> <!--20220103-->
		<th>송장번호(발송용)</th> 
		<th>최종업데이트</th>
		<!--th>상세</!th-->
		<th colspan="2">상세관리</th><!--20230707-->
	</tr>
	</thead>
	<tbody>
	<?
		$today = date("Y-m-d");
		while($row = mysqli_fetch_array($result)){
			$reg_date	= $tools->strDateCut($row[reg_date], 3);
			$update	= $tools->strDateCut($row[update_time], 3);
			$customer_desc = $tools->strCut_utf($tools->strHtml($row[customer_desc]), 100);

			$memo = $row[admin_memo];
			$memo = str_replace("(V)","",$memo);
			$memo = str_replace("(R)","",$memo);
			$memo = str_replace("(H)","",$memo);
			$memo = str_replace("(S)","",$memo);
			$memo = str_replace("(M)","",$memo);
			$memo = str_replace("[ETC]","",$memo);			

			//20210213-2회이상 중복접수검색
			//20210220-01000000000 제외
			$query2="select count(*) as cnt from $table where process_state=4 and customer_phone='$row[customer_phone]' and customer_phone!='01000000000' ";
			$rs2 = mysqli_query($db->db_conn, $query2);
			$row2 = mysqli_fetch_object($rs2);
//			echo $row2->cnt;

	?>
	<tr>
		<!--<td class="text-center"><input type="checkbox" name="check_list" value="<? echo $row[idx] ?>"></td>-->
		<td class="text-center"><? echo $listNo ?></td>
		<td class="text-center"><? echo $row[reg_num] ?></td>
		<td class="text-center"><? echo $proc_state[$row[process_state]] ?></td>
		<td class="text-center"><? echo $row[customer_name] ?><!--a href="./online_as_view.php?idx=<? echo $row[idx] ?>&from=<? echo $menu ?>#as_history"><span class="badge"><?if($row2->cnt>1){echo $row2->cnt;}?></span></a--><? if($today==$reg_date){ ?>&nbsp;<span class="label label-danger">New</span><? } ?></td><!--20210220-->
		<!--td class="text-center"><span <?if($row2->cnt>1){echo 'style="background-color:#FF00FF"';}?> ><? echo $row[customer_name] ?></span><? if($today==$reg_date){ ?>&nbsp;<span class="label label-danger">New</span><? } ?></td--> <!--20210213-->
		<!--td class="text-center"><? echo $row[customer_name] ?><? if($today==$reg_date){ ?>&nbsp;<span class="label label-danger">New</span><? } ?></td--> 
		<td class="text-center"><? echo $row[product_name] ?> </td>
		<!--td class="text-center"><? echo $row[broken_type] ?> </td-->
		<td><? echo $customer_desc ?> </td>
		<td class="text-center"><? echo $memo ?> </td>
		<td class="text-center"><? echo $row['pic_name'] ?> </td> <!--20220103-->
		<td class="text-center"><a href="<? echo constant('TRACKING_CJ').$row[parcel_num_return]?>" target="_blank"><?echo $row[parcel_num_return]?></td>
		<td class="text-center"><? echo $update?></td>

		<td class="text-center">
			<a href="<?=$site_url?>/online_as/online_as_edit.php?idx=<? echo $row[idx] ?>&from=<? echo $menu ?>" class="btn btn-default btn-sm"> 
			수정
		    </a>
		</!td>

		<td class="text-center"><a href="./online_as_view.php?idx=<? echo $row[idx] ?>&from=<? echo $menu ?>" class="btn btn-primary btn-sm">보기</a></td>
	</tr>
	<? 
		$listNo--;
		}
	?>
	</tbody>
	</table>
	</div>


	<div class="text-center">
		<ul class="pagination">
		<?
			if( $totalList > $listScale ) {
				if( $startPage+1 > $listScale*$pageScale ) {
					$prePage = $startPage - $listScale * $pageScale;
					echo "<li><a href='$_SERVER[PHP_SELF]?$param_url&startPage=$prePage'><span aria-hidden='true'>&laquo;</span></a></li>";
				}
				for( $j=0; $j<$pageScale; $j++ ) {
					$nextPage = ($totalPage * $pageScale + $j) * $listScale;
					$pageNum = $totalPage * $pageScale + $j+1;
					if( $nextPage < $totalList ) {
						if( $nextPage!= $startPage ) {
							echo "<li><a href='$_SERVER[PHP_SELF]?$param_url&startPage=$nextPage'>$pageNum</a></li>";
						} else {
							echo "<li class='active'><a href='javascript:;'>$pageNum</a></li>";
						}
					}
				}
				if( $totalList > (($totalPage+1) * $listScale * $pageScale)) {
					$nNextPage = ($totalPage+1) * $listScale * $pageScale;
					echo "<li><a href='$_SERVER[PHP_SELF]?$param_url&startPage=$nNextPage'><span aria-hidden='true'>&raquo;</span></a></li>";
				}
			}
			if( $totalList <= $listScale) {
				echo "<li class='active'><a href='javascript:;' >1</a></li>";
			}
		?>
		</ul>
	</div>


<script type="text/javascript">
function changeSearchOption() {
	var form=document.search_form;
	if (form.search_item.value == 'reg_num') {
		form.search_order.placeholder="YYYYMM-NUM";
	} 
	else if (form.search_item.value == 'customer_phone') {
		form.search_order.placeholder="- 없이 숫자만 입력";
	} 
	else {
		form.search_order.placeholder="";
	}
}
</script>

<script>
$(document).ready(function(){
  $('[data-toggle="tooltip"]').tooltip();
});
</script>

<script>
$(".ajax-button").on("click", function(e) {
	
	var from	= $(this).attr("data-from");
	var to		= $(this).attr("data-to");
	var postData = 
			{ 
				"date_from": from,
				"date_to": to,
				"search_item": $(this).attr("data_item"),
				"search_order": $(this).attr("data_order")
			};

	$.ajax({
		type: "GET",
		url: "online_as_excel_report_p.php",
		data: postData,
		dataType: 'json',
		success:function(result){
			if (result.success=='ok') {
				var target_file = "../temp/" + result.name;
				var win = window.open(target_file, "_blank");
				location.reload();
			}
		},
		beforeSend:function(){
			$('.wrap-loading').removeClass('display-none');
			$('#report_btn').attr('disabled', true);
		},
		complete:function(result){
			$('.wrap-loading').addClass('display-none');
			$('#report_btn').attr('disabled', false);
		},
		error:function(e){
			$('.wrap-loading').addClass('display-none');
			$('#report_btn').attr('disabled', false);
			alert('error');
		}
	});

});

//20211029
$(".ajax-button-weeklyreport").on("click", function(e) {
	
	var from	= $(this).attr("data-from");
	var to		= $(this).attr("data-to");
	var postData = 
			{ 
				"date_from": from,
				"date_to": to,
				"search_item": $(this).attr("data_item"),
				"search_order": $(this).attr("data_order")
			};

	$.ajax({
		type: "GET",
		url: "online_as_excel_report_weekly.php",
		data: postData,
		dataType: 'json',
		success:function(result){
			if (result.success=='ok') {
				var target_file = "../temp/" + result.name;
				var win = window.open(target_file, "_blank");
				location.reload();
			}
		},
		beforeSend:function(){
			$('.wrap-loading').removeClass('display-none');
			$('#weeklyreport_btn').attr('disabled', true);
		},
		complete:function(result){
			$('.wrap-loading').addClass('display-none');
			$('#weeklyreport_btn').attr('disabled', false);
		},
		error:function(e){
			$('.wrap-loading').addClass('display-none');
			$('#weeklyreport_btn').attr('disabled', false);
			alert('error');
		}
	});

});

</script>

 <? include('../footer.php');?>