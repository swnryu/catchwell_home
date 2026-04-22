<?
error_reporting(E_ALL);
include("../def_inc.php");
include("event_def.php");

$mod	= M_EVENT;
$menu	= S_EVENT; 

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

	$table			= "cs_online_event";
	$listScale		= 10;
	$pageScale		= 10;
	if( !$startPage ) { $startPage = 0; }
	$totalPage = floor($startPage / ($listScale * $pageScale));

	//select * from cs_online_event where MONTH(udate) = MONTH('2009-09-10') 
	//$query		= "select * from $table where 1";
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


//20230426 임시주석삭제
/*
echo "search_item: ".$search_item."<br>";
echo "search_order: ".$search_order."<br>";
echo "date_from: ".$date_from."<br>";
echo "date_to: ".$date_to."<br>";
echo "view_mode: ".$view_mode."<br>";

echo "query: ".$query."<br>";
echo "param_url: ".$param_url."<br>";
*/

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

	<h4 class="page-header">포토상품평 이벤트 신청서</h4>

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
			<a href="online_event_excel_download.php?from=<?=$date_from?>&to=<?=$date_to?>&view_mode=<?=$view_mode?>" class="btn btn-success btn-sm" >엑셀 다운로드 ALL</a>&nbsp;
			<!--a href="online_event_excel_download.php?from=<?=$date_from?>&to=<?=$date_to?>&view_mode=<?=$view_mode?>&delivery=cj" class="btn btn-info btn-sm" data-toggle="tooltip" title="[적합] 상태만 처리됩니다.">택배접수용 엑셀 다운로드</a-->
		</td>
	</tr>
	</tbody>
	</table>
	</form>

	<table class="table table-bordered">
		<colgroup>
		<col width="12%">
		<col width="*">
		</colgroup>
		<tbody>
			<tr>
				<!-- 20230426 form으로 추가로 인하여 위치 변경-->
				<form method="post" name="upload_form" class="form-inline" enctype="multipart/form-data" action="<?='online_event_excel_upload.php' ?>" >
					<th>송장번호 입력</th>
					<td>
					<span>발송고객 일별 배달상세_yyyymmdd.xlsx </span>
						<input type="file" name="userfile" id="userfile" style="text-center" accept=".xls,.xlsx" >
						<button type="submit" class="btn btn-info btn-sm" <?if(($PERMISSION & PERMISSION_SALES)!=PERMISSION_SALES) { echo 'disabled';}?> >엑셀 업로드</a>
					</td>
				</form>
				<!-- 20230426 form으로 추가하여 변경-->
				<form method="post" name="upload_form1" class="form-inline" enctype="multipart/form-data" action="<?='online_event_excel_upload1.php' ?>" >
					<!-- 20230426 핀번호 -->
					<th>핀번호 입력</th>
					<td>
						<span>포토이벤트 상품평취합_yyyymmdd.xlsx </span>
						<!--input type="file" name="userfile" id="userfile" style="text-center" accept=".xls,.xlsx" -->
						<input type="file" name="userfile1" id="userfile1" style="text-center" accept=".xls,.xlsx" > <!-- 20230426 name, id 추가-->
						<button type="submit" class="btn btn-info btn-sm" <?if(($PERMISSION & PERMISSION_SALES)!=PERMISSION_SALES) { echo 'disabled';}?> >엑셀 업로드</a>
					</td>
				</form>

			</tr>
		</tbody>
	</table>

	

	<div class="table-responsive">
	<table class="table table-bordered table-hover">
	<colgroup>
	<col width="5%">
	<col width="6%">
	<col width="8%">
	<col width="10%">
	<col width="8%">
	<col width="8%">
	<col width="8%">
	<col width="10%">
	<col width="*">
	<col width="8%">
	<col width="7%">
	<col width="10%">
	</colgroup>
	<thead>
	<tr>
		<th colspan="2"></th>
		<th colspan="7"></th>
		<th colspan="2">
		<select name="view_mode" class="form-control input-sm" onchange="javascript:changeViewMode(this);">
			<option value="0" <?if($view_mode==0) {echo "selected";}?> >전체 보기</option>
			<option value="1" <?if($view_mode==1) {echo "selected";}?> >[처리중] 보기</option>
			<option value="2" <?if($view_mode==2) {echo "selected";}?> >[적합] 보기</option>
			<option value="3" <?if($view_mode==3) {echo "selected";}?> >[부적합] 보기</option>
			<option value="4" <?if($view_mode==4) {echo "selected";}?> >[미발송] 보기</option>
			<option value="5" <?if($view_mode==5) {echo "selected";}?> >[발송완료] 보기</option>
		</select>
		</th>
		<th>
		<a href="javascript:;" class="btn btn-default btn-xs ajax-checkbox" data-dbname="<?=$table?>" data-name="delete" data-val="" <?if(($PERMISSION & PERMISSION_SALES)!=PERMISSION_SALES) { echo 'disabled';}?> >삭제하기</a>
		</th>
	</tr>
	<tr>
		<th><input type="checkbox" id="allCheck"></th>
		<th>N O</th>
		<th>이 름</th>
		<th>휴대폰</th>
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
		<td class="text-center"><? echo $row['japum'] ?> </td>
		<td class="text-center"><a href=<?echo constant("URL-".$row['shoppingmall']);?> target="_blank"><? echo $row['shoppingmall'] ?></a></td>
		<td class="text-center"><? echo $row['id']; if ($row['shoppingmall']=='오늘의집' && $row['nickname']!=''){echo "<br>(".$row['nickname'].")";} ?> </td>
		<td class="text-center"><? echo $row['oid']; ?></td>
		<!--td class="text-center"><a href="JavaScript:newWin('data/<?=$row['bbs_file']?>');"><img src="data/<?=$row['bbs_file']?>" width="80px" ></a></td--> 
		<td class="text-center"><a href="JavaScript:newWin('data/<?=$row['bbs_file']?>');" data-toggle="tooltip" data-placement="right" data-html="true" title="<img src='data/<?=$row['bbs_file']?>' width='180px' />" >
			<!--img src="data/<?=$row['bbs_file']?>" width="80px" height="80px" /-->
			<? if( strstr($row['bbs_file'], '.mp4') ) { ?> 
				<video src="data/<?=$row['bbs_file']?>" width="80px" height="80px"> </video> 
			<? } else { ?> 
				<img src="data/<?=$row['bbs_file']?>" width="80px" height="80px"> 
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
			<a href="./online_event_edit.php?idx=<?=$row['idx']?>" class="btn btn-default btn-sm">수정</a>
			<a href="./online_event_view.php?idx=<?=$row['idx']?>" class="btn btn-primary btn-sm">보기</a>
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

<? include('../footer.php');?>