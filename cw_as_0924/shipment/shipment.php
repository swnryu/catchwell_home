<?
error_reporting(E_ALL);
include("../def_inc.php");

$mod	= M_SHIPMENT;
$menu	= S_SHIPMENT; 

include("../header.php");



$table			= "shipping_date_new";
$listScale		= 100;
$pageScale		= 10;


$startPage = isset($_GET['startPage'])?$_GET['startPage']:0;
if( !$startPage ) { $startPage = 0; }



$search_item = isset($_GET["search_item"]) ? $_GET["search_item"] : "";
$search_order = isset($_GET["search_order"]) ? $_GET["search_order"] : "";
$date_from = isset($_GET["date_from"]) ? $_GET["date_from"] : "";
$date_to = isset($_GET["date_to"]) ? $_GET["date_to"] : "";



if ($date_to == "") {
    $date_to = isset($_POST["date_to"]) ? $_POST["date_to"] : date("Y-m-d");
//    $date_to = "2019-12-31"; //test
}
if ($date_from == "") {
    $date_from = isset($_POST["date_from"]) ? $_POST["date_from"] : date("Y-m-d", strtotime($date_to." -1 month"));
//    $date_from = date("Y-m-d", strtotime($date_to." -1 month")); //test
}
$date_to2 = date("Y-m-d", strtotime($date_to." +0 day"));

$totalPage = floor($startPage / ($listScale * $pageScale));
$query		= "select * from $table where status=1 and date between date('$date_from') and date('$date_to2') ";

if ($search_item=="") {
    $search_item = isset($_POST["search_item"]) ? $_POST["search_item"] : "";
}
if ($search_order=="") {
    $search_order = isset($_POST["search_order"]) ? $_POST["search_order"] : "";
}

if($search_order){
    if($search_item){
        $query.="and $search_item like '%$search_order%' ";
    }else{
        $query.="and (model like '%$search_order%' or name like '%$search_order%' or orderid like '%$search_order%' or orderid_sabangnet like '%$search_order%') ";
    }
}
$result		= mysqli_query($db->db_conn, $query);
$totalList	= mysqli_num_rows($result);
//echo $totalList."<br>";


$query.=" order by idx desc ";
$query.="LIMIT $startPage, $listScale";
$result		= mysqli_query($db->db_conn, $query);

if( $startPage ) { $listNo = $totalList - $startPage; } else { $listNo = $totalList; }

$param_url = "date_from=".$date_from."&date_to=".$date_to. "&search_item=".$search_item."&search_order=".$search_order;

//echo $_POST["date_from"]. "----". $_POST["date_to"] ."<br>";
//echo $_POST["search_order"]. "----". $_POST["search_item"] ."<br>";
//echo $_GET["startPage"]. "<br>";
//echo $query."<br>";
//echo $param_url."<br>";

?>


<h4 class="page-header">출고 완료 전체 조회</h4>

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
            </div>
            
        </td>
    </tr>
    <tr>
    <th>검색어</th>
    <td>
        <div class="form-group">
            <div class="input-group-btn">
                <select name="search_item" class="form-control input-sm" >
                    <option value="">통합검색</option>
                    <option value="model" <?if($search_item=="model"){?>selected<?}?>>모델명</option>
                    <option value="name" <?if($search_item=="name"){?>selected<?}?>>이름</option>
                    <option value="orderid" <?if($search_item=="orderid"){?>selected<?}?>>주문번호</option>
                    <option value="orderid_sabangnet" <?if($search_item=="orderid_sabangnet"){?>selected<?}?>>주문번호 사방넷</option>
                    <!--option value="serial" <?if($search_item=="serial"){?>selected<?}?>>시리얼번호</option-->
                </select>
            </div>
        </div>
        <input type="text" name="search_order" class="form-control input-sm" value="<?=$search_order?>">
    </td>
</tr>
<tr>
    <td colspan="2" class="text-center">
        <button type="submit" class="btn btn-primary btn-sm">검색</button>&nbsp;
        <a href="<?=$_SERVER['PHP_SELF'] ?>" class="btn btn-default btn-sm">초기화</a>&nbsp;&nbsp;&nbsp;
        <a href="shipment_excel_download.php?<?echo $param_url;?>" class="btn btn-success btn-sm" >엑셀 다운로드 ALL</a>
    </td>
</tr>
</tbody>
</table>
</form>


<div class="table-responsive">
<table class="table table-bordered table-hover">
<colgroup>
<!--col width="3%"-->
<col width="4%">
<col width="7%">
<col width="10%">
<col width="8%">
<col width="8%">
<col width="10%">
<col width="*">
<col width="8%">
<col width="8%">
<col width="8%">
</colgroup>

<thead>
<tr>
    <!--th><input type="checkbox" id="allCheck"></th-->
    <th>N O</th>
    <th>출고일</th>
    <th>모델명</th>
    <th>구매처</th>
    <th>이름</th>
    <th>전화번호(모바일)</th>
    <th>주소</th>
    <!--th>배송메모</th-->
    <th>주문번호</th>
    <th>사방넷/접수번호</th>
    <th>송장번호</th>
    <th>상세</th>
</tr>
</thead>
<tbody>

<?
	while($row = mysqli_fetch_array($result)) {
?>
        <tr>
        <!--td class="text-center"><input type="checkbox" name="check_list" value="<? echo $row['idx'] ?>"></td-->
        <td class="text-center"><? echo $listNo ?></td>
        <td class="text-center"><? echo $row['date'] ?></td>
        <td class="text-center"><? echo $row['model'] ?></td>
        <td class="text-center"><? echo $row['mall']; ?></td>
        <td class="text-center"><a href="./shipment_view.php?idx=<? echo $row['idx'] ?>" ><? echo $row['name']; ?></a></td>
        <td class="text-center"><? if($row['phone2']==""){echo $row['phone1'];}else{echo $row['phone2'];}; ?></td>
        <td class="text-center"><? echo $row['address']; ?></td>
        <td class="text-center"><? echo $row['orderid']; ?></td>
        <td class="text-center"><? echo $row['orderid_sabangnet']; ?></td>

        <? $tracking_num = preg_replace("/[^0-9]*/s", "", $row['tracking']); ?>
        <td><a href="<? if (strlen($tracking_num)==12) {echo constant('TRACKING_CJ').$row['tracking'];} else {echo constant('TRACKING_EPOST').$tracking_num;} ?>" target="_blank"><?echo $row['tracking'] ?></a></td>
        
        <td class="text-center"><a href="./shipment_view.php?idx=<? echo $row['idx'] ?>" class="btn btn-primary btn-sm">보기</a></td>
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



<? include('../footer.php');?>