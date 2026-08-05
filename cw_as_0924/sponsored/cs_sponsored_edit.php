<?
include("../def_inc.php");
$mod  = M_CANCELLATION;
$menu = isset($_GET['idx']) ? S_SPONSORED_LIST : S_SPONSORED_LIST;
$menu = isset($_GET['shipment']) ? S_SPONSORED_SHIPMENT : $menu;
include("../header.php");

$table = "cs_sponsored_orders";
$idx   = isset($_GET['idx']) ? (int)$_GET['idx'] : 0;
$row   = $db->object($table, "where idx='$idx'");

$mode = ($row) ? "edit" : "new";
$return_url = isset($_GET['return_url']) ? $_GET['return_url'] : $_SERVER['REQUEST_URI'];
?>

<h4 class="page-header"><?if($idx>0){echo "협찬/샘플 출고 상세 정보 ($idx)";}else{echo "협찬/샘플 출고 등록";}?></h4>

<form method="post" action="cs_sponsored_edit_ok.php" name="tx_editor_form" enctype="multipart/form-data">
<input type="hidden" name="mode" value="<?=$mode?>">
<input type="hidden" name="idx"  value="<?=$idx?>">
<input type="hidden" name="return_url" value="<?=$return_url?>">

<table class="table table-bordered">
<colgroup><col width="15%"><col width="*"></colgroup>
<tbody>
<tr>
    <th>요청날짜</th>
    <td>
        <div class="input-group datetime" style="width:170px;">
            <input type="text" name="request_date" class="form-control input-sm text-center" placeholder="YYYY-MM-DD"
                   value="<?=htmlspecialchars($row->request_date)?>" autocomplete="off">
            <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
        </div>
    </td>
</tr>
<tr>
    <th>제품명*</th>
    <td>
        <input type="text" class="form-control input-sm" name="product_name" style="width:60%;"
               placeholder="제품명" autocomplete="off" value="<?=htmlspecialchars($row->product_name)?>">
    </td>
</tr>
<tr>
    <th>악세서리추가</th>
    <td>
        <input type="text" class="form-control input-sm" name="accessory_name" style="width:80%;"
               placeholder="악세서리 추가 내용" autocomplete="off" value="<?=htmlspecialchars($row->accessory_name)?>">
    </td>
</tr>
<tr>
    <th>수량</th>
    <td>
        <input type="number" class="form-control input-sm" name="quantity" style="width:100px;"
               min="1" value="<?=$row->quantity ? $row->quantity : 1?>">
    </td>
</tr>
<tr>
    <th>사유</th>
    <td>
        <textarea class="form-control input-sm" rows="2" name="reason" placeholder="사유 입력" autocomplete="off"><?=htmlspecialchars($row->reason)?></textarea>
    </td>
</tr>
<tr>
    <th>인플루언서명</th>
    <td>
        <input type="text" class="form-control input-sm" name="influencer_name" style="width:50%;"
               placeholder="인플루언서명" autocomplete="off" value="<?=htmlspecialchars($row->influencer_name)?>">
    </td>
</tr>
<tr>
    <th>업체명</th>
    <td>
        <input type="text" class="form-control input-sm" name="company_name" style="width:50%;"
               placeholder="업체명" autocomplete="off" value="<?=htmlspecialchars($row->company_name)?>">
    </td>
</tr>
<?if($mode != "new"){?>
<tr>
    <th>처리상태*</th>
    <td>
        <label class="radio-inline"><input type="radio" name="status" value="0" <?if($row->status==0){echo "checked";}?>> 처리중</label>&nbsp;&nbsp;&nbsp;&nbsp;
        <label class="radio-inline"><input type="radio" name="status" value="1" <?if($row->status==1){echo "checked";}?>> 처리완료</label>
    </td>
</tr>
<?}?>
<tr>
    <th>메모</th>
    <td>
        <textarea class="form-control input-sm" rows="2" name="pic_memo" placeholder="메모" autocomplete="off"><?=htmlspecialchars($row->pic_memo)?></textarea>
    </td>
</tr>
<tr>
    <th>담당자*</th>
    <td>
        <input type="hidden" name="pic_name" value="<?if($row->pic_name==""){echo $ADMIN_NAME;}else{echo $row->pic_name;}?>">
        <?if($row->pic_name==""){echo $ADMIN_NAME;}else{echo $row->pic_name;}?>
    </td>
</tr>
</tbody>
</table>

<table class="table table-bordered">
<colgroup><col width="15%"><col width="*"></colgroup>
<tbody>
<tr>
    <th>수령자명*</th>
    <td>
        <input type="text" class="form-control input-sm" name="customer_name" style="width:50%;"
               placeholder="수령자명" autocomplete="off" value="<?=htmlspecialchars($row->customer_name)?>">
    </td>
</tr>
<tr>
    <th>핸드폰*</th>
    <td>
        <input type="text" class="form-control input-sm" name="customer_phone" style="width:50%;"
               placeholder="010-0000-0000" autocomplete="off" value="<?=htmlspecialchars($row->customer_phone)?>">
    </td>
</tr>
<tr>
    <th>일반전화</th>
    <td>
        <input type="text" class="form-control input-sm" name="customer_phone2" style="width:50%;"
               placeholder="일반전화" autocomplete="off" value="<?=htmlspecialchars($row->customer_phone2)?>">
    </td>
</tr>
<tr>
    <th>주소*</th>
    <td>
        <textarea class="form-control input-sm" rows="2" name="customer_addr" placeholder="주소 입력" autocomplete="off"><?=htmlspecialchars($row->customer_addr)?></textarea>
    </td>
</tr>
<tr>
    <th>배송메시지</th>
    <td>
        <input type="text" class="form-control input-sm" name="delivery_memo" style="width:80%;"
               placeholder="배송 메시지" autocomplete="off" value="<?=htmlspecialchars($row->delivery_memo)?>">
    </td>
</tr>
<?if($idx>0){?>
<tr>
    <th>송장번호</th>
    <td>
        <input type="text" class="form-control input-sm" name="delivery_num" style="width:50%;"
               placeholder="송장번호(숫자만 입력)" autocomplete="off" value="<?=htmlspecialchars($row->delivery_num)?>">
    </td>
</tr>
<?}else{?>
<tr style="display:none;">
    <th>송장번호</th>
    <td><input type="text" name="delivery_num" value=""></td>
</tr>
<?}?>
</tbody>
</table>

<table class="table">
<tr>
    <td class="text-center">
        <a href="javascript:;" class="btn btn-primary" onclick="sendit();"
           <?if(($PERMISSION & PERMISSION_CS)!=PERMISSION_CS){echo 'disabled';}?>>등록</a>
        <?if($idx>0){?>
        <a href="<?=$return_url?>" class="btn btn-default">목록</a>
        <a href="javascript:;" class="btn btn-danger" style="margin-left:40px;" onclick="deleteit();"
           <?if(($PERMISSION & PERMISSION_CS)!=PERMISSION_CS){echo 'disabled';}?>>삭제</a>
        <?}?>
    </td>
</tr>
</table>
</form>

<script type="text/javascript">
function sendit() {
    var form = document.tx_editor_form;
    if (form.product_name.value == "") {
        alert("제품명을 입력해 주세요."); form.product_name.focus(); return;
    }
    if (form.customer_name.value == "") {
        alert("수령자명을 입력해 주세요."); form.customer_name.focus(); return;
    }
    if (form.customer_phone.value == "") {
        alert("핸드폰 번호를 입력해 주세요."); form.customer_phone.focus(); return;
    }
    if (form.customer_addr.value == "") {
        alert("주소를 입력해 주세요."); form.customer_addr.focus(); return;
    }
    form.submit();
}

function deleteit() {
    if (confirm("삭제할까요?")) {
        if (confirm("삭제된 데이터는 복구할 수 없습니다. 삭제할까요?")) {
            var form = document.tx_editor_form;
            form.mode.value = 'del';
            form.action = "cs_sponsored_edit_ok.php";
            form.submit();
        }
    }
}
</script>

<? include('../footer.php'); ?>
