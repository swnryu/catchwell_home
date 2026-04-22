<?
include("../def_inc.php");
$mod	= M_INBOUND;
$menu	= isset($_GET['idx']) ? S_PARTS_LIST : S_PARTS_EDIT;
$menu	= isset($_GET['shipment']) ? S_PARTS_SHIPMENT : $menu; //20211206

include("../header.php");
include("cs_inbound_def.php");

include("../product_category_inc.php");

$idx = isset($_GET['idx'])?$_GET['idx']:0;

$table = "cs_shipping_parts";

$row = $db->object($table,"where idx='$idx'");

/////////////////////TEST
if (0) {
	class objTableCsShippingParts {
		var $idx, $reg_datetime, $product_name, $parts_name, $parts_name_ex, $status, $reason, $pic_name, $pic_memo;
		var $customer_name, $customer_phone, $customer_addr, $customer_addr_detail, $customer_zipcode, $delivery_memo, $delivery_num;
	}		
	$row = new objTableCsShippingParts;

	//$row->idx = 1;
	$row->reg_datetime = "2020-11-22 17:33:44";
	$row->product_name = "CV8 Light";
	$row->parts_name = "";
	$row->parts_price = 1000;
	$row->status = 1;
	$row->reason = "초기누락";
	$row->pic_name = "관리자";
	$row->pic_memo = "홈앤쇼핑 10월 6일 구매";
	$row->customer_name = "개나리";
	$row->customer_phone = "010-2222-3333";
	$row->customer_addr = "경기도 성남시 분당구 판교로 723";
	$row->customer_addr_detail = "분당테크노파크 B동 502호";
	$row->customer_zipcode = "03511";
	$row->delivery_memo = "빠른 배송요";
	$row->delivery_num = "123456789012";
	
}
////////////////////

$arr_as_model_ex = array();
for($i=0;$i<count($arr_as_model);$i++)
{
	$arr_as_model_ex += array($i => $arr_as_model[$i]);

	for($j=0;$j<count($arr_as_model[$i]);$j++)
	{
		if ($arr_as_model[$i][$j] == 'CF6') {
			array_splice($arr_as_model_ex[$i], $j, 1);
		}
		if ($arr_as_model_ex[$i][$j] == 'F6') {
			$arr_as_model_ex[$i][$j] = 'F6/CF6';
		}
		if ($arr_as_model_ex[$i][$j] == 'THC-1000') {
			$arr_as_model_ex[$i][$j] = 'THC1000';
		}
	}
}



$product_name = $row->product_name; 
$parts_name = $row->parts_name; 
$sel_category = 0;
$mode = "";

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

?>

	<h4 class="page-header"><?if($idx>0) {echo "부품출고 상세 정보"."($idx)";} else {echo "부품출고 등록";} ?>  </h4>

	<form method="post" action="cs_parts_edit_ok.php" name="tx_editor_form" enctype="multipart/form-data" >
	
	<input type="hidden" name="mode" value="<?echo $mode;?>">
	<input type="hidden" name="idx" value="<?echo $idx;?>">
	<input type="hidden" name="return_url" value="<?echo $return_url;?>">

	<table class="table table-bordered">
	<colgroup>
	<col width="15%">
	<col width="*">
	</colgroup>
	<tbody>
	<tr>
		<th>제품 모델명*</th>
		<td>
			<div class="form-inline" >
				<select id="product_category" name="product_category" style="width:220px;" class="form-control input-sm" onchange="changeProductCategory(this)" > 
					<?for($i=0;$i<count($arr_category_name);$i++) { ?>
						<option value="<?echo $arr_category_name[$i]?>" <?if(in_array($product_name, $arr_as_model_ex[$i])){ $sel_category=$i;?>selected<?}?> ><?echo $arr_category_name[$i]?></option>
					<? } ?>
				</select>

				<select id="product_name" name="product_name" style="width:240px;" class="form-control input-sm" onchange="changeProductName(this)" >
					<option value="">-- 모델명 입력 --</option>
					<?for($i=0;$i<count($arr_as_model_ex[$sel_category]);$i++) { ?>
						<option value="<?echo $arr_as_model_ex[$sel_category][$i]?>" <?if($product_name==$arr_as_model_ex[$sel_category][$i]){?>selected<?}?>><?echo $arr_as_model_ex[$sel_category][$i]?></option>
					<? } ?>
				</select>
			</div>
		</td>
	</tr>
	<tr style="height:50px;">
		<th>부품명*</th>
		<td>
		<div class="form-inline" >
		<table id="parts_list" class="" style="width:80%;">
		<colgroup>
		<col width="33%">
		<col width="33%">
		<col width="34%">
		</colgroup>
		<?
			$existModel = 0;
			//echo count($arr_parts)."<br>";
			for($i=0;$i<count($arr_parts);$i++) 
			{
				//echo count($arr_parts[$i])."<br>";
				//echo $arr_parts[$i][0]."<br>";
				if (strpos($arr_parts[$i][0], $product_name) !== false) 
				{
					for ($j=1;$j<count($arr_parts[$i]);$j+=3) 
					{ 
						$parts1 = $arr_parts[$i][$j];
						$parts2 = $arr_parts[$i][$j+1];
						$parts3 = $arr_parts[$i][$j+2];
					?>
						<tr style="height:30px;">
						<td>
							<?if($parts1!=NULL){?><label class="checkbox-inline"><input type="checkbox" value='<?echo $parts1;?>' onclick="clickAdminMemo(this,'<?echo $parts1; ?>');" <?if (strpos($row->parts_name, $parts1) !== false) {echo 'checked';}?> ><?echo str_replace("(V)","",$parts1);?>
							<?
								if (strpos($row->parts_name, $parts1) !== false) { ?>
									<select id="<?echo "select_".$parts1;?>" style="margin-left:10px;" onchange="changeCkSelect(this)">
									<?
										for($opt=1;$opt<=10;$opt++) {
											if ( strpos($row->parts_name, $parts1."[".$opt."개]") !== false ) 
											{ 
												?><option value="<?echo $opt;?>" selected ><?echo $opt."개";?></option> <?
											} 
											else 
											{ 
												?><option value="<?echo $opt;?>" ><?echo $opt."개";?></option> <?
											}
										}
										?>
									</select>
								<? } ?>
							</label><?}?>
						</td>

						<td>
							<?if($parts2!=NULL){?><label class="checkbox-inline"><input type="checkbox" value='<?echo $parts2;?>' onclick="clickAdminMemo(this,'<?echo $parts2; ?>');" <?if (strpos($row->parts_name, $parts2) !== false) {echo 'checked';}?> ><?echo str_replace("(V)","",$parts2);?>
							<?
								if (strpos($row->parts_name, $parts2) !== false) { ?>
									<select id="<?echo "select_".$parts2;?>" style="margin-left:10px;" onchange="changeCkSelect(this)">
										<?
										for($opt=1;$opt<=10;$opt++) {
											if ( strpos($row->parts_name, $parts2."[".$opt."개]") !== false ) 
											{ 
												?><option value="<?echo $opt;?>" selected ><?echo $opt."개";?></option> <?
											} 
											else 
											{ 
												?><option value="<?echo $opt;?>" ><?echo $opt."개";?></option> <?
											}
										}
										?>
									</select>
								<? } ?>
							</label><?}?>
							
						</td>
						
						<td>
							<?if($parts3!=NULL){?><label class="checkbox-inline"><input type="checkbox" value='<?echo $parts3;?>' onclick="clickAdminMemo(this,'<?echo $parts3; ?>');" <?if (strpos($row->parts_name, $parts3) !== false) {echo 'checked';}?> ><?echo str_replace("(V)","",$parts3);?>
							<?
								if (strpos($row->parts_name, $parts3) !== false) { ?>
									<select id="<?echo "select_".$parts3;?>" style="margin-left:10px;" onchange="changeCkSelect(this)">
									<?
										for($opt=1;$opt<=10;$opt++) {
											if ( strpos($row->parts_name, $parts3."[".$opt."개]") !== false ) 
											{ 
												?><option value="<?echo $opt;?>" selected ><?echo $opt."개";?></option> <?
											} 
											else 
											{ 
												?><option value="<?echo $opt;?>" ><?echo $opt."개";?></option> <?
											}
										}
										?>
									</select>
								<? } ?>
							</label><?}?>
						</td>
						</tr>					
					<? } ?>
					<?
					$existModel = 1;
					break;
				} 
			}?> 

		</table><br>
		부 품 : 
		<input type="text" class="form-control input-sm" name="parts_name_ex" id="parts_name_ex" value='<? echo $row->parts_name_ex; ?>' style="width:90%;" placeholder="부품명 직접 입력" autocomplete="off" ><br>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input readonly type="text" class="form-control input-sm" name="parts_name" id="parts_name" value='<? echo str_replace("(V)","",$row->parts_name); ?>' style="width:90%;" placeholder="" autocomplete="off" ><br>
		<input readonly type="hidden" class="form-control input-sm" name="parts_name_org" id="parts_name_org" value='<?echo $row->parts_name;?>' style="width:90%;" placeholder="" autocomplete="off" ><br>
		가 격 : 
		<input type="text" class="form-control input-sm" name="parts_price" style="width:150px;text-align:right;" placeholder="₩" autocomplete="off" value=<?=number_format($row->parts_price)?> oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" onkeyup="this.value=number_format(this.value)">(원)
		</div>
		</td>
	</tr>
	<tr>
		<th>사 유*</th>
		<td>
			<textarea class="form-control input-sm" rows="2" name="reason" placeholder="출고 사유 입력" autocomplete="off" ><?=$row->reason?></textarea>
		</td>
	</tr>
<? if($mode != "new") { ?>
	<tr>
		<th>처리상태*</th>
		<td>
			<div class="form-inline" >
			<label class="radio-inline"><input type="radio" name="status" value="0" <?if ($row->status==0) {echo "checked";}?> > 처리중	</label>&nbsp;&nbsp;&nbsp;&nbsp;
			<label class="radio-inline"><input type="radio" name="status" value="1" <?if ($row->status==1) {echo "checked";}?> > 처리완료 </label>&nbsp;&nbsp;&nbsp;&nbsp;
			</div>
		</td>
	</tr>
<? } ?>	
	<tr>
		<th>담당자 메모</th>
		<td>
			<textarea class="form-control input-sm" rows="2" name="pic_memo" placeholder="" autocomplete="off" ><?=$row->pic_memo?></textarea>
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

	<table class="table table-bordered">			
	<colgroup>
	<col width="15%">
	<col width="*">
	</colgroup>
	<tbody>
	<tr>
		<th>고객명*</th>
		<td>
			<input type="text" class="form-control input-sm" name="customer_name" style="width:50%;" placeholder="고객명" autocomplete="off" value=<?=$row->customer_name?> >
		</td>
	</tr>
	<tr>
		<th>연락처*</th>
		<td>
			<input type="text" class="form-control input-sm" name="customer_phone" style="width:50%;" placeholder="연락처 (010-0000-0000)" autocomplete="off" value=<?=$row->customer_phone?> >
		</td>
	</tr>
	<tr>
		<th>주 소*</th>
		<td>
			<div class="input-group">
				<div class="input-group-btn">
					<button class="btn btn-default btn-sm" type="button" onclick="searchAddr('customer_addr','customer_zipcode');"><i class="glyphicon glyphicon-search"></i></button>
				</div>
				<input name="customer_addr" id="customer_addr" class="form-control input-sm" value="<?=$row->customer_addr?>" placeholder="주소 입력" autocomplete="off">
			</div>
		</td>
	</tr>
	<tr>
		<th>상세주소*</th>
		<td><input name="customer_addr_detail" class="form-control input-sm" value="<?=$row->customer_addr_detail?>" placeholder="상세주소 입력" autocomplete="off"></td>
	</tr>
	<tr>
		<th>우편번호*</th>
		<td><input name="customer_zipcode" id="customer_zipcode" class="form-control input-sm" style="width:170px;" value="<?if($row->customer_zipcode!=null){echo sprintf("%05d", $row->customer_zipcode);}?>" placeholder="우편번호" autocomplete="off"></td>
	</tr>
	<tr>
		<th>배송 메시지</th>
		<td><input name="delivery_memo" class="form-control input-sm" placeholder="배송 메시지" value="<?=$row->delivery_memo?>" autocomplete="off"></td>
	</tr>
	<tr>
		<th>송장번호</th>
		<td><input name="delivery_num" class="form-control input-sm" placeholder="송장번호(숫자만 입력)" value="<?=$row->delivery_num?>" autocomplete="off"></td>
	</tr>
	</tbody>	
	</table>

	
	<table class="table">
		<tr>
			<td class="text-center">

				<a href="javascript:;" class="btn btn-primary" onClick="sendit();" <?if(($PERMISSION & PERMISSION_CS)!=PERMISSION_CS) { echo 'disabled';}?> >등록</a>


<? if ($idx>0) { ?>
				<a href="<?=$return_url?>" class="btn btn-default" >목록</a>
	
				<a href="javascript:;" class="btn btn-danger" style="margin-left:40px;" onclick="deleteit()" <?if(($PERMISSION & PERMISSION_CS)!=PERMISSION_CS) { echo 'disabled';}?> >삭제</a> 

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
	else if(form.parts_name.value=="" && form.parts_name_ex.value=="") { //20211213
		alert("부품명을 입력해 주세요.");
		form.parts_name.focus();
	} 
	else if(form.reason.value=="") {
		alert("사유를 입력해 주세요.");
		form.reason.focus();
	} 
	else if(form.customer_name.value=="") {
		alert("고객명을 입력해 주세요.");
		form.customer_name.focus();
	} 
	else if(form.customer_phone.value=="") {
		alert("고객 연락처를 입력해 주세요.");
		form.customer_phone.focus();
	} 
	else if(form.customer_addr.value=="") {
		alert("고객 주소를 입력해 주세요.");
		form.customer_addr.focus();
	} 
	else if(form.customer_addr_detail.value=="") {
		alert("상세 주소를 입력해 주세요.");
		form.customer_addr_detail.focus();
	} 
	else if(form.customer_zipcode.value=="") {
		alert("우편번호를 입력해 주세요.");
		form.customer_zipcode.focus();
	} 
	else 
	{
		form.parts_price.value=form.parts_price.value.replace(/[^0-9]/g,""); 
		if (form.parts_price.value=="") {
			form.parts_price.value = 0;
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
			form.action = "cs_parts_edit_ok.php";
			form.submit();
		}
	}
}


function changeProductCategory(obj)
{
	var idx = obj.selectedIndex;
	var arr_product_name = <?php echo json_encode($arr_as_model_ex)?>; 

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

function changeProductName(obj)
{
	var idx = obj.selectedIndex;
	var product_name_cur_sel = obj.value;

	document.tx_editor_form.parts_name.value = "";
	document.tx_editor_form.parts_name_org.value = "";

	var arr_as_model_ex = <?php echo json_encode($arr_as_model_ex)?>; 
	var arr_fix_desc = <?php echo json_encode($arr_parts)?>; 
	var product_name = <?php echo json_encode($product_name)?>; 
	var parts_name = <?php echo json_encode($parts_name)?>; 
	
	var existModel = 0;
	
	for(var i=0; i<arr_as_model_ex.length; i++) 
	{
		for(var j=0; j<arr_as_model_ex[i].length; j++) 
		{
			if (arr_as_model_ex[i][j] == product_name_cur_sel)
			{	
				//alert(arr_as_model_ex[i][j] + "-------" + product_name_cur_sel);
				for(var k=0; k<arr_fix_desc.length; k++)
				{
					if (arr_fix_desc[k][0].indexOf(product_name_cur_sel) >= 0) 
					{	
						//alert(product_name_cur_sel + "-----" + arr_fix_desc[k][0])
						existModel = 1;
						setadminfixdesc(k, arr_fix_desc, product_name, product_name_cur_sel, parts_name);
						break;
					}
					if(existModel)
					{
						break;
					}
				}
			}
			if(existModel)
			{
				break;
			}
		}
		if(existModel)
		{
			break;
		}
	}
	
	if (existModel==0)
	{
		$("#parts_list").empty();
		//setadminfixdesc(fix_desc_unknown, arr_fix_desc, product_name, product_name_cur_sel, parts_name);
	}

}

//20211213
function setadminfixdesc(k, arr_fix_desc, product_name, product_name_cur_sel, parts_name)
{
	$("#parts_list").empty();
	
	//alert(parts_name);

	//document.getElementById('parts_name').value = "";
	
//	alert(product_name);
//	alert(product_name_cur_sel);
//	alert(parts_name);

	for (var y=1; y<arr_fix_desc[k].length; y+=3) 
	{	
		var parts1 = arr_fix_desc[k][y];
		var parts2 = arr_fix_desc[k][y+1];
		var parts3 = arr_fix_desc[k][y+2];

		var html = "";

		html += "<colgroup><col width=\'33%\'><col width=\'33%\'><col width=\'34%\'></colgroup><tr style=\'height:30px;\'>";
		if (typeof parts1 !== "undefined" && parts1!="") 
		{ 
			var desc = "";
			desc += "<td><label class='checkbox-inline'><input type='checkbox' value=\'";
			desc += parts1; 
			desc += "\' "; 
			desc += "onclick='clickAdminMemo(this,";
			desc += ('"'+parts1+'"'); 
			desc += ");\' ";
			if( (product_name==product_name_cur_sel) && (parts_name.indexOf(parts1) >= 0) ) 
			{
				desc += " checked";	
			}
			desc += " >";
			desc += parts1.replace('(V)', ''); 
			if( (product_name==product_name_cur_sel) && (parts_name.indexOf(parts1) >= 0) ) 
			{
				//alert(parts1);
				//select/option 추가 
				var sel_id = "select_" + parts1;
				desc += "<select id=\'" + sel_id + "\'" + "style=\'margin-left:10px;\' onchange=\'changeCkSelect(this)\'>";
				for(var opt=1;opt<=10;opt++)
				{
					if (parts_name.indexOf(parts1+"["+opt+"개]") >= 0) {
						desc += ("<option value=\'" + opt + "\' selected>" + opt + "개</option>");
					} else {
						desc += ("<option value=\'" + opt + "\'>" + opt + "개</option>");
					}
				}
				desc += "</select>";
			}
			desc += "</label></td>"; 
			html += desc;
		}
		if (typeof parts2 !== "undefined" && parts2!="") 
		{
			var desc = "";
			desc += "<td><label class='checkbox-inline'><input type='checkbox' value=\'";
			desc += parts2; 
			desc += "\' "; 
			desc += "onclick='clickAdminMemo(this,";
			desc += ('"'+parts2+'"'); 
			desc += ");\' "; 
			if( (product_name==product_name_cur_sel) && (parts_name.indexOf(parts2) >= 0) ) 
			{
				desc += " checked";	
			}
			desc += " >";
			desc += parts2.replace('(V)', ''); 
			if( (product_name==product_name_cur_sel) && (parts_name.indexOf(parts2) >= 0) ) 
			{
				//select/option 추가 
				var sel_id = "select_" + parts2;
				desc += "<select id=\'" + sel_id + "\'" + "style=\'margin-left:10px;\' onchange=\'changeCkSelect(this)\'>";
				for(var opt=1;opt<=10;opt++)
				{
					if (parts_name.indexOf(parts2+"["+opt+"개]") >= 0) {
						desc += ("<option value=\'" + opt + "\' selected>" + opt + "개</option>");
					} else {
						desc += ("<option value=\'" + opt + "\'>" + opt + "개</option>");
					}
				}
				desc += "</select>";
			}
			desc += "</label></td>"; 
			html += desc;
		}
		if (typeof parts3 !== "undefined" && parts3!="") 
		{
			var desc = "";
			desc += "<td><label class=\'checkbox-inline\'><input type=\'checkbox\' value=\'";
			desc += parts3; 
			desc += "\' "; 
			desc += "onclick='clickAdminMemo(this,";
			desc += ('"'+parts3+'"'); 
			desc += ");' "; 
			if( (product_name==product_name_cur_sel) && (parts_name.indexOf(parts3) >= 0) ) 
			{
				desc += " checked";	
			}
			desc += " >";
			desc += parts3.replace('(V)', ''); 
			if( (product_name==product_name_cur_sel) && (parts_name.indexOf(parts3) >= 0) ) 
			{
				//select/option 추가 
				var sel_id = "select_" + parts3;
				desc += "<select id=\'" + sel_id + "\'" + "style=\'margin-left:10px;\' onchange=\'changeCkSelect(this)\'>";
				for(var opt=1;opt<=10;opt++)
				{
					if (parts_name.indexOf(parts3+"["+opt+"개]") >= 0) {
						desc += ("<option value=\'" + opt + "\' selected>" + opt + "개</option>");
					} else {
						desc += ("<option value=\'" + opt + "\'>" + opt + "개</option>");
					}
				}
				desc += "</select>";
			}
			desc += "</label></td>"; 
			html += desc;
		}
		
		html += "</tr>";

		$("#parts_list").append(html);
	}

	if (product_name==product_name_cur_sel)
	{
		document.getElementById('parts_name').value = parts_name.replaceAll('(V)', '');
		document.getElementById('parts_name_org').value = parts_name;
	}	
}

//20211213
function clickAdminMemo(ckbox, val)
{
	var val_org = val;
	val = val.replace('(V)', '');

	if (ckbox.checked==true)
	{
		var str = document.tx_editor_form.parts_name.value;
		var lastChar = str.substr(str.length - 1);
		if (lastChar != ';' && str.length > 0) {
			str += ';';
		}


		var str_org = document.tx_editor_form.parts_name_org.value;
		var lastChar = str_org.substr(str_org.length - 1);
		if (lastChar != ';' && str_org.length > 0) {
			str_org += ';';
		}

		if (val.includes("AS선조치")) //AS선조치 면 항상 맨앞에 추가 
		{
			document.tx_editor_form.parts_name.value = (val+";") + str;
			document.tx_editor_form.parts_name_org.value = (val_org+";") + str_org;
		}else{
			document.tx_editor_form.parts_name.value = str + (val+"[1개];");
			document.tx_editor_form.parts_name_org.value = str_org + (val_org+"[1개];");
		}

		createSelect(ckbox, true);
	}
	else 
	{ //remove
		removePartsname(val_org);
/*		
		var str = document.tx_editor_form.parts_name.value.replace(val+";","");
		document.tx_editor_form.parts_name.value = str;

		var str_org = document.tx_editor_form.parts_name_org.value.replace(val_org+";","");
		document.tx_editor_form.parts_name_org.value = str_org;
*/
		createSelect(ckbox, false);
	}
}

//20211213
function createSelect(ckbox, bShow)
{
	//alert(ckbox.value);
	var lb = ckbox.parentNode;

	if (bShow)
	{
		var x = document.createElement("SELECT");
		x.setAttribute("id", "select_"+ckbox.value);
		x.setAttribute("style", "margin-left:10px;");
		x.setAttribute("onchange", "changeCkSelect(this)");
		lb.appendChild(x);

		for(var v=1;v<=10;v++)
		{
			var opt1 = document.createElement("option");
			opt1.value = v;
			opt1.text = v+'개';
			x.add(opt1, null);
		}

	}
	else
	{
		document.getElementById("select_"+ckbox.value).remove();
	}
}

//20211213
function changeCkSelect(sel)
{
	var lb = sel.parentNode; //label
	var ck = lb.childNodes; //checkbox

	if (ck.length > 0)
	{
		var ck_val_org = ck[0].value;
		var ck_val = ck_val_org.replace('(V)', '');
		var sel_val = sel.value + '개';//수량 

		var str = document.tx_editor_form.parts_name.value;
		var lastChar = str.substr(str.length - 1);
		if (lastChar != ';' && str.length > 0) {
			str += ';';
		}

		var parts_name = ck_val + "[" + sel_val + "]";
		var pattern = new RegExp(ck_val + '[\[\d]');

//		if ( pattern.test(parts_name) )
		{
			//remove
			removePartsname(ck_val_org);

/*			if (sel.value == 1)
			{
				//add
				str = document.tx_editor_form.parts_name.value;
				document.tx_editor_form.parts_name.value = str + (ck_val+";");
				
				str = document.tx_editor_form.parts_name_org.value;
				var parts_name_org = ck_val_org;
				document.tx_editor_form.parts_name_org.value = str + (parts_name_org+";");
			}
			else */
			{
				//add
				str = document.tx_editor_form.parts_name.value;
				document.tx_editor_form.parts_name.value = str + (parts_name+";");
				
				str = document.tx_editor_form.parts_name_org.value;
				var parts_name_org = ck_val_org + "[" + sel_val + "]";
				document.tx_editor_form.parts_name_org.value = str + (parts_name_org+";");
			}
		}

	}

}

//20211213
function removePartsname(partsname_org)
{
	var val = partsname_org.replace('(V)', '');
	var val_org = partsname_org;

	var str = document.tx_editor_form.parts_name.value;
	var str_org = document.tx_editor_form.parts_name_org.value;

	if (str.indexOf(val) != -1)
	{
		var ret = "";

		if (str.indexOf(val+';') != -1) {
			ret = document.tx_editor_form.parts_name.value.replace(val+';', "");
		}
		else if (str.indexOf(val+'[1개]'+';') != -1) {
			ret = document.tx_editor_form.parts_name.value.replace(val+"[1개]"+";","");
		}
		else if (str.indexOf(val+'[2개]'+';') != -1) {
			ret = document.tx_editor_form.parts_name.value.replace(val+"[2개]"+";","");
		}
		else if (str.indexOf(val+'[3개]'+';') != -1) {
			ret = document.tx_editor_form.parts_name.value.replace(val+"[3개]"+";","");
		}
		else if (str.indexOf(val+'[4개]'+';') != -1) {
			ret = document.tx_editor_form.parts_name.value.replace(val+"[4개]"+";","");
		}
		else if (str.indexOf(val+'[5개]'+';') != -1) {
			ret = document.tx_editor_form.parts_name.value.replace(val+"[5개]"+";","");
		}
		else if (str.indexOf(val+'[6개]'+';') != -1) {
			ret = document.tx_editor_form.parts_name.value.replace(val+"[6개]"+";","");
		}
		else if (str.indexOf(val+'[7개]'+';') != -1) {
			ret = document.tx_editor_form.parts_name.value.replace(val+"[7개]"+";","");
		}
		else if (str.indexOf(val+'[8개]'+';') != -1) {
			ret = document.tx_editor_form.parts_name.value.replace(val+"[8개]"+";","");
		}
		else if (str.indexOf(val+'[9개]'+';') != -1) {
			ret = document.tx_editor_form.parts_name.value.replace(val+"[9개]"+";","");
		}
		else if (str.indexOf(val+'[10개]'+';') != -1) {
			ret = document.tx_editor_form.parts_name.value.replace(val+"[10개]"+";","");
		}
		else {
			
		}
		document.tx_editor_form.parts_name.value = ret;

		var ret_org = "";
		if (str_org.indexOf(val_org+';') != -1) {
			
			ret_org = document.tx_editor_form.parts_name_org.value.replace(val_org+";","");
		}
		else if (str_org.indexOf(val_org+'[1개]'+';') != -1) {
			ret_org = document.tx_editor_form.parts_name_org.value.replace(val_org+"[1개]"+";","");
		}
		else if (str_org.indexOf(val_org+'[2개]'+';') != -1) {
			ret_org = document.tx_editor_form.parts_name_org.value.replace(val_org+"[2개]"+";","");
		}
		else if (str_org.indexOf(val_org+'[3개]'+';') != -1) {
			ret_org = document.tx_editor_form.parts_name_org.value.replace(val_org+"[3개]"+";","");
		}
		else if (str_org.indexOf(val_org+'[4개]'+';') != -1) {
			ret_org = document.tx_editor_form.parts_name_org.value.replace(val_org+"[4개]"+";","");
		}
		else if (str_org.indexOf(val_org+'[5개]'+';') != -1) {
			ret_org = document.tx_editor_form.parts_name_org.value.replace(val_org+"[5개]"+";","");
		}
		else if (str_org.indexOf(val_org+'[6개]'+';') != -1) {
			ret_org = document.tx_editor_form.parts_name_org.value.replace(val_org+"[6개]"+";","");
		}
		else if (str_org.indexOf(val_org+'[7개]'+';') != -1) {
			ret_org = document.tx_editor_form.parts_name_org.value.replace(val_org+"[7개]"+";","");
		}
		else if (str_org.indexOf(val_org+'[8개]'+';') != -1) {
			ret_org = document.tx_editor_form.parts_name_org.value.replace(val_org+"[8개]"+";","");
		}
		else if (str_org.indexOf(val_org+'[9개]'+';') != -1) {
			ret_org = document.tx_editor_form.parts_name_org.value.replace(val_org+"[9개]"+";","");
		}
		else if (str_org.indexOf(val_org+'[10개]'+';') != -1) {
			ret_org = document.tx_editor_form.parts_name_org.value.replace(val_org+"[10개]"+";","");
		}
		document.tx_editor_form.parts_name_org.value = ret_org;
	
	}

}

</script>

<script src="https://t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js"></script>
<script type="text/javascript">
function searchAddr(addr, zipcode)
{
    new daum.Postcode({
        oncomplete: function(data) {
            // 팝업에서 검색결과 항목을 클릭했을 때 실행할 코드
            
            // 우편번호와 주소 정보를 해당 필드에 넣는다.
            document.getElementById(addr).value = data.roadAddress;
            document.getElementById(zipcode).value = data.zonecode;
        }
    }).open();
}

//20210104
var number, nArr;
function number_format( number )
{
	number=number.replace(/\,/g,"");

	nArr = String(number).split('').join(',').split('');

	for( var i=nArr.length-1, j=1; i>=0; i--, j++)  if( j%6 != 0 && j%2 == 0) nArr[i] = '';

	return nArr.join('');
}

</script>

<? include('../footer.php');?>