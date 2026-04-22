<?
include("../def_inc.php");
$mod	= M_INBOUND;	
$menu	= isset($_GET['shipment']) ? S_PARTS_SHIPMENT : S_PARTS_LIST; //20211206
include("../header.php");
include("cs_inbound_def.php");


$table = "cs_shipping_parts";
$listScale		= 50; 
$pageScale		= 10;


$startPage = isset($_GET['startPage'])?$_GET['startPage']:0;
if( !$startPage ) { $startPage = 0; }



$search_item = isset($_GET["search_item"]) ? $_GET["search_item"] : "";
$search_order = isset($_GET["search_order"]) ? $_GET["search_order"] : "";
$date_from = isset($_GET["date_from"]) ? $_GET["date_from"] : "";
$date_to = isset($_GET["date_to"]) ? $_GET["date_to"] : "";

if ($menu==S_PARTS_SHIPMENT) {
    $status = 1;
}
else {
    $status = 0;
}

if ($date_to == "") {
    $date_to = isset($_POST["date_to"]) ? $_POST["date_to"] : date("Y-m-d");
}
if ($date_from == "") {
    $date_from = isset($_POST["date_from"]) ? $_POST["date_from"] : date("Y-m-d", strtotime($date_to." -1 month"));
}
$date_to2 = date("Y-m-d", strtotime($date_to." +1 day"));

if ($menu==S_PARTS_LIST) {
    $date_from = "2021-12-01";
}

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
        $query.="and (product_name like '%$search_order%' or customer_name like '%$search_order%' or pic_name like '%$search_order%' or customer_phone like '%$search_order%' ) ";
        $query_where.="and (product_name like '%$search_order%' or customer_name like '%$search_order%' or pic_name like '%$search_order%' or customer_phone like '%$search_order%' ) ";
    }
}

//if ($status==0 || $status==1) 
{
    $query.="and (status=$status) ";
    $query_where.="and (status=$status) ";
}


$result		= mysqli_query($db->db_conn, $query);
$totalList	= mysqli_num_rows($result);
//echo $totalList."<br>";


$query.=" order by idx desc ";
$query_where.=" order by idx desc ";
$query.="LIMIT $startPage, $listScale";
$result		= mysqli_query($db->db_conn, $query);

if( $startPage ) { $listNo = $totalList - $startPage; } else { $listNo = $totalList; }

//20220117
if ($menu==S_PARTS_SHIPMENT) {
    $param_url = "shipment=1&date_from=".$date_from."&date_to=".$date_to. "&search_item=".$search_item."&search_order=".$search_order."&inquiry_type=".$inquiry_type;
}else{
    $param_url = "date_from=".$date_from."&date_to=".$date_to. "&search_item=".$search_item."&search_order=".$search_order."&inquiry_type=".$inquiry_type;
}

$return_url = $_SERVER['REQUEST_URI'];

//echo "query: ".$query."<br>";
//echo "param_url: ".$param_url."<br>";
//echo $_SERVER['PHP_SELF'];
?>


<h4 class="page-header"><?if($menu==S_PARTS_SHIPMENT) {echo "부품 출고 완료 조회";} else {echo "부품 출고 리스트";}?> </h4>

<?if ($menu==S_PARTS_SHIPMENT) { ?>
<form method="post" name="search_form" class="form-inline" action="<?=$_SERVER['PHP_SELF']."?shipment=1";?>" >
<? } else { ?>
<form method="post" name="search_form" class="form-inline" action="<?=$_SERVER['PHP_SELF'];?>" >
<? } ?>        

<?if ($menu==S_PARTS_SHIPMENT) { ?>
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

            <!--label class="radio-inline"><input type="radio" name="status" value="0" <?if ($status==0) {echo "checked";}?> > [처리중] 보기	</label>&nbsp;&nbsp;&nbsp;&nbsp;
            <label class="radio-inline"><input type="radio" name="status" value="1" <?if ($status==1) {echo "checked";}?> > [처리완료] 보기 </label>&nbsp;&nbsp;&nbsp;&nbsp;
            <label class="radio-inline"><input type="radio" name="status" value="2" <?if ($status==2) {echo "checked";}?> > 전체 보기 </label-->

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
                    <option value="customer_name" <?if($search_item=="customer_name"){?>selected<?}?>>고객명 </option>
                </select>
            </div>
        </div>
        <input type="text" name="search_order" id="search_order" class="form-control input-sm" value="<?=$search_order?>" style="width:170px;" >
    </td>
    </tr>
    <tr>
    <td colspan="2" class="text-center">
        <button type="submit" class="btn btn-primary btn-sm">검색</button>
<?if ($menu==S_PARTS_SHIPMENT) { ?>        
        <a href="<?=$_SERVER['PHP_SELF']."?shipment=1" ?>" class="btn btn-default btn-sm">초기화</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <a href="cs_parts_excel_download.php?<?echo ($param_url);?>" class="btn btn-success btn-sm">엑셀 다운로드 ALL</a>
<? } else { ?>
    <a href="<?=$_SERVER['PHP_SELF'] ?>" class="btn btn-default btn-sm">초기화</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<? } ?>    
        <!--a href="cs_inbound_excel_download.php?<?echo $param_url;?>" class="btn btn-success btn-sm" data-toggle="tooltip" title="" >엑셀 다운로드 ALL</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <a href="cs_inbound_report_download.php?rptype=daily&date_from=<?echo $date_from;?>&date_to=<?echo $date_to;?>" class="btn btn-info btn-sm" data-toggle="tooltip" title="" >일일 보고서 다운로드</a>&nbsp;&nbsp;&nbsp;
        <a href="cs_inbound_report_download.php?rptype=weekly&date_from=<?echo $date_from;?>&date_to=<?echo $date_to;?>" class="btn btn-warning btn-sm" data-toggle="tooltip" title="" >주간 보고서 다운로드</a>&nbsp;&nbsp;&nbsp;
        <a href="cs_inbound_report_download.php?rptype=monthly&date_from=<?echo $date_from;?>&date_to=<?echo $date_to;?>" class="btn btn-danger btn-sm" data-toggle="tooltip" title="" >월간 보고서 다운로드</a-->

    </td>
</tr>
</tbody>
</table>
<? } ?>
</form>

<? if ($menu==S_PARTS_LIST) {?>
<form method="post" name="upload_form" class="form-inline" enctype="multipart/form-data" action="<?='cs_parts_excel_upload.php'?>" >
	<table class="table table-bordered">
	<colgroup>
	<col width="15%">
	<col width="*">
	</colgroup>
	<tbody>
	<tr>
		<th>택배 송장번호 업로드</th>
		<!--td>발송고객 일별 배달상세_yyyymmdd.xlsx
			<input type="file" name="userfile" id="userfile" style="text-center" accept=".xls,.xlsx" >
			<input type="hidden" name="return_url" value="<?=$_SERVER['PHP_SELF']?>" >
			<input type="hidden" name="state" value="<?=$state?>" >
			<button type="submit" class="btn btn-info btn-sm" <?if(($PERMISSION & PERMISSION_CS)!=PERMISSION_CS) { echo 'disabled';}?> >송장번호 엑셀 파일 업로드</button>
		</td-->
        
        <!--20230822 발송고객 일별 배달상세_yyyymmdd.xlsx 삭제 파일명 :  기업고객 일별 배송상세_yyyymmdd.xlsx  변경-->
        <td>
			<input type="file" name="userfile" id="userfile" style="text-center" accept=".xls,.xlsx" >
			<input type="hidden" name="return_url" value="<?=$_SERVER['PHP_SELF']?>" >
			<input type="hidden" name="state" value="<?=$state?>" >
			<button type="submit" class="btn btn-info btn-sm" <?if(($PERMISSION & PERMISSION_CS)!=PERMISSION_CS) { echo 'disabled';}?> >송장번호 엑셀 파일 업로드</button>
		</td>
	</tr>
	</tbody>
	</table>
</form>
<? } ?>

<div class="table-responsive">
<table class="table table-bordered table-hover">
<colgroup>
<? if ($menu==S_PARTS_LIST) {?>
<col width="3%">
<? } ?>
<col width="4%">
<col width="8%">
<col width="6%">
<col width="10%">
<col width="12%">
<col width="6%">
<col width="12%">
<col width="*">
<col width="7%">
<col width="6%">
<col width="8%">
<col width="6%">
</colgroup>
<!--br-->
<thead>
<? if ($menu==S_PARTS_LIST) {?>    
<tr>
<th colspan="2" class="form-inline">
    <a href="javascript:;" class="btn btn-default btn-xs ajax-checkbox" data-dbname="<?=$table?>" data-name="delete" data-val="" <?if(($PERMISSION & PERMISSION_CS)!=PERMISSION_CS) { echo 'disabled';}?> >
    삭제하기</a>
</th>
<th>
<a href="javascript:;" class="btn btn-default btn-xs ajax-checkbox-local" data-dbname="<?=$table?>" data-name="status" data-val="" <?if(($PERMISSION & PERMISSION_CS)!=PERMISSION_CS) { echo 'disabled';}?> >
    완료처리하기</a>
</th>    
<td colspan="10">
<a href="javascript:;" class="btn btn-success btn-xs ajax-checkbox-local" data-dbname="<?=$table?>" data-name="delivery_excel_download" data-val="" data-toggle="tooltip" title="CJ택배 송장" <?if(($PERMISSION & PERMISSION_CS)!=PERMISSION_CS) { echo 'disabled';}?> >
    택배송장 엑셀 다운로드</a>
</td>
</tr>
<? } ?>

<tr>
<? if ($menu==S_PARTS_LIST) {?>        
    <th><input type="checkbox" id="allCheck"></th>
<? } ?>      
    <th>N O</th>
    <th>등록일</th>
    <th>고객명</th>
    <th>모델명</th>
    <th>부품명</th>
    <th>가격(원)</th>
    <th>사유</th>
    <th>담당자 메모</th>
    <th>상태</th>
    <th>담당자</th>
    <th>송장번호</th>
    <th>상세</th>
</tr>
</thead>
<tbody>

<?
	while($row = mysqli_fetch_array($result)) {

        $date = date('Y-m-d', strtotime($row['reg_datetime']));

        $parts_name = rtrim($row['parts_name'],";");
        $parts_name = str_replace(";",", ",$parts_name);
        $parts_name_ex = rtrim($row['parts_name_ex'],";");
        if ($parts_name_ex != "")
        {
            $parts = str_replace("(V)","",$parts_name).", ".$parts_name_ex; //20211213
        }
        else
        {
            $parts = str_replace("(V)","",$parts_name).$parts_name_ex; //20211213
        }
        $parts = ltrim($parts,", ");
        
?>
        <tr>
<? if ($menu==S_PARTS_LIST) {?>        
        <td class="text-center"><input type="checkbox" name="check_list" value="<? echo $row['idx']; ?>"></td>
<? } ?>
        <td class="text-center"><? echo $listNo ?></td>
        <td class="text-center"><? echo date("Y-m-d", strtotime($row['reg_datetime'])); ?></td>
        <td class="text-center" style="color:blue;"><? echo $row['customer_name']; ?></td>
        <td class="text-center"><? echo $row['product_name']  ?></td>

        <td class="text-center"><? echo $parts ?></td>
        <td class="text-right"><? echo number_format($row['parts_price']) ?></td>

        <td class="text-center"><? echo $row['reason'] ?></td>
        <td class="text-center"><? echo $row['pic_memo']; ?></td>
        <td class="text-center"><? if($row['status']==0){echo "처리중";} else{echo "처리완료";} ?></td>
        <td class="text-center"><? echo $row['pic_name']; ?></td>
        <td><a href="<? if (strlen($row['delivery_num'])==12) {echo constant('TRACKING_CJ').$row['delivery_num'];} ?>" target="_blank"><?echo $row['delivery_num'] ?></a></td>
<? if ($menu==S_PARTS_LIST) {?>
        <td class="text-center"><a href="./cs_parts_edit.php?idx=<? echo $row['idx']."&return_url=".urlencode($return_url); ?>" class="btn btn-primary btn-sm">보기</a></td>
<? } else { ?>
        <td class="text-center"><a href="./cs_parts_edit.php?shipment=1&idx=<? echo $row['idx']."&return_url=".urlencode($return_url);?>" class="btn btn-primary btn-sm">보기</a></td>
<? } ?>
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

var number, nArr;
function number_format( number )
{
	number=number.replace(/\,/g,"");

	nArr = String(number).split('').join(',').split('');

	for( var i=nArr.length-1, j=1; i>=0; i--, j++)  if( j%6 != 0 && j%2 == 0) nArr[i] = '';

	return nArr.join('');
}

</script>



<script>
$(".ajax-checkbox-local").on("click", function(e) {
	
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


    if(name=="status") {
        
        var msg = "[완료처리]";var msg2 = "하시겠습니까?";

        if(  $("input:checkbox[name='check_list']").is(":checked") ){
            ans = confirm(msg + " " + msg2);
            if(ans==true){	
            $.ajax({
                
                url : "cs_parts_ajax_checkbox.php",
                type: "post",
                data: postData,
                success:function(obj){ 
                    location.reload();
                }
                
            });
            }
        }else{
            alert(msg+" "+"항목을 선택하여 주세요.");
        }
    }
    else if(name=="delivery_excel_download") {
        
        var msg = "[택배송장 엑셀 다운로드]";var msg2 = "하시겠습니까?";

        if(  $("input:checkbox[name='check_list']").is(":checked") ){
            ans = confirm(msg + " " + msg2);
            if(ans==true){	
            $.ajax({
                
                url : "cs_parts_ajax_checkbox.php",
                type: "post",
                data: postData,
 
                success:function(obj){ 
                    //AS_YYYYMMDD.xlsx
                    var dt = new Date();
                    var y = dt.getFullYear().toString();
                    var m = (dt.getMonth()+1).toString();
                    var d = (dt.getDate().toString());

                    if (dt.getMonth()+1 < 10)	m = "0"+(dt.getMonth()+1).toString();
                    if (dt.getDate() < 10)		d = "0"+(dt.getDate().toString());

                    var filename = "../temp"+"/CS부품출고_"+y+m+d+".xlsx";

                    var win = window.open(filename, "_blank");
                    location.reload();
				}            

            });
            }
        }else{
            alert(msg+" "+"항목을 선택하여 주세요.");
        }
    }

});

</script>

<script>
$(document).ready(function(){
  $('[data-toggle="tooltip"]').tooltip();
});
</script>

<? include('../footer.php');?>