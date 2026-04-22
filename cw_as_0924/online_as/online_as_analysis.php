<?
error_reporting(E_ALL);
include("../def_inc.php");

$mod	= M_AS;
$menu	= S_AS_ANALYSIS; 

include("../header.php");

//
$table			= "as_parcel_service";

//기간 MIN/MAX 
$arr_date = mysqli_query($db->db_conn, "select MIN(update_time), MAX(update_time) from $table");
$date = mysqli_fetch_row($arr_date);
$date1=date_create($date[0]);
$date_from = date_format($date1,"Y-m-d");
$date2=date_create($date[1]);
$date_to = date_format($date2,"Y-m-d");
$date_diff = date_diff(date_create($date_from), date_create($date_to))->format('%a');
//echo $date_diff;

$state = constant('ST_FIX_DONE');
$total_cnt = $db->cnt($table, "where process_state > $state");
//echo $total_cnt."<br>";


//모델명, 개수
$arr_data = array();
$dataPoints = array();


?>



<h4 class="page-header">AS 분석 기간 : <? echo $date_from." ~ ".$date_to; ?></h4>
<h5 class="page-header">1. 제품별 AS 접수율 (<?echo "Total : " . $total_cnt;?>)</h5>

<!--////////////////////////////////////////////////// 제품별 AS 접수율 Chart -->
<?
$query		= "select product_name as model, COUNT(*) as cnt from $table where process_state>$state group by product_name order by cnt desc";
$result = mysqli_query($db->db_conn, $query);
while($row = mysqli_fetch_array($result)){
	array_push($arr_data, array($row['model'], $row['cnt']) );
  
	$temp = round($row['cnt'] / $total_cnt * 100);
	if ($temp > 1) {
		array_push($dataPoints, array("y" => $row['cnt'], "percent" => $temp, "label" => $row['model']) );
//		echo $row['cnt']."-----".$total_cnt."-----".$temp."<br>";
	}
}
?>

<div id="chartContainer1" style="height: 370px; width: 100%;"></div><br><br><br>




<!--////////////////////////////////////////////////// 2회이상 접수율 Chart -->
<?
/*
$query		= "select customer_name as name, customer_phone as phone, COUNT(*) as cnt, product_name from $table GROUP BY customer_phone HAVING COUNT(*) > 1 ORDER BY cnt desc";
$result = mysqli_query($db->db_conn, $query);
while($row = mysqli_fetch_array($result)){
  array_push($arr_data, array($row['name'], $row['cnt']) );
  
  $temp = round($row['cnt'] / $total_cnt * 100);
  if ($temp > 0) {
 	 array_push($dataPoints2, array("y" => $row['cnt'], "percent" => $temp, "label" => $row['name']) );
	  echo $row['cnt']."-----".$total_cnt."-----".$temp."<br>";
	}
}
echo $query;
*/
?>
<?


//$result = mysqli_query($db->db_conn, $query);
//20210220-01000000000 제외
$query = "select *, COUNT(*) as cnt from $table where process_state>$state and customer_phone!='01000000000' GROUP BY customer_phone HAVING COUNT(*) > 1 ORDER BY cnt desc";
$result = mysqli_query($db->db_conn, $query);
$listNo = mysqli_num_rows($result);
$query_get = $query;

//echo $listNo."<br>";
//echo $query;

$duplicate_total = 0;

?>

<h5 class="page-header">2. AS 2회 이상 접수 (출고완료 상태, 동일 전화번호 기준)</h5>

<!--div id="chartContainer2" style="height: 370px; width: 100%;"></div-->
<div class="table-responsive">
<table class="table table-bordered table-hover">
<colgroup>
<col width="5%">
<col width="8%">
<col width="10%">
<col width="8%">
<col width="10%">
<col width="10%">
<col width="10%">
<col width="10%">
<col width="*">
<col width="12%">
<!--col width="5%"-->
</colgroup>
<thead>
<tr>
	<th colspan="2" class="form-inline">
	<a href="online_as_excel_download_analysis.php?query=<?=$query_get?>" class="btn btn-success btn-sm" >엑셀 다운로드 ALL</a>
	</th>
	<td colspan="8"></td>
</tr>
<tr>
	<th>NO</th>
	<th>최종업데이트</th>
	<th>접수번호</th>
	<th>상태</th>
	<th>이 름</th>
	<th>전화번호</th>
	<th>모델명</th>
	<th>불량 유형</th>
	<th>불량 내용</th>
	<th>조치 사항</th>
	<!--th>최종업데이트</th-->
	<!--th>상세</th-->
</tr>
</thead>
<tbody>
<?
	
	$today = date("Y-m-d");
	while($row = mysqli_fetch_array($result)){
	
		$duplicate_total += $row['cnt'];
		
		$query = "select * from $table where process_state>$state and customer_phone='$row[customer_phone]' ORDER BY update_time desc limit $row[cnt] ";
		$rs = mysqli_query($db->db_conn, $query);
		while($rs_row = mysqli_fetch_array($rs)){
			
			$reg_date	= $tools->strDateCut($rs_row['reg_date'], 3);
			$update	= $tools->strDateCut($rs_row['update_time'], 3);
			$customer_desc = $tools->strCut_utf($tools->strHtml($rs_row['customer_desc']), 100);
	
			$memo = $rs_row['admin_memo'];
			$memo = str_replace("(V)","",$memo);
			$memo = str_replace("(R)","",$memo);
			$memo = str_replace("(H)","",$memo);
			$memo = str_replace("(S)","",$memo);
			$memo = str_replace("(M)","",$memo);
			$memo = str_replace("[ETC]","",$memo);

			$name = str_replace(" ","",$rs_row['customer_name']);
			$name = str_replace(".","",$rs_row['customer_name']);
			$name = str_replace(",","",$rs_row['customer_name']);

?>
			<tr>
				<td class="text-center"><? echo $listNo ?></td>
				<td class="text-center"><? echo date_format(date_create($rs_row['update_time']),"Y-m-d"); ?></td>
				<td class="text-center"><? echo $rs_row['reg_num'] ?></td>
				<td class="text-center"><? echo $proc_state[$rs_row['process_state']] ?></td>
				<td class="text-center"><? echo $name ."(".$row['cnt'].")"?></td>
				<td class="text-center"><? echo $rs_row['customer_phone'] ?> </td>
				<td class="text-center"><? echo $rs_row['product_name'] ?> </td>
				<td class="text-center"><? echo $rs_row['broken_type'] ?> </td>
				<td><? echo $customer_desc ?> </td>
				<td class="text-center"><? echo $memo ?> </td>
				<!--td class="text-center"><? echo $update?></td-->
				<!--td class="text-center"><a href="./online_as_view.php?idx=<? echo $rs_row['idx'] ?>&from=<? echo $menu ?>" class="btn btn-primary btn-sm">보기</a></td-->
			</tr>
<? 
		}

		$listNo--;
	}

	echo "Total : " . $duplicate_total . " (". round($duplicate_total/$total_cnt*100) ."%)";
?>

</tbody>
</table>
</div>



<!--////////////////////////////////////////////////// draw Chart -->
<script>
window.onload = function () {
 
var chart = new CanvasJS.Chart("chartContainer1", {
	animationEnabled: true,
	title: {
		text: "제품별 AS 접수율"
	},
	data: [{
		type: "pie",
		startAngle: 240,
		indexLabel: "{label} - {percent}% ({y})",
		dataPoints: <?php echo json_encode($dataPoints, JSON_NUMERIC_CHECK); ?>
	}]
});
chart.render();
/*
var chart2 = new CanvasJS.Chart("chartContainer2", {
	title: {
		text: "2회 이상 AS 접수율"
	},
	data: [{
		type: "pie",
		startAngle: 240,
		indexLabel: "{label} - {percent}% ({y})",
		dataPoints2: <?php echo json_encode($dataPoints2, JSON_NUMERIC_CHECK); ?>
	}]
});
chart2.render();
*/
}

</script>






	

<? include('../footer.php');?>