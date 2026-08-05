<?
include("../def_inc.php");

if (isset($_GET['shipment'])) {
    $menu = S_SPONSORED_SHIPMENT;
} elseif (isset($_GET['approve'])) {
    $menu = S_SPONSORED_APPROVE;
} else {
    $menu = S_SPONSORED_LIST;
}
$mod = M_CANCELLATION;
include("../header.php");

$table     = "cs_sponsored_orders";
$listScale = 50;
$pageScale = 10;

$startPage = isset($_GET['startPage']) ? (int)$_GET['startPage'] : 0;

$search_item  = isset($_GET['search_item'])  ? $_GET['search_item']  : "";
$search_order = isset($_GET['search_order']) ? $_GET['search_order'] : "";
$date_from    = isset($_GET['date_from'])    ? $_GET['date_from']    : "";
$date_to      = isset($_GET['date_to'])      ? $_GET['date_to']      : "";

// 상태값 결정
if ($menu == S_SPONSORED_SHIPMENT) {
    $status = 2;
} elseif ($menu == S_SPONSORED_APPROVE) {
    $status = 1;
} else {
    $status = 0;
}

// 출고완료만 날짜 필터 적용
if ($menu == S_SPONSORED_SHIPMENT) {
    if ($date_to == "")   $date_to   = isset($_POST['date_to'])   ? $_POST['date_to']   : date("Y-m-d");
    if ($date_from == "") $date_from = isset($_POST['date_from']) ? $_POST['date_from'] : date("Y-m-d", strtotime($date_to." -1 month"));
    $date_to2 = date("Y-m-d", strtotime($date_to." +1 day"));
} else {
    $date_from = "2020-01-01";
    $date_to   = date("Y-m-d");
    $date_to2  = date("Y-m-d", strtotime($date_to." +1 day"));
}

if ($search_item == "")  $search_item  = isset($_POST['search_item'])  ? $_POST['search_item']  : "";
if ($search_order == "") $search_order = isset($_POST['search_order']) ? $_POST['search_order'] : "";

$totalPage = floor($startPage / ($listScale * $pageScale));

$query       = "select * from $table where reg_datetime between date('$date_from') and date('$date_to2') and status=$status ";
$query_where = "where reg_datetime between date('$date_from') and date('$date_to2') and status=$status ";

if ($search_order) {
    $sq = mysqli_real_escape_string($db->db_conn, $search_order);
    if ($search_item) {
        $query       .= "and $search_item like '%$sq%' ";
        $query_where .= "and $search_item like '%$sq%' ";
    } else {
        $q = "and (product_name like '%$sq%' or customer_name like '%$sq%' or influencer_name like '%$sq%' or pic_name like '%$sq%') ";
        $query       .= $q;
        $query_where .= $q;
    }
}

$result    = mysqli_query($db->db_conn, $query);
$totalList = mysqli_num_rows($result);

$query .= " order by idx desc LIMIT $startPage, $listScale";
$result = mysqli_query($db->db_conn, $query);

if ($startPage) { $listNo = $totalList - $startPage; } else { $listNo = $totalList; }

if ($menu == S_SPONSORED_SHIPMENT) {
    $param_url = "shipment=1&date_from=$date_from&date_to=$date_to&search_item=$search_item&search_order=$search_order";
} elseif ($menu == S_SPONSORED_APPROVE) {
    $param_url = "approve=1&search_item=$search_item&search_order=$search_order";
} else {
    $param_url = "search_item=$search_item&search_order=$search_order";
}

$return_url = $_SERVER['REQUEST_URI'];

// 팀장 여부
$is_manager = ($PERMISSION == PERMISSION_ALL);
$can_write  = (($PERMISSION & PERMISSION_CS) == PERMISSION_CS);
?>

<h4 class="page-header"><?
if ($menu == S_SPONSORED_SHIPMENT) echo "협찬/샘플 출고 완료";
elseif ($menu == S_SPONSORED_APPROVE) echo "협찬/샘플 승인 완료";
else echo "협찬/샘플 요청 리스트";
?></h4>

<?/* ── 출고완료: 날짜 검색 폼 ── */?>
<?if ($menu == S_SPONSORED_SHIPMENT) {?>
<form method="post" name="search_form" class="form-inline" action="<?=$_SERVER['PHP_SELF']?>?shipment=1">
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
            <select name="search_item" class="form-control input-sm" style="width:170px;">
                <option value="">통합검색</option>
                <option value="product_name"    <?if($search_item=="product_name"){?>selected<?}?>>제품 모델명</option>
                <option value="influencer_name" <?if($search_item=="influencer_name"){?>selected<?}?>>인플루언서명</option>
                <option value="customer_name"   <?if($search_item=="customer_name"){?>selected<?}?>>고객명</option>
                <option value="pic_name"        <?if($search_item=="pic_name"){?>selected<?}?>>담당자</option>
            </select>
            <input type="text" name="search_order" class="form-control input-sm" value="<?=$search_order?>" style="width:170px;">
        </td>
    </tr>
    <tr>
        <td colspan="2" class="text-center">
            <button type="submit" class="btn btn-primary btn-sm">검색</button>
            <a href="<?=$_SERVER['PHP_SELF']?>?shipment=1" class="btn btn-default btn-sm">초기화</a>
            &nbsp;&nbsp;
            <a href="cs_sponsored_excel_download.php?shipment=1&<?=$param_url?>" class="btn btn-success btn-sm">엑셀 다운로드</a>
        </td>
    </tr>
</tbody>
</table>
</form>
<?}?>

<?/* ── 요청 리스트: 업로드 폼 ── */?>
<?if ($menu == S_SPONSORED_LIST) {?>
<form method="post" name="upload_form" class="form-inline" enctype="multipart/form-data" action="cs_sponsored_excel_upload.php">
<table class="table table-bordered">
<colgroup><col width="15%"><col width="*"></colgroup>
<tbody>
<tr>
    <th>요청 리스트 업로드</th>
    <td>
        <input type="hidden" name="upload_type" value="file">
        <input type="file" name="userfile" id="userfile" accept=".xls,.xlsx">
        <input type="hidden" name="return_url" value="<?=$_SERVER['PHP_SELF']?>">
        <button type="submit" class="btn btn-info btn-sm" <?if(!$can_write){echo 'disabled';}?>>xlsx 파일 업로드</button>
    </td>
</tr>
</tbody>
</table>
</form>
<?}?>

<?/* ── 승인완료: 출고리스트 다운로드 + CJ 엑셀 업로드 ── */?>
<?if ($menu == S_SPONSORED_APPROVE) {?>
<table class="table table-bordered">
<colgroup><col width="15%"><col width="*"></colgroup>
<tbody>
<tr>
    <th>출고리스트 다운로드</th>
    <td>
        <a href="cs_sponsored_excel_download.php?approve=1" class="btn btn-success btn-sm">출고요청서 엑셀 다운로드</a>
        <span class="text-muted" style="margin-left:10px; font-size:12px;">승인완료된 전체 건을 협찬출고요청서 양식으로 다운로드합니다.</span>
    </td>
</tr>
<tr>
    <th>택배 접수완료 처리</th>
    <td>
        <form method="post" name="shipment_form" class="form-inline" enctype="multipart/form-data" action="cs_sponsored_shipment_upload.php">
        <input type="hidden" name="return_url" value="<?=$_SERVER['PHP_SELF']?>?approve=1">
        <input type="file" name="userfile" id="userfile_shipment" accept=".xls,.xlsx">
        <button type="submit" class="btn btn-warning btn-sm" <?if(!$can_write){echo 'disabled';}?>>엑셀 업로드 → 출고완료</button>
        <span class="text-muted" style="margin-left:10px; font-size:12px;">CJ택배 발송고객_일별_배달상세 엑셀을 업로드하면 송장번호 등록 및 출고완료 처리됩니다.</span>
        </form>
    </td>
</tr>
</tbody>
</table>
<?}?>

<?/* ── 검색 폼 (요청/승인완료) ── */?>
<?if ($menu != S_SPONSORED_SHIPMENT) {?>
<form method="get" name="search_form" class="form-inline" action="<?=$_SERVER['PHP_SELF']?>">
<?if ($menu == S_SPONSORED_APPROVE) {?><input type="hidden" name="approve" value="1"><?}?>
<div style="margin-bottom:8px;">
    <select name="search_item" class="form-control input-sm" style="width:150px;">
        <option value="">통합검색</option>
        <option value="product_name"    <?if($search_item=="product_name"){?>selected<?}?>>제품 모델명</option>
        <option value="influencer_name" <?if($search_item=="influencer_name"){?>selected<?}?>>인플루언서명</option>
        <option value="customer_name"   <?if($search_item=="customer_name"){?>selected<?}?>>고객명</option>
        <option value="pic_name"        <?if($search_item=="pic_name"){?>selected<?}?>>담당자</option>
    </select>
    <input type="text" name="search_order" class="form-control input-sm" value="<?=$search_order?>" style="width:180px;" placeholder="검색어">
    <button type="submit" class="btn btn-primary btn-sm">검색</button>
    <a href="<?=$_SERVER['PHP_SELF']?><?if($menu==S_SPONSORED_APPROVE){?>?approve=1<?}?>" class="btn btn-default btn-sm">초기화</a>
</div>
</form>
<?}?>

<div class="table-responsive">
<table class="table table-bordered table-hover">
<colgroup>
<?if ($menu == S_SPONSORED_LIST) {?><col width="3%"><?}?>
<col width="4%">
<col width="8%">
<col width="12%">
<col width="12%">
<col width="14%">
<col width="10%">
<col width="*">
<col width="10%">
<?if ($menu == S_SPONSORED_APPROVE) {?><col width="10%"><col width="8%"><?}?>
<col width="6%">
</colgroup>

<thead>
<?if ($menu == S_SPONSORED_LIST) {?>
<tr>
    <th colspan="2" class="form-inline">
        <a href="javascript:;" class="btn btn-danger btn-xs ajax-checkbox" data-name="delete"
           <?if(!$can_write){echo 'disabled';}?>>삭제</a>
    </th>
    <th>
        <?if ($is_manager) {?>
        <a href="javascript:;" class="btn btn-primary btn-xs ajax-approve" <?if(!$is_manager){echo 'disabled';}?>>
            ✔ 승인하기
        </a>
        <?} else {?>
        <span class="text-muted" style="font-size:12px;">팀장 승인 대기</span>
        <?}?>
    </th>
    <td colspan="6"></td>
</tr>
<?}?>
<tr>
    <?if ($menu == S_SPONSORED_LIST) {?><th><input type="checkbox" id="allCheck"></th><?}?>
    <th>NO</th>
    <th>등록일</th>
    <th>모델명</th>
    <th>사유</th>
    <th>인플루언서명</th>
    <th>고객명</th>
    <th>송장번호</th>
    <th>담당자</th>
    <?if ($menu == S_SPONSORED_APPROVE) {?>
    <th>승인일시</th>
    <th>승인자</th>
    <?}?>
    <th>상세보기</th>
</tr>
</thead>
<tbody>
<?
while ($row = mysqli_fetch_array($result)) {
    $reg_date = date('Y-m-d', strtotime($row['reg_datetime']));
?>
    <tr>
        <?if ($menu == S_SPONSORED_LIST) {?>
        <td class="text-center"><input type="checkbox" name="check_list" value="<?=$row['idx']?>"></td>
        <?}?>
        <td class="text-center"><?=$listNo?></td>
        <td class="text-center"><?=$reg_date?></td>
        <td class="text-center"><?=htmlspecialchars($row['product_name'])?></td>
        <td class="text-center"><?=htmlspecialchars($row['reason'])?></td>
        <td class="text-center" style="color:blue;"><?=htmlspecialchars($row['influencer_name'])?></td>
        <td class="text-center"><?=htmlspecialchars($row['customer_name'])?></td>
        <td class="text-center">
            <a href="<?if(strlen($row['delivery_num'])==12){echo constant('TRACKING_CJ').$row['delivery_num'];}?>" target="_blank">
                <?=htmlspecialchars($row['delivery_num'])?>
            </a>
        </td>
        <td class="text-center"><?=htmlspecialchars($row['pic_name'])?></td>
        <?if ($menu == S_SPONSORED_APPROVE) {?>
        <td class="text-center" style="font-size:12px;"><?=htmlspecialchars($row['approved_at'])?></td>
        <td class="text-center"><?=htmlspecialchars($row['approved_by'])?></td>
        <?}?>
        <td class="text-center">
            <a href="cs_sponsored_edit.php?idx=<?=$row['idx']?>&return_url=<?=urlencode($return_url)?><?if($menu==S_SPONSORED_SHIPMENT){?>&shipment=1<?}?>" class="btn btn-default btn-xs">보기</a>
        </td>
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

// 승인하기 (팀장 전용)
$(".ajax-approve").on("click", function() {
    var idxs = [];
    $("input[name='check_list']:checked").each(function() {
        idxs.push($(this).val());
    });
    if (idxs.length === 0) {
        alert("승인할 항목을 선택하여 주세요.");
        return;
    }
    if (confirm("[승인] 선택한 " + idxs.length + "건을 승인완료 처리하시겠습니까?")) {
        $.ajax({
            url: "cs_sponsored_ajax_checkbox.php",
            type: "post",
            data: { name: "approve", idx: idxs },
            success: function(res) {
                try {
                    var r = typeof res === 'string' ? JSON.parse(res) : res;
                    if (r.ok) {
                        location.reload();
                    } else {
                        alert("승인 실패: " + (r.msg || "알 수 없는 오류"));
                    }
                } catch(e) {
                    alert("응답 오류: " + res);
                }
            },
            error: function(xhr) {
                alert("요청 실패 (HTTP " + xhr.status + ")");
            }
        });
    }
});

// 삭제
$(".ajax-checkbox").on("click", function() {
    var idxs = [];
    $("input[name='check_list']:checked").each(function() {
        idxs.push($(this).val());
    });
    if (idxs.length === 0) {
        alert("삭제할 항목을 선택하여 주세요.");
        return;
    }
    if (confirm("[삭제] 선택한 " + idxs.length + "건을 삭제하시겠습니까?")) {
        $.ajax({
            url: "cs_sponsored_ajax_checkbox.php",
            type: "post",
            data: { dbname: "cs_sponsored_orders", name: "delete", idx: idxs },
            success: function() { location.reload(); }
        });
    }
});
</script>

<? include('../footer.php'); ?>
