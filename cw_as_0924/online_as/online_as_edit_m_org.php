<?
include("../def_inc.php");
$mod	= M_AS;	
$menu	= isset($_GET['from'])?$_GET['from']:S_AS_M;
include("../header.php");

$reg_num = isset($_GET['reg_num'])?$_GET['reg_num']:"";
$tracking_num = isset($_GET['tracking_num'])?$_GET['tracking_num']:"";
$tracking_num = preg_replace("/[^0-9]*/s", "", $tracking_num);

$state = 0;

$isMobile = preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]); 

if (DBG_MODE) {
	//$reg_num = '200916-012';
}

$table = "as_parcel_service";
if ($tracking_num!="") {
	$state = ST_REG_DONE;
	$row = $db->object($table,"where parcel_num='$tracking_num' AND (process_state=$state) ORDER BY parcel_num DESC LIMIT 1");
	if ($row==NULL) { 
		$tools->alertJavaGo("송장번호를 찾을 수 없습니다.", $_SERVER['PHP_SELF']);
	}
}

if ($reg_num!="") {
	$state = ST_REG_DONE;
	$row = $db->object($table,"where reg_num='$reg_num' AND (process_state=$state) ORDER BY reg_num DESC LIMIT 1");
	if ($row==NULL) { 
		$tools->alertJavaGo("접수번호를 찾을 수 없습니다.", $_SERVER['PHP_SELF']);
	}
}

$admin_memo = $row->admin_memo;
$admin_memo_etc = '';

if ($row->admin_memo != "") {
	if (strstr($row->admin_memo, '[ETC]') == false) {
		$admin_memo_etc='';
	} else {
		$admin_memo_etc = strstr($row->admin_memo, '[ETC]');
		$admin_memo_etc = str_replace('[ETC]','',$admin_memo_etc);
	}

	if (strstr($row->admin_memo, '[ETC]', true) == false) {
		$admin_memo=$row->admin_memo;
	} else {
		$admin_memo = strstr($row->admin_memo, '[ETC]', true);
	}
}


//20210213-2회이상 중복접수검색
//20210220-01000000000 제외
$query2="select count(*) as cnt from as_parcel_service where process_state=4 and customer_phone='$row->customer_phone' and customer_phone!='01000000000' ";
$rs2 = mysqli_query($db->db_conn, $query2);
$row2 = mysqli_fetch_object($rs2);
//echo $row2->cnt;

?>

<script type="text/javascript">
function init() {
	document.tx_editor_form.parcel_num.focus();
}
window.onload=init;
</script>

</head>


	<h4 class="page-header" style="margin-top:-13px; height:43px;">수리 업무 진행 <? if($row->customer_name!="") { echo "(".$row->customer_name.")"; } ?> 
	<!--span class="badge" style="background-color:#FF00FF;font-size:17px;"><?if($row2->cnt>0){echo $row2->cnt+1;}?></span-->&nbsp;&nbsp;<!--20210213-->
	<!--h4 class="page-header" style="margin-top:-13px; height:43px;">수리 업무 진행 <span <?if($row2->cnt==1){echo 'style="background-color:yellow"';} else if($row2->cnt>1){echo 'style="background-color:#FF00FF"';}?>><? if($row->customer_name!="") { echo "(".$row->customer_name.")"; } ?></span-->&nbsp;&nbsp;&nbsp;<!--20210213-->
	<a href="<?=$_SERVER['PHP_SELF']?>" class="btn btn-default" >초기화</a></h4>
	
	<form method="post" action="online_as_edit_m_ok.php?idx=<?=$row->idx?>" name="tx_editor_form" enctype="multipart/form-data" style="margin-top:-5px;"> <!--20210105-->
	
	<input type="hidden" name="idx" value="<?=$row->idx?>">
	<input type="hidden" name="process_state" value=<?echo ST_FIX_DONE;?> >
	
	<table class="table table-bordered">
	<colgroup>
	<col width="20%">
	<col width="*">
	</colgroup>
	<tbody>
	<tr>
	<th>송장번호</th>
		<td>
		<div class="input-group">
			<input name="parcel_num" class="form-control" autocomplete="off" value="<?=$row->parcel_num?>" placeholder="송장번호 입력" onchange="changeTrackingNum();">
			<div class="input-group-btn">
				<button class="btn btn-default" type="button" onclick="searchByTrackingNum();">조회</button>
			</div>
		</div>
		</td>
	</tr>
	<tr>
	<th>접수번호</th>
		<td>
		<div class="input-group">
			<input name="reg_num" class="form-control" autocomplete="off" value="<?=$row->reg_num?>" placeholder="접수번호 입력" onchange="changeRegNum();">
			<div class="input-group-btn">
				<button class="btn btn-default" type="button" onClick="searchByRegNum();">조회</button>
			</div>
		</div>
		</td>
	</tr>
	<!--tr> 
		<th>진행상태</th>
		<td><?=$proc_state[$row->process_state] .' - '. $row->product_name ?></td>
	</tr-->
	<tr> 
		<th>모델명</th>
		<td>
			<div class="input-group-btn" style="width:250px;">
				<select name="product_name"  class="form-control input-sm" >
					<option value="">-- 모델명 입력 --</option>
					<?for($i=0;$i<count($arr_product_name);$i++) { ?>
						<option value="<?echo $arr_product_name[$i]?>" <?if($row->product_name==$arr_product_name[$i]){?>selected<?}?>><?echo $arr_product_name[$i]?></option>
					<? } ?>
				</select>
			</div>
		</td>
	</tr>	
	<tr> 
		<th>처리상태</th>
		<!--td><?=$row->broken_type?> <?=" - ".$proc_state[$row->process_state] ?> </td-->
		<td><?=$proc_state[$row->process_state] ?> </td>
	</tr>
	<tr>
		<th>불량내용</th>
		<td><?=$row->customer_desc?></td>
	</tr>
	<tr>
		<th>조치사항</th>
		<td>
			<div class="input-group-text" >
			<? if ($isMobile) { ?>
				<select name="admin_memo_sel" id="admin_memo_sel" class="form-control " multiple="multiple" onchange="changeAdminMemo();" >
				<?
					if (array_search($row->product_name, $arr_product_vc) !== false) { //[무선청소기]
						for($i=0;$i<count($arr_admin_memo_vc);$i++) { ?>
							<option value="<?echo $arr_admin_memo_vc[$i]?>" <?if (strpos($row->admin_memo, $arr_admin_memo_vc[$i]) !== false) {?>selected<?}?>>
							<?/*echo $arr_admin_memo_vc[$i]*/$memo = $arr_admin_memo_vc[$i]; $memo = str_replace("(V)","",$memo); echo $memo;?></option>
						<?}
					} 
					else if (array_search($row->product_name, $arr_product_rc) !== false) { //[로봇청소기]
						for($i=0;$i<count($arr_admin_memo_rc);$i++) { ?>
							<option value="<?echo $arr_admin_memo_rc[$i]?>" <?if (strpos($row->admin_memo, $arr_admin_memo_rc[$i]) !== false) {?>selected<?}?>>
							<?/*echo $arr_admin_memo_rc[$i]*/$memo = $arr_admin_memo_rc[$i]; $memo = str_replace("(R)","",$memo); echo $memo;?></option>
						<?}
					}
					else if (array_search($row->product_name, array("CH200")) !== false) { //[가습기]
						for($i=0;$i<count($arr_admin_memo_hm);$i++) { ?>
							<option value="<?echo $arr_admin_memo_hm[$i]?>" <?if (strpos($row->admin_memo, $arr_admin_memo_hm[$i]) !== false) {?>selected<?}?>>
							<?/*echo $arr_admin_memo_hm[$i]*/$memo = $arr_admin_memo_hm[$i]; $memo = str_replace("(H)","",$memo); echo $memo;?></option>
						<?}
					}
					else if (array_search($row->product_name, array("SECRET01 블랙","SECRET01 화이트/핑크/민트","스나이퍼CG-72","CW44")) !== false) { //[고데기&기타]
						for($i=0;$i<count($arr_admin_memo_hs);$i++) { ?>
							<option value="<?echo $arr_admin_memo_hs[$i]?>" <?if (strpos($row->admin_memo, $arr_admin_memo_hs[$i]) !== false) {?>selected<?}?>>
							<?/*echo $arr_admin_memo_hs[$i]*/$memo = $arr_admin_memo_hs[$i]; $memo = str_replace("(S)","",$memo); echo $memo;?></option>
						<?}
					}
					else if (array_search($row->product_name, array("CM7")) !== false) { //[물걸레청소기]
						for($i=0;$i<count($arr_admin_memo_hs);$i++) { ?>
							<option value="<?echo $arr_admin_memo_mc[$i]?>" <?if (strpos($row->admin_memo, $arr_admin_memo_mc[$i]) !== false) {?>selected<?}?>>
							<?/*echo $arr_admin_memo_mc[$i]*/$memo = $arr_admin_memo_mc[$i]; $memo = str_replace("(M)","",$memo); echo $memo;?></option>
						<?}
					}
					else {
						for($i=0;$i<count($arr_admin_memo_vc);$i++) { ?>
							<option value="<?echo $arr_admin_memo_vc[$i]?>" <?if (strpos($row->admin_memo, $arr_admin_memo_vc[$i]) !== false) {?>selected<?}?>>
							<?/*echo $arr_admin_memo_vc[$i]*/$memo = $arr_admin_memo_vc[$i]; $memo = str_replace("(V)","",$memo); echo $memo;?></option>
						<? } 
					}
				?>
				</select><br>
				<input type="text" name="admin_memo" class="form-control" value="<?=$admin_memo?>" autocomplete="off" readonly>
				<input type="text" name="admin_memo_etc" class="form-control" value="<?=$admin_memo_etc?>" placeholder="기타 조치사항 입력" autocomplete="off" >
			<? } 
			else { ?>
			<?
				if (array_search($row->product_name, $arr_product_vc) !== false) { ?>
					<div name="vc" class="checkbox" ><font color="blue">[무선청소기]</font><br>
					<?for($i=0;$i<count($arr_admin_memo_vc);$i++) { ?>
						<label><input type="checkbox" value="<?echo $arr_admin_memo_vc[$i]?>" onclick="clickAdminMemo(this,'<?echo $arr_admin_memo_vc[$i];?>');" <?if (strpos($row->admin_memo, $arr_admin_memo_vc[$i]) !== false) {echo 'checked';}?> >
						<?/*echo $arr_admin_memo_vc[$i]*/$memo = $arr_admin_memo_vc[$i]; $memo = str_replace("(V)","",$memo); echo $memo;?>&nbsp&nbsp</label>
					<? } ?> </div>
				<? } 
				else if (array_search($row->product_name, $arr_product_rc) !== false) { ?>
					<div name="rc" class="checkbox" ><font color="blue">[로봇청소기]</font><br>
					<?for($i=0;$i<count($arr_admin_memo_rc);$i++) { ?>
						<label><input type="checkbox" value="<?echo $arr_admin_memo_rc[$i]?>" onclick="clickAdminMemo(this,'<?echo $arr_admin_memo_rc[$i];?>');" <?if (strpos($row->admin_memo, $arr_admin_memo_rc[$i]) !== false) {echo 'checked';}?> > 
						<?/*echo $arr_admin_memo_rc[$i]*/$memo = $arr_admin_memo_rc[$i]; $memo = str_replace("(R)","",$memo); echo $memo;?>&nbsp&nbsp</label>
					<? } ?>	</div> 
				<? }	
				else if (array_search($row->product_name, array("CH200")) !== false) { ?>
					<div name="hm" class="checkbox" ><font color="blue">[가습기]</font><br>
					<?for($i=0;$i<count($arr_admin_memo_hm);$i++) { ?>
						<label><input type="checkbox" value="<?echo $arr_admin_memo_hm[$i]?>" onclick="clickAdminMemo(this,'<?echo $arr_admin_memo_hm[$i];?>');" <?if (strpos($row->admin_memo, $arr_admin_memo_hm[$i]) !== false) {echo 'checked';}?> > 
						<?/*echo $arr_admin_memo_hm[$i]*/$memo = $arr_admin_memo_hm[$i]; $memo = str_replace("(H)","",$memo); echo $memo;?>&nbsp&nbsp</label>
					<? } ?>	</div> 
				<? }	
				else if (array_search($row->product_name, array("SECRET01 블랙","SECRET01 화이트/핑크/민트","스나이퍼CG-72","CW44")) !== false) { ?>
					<div name="hs" class="checkbox" ><font color="blue">[기타]</font><br>
					<?for($i=0;$i<count($arr_admin_memo_hs);$i++) { ?>
						<label><input type="checkbox" value="<?echo $arr_admin_memo_hs[$i]?>" onclick="clickAdminMemo(this,'<?echo $arr_admin_memo_hs[$i];?>');" <?if (strpos($row->admin_memo, $arr_admin_memo_hs[$i]) !== false) {echo 'checked';}?> > 
						<?/*echo $arr_admin_memo_hs[$i]*/$memo = $arr_admin_memo_hs[$i]; $memo = str_replace("(S)","",$memo); echo $memo;?>&nbsp&nbsp</label>
					<? } ?>	</div>
				<? }	
				else if (array_search($row->product_name, array("CM7")) !== false) { ?>
					<div name="mc" class="checkbox" ><font color="blue">[물걸레청소기]</font><br>
					<?for($i=0;$i<count($arr_admin_memo_mc);$i++) { ?>
						<label><input type="checkbox" value="<?echo $arr_admin_memo_mc[$i]?>" onclick="clickAdminMemo(this,'<?echo $arr_admin_memo_mc[$i];?>');" <?if (strpos($row->admin_memo, $arr_admin_memo_mc[$i]) !== false) {echo 'checked';}?> > 
						<?/*echo $arr_admin_memo_mc[$i]*/$memo = $arr_admin_memo_mc[$i]; $memo = str_replace("(M)","",$memo); echo $memo;?>&nbsp&nbsp</label>
					<? } ?>	</div>
				<? } 
				else { ?>
					<div name="vc" class="checkbox" ><font color="blue">[무선청소기]</font><br>
					<?for($i=0;$i<count($arr_admin_memo_vc);$i++) { ?>
						<label><input type="checkbox" value="<?echo $arr_admin_memo_vc[$i]?>" onclick="clickAdminMemo(this,'<?echo $arr_admin_memo_vc[$i];?>');" <?if (strpos($row->admin_memo, $arr_admin_memo_vc[$i]) !== false) {echo 'checked';}?> >
						<?/*echo $arr_admin_memo_vc[$i]*/$memo = $arr_admin_memo_vc[$i]; $memo = str_replace("(V)","",$memo); echo $memo;?>&nbsp&nbsp</label>
					<? } ?> </div>
				<? } ?>
				
				<input type="text" name="admin_memo_etc" class="form-control" value="<?=$admin_memo_etc?>" placeholder="기타 조치사항 입력" autocomplete="off" >
				<input type="hidden" name="admin_memo" class="form-control" value="<?=$admin_memo?>" autocomplete="off">
			<? } ?>
			</div>	
		</td>
	</tr>
	<tr> <!--20210105-->
		<th>수리비용</th>
		<td><input type="text" name="price" class="form-control" style="width:100px; text-align:right;" value="<? if($row->price!=0) {echo number_format($row->price);}?>" placeholder="0" autocomplete="off"  oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" onkeyup="this.value=number_format(this.value)" ></td>
	</tr>
	</form>

	<table class="table">
		<tr>
			<td class="text-center">
			<a href="<?=$_SERVER['PHP_SELF']?>" class="btn btn-default" >초기화</a>
			<a href="javascript:;" class="btn btn-primary" onClick="sendit();" style="margin-left:50px;" <?if(($PERMISSION & PERMISSION_CS)!=PERMISSION_CS) { echo 'disabled';}?> >수리완료 등록</a>
			</td>
		</tr>
	</table>


<script type="text/javascript">
function changeTrackingNum() {
	searchByTrackingNum();
/*
	//CW50A	
	var data = document.tx_editor_form.parcel_num.value;

	if (data.length > 10) {
		var lastChar = data.substr(data.length - 4);
		//alert(document.tx_editor_form.parcel_num.value);
		//alert(lastChar);
		if (lastChar=='[CR]')
		{
			searchByTrackingNum();
		}
	}
*/	
}

function searchByTrackingNum() {
	var form=document.tx_editor_form;
	var tracking_num = form.parcel_num.value;

	window.location.href = "online_as_edit_m.php?tracking_num=" + tracking_num;
}

function changeRegNum() {
	searchByRegNum();
}
function searchByRegNum() {
	var form=document.tx_editor_form;
	var reg_num = form.reg_num.value;

	//reg_num이 null 이면, [접수완료] 리스트의 접수번호를 모두 표시하여 선택하도록함.

	window.location.href = "online_as_edit_m.php?reg_num=" + reg_num;
}

function sendit() {
	
	var form=document.tx_editor_form;
	if (form.parcel_num.value=="" && form.reg_num.value=="") {
		alert("송장번호나 접수번호로 조회해 주세요.");
		form.parcel_num.focus();		
	}
	else if(form.reg_num.value=="") {
		alert("접수번호를 입력해 주세요.");
		form.reg_num.focus();
	}
	else if(form.admin_memo.value=="" && form.admin_memo_etc.value=="") {
		alert("조치사항을 입력해 주세요.");
		form.admin_memo.focus();
	}
	else {
		//20210105
		form.price.value=form.price.value.replace(/[^0-9]/g,""); 
		if (form.price.value=="") {
			form.price.value = 0;
		}

		//조치사항, 상태 변경후 저장 
		if (form.admin_memo_etc.value != "") {
			form.admin_memo.value = form.admin_memo.value + '[ETC]' + (form.admin_memo_etc.value);
		}

		form.submit();
	}
}

$(function() {
    $('#admin_memo_sel').change(function(e) {
		var selected = $(e.target).val();
		console.dir(selected);
    }); 
});

function changeAdminMemo()
{
	var orgData = document.tx_editor_form.admin_memo.value;
	var data = orgData;
	var obj = document.getElementById('admin_memo_sel');

	for(var i=0; i<obj.length; i++)
	{
		if (obj[i].selected) 
		{
			if (orgData.indexOf(obj[i].value)<0) {
				//add
				var lastChar = data.substr(data.length - 1);
				if (lastChar != ';' && data.length > 0) {
					data += ';';
				}

//				data += (obj[i].value+';'); //20210201
				if (obj[i].value.includes("AS선조치")) //AS선조치 면 항상 맨앞에 추가 
				{
					data = (obj[i].value+";") + data;
				}else{
					data = data + (obj[i].value+";");
				}
			} 
		}
		else 
		{	
			if (orgData.indexOf(obj[i].value)>=0) {
				//remove
				data = data.replace(obj[i].value+";","");
			}
		}

	}
	
	document.tx_editor_form.admin_memo.value = data;

}

function clickAdminMemo(ckbox, val)
{
	if (ckbox.checked==true)
	{
		var str = document.tx_editor_form.admin_memo.value;
		var lastChar = str.substr(str.length - 1);
		if (lastChar != ';' && str.length > 0) {
			str += ';';
		}
//		document.tx_editor_form.admin_memo.value = str + (ckbox.value+";"); //20210201
		if (val.includes("AS선조치")) //AS선조치 면 항상 맨앞에 추가 
		{
			document.tx_editor_form.admin_memo.value = (ckbox.value+";") + str;
		}else{
			document.tx_editor_form.admin_memo.value = str + (ckbox.value+";");
		}
	}
	else 
	{
		var str = document.tx_editor_form.admin_memo.value.replace(val+";","");
		document.tx_editor_form.admin_memo.value = str;
	}
}

//20210105
var number, nArr;
function number_format( number )
{
	number=number.replace(/\,/g,"");

	nArr = String(number).split('').join(',').split('');

	for( var i=nArr.length-1, j=1; i>=0; i--, j++)  if( j%6 != 0 && j%2 == 0) nArr[i] = '';

	return nArr.join('');
}

</script>





<!--  <? include('../footer.php');?> -->
</div>
</body>
</html> 
