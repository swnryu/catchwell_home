<?
error_reporting(E_ALL);
include("../def_inc.php");
include("event_def.php");

$mod	= M_EVENT;
$menu	= S_EVENT_COMMON; 

include("../header.php");

	$search_item = isset($_GET["search_item"]) ? $_GET["search_item"] : "";
	$search_order = isset($_GET["search_order"]) ? $_GET["search_order"] : "";

	if ($search_item=="") {
		$search_item = isset($_POST["search_item"]) ? $_POST["search_item"] : "";
	}

	if ($search_order=="") {
		$search_order = isset($_POST["search_order"]) ? $_POST["search_order"] : "";
	}

	$startPage = isset($_GET['startPage']) ? $_GET['startPage'] : 0;
	if( !$startPage ) { $startPage = 0; }
	
	$date_from = isset($_GET["date_from"]) ? $_GET["date_from"] : "";
	if ($date_from=="") {
		$date_from = isset($_POST['date_from']) ? $_POST['date_from'] : date("Y-m-d", strtotime(date("Y-m-d")." -2 week"));
	}

	$date_to = isset($_GET["date_to"]) ? $_GET["date_to"] : "";
	if ($date_to=="") {
		$date_to = isset($_POST['date_to']) ? $_POST['date_to'] : date("Y-m-d");
	}
	$date_to2 = date("Y-m-d", strtotime($date_to." +1 day"));
	
	$view_mode = isset($_GET["view_mode"]) ? $_GET["view_mode"] : "";
	if ($view_mode=="") {
		$view_mode = isset($_POST['view_mode']) ? $_POST['view_mode'] : 0;
	}

	$table			= "lab_online_event";
	$listScale		= 50;
	$pageScale		= 10;
	if( !$startPage ) { $startPage = 0; }
	$totalPage = floor($startPage / ($listScale * $pageScale));

	$query		= "select * from $table where date between date('$date_from') and date('$date_to2') ";
		if($search_order){
			if($search_item){
				$query.=" and $search_item like '%$search_order%'";
			}else{
				$query.=" and (customer_name like '%$search_order%' or customer_phone like '%$search_order%' or model_name like '%$search_order%')";
			}
		}
	
		if ($view_mode == 1) { //처리중  
			$query.=" and (status is null OR status=0)";
		} 
		else if ($view_mode == 2) { //완료
			$query.=" and status=1";
		}


	$rs			= mysqli_query($db->db_conn, $query);
	$totalList	= mysqli_num_rows($rs);

	//$query = "select * from $table where 1";
	$query = "select * from $table where date between date('$date_from') and date('$date_to2') ";
		if($search_order){
			if($search_item){
				$query.=" and $search_item like '%$search_order%'";
			}else{
				$query.=" and (customer_name like '%$search_order%' or customer_phone like '%$search_order%' or model_name like '%$search_order%')";
			}
		}

		if ($view_mode == 1) { //처리중  
			$query.=" and (status is null OR status=0)";
		} 
		else if ($view_mode == 2) { //완료
			$query.=" and status=1";
		}


	$query.="  order by idx desc LIMIT $startPage, $listScale";
	$result = mysqli_query($db->db_conn, $query);

	if( $startPage ) { $listNo = $totalList - $startPage; } else { $listNo = $totalList; }

	$param_url = 
	"search_item=".$search_item.
	"&search_order=".$search_order. 
	"&date_from=".$date_from.
	"&date_to=".$date_to. 
	"&view_mode=".$view_mode;

//echo $query."<br>";
//echo $param_url."<br>";


?>

<script>
function newWin(url) {
	var img = new Image();
	img.src = url;

	var img_w = img.width;
	var win_w = img.width+25;
	
	var img_h = img.height;
	var win_h = img.height+30;
	
	window.open(url,'new_win','width='+win_w+',height='+win_h+',toolbars=no,menubars=no,scrollbars=no');
}

</script>

	<h4 class="page-header">이벤트 신청서</h4>

	<form method="post" name="search_form" class="form-inline" action="<?=$_SERVER['PHP_SELF'];?>" >
	<input type="hidden" name="view_mode"  value="<?=$view_mode;?>">

	<table class="table table-bordered">
	<colgroup>
	<col width="12%">
	<col width="41%">
	<col width="12%">
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
			</div>&nbsp;&nbsp;&nbsp;&nbsp;

		</td>
		<th>검색어</th>
		<td>
			<div class="form-group">
				<div class="input-group-btn">
					<select name="search_item" class="form-control input-sm" >
						<option value="">통합검색</option>
						<option value="customer_name" <?if($search_item=="customer_name"){?>selected<?}?>>이름</option>
						<option value="customer_phone" <?if($search_item=="customer_phone"){?>selected<?}?>>휴대폰</option>
						<option value="model_name" <?if($search_item=="model_name"){?>selected<?}?>>모델명</option>
					</select>
				</div>
			</div>
			<input type="text" name="search_order" class="form-control input-sm" value="<?=$search_order?>">
		</td>
	</tr>
	<tr>
		<td colspan="4" class="text-center">
			<button type="submit" class="btn btn-primary btn-sm">검색</button>&nbsp;
			<a href="<?=$_SERVER['PHP_SELF']?>" class="btn btn-default btn-sm">초기화</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<a href="common_online_event_excel_download.php?from=<?=$date_from?>&to=<?=$date_to?>&view_mode=<?=$view_mode?>" class="btn btn-success btn-sm" >엑셀 다운로드 ALL</a>&nbsp;
			<!--a href="online_event_excel_download.php?from=<?=$date_from?>&to=<?=$date_to?>&view_mode=<?=$view_mode?>&delivery=cj" class="btn btn-info btn-sm" data-toggle="tooltip" title="">택배접수용 엑셀 다운로드</a-->
		</td>
	</tr>
	</tbody>
	</table>
	</form>

	<!--form method="post" name="upload_form" class="form-inline" enctype="multipart/form-data" action="<?='common_delivery_excel_upload.php' ?>" >
	<table class="table table-bordered">
	<colgroup>
	<col width="12%">
	<col width="*">
	</colgroup>
	<tbody>
	<tr>
		<th>송장번호 업로드</th>
		<td>
		<span>발송고객 일별 배달상세_yyyymmdd.xlsx </span>
			<input type="file" name="userfile" id="userfile" style="text-center" accept=".xls,.xlsx" disabled >
			<button type="submit" class="btn btn-info btn-sm" <?if(($PERMISSION & PERMISSION_LAB)!=PERMISSION_LAB) { echo 'disabled';}?> disabled >송장번호 엑셀 파일 업로드</a>
		</td>
	</tr>
	</tbody>
	</table>
	</form-->

	<div class="table-responsive">
	<table class="table table-bordered table-hover">
	<colgroup>
	<col width="3%">
	<col width="4%">
	<col width="8%">
	<col width="9%">
	<col width="*">
	<col width="6%">
	<col width="9%">
	<col width="8%">
	<col width="8%">
	<col width="7%">
	<col width="5%">
	<col width="7%">
	</colgroup>
	<thead>
	<tr>
		<th colspan="2">
			<a href="javascript:;" class="btn btn-default btn-xs ajax-checkbox" data-dbname="<?=$table?>" data-name="delete" data-val="" <?if(($PERMISSION & PERMISSION_LAB)!=PERMISSION_LAB) { echo 'disabled';}?> >삭제하기</a>
		</th>
		<th>
			<a href="javascript:;" class="btn btn-default btn-xs ajax-checkbox-excel" data-dbname="<?=$table?>" data-name="deliverydone" data-val="" <?if(($PERMISSION & PERMISSION_LAB)!=PERMISSION_LAB) { echo 'disabled';}?> >발송완료 처리</a>
		</th>
		<th>
			<a href="javascript:;" class="btn btn-success btn-sm ajax-checkbox-excel" data-dbname="<?=$table?>" data-name="export2excel" data-val="" >배송리스트 다운로드</a>
		</th>
		<th colspan="6"></th>
		<th colspan="2">
		<select name="view_mode" class="form-control input-sm" onchange="javascript:changeViewMode(this);">
			<option value="0" <?if($view_mode==0) {echo "selected";}?> >전체 보기</option>
			<option value="1" <?if($view_mode==1) {echo "selected";}?> >[처리중] 보기</option>
			<option value="2" <?if($view_mode==2) {echo "selected";}?> >[완료] 보기</option>
		</select>
		</th>
	</tr>
	<tr>
		<th><input type="checkbox" id="allCheck"></th>
		<th>N O</th>
		<th>이 름</th>
		<th>휴대폰</th>
		<th>주 소</th>
		<th>모델명</th>
		<th>쇼핑몰</th>
		<th>아이디</th>
		<th>주문번호</th>
		<th>사은품</th>
		<th>상 태</th>
		<th>등록일</th>
	</tr>
	</thead>
	<tbody>
	<?
		$today = date("Y-m-d");
		while($row = mysqli_fetch_array($result)){
			$reg_date = $tools->strDateCut($row['date'], 3);
	?>
	<tr>
<?
		$info_detail = "<div class='tooltip-inner' role='tooltip' style='text-align:left; width:100%;' >".
		"[사은품]"."<br>".$row['gift']."<br><br>".
		"[이벤트명]"."<br>".$row['event_name']."<br><br>".
		"[홈페이지ID]"."<br>".$row['homepage_id']."<br><br>".
		"[시리얼번호]"."<br>".$row['serial_no']."<br><br>".
		"[송장번호]"."<br>".$row['tracking_num']."<br><br>".
		"</div>";
?>	
		<td class="text-center"><input type="checkbox" name="check_list" value="<? echo $row['idx'] ?>"></td>
		<td class="text-center"><? echo $listNo ?></td>
		<td class="text-center"><a href="#" data-toggle="tooltip" data-placement="right" data-html="true"  title="<?echo $info_detail;?>"><? echo $row['customer_name'] ?><? if($today==$reg_date){ ?>&nbsp;<span class="label label-danger">New</span><? } ?></a></td>
		<td class="text-center"><? echo $row['customer_phone'] ?> </td>
		<td class="text-center"><? echo $row['customer_addr'] . $row['customer_addr_detail'] ?> </td>
		<td class="text-center"><? echo $row['model_name'] ?> </td>
		<td class="text-center"><? echo $row['market_name'] ?></a></td>
		<td class="text-center"><? echo $row['market_id']; ?> </td>
		<td class="text-center"><? echo $row['order_id']; ?></td>
		<!--td class="text-center"><? echo $row['order_date']; ?></td-->
		<td class="text-center"><? echo $row['gift']; ?></td>
		<td class="text-center"><? if($row['status']==0) {echo '처리중';} else {echo '완료';} ?> </td>
		<td class="text-center"><? echo $reg_date?></td>
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

<script>
function changeViewMode(obj) {

	var form=document.search_form;
	form.view_mode.value = obj.value;
	form.submit();
}
</script>

<script>
$(document).ready(function(){
  $('[data-toggle="tooltip"]').tooltip();
});
</script>


<script>
$(function() {
	$(".ajax-checkbox-excel").click(function() {

		var checkboxVal = [];
		$("input[name='check_list']:checked").each(function(i) {
			checkboxVal.push($(this).val());
		});

		var dbname		= $(this).attr("data-dbname");
		var idx			= checkboxVal;
		var name		= $(this).attr("data-name");
		var val			= $(this).attr("data-val");
		var postData = 
			{ 
				"dbname": dbname,
				"idx": idx,
				"name": name,
				"val": val
			};

		if(name=="export2excel") {
			
			var msg = "선택 항목을 [엑셀 다운로드]";var msg2 = "하시겠습니까?";

			if(  $("input:checkbox[name='check_list']").is(":checked") ){

				//filename : 배송리스트_event_YYYYMMDD.xlsx
				var dt = new Date();
				var y = dt.getFullYear().toString();
				var m = (dt.getMonth()+1).toString();
				var d = (dt.getDate().toString());
				if (dt.getMonth()+1 < 10)	m = "0"+(dt.getMonth()+1).toString();
				if (dt.getDate() < 10)		d = "0"+(dt.getDate().toString());
				var filename = "배송리스트_event_"+y+m+d+".xlsx"; 


				ans = confirm(msg + " " + msg2);
				if(ans==true){
					$.ajax({
						url : "common_delivery_excel_download.php?filename="+filename,
						type: "post",
						data: postData,
						success:function(result){ 
							
							var pathname = "./temp/"+filename;

							var win = window.open(pathname, "_blank");
							location.reload();
							
						}
					});
				}
			}else{
				alert("항목을 선택하여 주세요.");
			}
		} 
		else if (name=="deliverydone") {

			var msg = "선택 항목을 [발송완료 처리] 하시겠습니까?";

			if( $("input:checkbox[name='check_list']").is(":checked") ){

				ans = confirm(msg);
				if(ans==true){
				$.ajax({
					url : "common_delivery_done.php",
					type: "post",
					data: postData,
					success:function(result){ 
						alert('처리되었습니다.');
						location.reload();
						/*
						if (result.success == 'ok'){
							alert('success');	
						}
						*/
					},
					error : function(error) {
						alert("error");
					}

				});
				}

			} else {
				alert("항목을 선택하여 주세요.");
			}
			
		}
	
	});//.ajax-checkbox-excel
});

</script>

<? include('../footer.php');?>