<?
error_reporting(E_ALL);
include("../def_inc.php");
$mod	= M_AS;

$state = isset($_GET['state'])?$_GET['state']:0;
switch($state) {
	case ST_REGISTERING: 	$menu	= S_AS_REGISTERING; break;
	case ST_REG_DONE: 		$menu	= S_AS_REGDONE; break;
	case ST_FIXING:			$menu	= S_AS_FIXING; break;
	case ST_FIX_DONE:		$menu	= S_AS_FIXDONE; break;
	case ST_AS_COMPLETED: 	$menu	= S_AS_COMPLETED; break;
	case ST_DC: 			$menu	= S_AS_DC; break;
	default: 				$menu	= S_AS_REGISTERING; break;
}

include("../header.php");

	$table			= "as_parcel_service";
	$listScale		= 20;
	$pageScale		= 10;

	$startPage = isset($_GET['startPage'])?$_GET['startPage']:0;
	if( !$startPage ) { $startPage = 0; }

	$totalPage = floor($startPage / ($listScale * $pageScale));
	$query		= "select * from $table where process_state=$state";
	if ($state==ST_REGISTERING) {
		$query .= " and reg_date >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";//1달치만 보이게
	}

	$search_item = isset($_GET["search_item"]) ? $_GET["search_item"] : "";
	$search_order = isset($_GET["search_order"]) ? $_GET["search_order"] : "";
	
	if ($search_item=="") {
		$search_item	= isset($_POST["search_item"]) ? $_POST["search_item"] : "";
	}
	if ($search_order=="") {
		$search_order	= isset($_POST["search_order"]) ? $_POST["search_order"] : "";
	}

	if($search_order){
		if($search_item){
			$query.=" and $search_item like '%$search_order%'";
		}else{
			$query.=" and (reg_num like '%$search_order%' or customer_name like '%$search_order%' or customer_phone like '%$search_order%' or customer_desc like '%$search_order%' or product_name like '%$search_order%')";
		}
	}
	$rs			= mysqli_query($db->db_conn, $query);
	$totalList	= mysqli_num_rows($rs);

	$query = "select * from $table where process_state=$state";
	if ($state==ST_REGISTERING) {
		$query .= " and reg_date >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";//1달치만 보이게
	}
		if($search_order){
			if($search_item){
				$query.=" and $search_item like '%$search_order%'";
			}else{
				$query.=" and (reg_num like '%$search_order%' or customer_name like '%$search_order%' or customer_phone like '%$search_order%' or customer_desc like '%$search_order%' or product_name like '%$search_order%')";
			}
		}
//	$query.="  order by idx desc LIMIT $startPage, $listScale";
	if ($state==ST_FIX_DONE) {
		$query.="  order by update_time asc LIMIT $startPage, $listScale";
	}
	else {
		$query.="  order by idx desc LIMIT $startPage, $listScale";
	} 
	$result = mysqli_query($db->db_conn, $query);

	if( $startPage ) { $listNo = $totalList - $startPage; } else { $listNo = $totalList; }
/*
echo "totalPage=".$totalPage."<br>";
echo "listScale=".$listScale."<br>";
echo "listNo=".$listNo."<br>";
echo "totalList=".$totalList."<br>";
echo "startPage=".$startPage."<br>";
*/
	$param_url = 
	"search_item=".$search_item.
	"&search_order=".$search_order;
	
//echo $query."<br>";

	if ($state==ST_REGISTERING)	  { $move_item_sel = ST_DC; }
	else if ($state==ST_DC) { $move_item_sel = ST_REG_DONE; }
	else if ($state==ST_REG_DONE) { $move_item_sel = ST_FIX_DONE; }
	else if ($state==ST_FIX_DONE) { $move_item_sel = ST_AS_COMPLETED; }

?>

	<h4 class="page-header">신청서 보기 <?echo ' ('.$proc_state[$state].')'?></h4>

	<form method="post" name="search_form" class="form-inline" action="<?=$_SERVER['PHP_SELF'].'?state='.$state;?>" >
	<table class="table table-bordered">
	<colgroup>
	<col width="15%">
	<col width="*">
	</colgroup>
	<tbody>
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
						<option value="reg_date" <?if($search_item=="reg_date"){?>selected<?}?>>등록일</option>
					</select>
				</div>
			</div>
			<input type="text" name="search_order" class="form-control input-sm" value="<?=$search_order?>">
		</td>
	</tr>
	<tr>
		<td colspan="2" class="text-center">
			<button type="submit" class="btn btn-primary btn-sm">검색</button>&nbsp;
			<a href="<?=$_SERVER['PHP_SELF'].'?state='.$state?>" class="btn btn-default btn-sm">초기화</a>
			<? if ($state==ST_REGISTERING || $state==ST_FIX_DONE || $state==ST_DC) { ?>
				<a href="online_as_excel_download.php?state=<?=$state?>" class="btn btn-success btn-sm" data-toggle="tooltip" title="<?if(!USE_DELIVERY_EPOST){echo "CJ택배 송장";} else {echo "우체국택배 송장";}?>">엑셀 다운로드 ALL</a>
			<?}?>
		</td>
	</tr>
	</tbody>
	</table>
	</form>

<? if ($state==ST_REGISTERING || $state==ST_REG_DONE || $state==ST_FIX_DONE || $state==ST_DC) {?> <!-- 접수중/접수완료/수리완료 페이지에만 표시-->
	<form method="post" name="upload_form" class="form-inline" enctype="multipart/form-data" action="<?='online_as_excel_upload.php?from=' . $state?>" >
	<table class="table table-bordered">
	<colgroup>
	<col width="15%">
	<col width="*">
	</colgroup>
	<tbody>
	<tr>
		<th>
		<? if ($state==ST_REGISTERING || $state==ST_FIX_DONE || $state==ST_DC) { echo "택배 접수완료 처리"; } else if ($state==1) { echo "송장번호(회수용) 입력 처리"; } ?>
		</th>
		<td>
			<input type="file" name="userfile" id="userfile" style="text-center" accept=".xls,.xlsx" >
			<input type="hidden" name="return_url" value="<?='online_as.php?state='.$state?>" >
			<input type="hidden" name="state" value="<?=$state?>" >
			<button type="submit" class="btn btn-info btn-sm" <?if(($PERMISSION & PERMISSION_CS)!=PERMISSION_CS) { echo 'disabled';}?> >엑셀 업로드</a>
		</td>
	</tr>
	</tbody>
	</table>
	</form>
<? }?>

	<div class="table-responsive">
	<table class="table table-bordered table-hover">
	<colgroup>
	<col width="3%">
	<col width="5%">
	<col width="8%">
	<col width="12%">
	<col width="10%">
	<col width="7%">
	<col width="*">
	<?if ($state!=ST_REGISTERING) { ?>
	<col width="15%">
	<? } ?>
	<col width="6%">
	<col width="10%">
	<col width="5%">
	<?if ($state==ST_FIXING) { ?>
	<col width="5%"><!--수리여부-->
	<? } ?>
	<col width="5%">
	</colgroup>
	<thead>
	<tr>
	<th colspan="3" class="form-inline">
		<a href="javascript:;" class="btn btn-default btn-xs ajax-checkbox" data-dbname="<?=$table?>" data-name="move" data-val="<?=$move_item_sel?>" <?if(($PERMISSION & PERMISSION_CS)!=PERMISSION_CS) { echo 'disabled';}?> >
		<?	if ($state==ST_REGISTERING)   { echo "수거 택배비 입금으로 이동하기"; } 
			else if ($state==ST_DC) { echo "접수완료로 이동하기"; }
			else if ($state==ST_REG_DONE) { echo "수리완료로 이동하기"; } 
			else if ($state==ST_FIX_DONE) { echo "발송완료로 이동하기"; } 
			else						  { echo "이동하기"; } 
		?>
		</a>
	</th>
	<td colspan="<?if($state==ST_FIXING){echo '7';} else if ($state!=ST_REGISTERING) { echo '6'; } else { echo '5'; } ?>" > </td><!--20220103-->
	<th>
	<? if ($state==ST_REGISTERING || $state==ST_FIX_DONE || $state==ST_DC) { ?>
	<a href="javascript:;" class="btn btn-success btn-xs ajax-checkbox" data-dbname="<?=$table?>" data-name="export2excel" data-val="<?=$state?>" data-toggle="tooltip" title="<?if(!USE_DELIVERY_EPOST){echo "CJ택배 송장";} else {echo "우체국택배 송장";}?>">엑셀 다운로드</a></th>
	<? } ?>
	<th colspan="2"><a href="javascript:;" class="btn btn-default btn-xs ajax-checkbox" data-dbname="<?=$table?>" data-name="delete" data-val="" <?if(($PERMISSION & PERMISSION_CS)!=PERMISSION_CS) { echo 'disabled';}?> >삭제하기</a></th>
	</tr>
	<tr>
		<th><input type="checkbox" id="allCheck"></th>
		<th>N O</th>
		<th>접수번호</th>
		<th>이 름</th>
		<th>휴대폰</th>
		<th>모델명</th>
		<th>불량내용</th>
		<?if ($state!=ST_REGISTERING) { ?>
		<th><?if($state==ST_FIX_DONE){echo "조치사항";} else if ($state==ST_REGISTERING) {echo "불량유형";} else{echo "송장번호(회수용)";}?></th>
		<? } ?>
		<th>담당자명</th> <!--20220103-->
		<th>등록일</th>
		<?if ($state==ST_FIXING) { ?>
		<th>수리여부</th><!--수리여부-->
		<? } ?>
		<th colspan="2">상세관리</th>
	</tr>
	</thead>
	<tbody>
	<?
		$today = date("Y-m-d");
		while($row = mysqli_fetch_array($result)){
			$reg_date	= $tools->strDateCut($row[reg_date], 3);

			$customer_desc = $tools->strCut_utf($tools->strHtml($row[customer_desc]), 100);

			//20210213-2회이상 중복접수검색
			//20210220-01000000000 제외
			$query2="select count(*) as cnt from $table where process_state=4 and customer_phone='$row[customer_phone]' and customer_phone!='01000000000' ";
			$rs2 = mysqli_query($db->db_conn, $query2);
			$cnt2 = mysqli_fetch_row($rs2);
//			echo $cnt2[0];
	?>
	<tr>
		<td class="text-center"><input type="checkbox" name="check_list" value="<? echo $row[idx] ?>"></td>
		<td class="text-center"><? echo $listNo ?></td>
		<td class="text-center"><? echo $row[reg_num] ?></td>
		<td class="text-center"><? echo $row[customer_name] ?><!--a href="./online_as_view.php?idx=<? echo $row[idx] ?>&from=<? echo $menu ?>#as_history"><span class="badge"><?if($cnt2[0]>0){echo $cnt2[0]+1;}?></span></a--><? if($today==$reg_date){ ?>&nbsp;<span class="label label-danger">New</span><? } ?></td><!--20210213--><!--20210220-->
		<!--td class="text-center"><span <?if($cnt2[0]==1){echo 'style="background-color:yellow"';} else if($cnt2[0]>1){echo 'style="background-color:#FF00FF"';}?> ><? echo $row[customer_name] ?></span><? if($today==$reg_date){ ?>&nbsp;<span class="label label-danger">New</span><? } ?></td-->
		<!--td class="text-center"><? echo $row[customer_name] ?><? if($today==$reg_date){ ?>&nbsp;<span class="label label-danger">New</span><? } ?></td--><!--20210213-->
		<td class="text-center"><a href='tel:<?echo $row[customer_phone];?>'><? echo $row[customer_phone] ?></a></td>
		<td class="text-center"><? echo $row[product_name] ?> </td>
		<td><? echo $customer_desc ?> </td>
		<?if ($state!=ST_REGISTERING) { ?>
		<td class="text-center">
			<? 
			if($state==ST_FIX_DONE) {
				//echo $row[admin_memo];
				$memo = $row[admin_memo];
				$memo = str_replace("(V)","",$memo);
				$memo = str_replace("(R)","",$memo);
				$memo = str_replace("(H)","",$memo);
				$memo = str_replace("(S)","",$memo);
				$memo = str_replace("(M)","",$memo);
				$memo = str_replace("[ETC]","",$memo);
				echo $memo;
			} 
			else if ($state==ST_REGISTERING) {echo $row[broken_type];} 
			else{?> <a href="<? if(strlen($row[parcel_num])==12) {echo constant('TRACKING_CJ').$row[parcel_num];} else {echo constant('TRACKING_EPOST').$row[parcel_num];} ?>" target="_blank"><?echo $row[parcel_num]; } 
			?>
		</td>
		<? } ?>
		<td class="text-center"><? echo $row['pic_name']?></td> <!--20220103-->
		<td class="text-center"><? echo $reg_date?></td>
		<?if ($state==ST_FIXING) { ?>
		<td class="text-center"><? echo $row[attached_files] ?></td><!--수리여부-->
		<? } ?>
		<td class="text-center"><a href="./online_as_edit.php?idx=<? echo $row[idx] ?>&from=<? echo $menu ?>" class="btn btn-default btn-sm">수정</a></td>
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
					echo "<li><a href='$_SERVER[PHP_SELF]?state=$state&$param_url&startPage=$prePage'><span aria-hidden='true'>&laquo;</span></a></li>";
				}
				for( $j=0; $j<$pageScale; $j++ ) {
					$nextPage = ($totalPage * $pageScale + $j) * $listScale;
					$pageNum = $totalPage * $pageScale + $j+1;
					if( $nextPage < $totalList ) {
						if( $nextPage!= $startPage ) {
							echo "<li><a href='$_SERVER[PHP_SELF]?state=$state&$param_url&startPage=$nextPage'>$pageNum</a></li>";
						} else {
							echo "<li class='active'><a href='javascript:;'>$pageNum</a></li>";
						}
					}
				}
				if( $totalList > (($totalPage+1) * $listScale * $pageScale)) {
					$nNextPage = ($totalPage+1) * $listScale * $pageScale;
					echo "<li><a href='$_SERVER[PHP_SELF]?state=$state&$param_url&startPage=$nNextPage'><span aria-hidden='true'>&raquo;</span></a></li>";
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
	if (form.search_item.value == 'reg_date') {
		form.search_order.placeholder="yyyy-mm-dd";
	} 
	else if (form.search_item.value == 'reg_num') {
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

 <? include('../footer.php');?>