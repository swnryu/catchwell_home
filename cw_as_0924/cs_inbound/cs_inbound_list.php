<?
include("../def_inc.php");
$mod	= M_INBOUND;	
$menu	= S_INBOUND_LIST;
include("../header.php");
include("cs_inbound_def.php");


$table = "cs_inbound_call";
$listScale		= 20; 
$pageScale		= 10;


$startPage = isset($_GET['startPage'])?$_GET['startPage']:0;
if( !$startPage ) { $startPage = 0; }



$search_item = isset($_GET["search_item"]) ? $_GET["search_item"] : "";
$search_order = isset($_GET["search_order"]) ? $_GET["search_order"] : "";
$date_from = isset($_GET["date_from"]) ? $_GET["date_from"] : "";
$date_to = isset($_GET["date_to"]) ? $_GET["date_to"] : "";

$inquiry_type = isset($_GET["inquiry_type"]) ? $_GET["inquiry_type"] : 0;
if ($inquiry_type==0)
{
    $inquiry_type = isset($_POST["inquiry_type"]) ? $_POST["inquiry_type"] : 0;
}


if ($date_to == "") {
    $date_to = isset($_POST["date_to"]) ? $_POST["date_to"] : date("Y-m-d");
}
if ($date_from == "") {
    $date_from = isset($_POST["date_from"]) ? $_POST["date_from"] : date("Y-m-d", strtotime($date_to." -7 day"));
}
$date_to2 = date("Y-m-d", strtotime($date_to." +1 day"));


$totalPage = floor($startPage / ($listScale * $pageScale));
$query		= "select * from $table where reg_datetime between date('$date_from') and date('$date_to2') " ;
$query_where = "where reg_datetime between date('$date_from') and date('$date_to2') " ;

if ($search_item=="") {
    $search_item = isset($_POST["search_item"]) ? $_POST["search_item"] : "";
}
if ($search_order=="") {
    $search_order = isset($_POST["search_order"]) ? $_POST["search_order"] : "";
}

//echo $search_item."<br>";
//echo $search_order."<br>";

if($search_order){
    if($search_item){
        $query.="and $search_item like '%$search_order%' ";
        $query_where.="and $search_item like '%$search_order%' ";
    }else{
        $query.="and (product_name like '%$search_order%' or black_consumer_desc like '%$search_order%' or pic_name like '%$search_order%' ) ";
        $query_where.="and (product_name like '%$search_order%' or black_consumer_desc like '%$search_order%' or pic_name like '%$search_order%' ) ";
    }
}
else if ($search_item=="black_consumer") 
{
    $query.="and $search_item = 1 ";
    $query_where.="and $search_item = 1 ";
}

if ($inquiry_type > 0) 
{
    $query.="and (inquiry_type=$inquiry_type) ";
    $query_where.="and (inquiry_type=$inquiry_type) ";
}

$result		= mysqli_query($db->db_conn, $query);
$totalList	= mysqli_num_rows($result);
//echo $totalList."<br>";


$query.=" order by idx desc ";
$query_where.=" order by idx desc ";
$query.="LIMIT $startPage, $listScale";
$result		= mysqli_query($db->db_conn, $query);

if( $startPage ) { $listNo = $totalList - $startPage; } else { $listNo = $totalList; }

$param_url = "date_from=".$date_from."&date_to=".$date_to. "&search_item=".$search_item."&search_order=".$search_order."&inquiry_type=".$inquiry_type;

$return_url = $_SERVER['REQUEST_URI'];

//echo "query: ".$query."<br>";
//echo "param_url: ".$param_url."<br>";
?>


<h4 class="page-header">고객센터 CALL 리스트</h4>

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
        </td>
    </tr>
    <tr>
    <th>검색 조건</th>
        <td>
        <div class="form-group">
            <div class="input-group-btn">
                <select name="search_item" class="form-control input-sm" onchange="onchangeSearchItem(this)" style="width:170px;" >
                    <option value="">통합검색</option>
                    <option value="product_name" <?if($search_item=="product_name"){?>selected<?}?>>제품 모델명</option>
                    <option value="pic_name" <?if($search_item=="pic_name"){?>selected<?}?>>담당자</option>
                    <option value="black_consumer" <?if($search_item=="black_consumer"){?>selected<?}?>>불량(강성) 고객 </option>
                </select>
            </div>
        </div>&nbsp;&nbsp;&nbsp;
        <input type="text" name="search_order" id="search_order" class="form-control input-sm" value="<?=$search_order?>" style="width:170px;" <?if($search_item=="black_consumer") { echo "disabled"; } ?> >
        <br><br>
		<? 
			for($i=0;$i<count($arr_inbound_call_type);$i++)
			{
		?>
				<label class="radio-inline">
					<input type="radio" name="inquiry_type" value="<?echo $i;?>" <? if($inquiry_type==$i) {echo 'checked';} ?> ><?echo $arr_inbound_call_type[$i]?>
				</label>&nbsp;&nbsp;&nbsp;
		<?
			}
		?>
        <br><br>
    </td>
    </tr>
    <tr>
    <td colspan="2" class="text-center">
        <button type="submit" class="btn btn-primary btn-sm">검색</button>
        <a href="<?=$_SERVER['PHP_SELF'] ?>" class="btn btn-default btn-sm">초기화</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

        <a href="cs_inbound_excel_download.php?<?echo $param_url;?>" class="btn btn-success btn-sm" data-toggle="tooltip" title="" >엑셀 다운로드 ALL</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

        <a href="cs_inbound_report_download.php?rptype=daily&date_from=<?echo $date_from;?>&date_to=<?echo $date_to;?>" class="btn btn-info btn-sm" data-toggle="tooltip" title="" >일일보고서 다운로드</a>&nbsp;&nbsp;&nbsp;
        <a href="cs_inbound_report_download.php?rptype=weekly&date_from=<?echo $date_from;?>&date_to=<?echo $date_to;?>" class="btn btn-warning btn-sm" data-toggle="tooltip" title="" >주간보고서 다운로드</a>&nbsp;&nbsp;&nbsp;
        <a href="cs_inbound_report_download.php?rptype=monthly&date_from=<?echo $date_from;?>&date_to=<?echo $date_to;?>" class="btn btn-danger btn-sm" data-toggle="tooltip" title="" >월간보고서 다운로드</a>

    </td>
</tr>
</tbody>
</table>
</form>


<div class="table-responsive">
<table class="table table-bordered table-hover">
<colgroup>
<col width="3%">
<col width="4%">
<col width="12%">
<col width="12%">
<col width="12%">
<col width="15%">
<col width="6%">
<col width="15%">
<col width="5%">
<col width="6%">
</colgroup>
<!--br-->
<thead>
<tr>
<th colspan="2" class="form-inline">
    <a href="javascript:;" class="btn btn-default btn-xs ajax-checkbox" data-dbname="<?=$table?>" data-name="delete" data-val="" <?if(($PERMISSION & PERMISSION_CS)!=PERMISSION_CS) { echo 'disabled';}?> >
    삭제하기</a>
</th>
<td colspan="9"></td>
</tr>
<tr>
    <th><input type="checkbox" id="allCheck"></th>
    <th>N O</th>
    <th>등록일시</th>
    <th>모델명</th>
    <th>문의유형</th>
    <th>불량고객(강성고객)</th>
    <th>담당자</th>
    <th style="background-color: #dfd">관리자전화요청</th>
    <th style="background-color: #dfd">처리결과</th>
    <th>상세보기</th>
</tr>
</thead>
<tbody>

<?
	while($row = mysqli_fetch_array($result)) {

        $date = date('Y-m-d', strtotime($row['reg_datetime']));

?>
        <tr>
        <td class="text-center"><input type="checkbox" name="check_list" value="<? echo $row['idx']; ?>"></td>
        <td class="text-center"><? echo $listNo ?></td>
        <td class="text-center"><? echo $row['reg_datetime'] ?> <? /*if(date('Y-m-d')==$date){ ?>&nbsp;<span class="label label-danger">New</span><? }*/ ?> </td>
        <td class="text-center"><? echo $row['product_name']  ?></td>
        <td class="text-center"><? echo $arr_inbound_call_type[ $row['inquiry_type'] ] ?></td>

        <td class="text-left"><? if($row['black_consumer']) { echo $row['black_consumer_desc']; ?>&nbsp;<span class="label label-danger">강성</span><?}  ?></td>
        <td class="text-center"><? echo $row['pic_name']; ?></td>

        <td class="text-left"><? echo $row['pic_memo']; ?></td>
        <td class="text-center"><? if($row['pic_memo'] != "") { if ($row['admin_result']==0) {echo "처리중";} else {echo "처리완료";} } ?></td>
        <td class="text-center"><a href="./cs_inbound_edit.php?idx=<? echo $row['idx']."&return_url=".urlencode($return_url); ?>" class="btn btn-primary btn-sm">보기</a></td>
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

<script type="text/javascript">
function onchangeSearchItem(obj) {
    var idx = obj.selectedIndex;
    
    if (idx==3)
    {
        //hide search_order
        document.getElementById("search_order").disabled = true;
    }
    else
    {
        //show search_order
        document.getElementById("search_order").disabled = false;
    }

}
</script>

<? include('../footer.php');?>