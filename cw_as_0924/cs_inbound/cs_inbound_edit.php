<?
include("../def_inc.php");
$mod	= M_INBOUND;
$menu	= isset($_GET['idx']) ? S_INBOUND_LIST : S_INBOUND_EDIT;
$menu	= isset($_GET['callback']) ? S_INBOUND_CALLBACK : $menu;
include("../header.php");
include("cs_inbound_def.php");
$idx = isset($_GET['idx'])?$_GET['idx']:0;

include('../product_category_inc.php');

$table = "cs_inbound_call";

$row = $db->object($table,"where idx='$idx'");

/////////////////////TEST
if (0) {
	class objTableCsInboundCall {
		var $idx, $reg_datetime, $product_name, $inquiry_type, $black_consumer, $black_consumer_desc, $pic_name, $pic_memo;
	}		
	$row = new objTableCsInboundCall;

	$row->idx = 1;
	$row->reg_datetime = "2020-11-22 17:33:44";
	$row->product_name = "CR10";
	$row->inquiry_type = 2;
	$row->black_consumer = 0;
	$row->black_consumer_desc = "개진상";
	$row->pic_name = "서강준";
	$row->pic_memo = "";
}
////////////////////

$isCallback = isset($_GET['callback']) ? 1 : 0;

$product_name = $row->product_name; 
$sel_category = 0;
$mode = "new";

if(!$row) //new
{ 
	$mode = "new";
}
else //edit
{
	$mode = "edit";
}

$return_url = isset($_GET['return_url']) ? $_GET['return_url'] : $_SERVER['REQUEST_URI'];
//echo $return_url;
//echo $ADMIN_NAME;
//echo $row->admin_result;

?>

	<h4 class="page-header"><?if($idx>0) {echo "CALL 상세 정보"."($idx)";} else {echo "신규 CALL 등록";} ?></h4>

	<form method="post" action="cs_inbound_edit_ok.php" name="tx_editor_form" enctype="multipart/form-data" >
	
	<input type="hidden" name="mode" value="<?echo $mode;?>">
	<input type="hidden" name="idx" value="<?echo $idx;?>">
	<input type="hidden" name="return_url" value="<?echo $return_url;?>">

	<table class="table table-bordered" style="<?if(!$isCallback) { echo "border:2px solid #999"; }?>">
	<colgroup>
	<col width="15%">
	<col width="*">
	</colgroup>
	<tbody>
	<tr>
		<th>날짜 / 시간*</th>
		<td>
		<? 
		if ($idx>0) {
			echo date("Y년 m월 d일    g:i A", strtotime($row->reg_datetime));
			//date("Y년 m월 d일"). "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" .date("g:i A");
		}
		else {
			echo date("Y년 m월 d일");
		}
			
			
		?>
		<!--div class="form-inline" >
			<div class="input-group datetime" style="width:180px;">
				<input type="text" name="date" class="form-control input-sm text-center" placeholder="YYYY-MM-DD" value="<?if(!$row){echo date("Y-m-d");}else{echo date("Y-m-d", strtotime($row->reg_datetime));}?>" autocomplete="off" />
				<span class="input-group-addon" >
					<span class="glyphicon glyphicon-calendar" ></span>
				</span>
			</div>&nbsp;&nbsp;&nbsp;

			<div class="input-group time" style="width:180px;" id="timepicker" >
				<input type="text" name="time" class="form-control input-sm text-center" placeholder="HH:MM" value="<?if(!$row){echo date("g:i A");}else{echo date("g:i A", strtotime($row->reg_datetime));}?>" autocomplete="off" />
				<span class="input-group-addon">
					<span class="glyphicon glyphicon-time"></span>
				</span>
			</div>&nbsp;&nbsp;&nbsp;

<script type="text/javascript">
$(function () {
	$('#timepicker').datetimepicker({
		format: 'LT', pickDate: false
	});
});
</script>

		</div-->
		</td>
	</tr>
	<tr>
		<th>제품 모델명*</th>
		<td>
			<div class="form-inline" >
				<select id="product_category" name="product_category" style="width:220px;" class="form-control input-sm" onchange="changeProductCategory(this)" <?if($isCallback){echo "readonly";}?>> 
					<?for($i=0;$i<count($arr_category_name);$i++) { ?>
						<option value="<?echo $arr_category_name[$i]?>" <?if(in_array($product_name, $arr_as_model[$i])){ $sel_category=$i;?>selected<?}?> ><?echo $arr_category_name[$i]?></option>
					<? } ?>
				</select>

				<select id="product_name" name="product_name" style="width:240px;" class="form-control input-sm" onchange="changeProductName(this)" <?if($isCallback){echo "readonly";}?> >
					<option value="">-- 모델명 입력 --</option>
					<?for($i=0;$i<count($arr_as_model[$sel_category]);$i++) { ?>
						<option value="<?echo $arr_as_model[$sel_category][$i]?>" <?if($product_name==$arr_as_model[$sel_category][$i]){?>selected<?}?>><?echo $arr_as_model[$sel_category][$i]?></option>
					<? } ?>
				</select>

				<!--div class="checkbox">&nbsp;&nbsp;&nbsp;
				<label><input type="checkbox" value=<??> onclick="" >&nbsp;기타 문의</label>
				</div-->

			</div>
		</td>
	</tr>
	<tr style="height:50px;">
		<th>문의 유형*</th>
		<td>
		<? 
			for($i=1;$i<count($arr_inbound_call_type);$i++)
			{
		?>
				<label class="radio-inline">
					<input type="radio" name="inquiry_type" value="<?echo $i;?>" <? if($row->inquiry_type==$i) {echo 'checked';} ?> onclick="<?if($isCallback){echo "return(false);";}?>" ><?echo $arr_inbound_call_type[$i]?>
				</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<?
			}
		?>
		</td>
	</tr>
	<tr>
		<th class="text-danger" >
			<div class="">
				<label style="color:red;"><input type="hidden" name="black_consumer" value=<? if($row->black_consumer) { echo '1'; } else { echo '0'; } ?> >불량고객 (강성고객)
			</div>
		</th>
		<td>
			<textarea class="form-control input-sm" rows="3" name="black_consumer_desc" placeholder="불량고객 문의 내용 입력" autocomplete="off" <?if($isCallback){echo "readonly";}?> ><?=$row->black_consumer_desc?></textarea>
		</td>
	</tr>
	<tr>
		<th>담당자*</th>
		<td>
			<input type="hidden" name="pic_name" value="<? if($row->pic_name=="") {echo $ADMIN_NAME;} else {echo $row->pic_name;} ?>">
			<? if($row->pic_name=="") {echo $ADMIN_NAME;} else {echo $row->pic_name;} ?>
		</td>
	</tr>
	</tbody>
	</table>

	<span class="text-primary h5" style="font-weight:bold;">[ 관리자 전화 요청 ]</span>
	<table class="table table-bordered" style="<?if($isCallback) { echo "border:2px solid #999"; }?>">
	<colgroup>
	<col width="15%">
	<col width="*">
	</colgroup>
	<tbody>
	<tr>
		<th class="<?if(!$isCallback){echo "text-primary";}?>">요청사항</th>
		<td>
			<div class="form-inline" >
			고객명 <input type="text" class="form-control input-sm" name="customer_name" style="width:240px;" placeholder="고객명 입력" autocomplete="off" <?if($isCallback){echo "readonly";}?> value=<?=$row->customer_name?> >
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			연락처 <input type="text" class="form-control input-sm" name="customer_phone" style="width:240px;" placeholder="고객 연락처 (010-0000-0000)" autocomplete="off" <?if($isCallback){echo "readonly";}?> value=<?=$row->customer_phone?> >
			</div><br>

			<textarea class="form-control input-sm" rows="5" name="pic_memo" placeholder="관리자에게 요청할 사항을 입력" autocomplete="off" <?if($isCallback){echo "readonly";}?> ><?=$row->pic_memo?></textarea>
		</td>
	</tr>
<? if($isCallback) { ?>
	<tr>
		<th class="<?if($isCallback){echo "text-primary";}?>" >처리 결과</th>
		<td>
			<label class="radio-inline">
			<input type="radio" name="admin_result" value="0" <? if($row->pic_memo!="" && $row->admin_result==0) {echo 'checked';} ?> >처리중
			</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<label class="radio-inline">
			<input type="radio" name="admin_result" value="1" <? if($row->pic_memo!="" && $row->admin_result==1) {echo 'checked';} ?> >처리완료
			</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

		</td> 
	</tr>
	<tr>
		<th class="<?if($isCallback){echo "text-primary";}?>" >처리 내역</th>
		<td>
			<textarea class="form-control input-sm" rows="2" name="admin_desc" placeholder="처리내역 입력" autocomplete="off" ><?=$row->admin_desc?></textarea>
		</td>
	</tr>
	<tr>
		<th class="<?if($isCallback){echo "text-primary";}?>" >처리자명</th>
		<td>
		<?	
		if ($idx>0) {
		?>	
			<input type="hidden" name="admin_name" value="<? if($row->admin_name=="") {echo $ADMIN_NAME;} else {echo $row->admin_name;} ?>">
			<? if($row->admin_name=="") {echo $ADMIN_NAME;} else {echo $row->admin_name;} ?>
		<?}?>			
		</td>
	</tr>
<?}?>
	</tbody>
	</table>


	
	<table class="table">
		<tr>
			<td class="text-center">
				<a href="javascript:;" class="btn btn-primary" onClick="sendit();" <?if(($PERMISSION & PERMISSION_CS)!=PERMISSION_CS) { echo 'disabled';}?> >등록</a>

				

<? if ($idx>0) { ?>
				<a href="<?=$return_url?>" class="btn btn-default" >목록</a>
				<?if(!$isCallback) {?>
				<a href="javascript:;" class="btn btn-danger" style="margin-left:40px;" onclick="deleteit()" <?if(($PERMISSION & PERMISSION_CS)!=PERMISSION_CS) { echo 'disabled';}?> >삭제</a> 
				<?} else {?>
				<a href="javascript:;" class="btn btn-danger" style="margin-left:40px;" onclick="deletecallback()" <?if(($PERMISSION & PERMISSION_CS)!=PERMISSION_CS) { echo 'disabled';}?> >삭제</a> 
				<?} ?>
<? } ?>				
			</td>
		</tr>
	</table>

</form>


<script type="text/javascript">

function sendit() {

	var form=document.tx_editor_form;

	if(form.product_name.value=="" || form.product_name.value=="-- 모델명 입력 --") {
		alert("제품 모델명을 입력해 주세요.");
		form.product_name.focus();
	} 
	else if(form.inquiry_type.value=="") {
		alert("문의 유형을 선택해 주세요.");
		form.inquiry_type.focus();
	} 
	else {
		
		if (form.black_consumer_desc.value != "") 
		{
			form.black_consumer.value = 1;
		}
		else
		{
			form.black_consumer.value = 0;
		}

		form.submit();
	}
}

function deleteit() {
	
	if (confirm("삭제할까요?")) 
	{
		if (confirm("삭제된 데이터는 복구할 수 없습니다. 삭제할까요?")) 
		{
			var form=document.tx_editor_form;
		
			form.mode.value = 'del';
			form.action = "cs_inbound_edit_ok.php";
			form.submit();
		}
	}
}

function deletecallback() {
	
	if (confirm("전화요청을 삭제할까요?")) 
	{
		if (confirm("삭제된 데이터는 복구할 수 없습니다. 삭제할까요?")) 
		{
			var form=document.tx_editor_form;
			
			form.return_url.value="cs_inbound_admin_callback.php";
			form.mode.value = 'delcallback';
			form.action = "cs_inbound_edit_ok.php";
			form.submit();
		}
	}
}

function changeProductCategory(obj)
{
	var idx = obj.selectedIndex;
	var arr_product_name = <?php echo json_encode($arr_as_model)?>; 

	$('#product_name').empty();

	//append
	var option = $("<option>"+"-- 모델명 입력 --"+"</option>");
	$('#product_name').append(option);
	for(var count = 0; count < arr_product_name[idx].length; count++) 
	{                
		var option = $("<option>"+arr_product_name[idx][count]+"</option>");
		$('#product_name').append(option);
	}

}

</script>

<? include('../footer.php');?>