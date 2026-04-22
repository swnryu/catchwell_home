<?
//20230515 exchange shipment list add.

error_reporting(E_ALL);
include("../def_inc.php");
include("cancellation_def.php");

$mod	= M_CANCELLATION;
//$menu	= S_CANCELLATION; 
$menu	= S_EXCHANGE;//20230515 

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

//20230515 exchange ------------------------------------------------------
$type = isset($_GET["type"]) ? $_GET["type"] : ""; 
//주석
//echo "0status: ".$status."<br>";
//echo "0type: ".$type."<br>";

if ($date_to == "") {
    $date_to = isset($_POST["date_to"]) ? $_POST["date_to"] : date("Y-m-d");
//    $date_to = "2019-12-31"; //test
}
if ($date_from == "") {
    $date_from = isset($_POST["date_from"]) ? $_POST["date_from"] : date("Y-m-d", strtotime($date_to." -7 day"));
//    $date_from = date("Y-m-d", strtotime($date_to." -1 month")); //test
}
$date_to2 = date("Y-m-d", strtotime($date_to." +0 day"));
//주석
//echo "1status: ".$status."<br>";
//echo "1type: ".$type."<br>";

if ($status == "") 
{
    //$status = isset($_POST["status"]) ? $_POST["status"] : 0;//초기 고정값을 넣어준다.
    $status = isset($_POST["status"]) ? $_POST["status"] : 1;//초기 고정값을 넣어준다.//20230809 
}    
$status_query = ""; 
if ($status == 0) {
    $status_query = "and status = 0 ";
}
else if ($status == 1) {
    $status_query = "and status = 1 "; 
}

//-------------------------------------------------------20230515
//$cancellation_type = array("교환","변심반품","불량반품"); //0-교환, 1-변심반품, 2-불량반품
if ($type == "")
{
   //$type = isset($_POST["type"]) ? $_POST["type"] : "";
   $type = isset($_POST["type"]) ? $_POST["type"] : "교환";//초기 고정값을 넣어준다.
}

/*
$type_query = ""; 
if ($type == "교환")
{
    $type_query = "type='교환' ";
}

else if ($type == "반품") {
   $type_query = "type='반품' "; 
}

//주석
echo "3status: ".$status."<br>";
echo "3type: ".$type."<br>";
*/

$totalPage = floor($startPage / ($listScale * $pageScale));

//$query		= "select * from $table where date between date('$date_from') and date('$date_to2') " . $status_query;
//$query_where = "where date between date('$date_from') and date('$date_to2') " . $status_query;
//20230515 교환 필터링을 하려면 db에서 제일 먼저 넣어줘야한다.
//$query		= "select * from $table where type='교환'and date between date('$date_from') and date('$date_to2') " . $status_query;
//$query_where = "where type='교환'and date between date('$date_from') and date('$date_to2') " . $status_query;

//20230809 type : 교환 이고 status : 처리완료(1) 이다.
$query		= "select * from $table where type='교환' and status='1' and date between date('$date_from') and date('$date_to2') " . $status_query;
$query_where = "where type='교환'and status='1' and date between date('$date_from') and date('$date_to2') " . $status_query;


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
//주석
//echo "totalList: ".$totalList."<br>";


$query.=" order by idx desc ";
$query_where.=" order by idx desc ";
$query.="LIMIT $startPage, $listScale";
$result		= mysqli_query($db->db_conn, $query);

if( $startPage ) { $listNo = $totalList - $startPage; } else { $listNo = $totalList; }

$param_url = "date_from=".$date_from."&date_to=".$date_to. "&search_item=".$search_item."&search_order=".$search_order."&status=".$status;


/*
//20230515 로그 주석처리 
echo "query: ".$query."<br>";
echo "param_url: ".$param_url."<br>";
echo "status_query: ".$status_query."<br>";
echo "query_where: ".$query_where."<br>";
*/
?>


<h4 class="page-header">교환 출고 리스트</h4><!-- 20230515 -->

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
                    <!--a href="cancellation_excel_download.php?query_where=<?//echo $query_where;?>&date_from=<?//echo $date_from;?>&date_to=<?//echo $date_to;?>" class="btn btn-success btn-sm" >엑셀 다운로드 ALL</a-->
   
                </td>
            </tr>
            <!--a href="cancellation_report_download_test.php?rptype=monthly&date_from=<?echo $date_from;?>" class="btn btn-info btn-sm" >test</a-->
        </tbody>
    </table>
</form>

<!-- 20230904 송장번호 업로드 추가 -->
<table class="table table-bordered">
    <colgroup>
            <col width="12%">
            <col width="*">
    </colgroup>
    <tbody>
        <tr>
            <!-- form으로 추가로 인하여 위치 변경   exchange_delivery_excel_upload.php  -->
				<form method="post" name="upload_form" class="form-inline" enctype="multipart/form-data" action="<?='exchange_delivery_excel_upload.php' ?>" >
					<th>송장번호 입력</th>
					<td>
					<!--span>발송고객 일별 배달상세_yyyymmdd.xlsx </span-->
                    <span>배송리스트_event_YYYYMMDD.xlsx </span> <!-- 배송리스트_event_'.date("Ymd");  var filename = "배송리스트_event_"+y+m+d+".xlsx"; -->
						<input type="file" name="userfile" id="userfile" style="text-center" accept=".xls,.xlsx" >
						<button type="submit" class="btn btn-info btn-sm" <?if(($PERMISSION & PERMISSION_SALES)!=PERMISSION_SALES) { echo 'disabled';}?> >엑셀 업로드</a>
					</td>
				</form>
        </tr>
    </tbody>

</table>


<div class="table-responsive">
    <table class="table table-bordered table-hover">
        <colgroup>
            <col width="4%">
            <col width="7%">
            <col width="9%">
            <col width="12%">
            <col width="9%">
            <col width="8%">
            <col width="8%"><!--구분 -->
            <col width="*">
            <col width="11%">
            <col width="8%">
            <col width="6%">

            <!--<col width="6%"> 임시1 -->
            <!--
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
            -->
        </colgroup>
        <br>
        <thead>
            <tr>
                <!--https://2018-start.tistory.com/21 colspan 열합치기 -->
                <th colspan="2" class="form-inline">
                    <a href="javascript:;" class="btn btn-default btn-sm ajax-checkbox" data-dbname="<?=$table?>" data-name="delete" data-val="" <?//if(($PERMISSION & PERMISSION_CS)!=PERMISSION_CS) { echo 'disabled';}?> >삭제</a>
                </th>
                <th>
                    <!--a href="javascript:;" class="btn btn-default btn-xs ajax-checkbox-local" data-dbname="<?//=$table?>" data-name="status" data-val="" <?//if(($PERMISSION & PERMISSION_CS)!=PERMISSION_CS) { echo 'disabled';}?> >완료처리하기</a-->
                    <a href="javascript:;" class="btn btn-success btn-sm ajax-checkbox-excel" data-dbname="<?=$table?>" data-name="export2excel" data-val="" >출고리스트 다운로드</a><!--   배송리스트_event_YYYYMMDD.xlsx 배송리스트 엑셀 양식과 같다-->
                </th>    
                <td colspan="10">
                   
                 
                </td>
            </tr>
            
            <tr>
                <th><input type="checkbox" id="allCheck"></th><!-- -->
                <th>N O</th> <!-- 1-->
                <th>등록일</th> <!-- 2-->
                <th>모델명</th> <!-- 3-->


                <!--th>업체명</th--> <!-- 20230913 업체명에서 구매처로 변경 요청 -->
                <th>구매처</th> <!-- 4-->

                <th>고객명</th> <!-- 5-->

                <th>구분</th><!-- 임시 -->

                <th>사유</th>
                <!--th>출고송장번호</th--> <!-- 6-->
                <th>재 출고 송장번호</th> <!-- 6 20230809-->
                <th>완료일</th> <!-- 7-->
                <th>상세보기</th> <!-- 8-->

                <!--<th>상태</th>  임시1-->

                <!--
                <th>N O 1 </th>
                <th>요청일2 </th>
                <th>모델명3 </th>
                <th>주문번호4 </th>
                <th>구매처5 </th>
                <th>업체명6 </th>
                <th>구분7 </th>
                <th>고객명8 </th>
                <th>사유9 </th>
                <th>고객 조율사항10 </th>
                <th>완료일11 </th>
                <th>상세보기12 </th>
                -->
            </tr>
        </thead>
        <tbody>

            <?
            while($row = mysqli_fetch_array($result)) {
                include ("replacement_tracking_from_salesdb.php");
            ?>

                <tr>
                    <td class="text-center"><input type="checkbox" name="check_list" value="<? echo $row['idx']; ?>"></td>  <!-- NO 1 -->
                    <td class="text-center"><? echo $listNo ?></td>                                                         <!-- NO 2 -->
                    <td class="text-center"><? echo $row['date'] ?></td>                                                    <!-- 요청일(등록일) 3-->
                    <td class="text-center"><? echo $row['model_name']  ?></td>                                             <!--모델명 4-->
                    <!--td class="text-center"><? //echo $row['order_id'] ?></td-->
                   
                    <!-- 20230913 업체명에서 구매처로 변경 요청 -->
                    <!--td class="text-center"><? //echo $row['company_name']; ?></td-->                                      <!--업체명 5-->
                    <td class="text-center"><? echo $row['shopping_mall']; ?></td>                                          <!--구매처 5-->

                    <!--td class="text-center"><? //echo $row['type']; ?></td-->
                    <td class="text-center" style="color:blue;"><? echo $row['customer_name']; ?></td>                      <!--고객명(수령자명) 6-->

                    <td class="text-center"><? echo $row['type']; ?></td>                                                   <!-- 구분 -->

                    <td class="text-center"><? echo $row['reason']; ?></td>                                                 <!--사유 7-->

                    <td class="text-center"><? echo $row['exchange_tracking_number']; ?></td>                               <!--출고송장번호 8 추가  db추가?? 교환출고송장이니깐새로운db생성 20230809 재출고송장번호-->

                    <!--td class="text-center"><?//if($tracking_sales!="") { echo $tracking_sales . "<br>";} echo $row['memo']; ?></td-->

                    <td class="text-center">
                        <? 
                        if ( $row['status']==0) { echo ""; } 
                        else { echo date('m/d', strtotime($row['date_completed'])) . " " . $result_type[$row['result_type']];  } 
                        ?>
                    </td>                                                                                                   <!--사유 9 완료일-->

                    <td class="text-center"><a href="./cancellation_edit.php?idx=<? echo $row['idx']; ?>" class="btn btn-primary btn-sm">보기</a></td><!--상세보기 10-->

                    <!--td class="text-center"><? //echo $row['status']; ?></td-->                                                 <!-- 임시1 -->
                </tr>
  

            <?
                $listNo--;
            ?>
            <?
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

<script>
    //20230515
    $(function() {
        $(".ajax-checkbox-excel").click(function() {

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

                    //filename : 배송리스트_event_YYYYMMDD.xlsx
                    //filename : 출고리스트_event_YYYYMMDD.xlsx
                    var dt = new Date();
                    var y = dt.getFullYear().toString();
                    var m = (dt.getMonth()+1).toString();
                    var d = (dt.getDate().toString());
                    if (dt.getMonth()+1 < 10)	m = "0"+(dt.getMonth()+1).toString();
                    if (dt.getDate() < 10)		d = "0"+(dt.getDate().toString());
                    //var filename = "배송리스트_event_"+y+m+d+".xlsx"; 
                    var filename = "배송리스트_event_"+y+m+d+".xlsx"; 


                    ans = confirm(msg + " " + msg2);
                    if(ans==true){
                        $.ajax({
                            //url : "common_delivery_excel_download.php?filename="+filename,
                            url : "exchange_delivery_excel_download.php?filename="+filename,//20230809테스트로 exchange_delivery_excel_download1.php로 변경
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
                        url : "common_delivery_done.php",
                        type: "post",
                        data: postData,
                        success:function(result){ 
                            alert('처리되었습니다.');
                            location.reload();
                            /*
                            if (result.success == 'ok'){
                                alert('success');	
                            }
                            */
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