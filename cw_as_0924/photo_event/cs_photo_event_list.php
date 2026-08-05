<?
include("../def_inc.php");
$mod  = M_CANCELLATION;
$menu = isset($_GET['shipment']) ? S_PHOTO_EVENT_SHIPMENT : S_PHOTO_EVENT_LIST;
include("../header.php");

$table     = "cs_photo_event_orders";
$listScale = 50;
$pageScale = 10;

$startPage = isset($_GET['startPage']) ? (int)$_GET['startPage'] : 0;

$search_item  = isset($_GET['search_item'])  ? $_GET['search_item']  : "";
$search_order = isset($_GET['search_order']) ? $_GET['search_order'] : "";
$date_from    = isset($_GET['date_from'])    ? $_GET['date_from']    : "";
$date_to      = isset($_GET['date_to'])      ? $_GET['date_to']      : "";

$status = ($menu == S_PHOTO_EVENT_SHIPMENT) ? 1 : 0;

if ($date_to == "")   $date_to   = isset($_POST['date_to'])   ? $_POST['date_to']   : date("Y-m-d");
if ($date_from == "") $date_from = isset($_POST['date_from']) ? $_POST['date_from'] : date("Y-m-d", strtotime($date_to." -1 month"));
$date_to2 = date("Y-m-d", strtotime($date_to." +1 day"));

if ($menu == S_PHOTO_EVENT_LIST) $date_from = "2026-01-01";

if ($search_item == "")  $search_item  = isset($_POST['search_item'])  ? $_POST['search_item']  : "";
if ($search_order == "") $search_order = isset($_POST['search_order']) ? $_POST['search_order'] : "";

$totalPage = floor($startPage / ($listScale * $pageScale));

$query       = "select * from $table where reg_datetime between date('$date_from') and date('$date_to2') ";
$query_where = "where reg_datetime between date('$date_from') and date('$date_to2') ";

if ($search_order) {
    if ($search_item) {
        $query       .= "and $search_item like '%$search_order%' ";
        $query_where .= "and $search_item like '%$search_order%' ";
    } else {
        $q = "and (product_gift like '%$search_order%' or customer_name like '%$search_order%' or shop_name like '%$search_order%' or order_num like '%$search_order%') ";
        $query       .= $q;
        $query_where .= $q;
    }
}

$query       .= "and status=$status ";
$query_where .= "and status=$status ";

$result    = mysqli_query($db->db_conn, $query);
$totalList = mysqli_num_rows($result);

$query       .= " order by idx desc ";
$query_where .= " order by idx desc ";
$query       .= "LIMIT $startPage, $listScale";
$result = mysqli_query($db->db_conn, $query);

if ($startPage) { $listNo = $totalList - $startPage; } else { $listNo = $totalList; }

if ($menu == S_PHOTO_EVENT_SHIPMENT) {
    $param_url = "shipment=1&date_from=$date_from&date_to=$date_to&search_item=$search_item&search_order=$search_order";
} else {
    $param_url = "date_from=$date_from&date_to=$date_to&search_item=$search_item&search_order=$search_order";
}

$return_url = $_SERVER['REQUEST_URI'];
?>

<h4 class="page-header"><?if($menu==S_PHOTO_EVENT_SHIPMENT){echo "포토이벤트 출고 완료 조회";}else{echo "포토이벤트 요청 리스트";}?></h4>

<?if($menu==S_PHOTO_EVENT_SHIPMENT){?>
<form method="post" name="search_form" class="form-inline" action="<?=$_SERVER['PHP_SELF']."?shipment=1"?>">
<table class="table table-bordered">
<colgroup><col width="15%"><col width="*"></colgroup>
<tbody>
    <tr>
        <th>기간 선택</th>
        <td>
            <div class="input-group datetime" style="width:170px;">
                <input type="text" name="date_from" class="form-control input-sm text-center" placeholder="YYYY-MM-DD" value="<?=$date_from?>"/>
                <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
            </div>
            ~
            <div class="input-group datetime" style="width:170px;">
                <input type="text" name="date_to" class="form-control input-sm text-center" placeholder="YYYY-MM-DD" value="<?=$date_to?>"/>
                <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
            </div>
        </td>
    </tr>
    <tr>
        <th>검색 조건</th>
        <td>
            <div class="form-group">
                <div class="input-group-btn">
                    <select name="search_item" class="form-control input-sm" style="width:170px;">
                        <option value="">통합검색</option>
                        <option value="product_gift"  <?if($search_item=="product_gift"){?>selected<?}?>>품목명</option>
                        <option value="shop_name"     <?if($search_item=="shop_name"){?>selected<?}?>>쇼핑몰</option>
                        <option value="customer_name" <?if($search_item=="customer_name"){?>selected<?}?>>고객명</option>
                        <option value="order_num"     <?if($search_item=="order_num"){?>selected<?}?>>주문번호</option>
                        <option value="pic_name"      <?if($search_item=="pic_name"){?>selected<?}?>>담당자</option>
                    </select>
                </div>
            </div>
            <input type="text" name="search_order" class="form-control input-sm" value="<?=$search_order?>" style="width:170px;">
        </td>
    </tr>
    <tr>
        <td colspan="2" class="text-center">
            <button type="submit" class="btn btn-primary btn-sm">검색</button>
            <a href="<?=$_SERVER['PHP_SELF']."?shipment=1"?>" class="btn btn-default btn-sm">초기화</a>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <a href="cs_photo_event_excel_download.php?shipment=1&<?=$param_url?>" class="btn btn-success btn-sm">엑셀 다운로드</a>
        </td>
    </tr>
</tbody>
</table>
</form>
<?}else{?>
<form method="post" name="search_form" class="form-inline" action="<?=$_SERVER['PHP_SELF']?>"></form>
<?}?>

<?if($menu==S_PHOTO_EVENT_LIST){?>
<form method="post" name="upload_form" class="form-inline" enctype="multipart/form-data" action="cs_photo_event_excel_upload.php">
    <table class="table table-bordered">
    <colgroup><col width="15%"><col width="*"></colgroup>
    <tbody>
    <tr>
        <th>요청 리스트 업로드</th>
        <td>
            <input type="file" name="userfile" id="userfile" accept=".xls,.xlsx">
            <input type="hidden" name="return_url" value="<?=$_SERVER['PHP_SELF']?>">
            <button type="submit" class="btn btn-info btn-sm" <?if(($PERMISSION & PERMISSION_CS)!=PERMISSION_CS){echo 'disabled';}?>>xlsx 파일 업로드</button>
        </td>
    </tr>
    <tr>
        <th>출고리스트 다운로드</th>
        <td>
            <a href="cs_photo_event_excel_download.php" class="btn btn-success btn-sm"
               <?if(($PERMISSION & PERMISSION_CS)!=PERMISSION_CS){echo 'disabled';}?>>
                출고리스트 엑셀 다운로드
            </a>
            <span class="text-muted" style="margin-left:10px; font-size:12px;">처리중인 전체 건을 출고요청서 양식으로 다운로드합니다.</span>
        </td>
    </tr>
    </tbody>
    </table>
</form>

<form method="post" name="shipment_form" class="form-inline" enctype="multipart/form-data" action="cs_photo_event_shipment_upload.php">
<input type="hidden" name="return_url" value="<?=$_SERVER['PHP_SELF']?>">
<table class="table table-bordered">
<colgroup><col width="15%"><col width="*"></colgroup>
<tbody>
<tr>
    <th>택배 접수완료 처리</th>
    <td>
        <input type="file" name="userfile" id="userfile_shipment" accept=".xls,.xlsx">
        <button type="submit" class="btn btn-warning btn-sm" <?if(($PERMISSION & PERMISSION_CS)!=PERMISSION_CS){echo 'disabled';}?>>엑셀 업로드 → 출고완료</button>
        <span class="text-muted" style="margin-left:10px; font-size:12px;">CJ택배 발송고객_일별_배달상세 엑셀을 업로드하면 송장번호 등록 및 출고완료 처리됩니다.</span>
    </td>
</tr>
</tbody>
</table>
</form>
<?}?>

<div class="table-responsive">
<table class="table table-bordered table-hover">
<colgroup>
<?if($menu==S_PHOTO_EVENT_LIST){?><col width="3%"><?}?>
<col width="4%">
<col width="8%">
<col width="14%">
<col width="10%">
<col width="10%">
<col width="*">
<col width="10%">
<col width="8%">
<col width="6%">
</colgroup>

<thead>
<?if($menu==S_PHOTO_EVENT_LIST){?>
<tr>
    <th colspan="2" class="form-inline">
        <a href="javascript:;" class="btn btn-default btn-xs ajax-checkbox" data-dbname="<?=$table?>" data-name="delete" data-val=""
           <?if(($PERMISSION & PERMISSION_CS)!=PERMISSION_CS){echo 'disabled';}?>>삭제하기</a>
    </th>
    <th>
        <a href="javascript:;" class="btn btn-default btn-xs ajax-checkbox-local" data-dbname="<?=$table?>" data-name="status" data-val=""
           <?if(($PERMISSION & PERMISSION_CS)!=PERMISSION_CS){echo 'disabled';}?>>완료처리하기</a>
    </th>
    <td colspan="7"></td>
</tr>
<?}?>
<tr>
    <?if($menu==S_PHOTO_EVENT_LIST){?><th><input type="checkbox" id="allCheck"></th><?}?>
    <th>NO</th>
    <th>등록일</th>
    <th>품목명</th>
    <th>쇼핑몰</th>
    <th>고객명</th>
    <th>주문번호</th>
    <th>송장번호</th>
    <th>담당자</th>
    <th>상세보기</th>
</tr>
</thead>
<tbody>
<?
while ($row = mysqli_fetch_array($result)) {
    $reg_date = date('Y-m-d', strtotime($row['reg_datetime']));
?>
    <tr>
        <?if($menu==S_PHOTO_EVENT_LIST){?>
        <td class="text-center"><input type="checkbox" name="check_list" value="<?=$row['idx']?>"></td>
        <?}?>
        <td class="text-center"><?=$listNo?></td>
        <td class="text-center"><?=$reg_date?></td>
        <td class="text-center"><?=htmlspecialchars($row['product_gift'])?></td>
        <td class="text-center"><?=htmlspecialchars($row['shop_name'])?></td>
        <td class="text-center" style="color:blue;"><?=htmlspecialchars($row['customer_name'])?></td>
        <td class="text-center"><?=htmlspecialchars($row['order_num'])?></td>
        <td class="text-center">
            <a href="<?if(strlen($row['delivery_num'])==12){echo constant('TRACKING_CJ').$row['delivery_num'];}?>" target="_blank">
                <?=htmlspecialchars($row['delivery_num'])?>
            </a>
        </td>
        <td class="text-center"><?=htmlspecialchars($row['pic_name'])?></td>
        <?if($menu==S_PHOTO_EVENT_LIST){?>
        <td class="text-center"><a href="cs_photo_event_edit.php?idx=<?=$row['idx']."&return_url=".urlencode($return_url)?>" class="btn btn-primary btn-sm">보기</a></td>
        <?}else{?>
        <td class="text-center"><a href="cs_photo_event_edit.php?shipment=1&idx=<?=$row['idx']."&return_url=".urlencode($return_url)?>" class="btn btn-primary btn-sm">보기</a></td>
        <?}?>
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
    if ($totalList > $listScale) {
        if ($startPage + 1 > $listScale * $pageScale) {
            $prePage = $startPage - $listScale * $pageScale;
            echo "<li><a href='$_SERVER[PHP_SELF]?$param_url&startPage=$prePage'><span aria-hidden='true'>&laquo;</span></a></li>";
        }
        for ($j = 0; $j < $pageScale; $j++) {
            $nextPage = ($totalPage * $pageScale + $j) * $listScale;
            $pageNum  = $totalPage * $pageScale + $j + 1;
            if ($nextPage < $totalList) {
                if ($nextPage != $startPage) {
                    echo "<li><a href='$_SERVER[PHP_SELF]?$param_url&startPage=$nextPage'>$pageNum</a></li>";
                } else {
                    echo "<li class='active'><a href='javascript:;'>$pageNum</a></li>";
                }
            }
        }
        if ($totalList > (($totalPage + 1) * $listScale * $pageScale)) {
            $nNextPage = ($totalPage + 1) * $listScale * $pageScale;
            echo "<li><a href='$_SERVER[PHP_SELF]?$param_url&startPage=$nNextPage'><span aria-hidden='true'>&raquo;</span></a></li>";
        }
    }
    if ($totalList <= $listScale) {
        echo "<li class='active'><a href='javascript:;'>1</a></li>";
    }
?>
</ul>
</div>

<script>
$("#allCheck").on("change", function() {
    $("input[name='check_list']").prop("checked", $(this).prop("checked"));
});

$(".ajax-checkbox-local").on("click", function() {
    var checkboxVal = [];
    $("input[name='check_list']:checked").each(function() { checkboxVal.push($(this).val()); });
    var postData = { "dbname": $(this).attr("data-dbname"), "idx": checkboxVal, "name": "status", "val": "" };
    if ($("input:checkbox[name='check_list']").is(":checked")) {
        if (confirm("[완료처리] 하시겠습니까?")) {
            $.ajax({ url: "cs_photo_event_ajax_checkbox.php", type: "post", data: postData, success: function() { location.reload(); } });
        }
    } else {
        alert("[완료처리] 항목을 선택하여 주세요.");
    }
});

$(".ajax-checkbox").on("click", function() {
    var checkboxVal = [];
    $("input[name='check_list']:checked").each(function() { checkboxVal.push($(this).val()); });
    var postData = { "dbname": $(this).attr("data-dbname"), "idx": checkboxVal, "name": "delete" };
    if ($("input:checkbox[name='check_list']").is(":checked")) {
        if (confirm("[삭제] 선택한 항목을 삭제하시겠습니까?")) {
            $.ajax({ url: "cs_photo_event_ajax_checkbox.php", type: "post", data: postData, success: function() { location.reload(); } });
        }
    } else {
        alert("[삭제] 항목을 선택하여 주세요.");
    }
});
</script>

<? include('../footer.php'); ?>
