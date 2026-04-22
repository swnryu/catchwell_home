<?
error_reporting(E_ALL);
include("../def_inc.php");
include("event_def.php");

$mod	= M_EVENT;
$menu	= S_EVENT_SNIPER; 

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
		$date_from = isset($_POST['date_from']) ? $_POST['date_from'] : date("Y-m-d", strtotime(date("Y-m-d")." -1 month"));
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

	$table			= "cs_online_event_sniper";
	$listScale		= 50;
	$pageScale		= 10;
	if( !$startPage ) { $startPage = 0; }
	$totalPage = floor($startPage / ($listScale * $pageScale));


	$query		= "select * from $table where udate between date('$date_from') and date('$date_to2') ";
		if($search_order){
			if($search_item){
				$query.=" and $search_item like '%$search_order%'";
			}else{
				$query.=" and (name like '%$search_order%' or hp like '%$search_order%' or japum like '%$search_order%')";
			}
		}
	
		if ($view_mode == 1) { //처리중  
			$query.=" and (status is null OR status=0)";
		} 
		else if ($view_mode == 2) { //적합
			$query.=" and status=1";
		}
		else if ($view_mode == 3) { //부적합
			$query.=" and status=2";
		} 
		else if ($view_mode == 4) { //미발송
			$query.=" and (status is null OR status!=99)";
		} 
		else if ($view_mode == 5) { //발송완료 
			$query.=" and status=99";
		}

	$rs			= mysqli_query($db->db_conn, $query);
	$totalList	= mysqli_num_rows($rs);

	//$query = "select * from $table where 1";
	$query = "select * from $table where udate between date('$date_from') and date('$date_to2') ";
		if($search_order){
			if($search_item){
				$query.=" and $search_item like '%$search_order%'";
			}else{
				$query.=" and (name like '%$search_order%' or hp like '%$search_order%' or japum like '%$search_order%')";
			}
		}

		if ($view_mode == 1) { //처리중  
			$query.=" and (status is null OR status=0)";
		} 
		else if ($view_mode == 2) { //적합
			$query.=" and status=1";
		}
		else if ($view_mode == 3) { //부적합
			$query.=" and status=2";
		} 
		else if ($view_mode == 4) { //미발송
			$query.=" and (status is null OR status!=99)";
		} 
		else if ($view_mode == 5) { //발송완료 
			$query.=" and status=99";
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

	<h4 class="page-header">포토상품평 이벤트 신청서 - 스나이퍼</h4>

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
						<option value="name" <?if($search_item=="name"){?>selected<?}?>>이름</option>
						<option value="hp" <?if($search_item=="hp"){?>selected<?}?>>휴대폰</option>
						<option value="japum" <?if($search_item=="japum"){?>selected<?}?>>모델명</option>
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
			<a href="online_event_excel_download.php?sniper=1&from=<?=$date_from?>&to=<?=$date_to?>&view_mode=<?=$view_mode?>" class="btn btn-success btn-sm" >엑셀 다운로드 ALL</a>&nbsp;

		</td>
	</tr>
	</tbody>
	</table>
	</form>

	<!--form method="post" name="upload_form" class="form-inline" enctype="multipart/form-data" action="<?='online_event_sniper_excel_upload.php' ?>" >
	<table class="table table-bordered">
	<colgroup>
	<col width="12%">
	<col width="*">
	</colgroup>
	<tbody>
	<tr>
		<th>송장번호 입력</th>
		<td>
		<span>발송고객 일별 배달상세_yyyymmdd.xlsx </span>
			<input type="file" name="userfile" id="userfile" style="text-center" accept=".xls,.xlsx" >
			<button type="submit" class="btn btn-info btn-sm" >엑셀 업로드</a>
		</td>
	</tr>
	</tbody>
	</table>
	</form-->

	<div class="table-responsive">
	<table class="table table-bordered table-hover">
	<colgroup>
	<col width="5%">
	<col width="5%">
	<col width="8%">
	<col width="10%">
	<col width="15%">
	<col width="8%">
	<col width="8%">
	<col width="8%">
	<col width="10%">
	<col width="*">
	<col width="8%">
	<col width="5%">
	<col width="15%">
	</colgroup>
	<thead>
	<tr>
		<th colspan="2"><a href="javascript:;" class="btn btn-default btn-xs ajax-checkbox" data-dbname="<?=$table?>" data-name="delete" data-val="" >삭제하기</a></th>
		<th><a href="javascript:;" class="btn btn-default btn-xs ajax-checkbox-snp" data-dbname="<?=$table?>" data-name="deliverydone" data-val="" >발송완료 처리</a></th>
		<th><a href="javascript:;" class="btn btn-success btn-sm ajax-checkbox-snp" data-dbname="<?=$table?>" data-name="export2excel" data-val="" >배송리스트 다운로드</a></th>
		<th colspan="7"></th>
		<th colspan="2">
		<select name="view_mode" class="form-control input-sm" onchange="javascript:changeViewMode(this);">
			<option value="0" <?if($view_mode==0) {echo "selected";}?> >전체 보기</option>
			<option value="1" <?if($view_mode==1) {echo "selected";}?> >[처리중] 보기</option>
			<option value="5" <?if($view_mode==5) {echo "selected";}?> >[발송완료] 보기</option>
		</select>
		</th>
		
	</tr>
	<tr>
		<th><input type="checkbox" id="allCheck"></th>
		<th>N O</th>
		<th>이 름</th>
		<th>휴대폰</th>
		<th>주소</th>
		<th>모델명</th>
		<th>쇼핑몰</th>
		<th>아이디</th>
		<th>주문번호</th>
		<th>이미지</th>
		<th>등록일</th>
		<th>상 태</th>
		<th>관리하기</th>
	</tr>
	</thead>
	<tbody>
	<?
		$today = date("Y-m-d");
		while($row = mysqli_fetch_array($result)){
			$reg_date = $tools->strDateCut($row['udate'], 3);
	?>
	<tr>
		<td class="text-center"><input type="checkbox" name="check_list" value="<? echo $row['idx'] ?>"></td>
		<td class="text-center"><? echo $listNo ?></td>
		<td class="text-center"><? echo $row['name'] ?><? if($today==$reg_date){ ?>&nbsp;<span class="label label-danger">New</span><? } ?></td>
		<td class="text-center"><? echo $row['hp'] ?> </td>
		<td class="text-center"><? echo $row['add1'].$row['add2'] ?> </td>
		<td class="text-center"><? echo $row['japum'] ?> </td>
		<td class="text-center"><a href=<?echo constant("URL-".$row['shoppingmall']);?> target="_blank"><? echo $row['shoppingmall'] ?></a></td>
		<td class="text-center"><? echo $row['id']; if ($row['shoppingmall']=='오늘의집' && $row['nickname']!=''){echo "<br>(".$row['nickname'].")";} ?> </td>
		<td class="text-center"><? echo $row['oid']; ?></td>

		<td class="text-center"><a href="JavaScript:newWin('data_sniper/<?=$row['bbs_file']?>');" data-toggle="tooltip" data-placement="right" data-html="true" title="<img src='data_sniper/<?=$row['bbs_file']?>' width='180px' />" >

			<? if( strstr($row['bbs_file'], '.mp4') ) { ?> 
				<video src="dadata_sniperta/<?=$row['bbs_file']?>" width="80px" height="80px"> </video> 
			<? } else { ?> 
				<img src="data_sniper/<?=$row['bbs_file']?>" width="80px" height="80px"> 
			<? } ?>	
		</a></td> 
		<td class="text-center"><? echo $reg_date?></td>
		<td class="text-center" <?if($row['status']==STATUS_NULL || $row['status']==STATUS_NOTOK){echo "style='color:red;'";}?>> 
			<?	if($row['status']==null) {echo '처리중';} 
				else if($row['status']==STATUS_NULL){echo '처리중';} 
				else if($row['status']==STATUS_OK){echo '적합';} 
				else if($row['status']==STATUS_NOTOK){echo '부적합';} 
				else if($row['status']==STATUS_DONE){echo '발송완료';} ?> 
		</td>
		<td class="text-center">
			<a href="./online_event_sniper_edit.php?idx=<?=$row['idx']?>" class="btn btn-default btn-sm">수정</a>
			<a href="./online_event_sniper_view.php?idx=<?=$row['idx']?>" class="btn btn-primary btn-sm">보기</a>
		</td>
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
	$(".ajax-checkbox-snp").click(function() {

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

				//filename : 배송리스트_스나이퍼2_event_YYYYMMDD.xlsx
				var dt = new Date();
				var y = dt.getFullYear().toString();
				var m = (dt.getMonth()+1).toString();
				var d = (dt.getDate().toString());
				if (dt.getMonth()+1 < 10)	m = "0"+(dt.getMonth()+1).toString();
				if (dt.getDate() < 10)		d = "0"+(dt.getDate().toString());
				var filename = "배송리스트_스나이퍼2_event_"+y+m+d+".xlsx"; 


				ans = confirm(msg + " " + msg2);
				if(ans==true){
					$.ajax({
						url : "sniper_delivery_excel_download.php?filename="+filename,
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
					url : "sniper_delivery_done.php",
					type: "post",
					data: postData,
					success:function(result){ 
						alert('처리되었습니다.');
						location.reload();
						
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