<?
include("../def_inc.php");
$mod	= M_CANCELLATION;	
$menu	= isset($_GET['idx']) ? S_CANCELLATION : S_CANCELLATION_NEW;
include("../header.php");
include("cancellation_def.php");
//include("cancellation_search_oid.php");

include("../product_category_inc.php");

$idx = isset($_GET['idx'])?$_GET['idx']:0;
$takeback_idx = isset($_GET['takeback_idx'])?$_GET['takeback_idx']:0;

//$table = "cancellation_order";
//$row = $db->object($table,"where idx='$idx'");

$table_shipping = "shipping_date_new";
$row1 = $db->object($table_shipping,"where idx='$takeback_idx'");

if($takeback_idx) { 
//수정 

	if (!$row1) 
	{
		$tools->alertJavaGo("데이터가 없습니다.", "cancellation_list.php"); 
		exit;
	}
	else
	{
		class objTableCancellation {
		var $idx, $date, $model_name, $order_id, $customer_name;
		var $shopping_mall, $type, $reason, $serial, $memo;
		var $date_completed, $admin_name, $admin_memo, $address, $phone, $tracking;
		}		

	if (1)  
		{	//TEST
		$row = new objTableCancellation;

		$row->date = date("Y-m-d");
		$row->model_name = $row1->model;
		$row->order_id = $row1->orderid;
		$row->customer_name = $row1->name;
		$row->shopping_mall = $row1->mall;
		$row->type = "교환";//구분
		$row->reason = "";//반품사유
		$row->memo = "";	//고객조율사항
		$row->address = $row1->address;
		$row->phone = $row1->phone2;
		$row->tracking = $row1->tracking;

		$row->status = 0;

		$row->date_completed = date('Y-m-d');
		$row->result_type = -1;
		$row->result_memo = "";//"CS팀전시용";
		$row->serial = "";//"CW-NF8-K20-01170"; 
		//$row->admin_name = "황정미";
		}
	}		



} else {
//신규 
/*
	class objTableCancellation {
		var $idx, $date, $model_name, $order_id, $customer_name;
		var $shopping_mall, $type, $reason, $serial, $memo;
		var $date_completed, $admin_name, $admin_memo;
	}		

	if (1)  {	//TEST
		$row = new objTableCancellation;

		$row->date = date("Y-m-d");
		$row->model_name = "NF8";
		$row->order_id = "50402966";
		$row->customer_name = "진상고객님";
		$row->shopping_mall = "오늘의집";
		$row->type = "불량반품";//구분
		$row->reason = "작동이 안됨";//반품사유
		$row->memo = "구매확정 이후 반품";	//고객조율사항

		$row->status = 0;

		$row->date_completed = date('Y-m-d');
		$row->result_type = -1;
		$row->result_memo = "";//"CS팀전시용";
		$row->serial = "";//"CW-NF8-K20-01170"; 
		$row->admin_name = "황정미";
	}
*/
}

$product_name = $row->model_name;
$selected_index = 0;


?>

	<h4 class="page-header">반품/교환 신청서 <?if($idx){echo "수정($idx)";}else{echo "등록";}?> </h4>

	<form method="post" action="cancellation_takeback_ok.php" name="tx_editor_form" enctype="multipart/form-data" >
	<input type="hidden" name="idx" value="<?=$row->idx?>">
	<input type="hidden" name="isdel" value="">
	<table class="table table-bordered">
	<colgroup>
	<col width="15%">
	<col width="*">
	<col width="15%">
	<col width="25%">
	</colgroup>
	<tbody>
	<tr>
		<th>요청일*</th>
		<td colspan="3">
			<div class="input-group datetime" style="width:180px;">
				<input type="text" name="date" class="form-control input-sm text-center" placeholder="YYYY-MM-DD" value="<?if(!$row){echo date("Y-m-d");}else{echo $row->date;}?>" autocomplete="off"/>
				<span class="input-group-addon">
					<span class="glyphicon glyphicon-calendar"></span>
				</span>
			</div>
		</td>
	</tr>
	<tr>
		<th>주문번호</th>
		<td colspan="3">
		<div class="form-inline">
		<input name="order_id" class="form-control input-sm" style="width:240px;" value="<?=$row->order_id?>" placeholder="주문번호" autocomplete="off">
		&nbsp;&nbsp;&nbsp;
		<a href="javascript:;" id="search_btn" class="btn btn-info btn-sm ajax-button-searchOid" <? if($idx){ echo "disabled"; } ?> >검색</a>&nbsp;&nbsp;&nbsp;<!--[출고관리시스템] 4월 1일부터 정상 운영중입니다. 4월 이전에 입력된 정보는 부정확한 데이터가 검색될 수 있습니다.-->
		<input type="hidden" name="order_id_sabangnet" class="form-control input-sm" style="width:240px;" value="<?=$row->order_id_sabangnet?>" placeholder="주문번호 사방넷" >
		</div>
		</td>
	</tr>
	<tr>
		<th>제품 모델명*</th>
		<td colspan="3">
			<div class="form-inline" >
				<select id="product_category" name="product_category" style="width:240px;" class="form-control" onchange="changeProductCategory(this)"> 
					<?for($i=0;$i<count($arr_category_name);$i++) { ?>
						<option value="<?echo $arr_category_name[$i]?>" <?if(in_array($product_name, $arr_cancellation_model[$i])) { echo 'selected'; $selected_index = $i; }?> ><?echo $arr_category_name[$i]?></option>
					<? } ?>
				</select>&nbsp;&nbsp;&nbsp;&nbsp;

				<select name="model_name" id="model_name" style="width:240px;" class="form-control " >
					<option value="">-- 모델명 선택 --</option>
					<?for($i=0;$i<count($arr_cancellation_model[$selected_index]);$i++) { ?>
						<option value="<?echo $arr_cancellation_model[$selected_index][$i]?>" <?if($row->model_name==$arr_cancellation_model[$selected_index][$i]){?>selected<?}?>><?echo $arr_cancellation_model[$selected_index][$i]?></option>
					<? } ?>
				</select>
			</div>
		</td>
	</tr>
	<tr>
		<th>구매처*</th>
		<td> 
			<div class="form-inline" >
				<select name="shopping_mall" style="width:240px;" class="form-control " >
					<option value="">-- 구매처 선택 --</option>
					<?for($i=0;$i<count($shopping_mall);$i++) { ?>
						<option value="<?echo $shopping_mall[$i]?>" <?if($row->shopping_mall==$shopping_mall[$i]){?>selected<?}?>><?echo $shopping_mall[$i]?></option>
					<? } ?>
				</select>&nbsp;&nbsp;&nbsp;&nbsp;
				<input name="shopping_mall2" class="form-control input-sm" style="width:240px;" value="<?=$row->shopping_mall?>" placeholder="구매처 직접 입력" autocomplete="off">
			</div>
		</td>
		<th>업체명</th>
		<td><? echo $row->company_name; ?> </td>
		<input type="hidden" name="company_name" class="form-control input-sm" value="<?=$row->company_name;?>" >
	</tr>
	<tr>
		<th>고객명*</th>
		<td><input name="customer_name" class="form-control input-sm" value="<?=$row->customer_name?>" placeholder="고객명" autocomplete="off"></td>
		<th id="th_phone">연락처</th>
		<td><input name="phone" class="form-control input-sm" value="<?=$row->phone?>" placeholder="연락처 (010-0000-0000)" autocomplete="off"></td>
		<!--td id="phone"><? echo $row->phone; ?></td-->
	</tr>
	<tr>
		<th id="th_address">주 소</th>
		<!--td><input name="address" class="form-control input-sm" value="<?=$row->address?>" placeholder="주소" autocomplete="off"></td-->
		<td>
			<div class="input-group">
				<div class="input-group-btn">
					<button class="btn btn-default input-sm" type="button" onclick="searchAddr('address','zipcode');"><i class="glyphicon glyphicon-search"></i></button>
				</div>
				<input name="address" id="address" class="form-control input-sm" value="<?=$row->address?>" placeholder="주소 입력" autocomplete="off">
				<input type="hidden" name="zipcode" id="zipcode" class="form-control input-sm" >
			</div>
		</td>
		<th>원송장번호</th>
		<td><input name="tracking" class="form-control input-sm" value="<?=$row->tracking?>" placeholder="원송장번호" autocomplete="off"></td>
	</tr>
	<tr> 
		<th>구 분*</th>
		<td colspan="3"> 
			<?for($i=0;$i<count($cancellation_type);$i++) { ?>
				<label class="radio-inline">
				<input type="radio" name="type" value="<?echo $cancellation_type[$i]?>" <?if($row->type == $cancellation_type[$i]){echo "checked";} ?> onclick="clickType(this);" ><?echo $cancellation_type[$i]?>
				</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<? } ?>
		</td>
	</tr>
	<tr>
		<th>사 유*</th>
		<td colspan="3"><textarea class="form-control input-sm" rows="2" name="reason" placeholder="반품/교환 사유" autocomplete="off"><?=$row->reason?></textarea></td>
	</tr>
	<tr>
		<th>고객과 조율사항</th>
		<td colspan="3"><textarea class="form-control input-sm" rows="2" name="memo" placeholder="고객과 조율사항" autocomplete="off"><?=$row->memo?></textarea></td>
	</tr>
	</tbody>	
	</table>


	<table class="table table-bordered">
	<colgroup>
	<col width="15%">
	<col width="*">
	</colgroup>
	<tbody>
	<th>상 태*</th>
		<td> 
		<div class="form-inline">
			<?for($i=0;$i<count($cancellation_status);$i++) { ?>
				<label class="radio-inline">
				<input type="radio" name="status" value="<?echo $i?>" <?if($row->status == $i){echo "checked";} ?> onclick="clickStatus(this);" ><?echo $cancellation_status[$i]?>
				</label>&nbsp;&nbsp;&nbsp;
			<? } ?>
		</div>
		</td>
	</tr>
	<tr>
		<th>완료일</th>
		<td>
			<div class="input-group datetime" style="width:180px;">
				<input type="text" name="date_completed" class="form-control input-sm text-center" placeholder="YYYY-MM-DD" value="<?=$row->date_completed?>" autocomplete="off"/>
				<span class="input-group-addon">
					<span class="glyphicon glyphicon-calendar"></span>
				</span>
			</div>
		</td>
	</tr>
	<tr>
	<th>처리 결과</th>
		<td> 
		<div class="form-inline ">
			<?for($i=0;$i<count($result_type);$i++) { ?>
				<label class="radio-inline">
				<input type="radio" name="result_type" value="<?echo $i?>" <?if($row && $row->result_type == $i){echo "checked";} ?> /><?echo $result_type[$i]?>
				</label>&nbsp;&nbsp;&nbsp;
			<? } ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			
			<a href="javascript:;" id="exchangeorder_btn" class="btn btn-warning btn-sm ajax-button-exchangeOrder" <?if($row->exchange_order!="" || $row->type!=$cancellation_type[0]) {echo "disabled";}?> data-toggle="tooltip" title="[온라인발주시스템] 테스트 기간" >새 상품 재출고 요청</a>
			<input type="text" name="exchange_order" class="form-control input-sm" value="<?=$row->exchange_order?>" readonly />
			<font color="red">[영업 온라인발주] 테스트중입니다</font>
		</div>
		</td>
	</tr>
	<tr>
		<th>처리 내역</th>
		<td><textarea class="form-control input-sm" rows="2" name="result_memo" placeholder="처리 내역" autocomplete="off"><?=$row->result_memo?></textarea></td>
	</tr>
	<tr>
		<th>시리얼번호</th>
		<td><input name="serial" class="form-control input-sm" value="<?=$row->serial?>" placeholder="시리얼번호" autocomplete="off"></td>
	</tr>
	<tr>
		<th>담당자*</th>
		<td><input name="admin_name" class="form-control input-sm" value="<? if($row->admin_name=="") {echo $ADMIN_NAME;}else{echo $row->admin_name;}?>" placeholder="담당자 이름" autocomplete="off" ></td>
	</tr>
	</tbody>
	</table>


	<table class="table">
		<tr>
			<td class="text-center">
				<a href="javascript:;" class="btn btn-primary" onClick="sendit();" <?if(($PERMISSION & PERMISSION_CS)!=PERMISSION_CS) { echo 'disabled';}?> >등록</a>
				<? if ($idx) { ?>
					<a href="#" class="btn btn-default" onClick="history.back();return false;">목록</a>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<a href="javascript:;" class="btn btn-danger" onClick="deleteit();" <?if(($PERMISSION & PERMISSION_CS)!=PERMISSION_CS) { echo 'disabled';}?> >삭제</a>
				<? } ?>
			</td>
		</tr>
	</table>


<script type="text/javascript">
function changeProductCategory(obj) {
	var idx = obj.selectedIndex;
	var arr_product_name = <?php echo json_encode($arr_cancellation_model)?>; 

	$('#model_name').empty();

	//append
	var option = $("<option>"+"-- 모델명 선택 --"+"</option>");
	$('#model_name').append(option);
	for(var count = 0; count < arr_product_name[idx].length; count++) 
	{                
		var option = $("<option>"+arr_product_name[idx][count]+"</option>");
		$('#model_name').append(option);
	}
}

function sendit() {
	var form=document.tx_editor_form;

	if(form.date.value=="") {
		alert("요청일을 입력해 주세요.");
		form.date.focus();
	} 
	else if(form.model_name.value=="") {
		alert("제품 모델명을 입력해 주세요.");
		form.model_name.focus();
	} 
	/*else if(form.order_id.value=="") {
		alert("주문번호를 입력해 주세요.");
		form.order_id.focus();
	}*/ 
	else if(form.shopping_mall.value=="" && form.shopping_mall2.value=="") {
		alert("제품 구매처를 입력해 주세요.");
		form.shopping_mall.focus();
	} 
	else if(form.address.value=="" && form.type.value=="교환") {
		alert("주소를 입력해 주세요.");
		form.address.focus();
	} 
	else if(form.phone.value=="" && form.type.value=="교환") {
		alert("연락처를 입력해 주세요.");
		form.phone.focus();
	} 
	else if(form.type.value=="") {
		alert("신청 구분을 선택해 주세요.");
		form.type.focus();
	} 
	else if(form.customer_name.value=="") {
		alert("고객명을 입력해 주세요.");
		form.customer_name.focus();
	} 
	else if(form.reason.value=="") {
		alert("반품 사유를 입력해 주세요.");
		form.reason.focus();
	} 
	else if(form.admin_name.value=="") {
		alert("담당자 이름을 입력해 주세요.");
		form.admin_name.focus();
	} 
	else if(form.status.value=="") {
		alert("상태를 입력해 주세요.");
		form.status.focus();
	}
	else if (form.status.value=="1" && form.date_completed.value=="") {
		alert("완료일을 선택하세요.");
		form.date_completed.focus();
	}
	else if (form.status.value=="1" && form.result_type.value=="") {
		alert("완료 처리 결과를 선택하세요.");
		form.result_type.focus();
	} 
	else {
		
		form.submit();
	}
}

function deleteit() {
	
	if (confirm("반품/교환 신청서를 삭제할까요?")) {
		var form=document.tx_editor_form;
	
		form.isdel.value = 'y';
		form.action = "cancellation_takeback_ok.php";
		form.submit();
	}
}

function clickType(radioType) {
	
	var type = radioType.value;
	if (type == "교환") {
		//alert(document.tx_editor_form.exchange_order.value);
		if (document.tx_editor_form.exchange_order.value == "") {
			document.getElementById('exchangeorder_btn').removeAttribute('disabled');
		} else {
			document.getElementById('exchangeorder_btn').setAttribute('disabled','disabled');
		}

		document.getElementById('th_phone').innerText = "연락처*";
		document.getElementById('th_address').innerText = "주 소*";
	}
	else {
		document.getElementById('exchangeorder_btn').setAttribute('disabled','disabled');

		document.getElementById('th_phone').innerText = "연락처";
		document.getElementById('th_address').innerText = "주 소";
	}

}

function clickStatus(radioStatus) {

var status = radioStatus.value;

if (status==1) {
//		alert(today);

	var today = new Date();
	var dd = today.getDate();
	var mm = today.getMonth()+1;
	var yyyy = today.getFullYear();

	if(dd<10) {
		dd='0'+dd
	} 
	if(mm<10) {
		mm='0'+mm
	} 
	today = yyyy+'-'+mm+'-'+dd; //mm+'/'+dd+'/'+yyyy;

	document.tx_editor_form.date_completed.value = today;
}

}

</script>

<script src="https://t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>
<script type="text/javascript">
function searchAddr(addr, zipcode)
{
    new daum.Postcode({
        oncomplete: function(data) {
            document.getElementById(addr).value = data.roadAddress;
            document.getElementById(zipcode).value = data.zonecode;
        }
    }).open();
}
</script>

<script>
$(".ajax-button-searchOid").on("click", function(e) {
	
	//test
	//alert("[물류관리시스템] 4월 1일부터 정상 운영합니다. 4월 이전에 입력된 정보는 부정확한 데이터가 검색될 수 있습니다.");
	
	var order_id = document.tx_editor_form.order_id.value;
	if (order_id=="") {
		alert("주문번호를 입력하세요.");return;
		return;
	}

	var postData = 
			{ 
				"oid": order_id
			};

	$.ajax({
		type: "GET",
		url: "cancellation_search_oid.php",
		data: postData,
		dataType: 'json',
		success:function(result){
			if (result.success=='ok') {

				//set result data 
				document.tx_editor_form.customer_name.value = result.name;
				document.tx_editor_form.address.value = result.address;
				if (result.phone2 == "") {
					document.tx_editor_form.phone.value = result.phone1;
				} else {
					document.tx_editor_form.phone.value = result.phone2;
				}
				document.tx_editor_form.tracking.value = result.tracking;
/*
				document.getElementById('address').innerText = result.address;
				if (result.phone2 == "") {
					document.getElementById('phone').innerText = result.phone1;
				} else {
					document.getElementById('phone').innerText = result.phone2;
				}
				document.getElementById('tracking').innerText = result.tracking;
*/
				document.tx_editor_form.shopping_mall2.value = result.mall;
				document.tx_editor_form.model_name.value = result.model;
				document.tx_editor_form.order_id_sabangnet.value = result.oid_sabangnet;
			}
			else {
				alert("검색된 데이터가 없습니다.");
			}
			
		},
		beforeSend:function(){
		},
		error:function(e){
			alert('error');
		}
	});

});
</script>


<script>
$(".ajax-button-exchangeOrder").on("click", function(e) {

	var type = document.tx_editor_form.type.value;
	if (type!="교환") {
		alert('교환 신청에만 가능합니다.');
		return;
	}

	//test
	alert("[온라인발주시스템] 테스트 기간으로 부정확한 데이터가 입력될 수 있습니다.");

/*
	$data = "
	MODEL_NAME='CX11_새상품 재출고',
	MODEL_ACC='CS_담당자이름',
	ORDER_MARKET='업체명',
	ORDER_NO='주문번호',
	SABANG_NO='사방넷주문번호' ";
	COMPANY_NAME="CS교환",
	RECEIPT_NAME='이름',
	RECEIPT_MOBILE='전화번호',
	RECEIPT_ADDRESS='주소',
*/	
	var idx = document.tx_editor_form.idx.value;
	var model_name = document.tx_editor_form.model_name.value;
	var model_acc = document.tx_editor_form.admin_name.value;
	
	var order_mrket = document.tx_editor_form.shopping_mall.value;
	var order_mrket2 = document.tx_editor_form.shopping_mall2.value;
	if (order_mrket=="") {
		order_mrket = order_mrket2;
	}

	var order_no = document.tx_editor_form.order_id.value;
	var sabang_no = document.tx_editor_form.order_id_sabangnet.value;
	var company_name = "CS교환"; //업체 온라인발주시스템의 ID: CS교환
	var receipt_name = document.tx_editor_form.customer_name.value;
	var receipt_mobile = document.tx_editor_form.phone.value;
	var receipt_address = document.tx_editor_form.address.value;

	if (model_name=="") {
		alert("모델명을 입력하세요.");	return;
	} /*else if (order_no=="") {
		alert("주문번호를 입력하세요.");	return;
	}*/ else if (order_mrket=="" && order_mrket2=="") {
		alert("구매처를 입력하세요.");	return;
	} else if (receipt_name=="") {
		alert("고객명을 입력하세요.");	return;
	} else if (receipt_mobile=="") {
		alert("연락처를 입력하세요.");	return;
	} else if (receipt_address=="") {
		alert("주소를 입력하세요.");	return;
	} else if (model_acc=="") {
		alert("담당자 이름을 입력하세요.");	return;
	}

	if (confirm("[새 상품 재출고] 요청을 진행할까요?")==false) {
		return;
	}

	var postData = 
			{ 
				"idx": idx,
				"model_name": model_name,
				"model_acc": model_acc,
				"order_mrket": order_mrket,
				"order_no": order_no,
				"sabang_no": sabang_no,
				"company_name": company_name,
				"receipt_name": receipt_name,
				"receipt_mobile": receipt_mobile,
				"receipt_address": receipt_address
			};

	$.ajax({
		type: "POST",
		url: "replacement_order_to_salesdb.php",
		data: postData,
		dataType: 'json',
		success:function(result){
			if (result.success=='ok') {
				//alert(result.REGISTER_NO);
				document.tx_editor_form.exchange_order.value = result.REGISTER_NO;
			}
			else {
				alert(result.success);
			}
		},
		beforeSend:function(){
//			alert('beforeSend');
		},
		error:function(e){
			alert('error');
		}
	});

});
</script>

<? include('../footer.php');?>