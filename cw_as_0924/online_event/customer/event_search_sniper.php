<? include("../../common.php");?>
<meta charset="utf-8">
<?

$hp1 = $tools->filter($_POST['hp1']);
$hp2 = $tools->filter($_POST['hp2']);
$hp3 = $tools->filter($_POST['hp3']);
$hp = $hp1."-".$hp2."-".$hp3;



	$table			= "cs_online_event_sniper";
	$listScale		= 5;
	$pageScale		= 10;
	$search_order = $hp;
	$search_item = "hp";
	if( !$startPage ) { $startPage = 0; }
	$totalPage = floor($startPage / ($listScale * $pageScale));
	$query		= "select * from $table where 1";
		if($search_order){
			if($search_item){
				$query.=" and $search_item like '%$search_order%'";
			}else{
				$query.=" and (name like '%$search_order%' or ph like '%$search_order% or content like '%$search_order%')";
			}
		}
	$rs			= mysqli_query($db->db_conn, $query);
	$totalList	= mysqli_num_rows($rs);

	$query = "select * from $table where 1";
		if($search_order){
			if($search_item){
				$query.=" and $search_item like '%$search_order%'";
			}else{
				$query.=" and (name like '%$search_order%' or ph like '%$search_order% or content like '%$search_order%')";
			}
		}
	$query.="  order by idx desc LIMIT $startPage, $listScale";
	$result = mysqli_query($db->db_conn, $query);

	if( $startPage ) { $listNo = $totalList - $startPage; } else { $listNo = $totalList; }

	$param_url = 
	"search_item=".$search_item.
	"&search_order=".$search_order;

if(USE_DEBUG) {
	//echo $query."<br>";
	//echo $site_url."<br>";
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
  	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
	<link href="<?=$site_url?>/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?=$site_url?>/css/skin/dashboard.css" rel="stylesheet">
	<link href="<?=$site_url?>/css/board.css" rel="stylesheet">
	<style>
	.cm-btn-controls{overflow:hidden; text-align:center; padding-top:30px;}
	.cm-btn-controls.cm-btn-align-left{text-align:left;}
	.cm-btn-controls.cm-btn-align-right{text-align:right;}
	.cm-btn-controls .left-btn-controls{float:left; }
	.cm-btn-controls .right-btn-controls{float:right;}
	.cm-btn-controls button,.cm-btn-controls a{display:inline-block; width:150px; height:42px; border:0; color:#fff; background-color:#000; font-size:17px; margin:0 2px 5px 2px;  cursor:pointer; vertical-align:top; text-align:center; font-weight:400; -webkit-border-radius:2px;-moz-border-radius:2px;-o-border-radius:2px;border-radius:2px; }
	.cm-btn-controls a{line-height:42px;}
	.cm-btn-controls.cm-btn-long-controls button,.cm-btn-controls.cm-btn-long-controls a{width:100%; height:50px; margin:0px; margin-bottom:5px;}
	.cm-btn-controls.cm-btn-long-controls a{box-sizing:border-box; line-height:50px;}
	.cm-btn-controls .btn-style01{background-color:#19315e;}
	.cm-btn-controls .btn-style02{background-color:#8c8c8c;}
	.cm-btn-controls .btn-style03{box-sizing:border-box; background:#fff; border:1px solid #888; color:#222;}

	body{margin-left:5%; width:90%; height:100%; /*font-family:"나눔고딕", NanumGothic, "Nanum Gothic","돋움", Dotum, Arial, sans-serif;*/}	
	</style>

	</head>
	<body>
	
		<td colspan="2" class="text-center">
			<h3 class="text-center">이벤트 접수 조회</h4>&nbsp;
			
		</td>
	
	<h5>사은품은 매월 2회 일괄 발송됩니다.</h5>
	<h5>상품평 작성시 사진 2장과 50자 이상 작성해야 하며, 부정적 상품평은 제외될 수 있습니다.</h5>
	<h5>상품평 한 건당 사은품 한 개만 증정됩니다.</h5>
	<h5>정상 접수시 기입하신 휴대폰으로 응모 확인 메시지가 발송됩니다.</h5>


	</form>

	<div class="table-responsive">
	<table class="table table-bordered table-hover">
	<colgroup>
	<col width="5%">
	<col width="5%">
	<col width="15%">
	<col width="10%">
	<col width="*">
	<col width="10%">
	<col width="7%">
	</colgroup>
	<table class="table table-bordered">
	<colgroup>
	<col width="15%">
	<col width="*">
	</colgroup>
	<thead>
	<tr>
		<th>N O</th>
		<th>이 름</th>
		<th>휴대폰</th>
		<th>제품명</th>
		<th>등록일</th>
	</tr>
	</thead>

	<?
		$today = date("Y-m-d");
		while($row = mysqli_fetch_array($result)){
			$reg_date	= $tools->strDateCut($row[udate], 3);
	?>
	
	<tbody>

	<tr>
		<td class="text-center"><? echo $listNo ?></td>
		<td class="text-center"><? echo preg_replace('/.(?=.$)/u','*',$row[name]); ?><? if($today==$reg_date){ ?>&nbsp;<span class="label label-danger">New</span><? } ?></td>
		<td class="text-center"><? echo $row[hp] ?> </td>
		<td class="text-center"><? echo $row[japum] ?> </td>
		<td class="text-center"><? echo $reg_date?></td>
	</tr>
	</tbody>
	
	
	
	<? 
		$listNo--;
		}
	?>
	</table>
	</div>
	<?
		if($totalList == 0){
			echo "<h5> 조회결과가 없습니다.</h5>";
			}
		else{
			echo "<h5> 정상적으로 접수되었습니다.</h5>";
			}
	?>
	<div class="cm-btn-controls">
            <button class="btn-style01" type="button" onclick="window.close();">현재 창 닫기</button>
    </div>

	</body>
</html>


