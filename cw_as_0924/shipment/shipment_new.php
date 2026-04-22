<?
error_reporting(E_ALL);
include("../def_inc.php");
include("event_def.php");

$mod	= M_SHIPMENT;
$menu	= S_SHIPMENT_NEW; 

include("../header.php");



$table			= "shipping_date_new";
$listScale		= 100;
$pageScale		= 10;


$startPage = isset($_GET['startPage'])?$_GET['startPage']:0;
if( !$startPage ) { $startPage = 0; }


$date_today = isset($_GET["date_today"]) ? $_GET["date_today"] : "";
if ($date_today == "") {
    $date_today = isset($_POST["date_today"]) ? $_POST["date_today"] : date("Y-m-d");
//    $date_today = "2021-03-16"; //test
}


$totalPage = floor($startPage / ($listScale * $pageScale));
//$query		= "select * from $table where date = '$date_today' and status = 0 "; //TEST 
$query		= "select * from $table where status = 0 ";

$result		= mysqli_query($db->db_conn, $query);
$totalList	= mysqli_num_rows($result);
//echo $query."<br>";

$query.=" order by idx desc LIMIT $startPage, $listScale";
$result		= mysqli_query($db->db_conn, $query);

if( $startPage ) { $listNo = $totalList - $startPage; } else { $listNo = $totalList; }

$param_url = "date_today=".$date_today;

//echo $query."<br>";
//echo $param_url."<br>";


//배송리스트 파일명 
$query_filename = "select DISTINCT filename FROM $table WHERE status=0 and date='$date_today' GROUP BY filename";
$rs_filename = mysqli_query($db->db_conn, $query_filename);
//$row_filename = mysqli_fetch_array($rs_filename);

?>


<h4 class="page-header">출고 처리 (<?echo $date_today;?>)</h4>

<!--TEST--><!--a href="shipment_test.php" class="btn btn-success btn-sm" >알림톡 테스트</a-->
<!---->
<form method="post" name="upload_form" class="form-inline" enctype="multipart/form-data" action="shipment_excel_upload.php" >
<table class="table table-bordered">
<colgroup>
<col width="15%">
<col width="*">
<col width="15%">
<col width="40%">
</colgroup>
<tbody>
<tr>
    <th>배송리스트 업로드</th>
    <td>
        <input type="hidden" name="return_url" id="return_url" value="<?=$_SERVER['PHP_SELF'];?>" >
        <input type="file" name="userfile1" id="userfile1" style="text-center" accept=".xls,.xlsx" >
        <button type="submit" class="btn btn-info btn-sm" <?if(($PERMISSION & PERMISSION_SHIPMENT)!=PERMISSION_SHIPMENT) { echo 'disabled';}?> >배송리스트 엑셀 파일 업로드</button> <!--onclick="sendit('shipment_excel_upload.php')" -->
    </td>
    <th>업로드된 파일</th>
    <td>
        <? 
        while($row_filename = mysqli_fetch_array($rs_filename)) { 
            echo '<a href="download_file.php?download_filename='.$row_filename['filename'].'">' . $row_filename['filename'].'</a><br>';
        }
        ?>
    </td>
</tr>
<tr>
    <th>CJ택배 접수용 다운로드</th>
    <td>
        <a href="shipment_delivery_excel_download.php?<?echo $param_url;?>" class="btn btn-success btn-sm" >엑셀 다운로드 ALL</a>
    </td>
    <th>우체국택배 접수용 다운로드</th>
    <td>
        <a href="" class="btn btn-success btn-sm" disabled>엑셀 다운로드 ALL</a>  우체국 택배 미지원
    </td>
</tr>
    <th>송장번호 업로드<br>
    <label class="checkbox-inline"><input type="checkbox" name="send_noti" id="send_noti" value="1" >카카오 알림톡 전송</label> <!-- 20211216 -->
	<!--<label class="checkbox-inline"><input type="checkbox" name="send_noti" id="send_noti" value="1" checked >카카오 알림톡 전송</label> -->
    </th>
    <td colspan="3">
        <input type="hidden" name="return_url" id="return_url" value="<?=$_SERVER['PHP_SELF'];?>" ><span>파일접수 상세내역_yyyymmddXXXXXX.xlsx </span>
        <input type="file" name="userfile" id="userfile" style="text-center" accept=".xls,.xlsx" > 
        <button type="button" onclick="sendit('shipment_delivery_excel_upload_new.php')" class="btn btn-info btn-sm" <?if(($PERMISSION & PERMISSION_SHIPMENT)!=PERMISSION_SHIPMENT) { echo 'disabled';}?> >송장번호 엑셀 파일 업로드</button>
        <!--span style="color:red">&nbsp&nbsp * 송장번호 업로드시, 고객의 연락처로 카카오 알림톡이 발송됩니다. </span-->
    </td>
<tr>
</tr>
</tbody>
</table>
</form>



<!---------------------------------- 연도별 청소기재고내역 엑셀 업로드 ---------------------------------->
<!--form method="post" name="upload_form" class="form-inline" enctype="multipart/form-data" action="<?='shipment_import_excel_upload.php' ?>" >
<table class="table table-bordered">
<colgroup>
<col width="15%">
<col width="*">
</colgroup>
<tbody>
    <tr>
    <th>출고데이터 업로드</th>
		<td>
            <input type="hidden" name="return_url" id="return_url" value="shipment_new.php" >
			<input type="file" name="userfile" id="userfile" style="text-center" accept=".xls,.xlsx" >
			<button type="submit" class="btn btn-info btn-sm" >엑셀 파일 업로드</a>
		</td>
    </tr>
</tbody>
</table>
</form-->
<!---------------------------------- 연도별 청소기재고내역 엑셀 업로드 ---------------------------------->



<div class="table-responsive">
<table class="table table-bordered table-hover">
<colgroup>
<col width="3%">
<col width="5%">
<col width="7%">
<col width="12%">
<col width="7%">
<col width="7%">
<col width="10%">
<col width="*">
<col width="8%">
<col width="8%">
<col width="8%">
<col width="8%">
</colgroup>

<thead>
<tr>
    <th colspan="2"><a href="javascript:;" class="btn btn-default btn-xs ajax-checkbox" data-dbname="<?=$table?>" data-name="shipment" data-val="1" <?if(($PERMISSION & PERMISSION_SHIPMENT)!=PERMISSION_SHIPMENT) { echo 'disabled';}?> >출고완료 처리</a></th>
    <th colspan="9"></th>
    <th><a href="javascript:;" class="btn btn-default btn-xs ajax-checkbox" data-dbname="<?=$table?>" data-name="delete" data-val="" <?if(($PERMISSION & PERMISSION_SHIPMENT)!=PERMISSION_SHIPMENT) { echo 'disabled';}?> >삭제하기</a></th>
</tr>
<tr>
    <th><input type="checkbox" id="allCheck"></th>
    <th>N O</th>
    <th>출고일</th>
    <th>모델명</th>
    <th>구매처</th>
    <th>이름</th>
    <th>전화번호(모바일)</th>
    <th>주소</th>
    <th>배송메모</th>
    <th>주문번호</th>
    <th>사방넷/접수번호</th>
    <th>송장번호</th>
</tr>
</thead>
<tbody>

<?
	while($row = mysqli_fetch_array($result)) {
?>
        <tr>
        <td class="text-center"><input type="checkbox" name="check_list" value="<? echo $row['idx'] ?>"></td>
        <td class="text-center"><? echo $listNo ?></td>
        <!--td class="text-center"><? echo $row['idx'] ?></td-->
        <td class="text-center"><? echo $row['date'] ?></td>
        <td class="text-center"><? echo $row['model'] ?></td>
        <td class="text-center"><? echo $row['mall']; ?></td>
        <td class="text-center"><a href="./shipment_view.php?status=0&idx=<? echo $row['idx'] ?>"> <? echo $row['name']; ?></a></td>
        <td class="text-center"><? if($row['phone2']==""){echo $row['phone1'];}else{echo $row['phone2'];}; ?></td>
        <td class="text-center"><? echo $row['address']; ?></td>
        <td class="text-center"><? echo $row['deliverymemo']; ?></td>
        <td class="text-center"><? echo $row['orderid']; ?></td>
        <td class="text-center"><? echo $row['orderid_sabangnet']; ?></td>
        <td class="text-center"><? echo $row['tracking']; ?></td>
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
$(document).ready(function(){
  $('[data-toggle="tooltip"]').tooltip();
});
</script>


<script type="text/javascript">
function sendit(url) {
//   alert(url);

    var form=document.upload_form;
    form.action = url;
    form.submit();
}

</script>

<? include('../footer.php');?>