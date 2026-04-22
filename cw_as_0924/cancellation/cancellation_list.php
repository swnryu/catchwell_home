<?
error_reporting(E_ALL);
include("../def_inc.php");
include("cancellation_def.php");

$mod	= M_CANCELLATION;
$menu	= S_CANCELLATION; 

include("../header.php");



$table			= "cancellation_order";
$listScale		= 50;
$pageScale		= 10;


$startPage = isset($_GET['startPage'])?$_GET['startPage']:0;
if( !$startPage ) { $startPage = 0; }



$search_item = isset($_GET["search_item"]) ? $_GET["search_item"] : "";
$search_order = isset($_GET["search_order"]) ? $_GET["search_order"] : "";
$date_from = isset($_GET["date_from"]) ? $_GET["date_from"] : "";
$date_to = isset($_GET["date_to"]) ? $_GET["date_to"] : "";
$status = isset($_GET["status"]) ? $_GET["status"] : "";

//echo $status."<br>";

if ($date_to == "") {
    $date_to = isset($_POST["date_to"]) ? $_POST["date_to"] : date("Y-m-d");
//    $date_to = "2019-12-31"; //test
}
if ($date_from == "") {
    $date_from = isset($_POST["date_from"]) ? $_POST["date_from"] : date("Y-m-d", strtotime($date_to." -30 day"));
//    $date_from = date("Y-m-d", strtotime($date_to." -1 month")); //test
}
$date_to2 = date("Y-m-d", strtotime($date_to." +0 day"));

//echo $status."<br>";
if ($status == "") 
{
    $status = isset($_POST["status"]) ? $_POST["status"] : 0;
}    
$status_query = ""; 
if ($status == 0) {
    $status_query = "and status = 0 ";
}
else if ($status == 1) {
    $status_query = "and status = 1 "; 
}




$totalPage = floor($startPage / ($listScale * $pageScale));
$query		= "select * from $table where date between date('$date_from') and date('$date_to2') " . $status_query;
$query_where = "where date between date('$date_from') and date('$date_to2') " . $status_query;

if ($search_item=="") {
    $search_item = isset($_POST["search_item"]) ? $_POST["search_item"] : "";
}
if ($search_order=="") {
    $search_order = isset($_POST["search_order"]) ? $_POST["search_order"] : "";
}

if($search_order){
    if($search_item){
        $query.="and $search_item like '%$search_order%' ";
        $query_where.="and $search_item like '%$search_order%' ";
    }else{
        $query.="and (model_name like '%$search_order%' or company_name like '%$search_order%' or customer_name like '%$search_order%' or order_id like '%$search_order%' or type like '%$search_order%') ";
        $query_where.="and (model_name like '%$search_order%' or company_name like '%$search_order%' or customer_name like '%$search_order%' or order_id like '%$search_order%' or type like '%$search_order%') ";
    }
}
$result		= mysqli_query($db->db_conn, $query);
$totalList	= mysqli_num_rows($result);
//echo $totalList."<br>";


$query.=" order by idx desc ";
$query_where.=" order by idx desc ";
$query.="LIMIT $startPage, $listScale";
$result		= mysqli_query($db->db_conn, $query);

if( $startPage ) { $listNo = $totalList - $startPage; } else { $listNo = $totalList; }

$param_url = "date_from=".$date_from."&date_to=".$date_to. "&search_item=".$search_item."&search_order=".$search_order."&status=".$status;


//echo $query."<br>";
//echo $param_url."<br>";
//echo $status_query."<br>";
//echo $query_where."<br>";
?>


<h4 class="page-header">반품/교환 리스트</h4>

<form method="post" name="search_form" class="form-inline" action="<?=$_SERVER['PHP_SELF'];?>" >
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
            </div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

            <label class="radio-inline"><input type="radio" name="status" value="0" <?if ($status==0) {echo "checked";}?> > [처리중] 보기	</label>&nbsp;&nbsp;&nbsp;&nbsp;
            <label class="radio-inline"><input type="radio" name="status" value="1" <?if ($status==1) {echo "checked";}?> > [처리완료] 보기 </label>&nbsp;&nbsp;&nbsp;&nbsp;
            <label class="radio-inline"><input type="radio" name="status" value="2" <?if ($status==2) {echo "checked";}?> > 전체 보기 </label>
        </td>
    </tr>
    <tr>
    <th>검색어</th>
    <td>
        <div class="form-group">
            <div class="input-group-btn">
                <select name="search_item" class="form-control input-sm" >
                    <option value="">통합검색</option>
                    <option value="order_id" <?if($search_item=="order_id"){?>selected<?}?>>주문번호</option>
                    <option value="customer_name" <?if($search_item=="customer_name"){?>selected<?}?>>고객명</option>
                    <option value="type" <?if($search_item=="type"){?>selected<?}?>>구분</option>
                    <option value="model_name" <?if($search_item=="model_name"){?>selected<?}?>>모델명</option>
                    <option value="company_name" <?if($search_item=="company_name"){?>selected<?}?>>업체명</option>
                </select>
            </div>
        </div>
        <input type="text" name="search_order" class="form-control input-sm" value="<?=$search_order?>">
    </td>
</tr>
<tr>
    <td colspan="2" class="text-center">
        <button type="submit" class="btn btn-primary btn-sm">검색</button>
        <a href="<?=$_SERVER['PHP_SELF'] ?>" class="btn btn-default btn-sm">초기화</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <a href="cancellation_excel_download.php?query_where=<?echo $query_where;?>&date_from=<?echo $date_from;?>&date_to=<?echo $date_to;?>" class="btn btn-success btn-sm" >엑셀 다운로드 ALL</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <a href="cancellation_report_download.php?rptype=monthly&date_from=<?echo $date_from;?>" class="btn btn-info btn-sm" data-toggle="tooltip" title="[처리완료] 만 통계됨" >월간 보고서 다운로드</a>
    </td>
</tr>
<!--a href="cancellation_report_download_test.php?rptype=monthly&date_from=<?echo $date_from;?>" class="btn btn-info btn-sm" >test</a-->
</tbody>
</table>
</form>


<div class="table-responsive">
<table class="table table-bordered table-hover">
<colgroup>
<col width="4%">
<col width="7%">
<col width="9%">
<col width="11%">
<col width="8%">
<col width="7%">
<col width="6%">
<col width="8%">
<col width="*">
<col width="12%">
<col width="8%">
<col width="6%">

</colgroup>
<br>
<thead>

<tr>
    <th>N O</th>
    <th>요청일</th>
    <th>모델명</th>
    <th>주문번호</th>
    <th>구매처</th>
    <th>업체명</th>
    <th>구분</th>
    <th>고객명</th>
    <th>사유</th>
    <th>고객 조율사항</th>
    <th>완료일</th>
    <th>상세보기</th>
</tr>
</thead>
<tbody>

<?
	while($row = mysqli_fetch_array($result)) {
        include ("replacement_tracking_from_salesdb.php");
?>
        <tr>
        <td class="text-center"><? echo $listNo ?></td>
        <td class="text-center"><? echo $row['date'] ?></td>
        <td class="text-center"><? echo $row['model_name']  ?></td>
        <td class="text-center"><? echo $row['order_id'] ?></td>
        <td class="text-center"><? echo $row['shopping_mall']; ?></td>
        <td class="text-center"><? echo $row['company_name']; ?></td>
        <td class="text-center"><? echo $row['type']; ?></td>
        <td class="text-center" style="color:blue;"><? echo $row['customer_name']; ?></td>
        <td class="text-center"><? echo $row['reason']; ?></td>
        <td class="text-center"><?if($tracking_sales!="") { echo $tracking_sales . "<br>";} echo $row['memo']; ?></td>
        <td class="text-center">
            <? 
            if ($row['status']==0) {
                echo "";
            } 
            else {
                echo date('m/d', strtotime($row['date_completed'])) . " " . $result_type[$row['result_type']]; 
            } 
            ?>
        </td>
        <td class="text-center"><a href="./cancellation_edit.php?idx=<? echo $row['idx']; ?>" class="btn btn-primary btn-sm">보기</a></td>
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
                $_POST['search_order'] = 'another value';
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
$(document).ready(function(){
  $('[data-toggle="tooltip"]').tooltip();
});
</script>

<? include('../footer.php');?>