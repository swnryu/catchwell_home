<?
include("../def_inc.php");
$mod	= M_AS;	
$menu	= isset($_GET['from'])?$_GET['from']:S_AS_NEW;
include("../header.php");

$idx = isset($_GET['idx'])?$_GET['idx']:0;

include('../product_category_inc.php');

$table = "as_parcel_service";

$row = $db->object($table,"where idx='$idx'");


$product_name = $row->product_name;

$admin_memo = $row->admin_memo;
$admin_memo_etc = '';

$sel_category = 0;

if($idx) { 
	$page_mode = "수정";
	$mode = "edit";
	$from = ' ('.$proc_state[$row->process_state].')';
	
	//20210113
	if ($row->customer_phone != "") {
		$len = strlen($row->customer_phone);
		if ($len == 12) { //4,4,4
			$customer_ph1 = substr($row->customer_phone,0,4); 
			$customer_ph2 = substr($row->customer_phone,4,4); 
			$customer_ph3 = substr($row->customer_phone,-4); 
		} else if ($len == 11) {//3,4,4
			$customer_ph1 = substr($row->customer_phone,0,3); 
			$customer_ph2 = substr($row->customer_phone,strlen($customer_ph1),4); 
			$customer_ph3 = substr($row->customer_phone,-4); 
		} else if ($len == 10) {//3,3,4 / 2,4,4
			if (substr($row->customer_phone,0,2) === "02") { //2,4,4
				$customer_ph1 = substr($row->customer_phone,0,2); 
				$customer_ph2 = substr($row->customer_phone,strlen($customer_ph1),4); 
				$customer_ph3 = substr($row->customer_phone,-4); 
			}
			else {
				$customer_ph1 = substr($row->customer_phone,0,3); 
				$customer_ph2 = substr($row->customer_phone,strlen($customer_ph1),3); 
				$customer_ph3 = substr($row->customer_phone,-4); 
			}
		} else if ($len == 9) {//2,3,4
			$customer_ph1 = substr($row->customer_phone,0,2); 
			$customer_ph2 = substr($row->customer_phone,strlen($customer_ph1),3); 
			$customer_ph3 = substr($row->customer_phone,-4); 
		} 
	}

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

	
}else{
	$page_mode = "등록";
	$mode = "write";
	$from = "";
	
	if (!$row) {
		class objTableParcelService {
			var $idx, $register_num, $reg_date, $update_time, $process_state;
			var $customer_name, $customer_phone, $customer_ph1, $customer_ph2, $customer_ph3;
			var $attached_files, $parcel_memo, $product_name, $product_date;
			var $broken_type, $customer_desc, $admin_memo;
			var $customer_addr, $customer_addr_detail, $customer_zipcode, $parcel_num;
			var $customer_addr_return, $customer_addr_detail_return, $customer_zipcode_return, $parcel_num_return, $parcel_memo_return;
		}		
		$row = new objTableParcelService;
		
		$row->process_state = "0";
		
		//test set default 
		if (DBG_MODE) {
			$row->customer_name = "서강준";
			$row->customer_phone = "01000000000";
			$customer_ph1 = $row->customer_ph1 = "010";
			$customer_ph2 = $row->customer_ph2 = "0000";
			$customer_ph3 = $row->customer_ph3 = "0000";
			$row->customer_addr = "경기 성남시 분당구 판교로 723";
			$row->customer_addr_detail = "분당테크노파크B동 502호";
			$row->customer_zipcode = "13511";

			$row->parcel_memo = "공동현관 비밀번호 4444";
			$row->broken_type = $predef_broken_type[1];
			$row->customer_desc = "전원이 안켜짐";
			$row->product_name = "CM7";
		}
	}
}
?>

	<h4 class="page-header">신청서 <?=$page_mode?> <?=$from?></h4>

	<form method="post" action="online_as_edit_ok.php?from=<?=$menu?>" name="tx_editor_form" enctype="multipart/form-data" >
	<input type="hidden" name="mode" value="<?=$mode?>">
	<input type="hidden" name="idx" value="<?=$row->idx?>">
	<input type="hidden" name="isdel" value="">
	<input type="hidden" name="estimate" value="">
	<!--input type="hidden" name="product_date" value="<?=$row->product_date?>" /-->
	<table class="table table-bordered">
	<colgroup>
	<col width="15%">
	<col width="*">
	</colgroup>
	<tbody>
	<tr>
		<th>접수번호</th>
		<td><input name="reg_num" class="form-control" value="<?=$row->reg_num?>" readonly></td>
	</tr>
	<tr> 
		<th>처리상태*</th>
		<td> <!-- 접수중/택배비입금/접수완료/수리중/수리완료/출고 -->
			<?for($i=0;$i<count($proc_state);$i++) { 
				if($i==7) {
					continue; //20220103
				}
			?>
				<label class="radio-inline"><input type="radio" name="process_state" value="<?echo $i?>" <?if($row->process_state == $i){echo "checked";} ?>><?echo $proc_state[$i]?></label>&nbsp;
			<? } ?>
		</td>
	</tr>
	<tr>
		<th>이름*</th>
		<td><input name="customer_name" class="form-control" value="<?=$row->customer_name?>" placeholder="이름" autocomplete="off"></td>
	</tr>
	<tr>
		<th>전화번호(휴대폰)*</th>
		<td>
			<input type="hidden" name="customer_phone" class="form-control" value="<?=$row->customer_phone?>" placeholder="숫자만 입력">
			<div class="form-inline">
				<!--select name="customer_ph1"  class="form-control input-sm" >
					<option value="010" <?if($customer_ph1=="010"){?>selected<?}?> >010</option>
					<option value="011" <?if($customer_ph1=="011"){?>selected<?}?> >011</option>
					<option value="016" <?if($customer_ph1=="016"){?>selected<?}?> >016</option>
					<option value="017" <?if($customer_ph1=="017"){?>selected<?}?> >017</option>
					<option value="018" <?if($customer_ph1=="018"){?>selected<?}?> >018</option>
					<option value="019" <?if($customer_ph1=="019"){?>selected<?}?> >019</option>
				</select-->
				<!--20210113-->
				<input name="customer_ph1" class="form-control input-sm" type="number" maxlength="4" value="<?=$customer_ph1?>" oninput="maxLengthCheck(this)" placeholder="010" autocomplete="off">
				<input name="customer_ph2" class="form-control input-sm" type="number" maxlength="4" value="<?=$customer_ph2?>" oninput="maxLengthCheck(this)" placeholder="숫자만 입력" autocomplete="off">
				<input name="customer_ph3" class="form-control input-sm" type="number" maxlength="4" value="<?=$customer_ph3?>" oninput="maxLengthCheck(this)" placeholder="숫자만 입력" autocomplete="off">
			</div>
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
		<!--<input type="hidden" name="product_type" class="form-control input-sm" value="<?=$row->product_type?>" >-->
		<th>제품 모델명*</th>
		<td>
			<div class="form-inline" > <!-- 20211029 -->
				<select id="product_category" name="product_category" style="width:220px;" class="form-control" onchange="changeProductCategory(this)"> 
					<?for($i=0;$i<count($arr_category_name);$i++) { ?>
						<option value="<?echo $arr_category_name[$i]?>" <?if(in_array($product_name, $arr_as_model[$i])){ $sel_category=$i;?>selected<?}?>><?echo $arr_category_name[$i]?></option>
					<? } ?>
				</select>

				<select id="product_name" name="product_name" style="width:240px;" class="form-control" onchange="changeProductName(this)">
					<option value="">-- 모델명 입력 --</option>
					<?for($i=0;$i<count($arr_as_model[$sel_category]);$i++) { ?>
						<option value="<?echo $arr_as_model[$sel_category][$i]?>" <?if($product_name==$arr_as_model[$sel_category][$i]){?>selected<?}?>><?echo $arr_as_model[$sel_category][$i]?></option>
					<? } ?>
				</select>
<?
echo $product_name."<br>";
//echo var_dump($arr_as_model[$sel_category]);
?>
				<!--select name="product_name" style="width:220px;" class="form-control" >
					<option value="">-- 모델명 입력 --</option>
					<?for($i=0;$i<count($arr_product_name);$i++) { ?>
						<option value="<?echo $arr_product_name[$i]?>" <?if($product_name==$arr_product_name[$i]){?>selected<?}?>><?echo $arr_product_name[$i]?></option>
					<? } ?>
				</select-->
				<!--span id="product_category">  </span-->
			</div>
			<!--div class="form-inline" >
				<select name="product_name" style="width:220px;" class="form-control" > 
					<option value="">-- 모델명 입력 --</option>
					<?for($i=0;$i<count($arr_product_name);$i++) { ?>
						<option value="<?echo $arr_product_name[$i]?>" <?if($product_name==$arr_product_name[$i]){?>selected<?}?>><?echo $arr_product_name[$i]?></option>
					<? } ?>
				</select>
				<span id="product_category">  </span>
			</div-->
		</td>
	</tr>
	<!--tr>
		<th>제품구입날짜</th>
		<td>
			<div class="input-group datetime" style="width:220px;">
				<input type="text" name="product_date" class="form-control input-sm text-center" placeholder="YYYY-MM-DD" value="<?=$row->product_date?>" autocomplete="off"/>
				<span class="input-group-addon">
					<span class="glyphicon glyphicon-calendar"></span>
				</span>
			</div>
		</td>
	</tr-->
<!--<tr>
		<th>이미지 첨부</th>
		<td>
			<input type="hidden" name="attached_name" class="form-control input-sm" value="<?=$row->attached_files?>" /> 
			<input type="file" name="attached_files" id="attached_files" accept=".png,.jpg,.bmp,.gif" />

			<? if($row->attached_files!="") { ?>
			<span id="img_link">
			<a href="files/<?php echo $row->attached_files; ?>" download><?php echo $row->attached_files;?></a>
			<img src="../images/delete.png" style="margin-left:5px; cursor:hand;" width="10" onclick="removeFile();"/>
			</span>
			<?}else{?>
			[ 권장사이즈 : OOO x OOO ]
			<?}?> 
		</td>
	</tr> -->
	<!--tr> 
		<th>불량유형*</th>
		<td>
			<?for($i=0;$i<count($predef_broken_type);$i++) { ?>
			<label class="radio-inline">
				<input type="radio" name="broken_type" value="<?echo $i?>" <?if($row->broken_type == $predef_broken_type[$i]){echo "checked";} ?>><?echo $predef_broken_type[$i]?>
			</label>
			<? } ?>
		</td>
	</tr-->
	<tr>
		<th>불량내용*</th>
		<td><textarea class="form-control" rows="2" name="customer_desc" placeholder="불량 내용 및 기타 전달 사항" autocomplete="off"><?=$row->customer_desc?></textarea></td>
	</tr>
	<tr>
		<th>관리자 조치사항</th>
		<td>
			
		<?	//20211029 
			$existModel = 0;
			//echo count($arr_fix_desc)."<br>";
			for($i=0;$i<count($arr_fix_desc);$i++) 
			{
				//echo count($arr_fix_desc[$i])."<br>";
				//echo $arr_fix_desc[$i][0]."<br>";
				if (strpos($arr_fix_desc[$i][0], $product_name) !== false) 
				{
					?>
					<table id="admin_fix_desc" style="font-family:arial, sans-serif; border-collapse:collapse; width:50%;">
					<?
					for ($j=1;$j<count($arr_fix_desc[$i]);$j++) 
					{
			?>
						<tr>
						<td style="background-color:white; text-align:left; width:150px; border:1px solid #dddddd; padding-left:5px;">
			<?
						//echo $arr_fix_desc[$i][$j];
						echo str_replace("(V)","",$arr_fix_desc[$i][$j]);
			?>
						</td>
						<td style="background-color:white; text-align:left; border: 1px solid #dddddd; padding-left: 5px;">
						<label><input type="checkbox" value=<?echo $arr_fix_desc[$i][$j].' 수리';?> onclick="clickAdminMemo(this,'<?echo $arr_fix_desc[$i][$j].' 수리'; ?>');" <?if (strpos($row->admin_memo, $arr_fix_desc[$i][$j].' 수리') !== false) {echo 'checked';}?> >&nbsp;수리</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						<label><input type="checkbox" value=<?echo $arr_fix_desc[$i][$j].' 교체';?> onclick="clickAdminMemo(this,'<?echo $arr_fix_desc[$i][$j].' 교체'; ?>');" <?if (strpos($row->admin_memo, $arr_fix_desc[$i][$j].' 교체') !== false) {echo 'checked';}?> >&nbsp;교체</label>
						</td>
						</tr>
			<?			
					}
					?>
					</table><br>
					<?
					$existModel = 1;
					break;
				} 
			}
			if ($existModel==0) 
			{
			?>
				<table id="admin_fix_desc" style="font-family:arial, sans-serif; border-collapse:collapse; width:50%;"><tr><td>
			<?

				for ($j=1;$j<count($arr_fix_desc[$arr_fix_desc_unknown]);$j++)
				{
			?>
				 	<div class="checkbox" >
					<label><input type="checkbox" value="<? echo $arr_fix_desc[$arr_fix_desc_unknown][$j]; ?>" onclick="clickAdminMemo(this,'<?echo $arr_fix_desc[$arr_fix_desc_unknown][$j]; ?>');" <?if (strpos($row->admin_memo, $arr_fix_desc[$arr_fix_desc_unknown][$j]) !== false) {echo 'checked';}?> ><? echo $arr_fix_desc[$arr_fix_desc_unknown][$j]; ?></label><br>
					</div>
			<?
				}
			?>
				</td></tr></table><br>
			<?		
			}
			?> 
			<input type="text" name="admin_memo_etc" class="form-control" value="<?=$admin_memo_etc?>" placeholder="기타 조치사항 입력" autocomplete="off" >
			<input type="text" name="admin_memo" id="admin_memo" class="form-control" value="<?=$admin_memo?>" autocomplete="off">
<!--			
			<div name="vc" class="checkbox" ><font color="blue">[무선청소기]</font><br>
			<?for($i=0;$i<count($arr_admin_memo_vc);$i++) { ?>
				<label><input type="checkbox" value="<?echo $arr_admin_memo_vc[$i]?>" onclick="clickAdminMemo(this,'<?echo $arr_admin_memo_vc[$i];?>');" <?if (strpos($row->admin_memo, $arr_admin_memo_vc[$i]) !== false) {echo 'checked';}?> >
				<?$memo = $arr_admin_memo_vc[$i]; $memo = str_replace("(V)","",$memo); echo $memo;?>&nbsp&nbsp</label>
			<? } ?> </div>
			
			<div name="rc" class="checkbox" ><font color="blue">[로봇청소기]</font><br>
			<?for($i=0;$i<count($arr_admin_memo_rc);$i++) { ?>
				<label><input type="checkbox" value="<?echo $arr_admin_memo_rc[$i]?>" onclick="clickAdminMemo(this,'<?echo $arr_admin_memo_rc[$i];?>');" <?if (strpos($row->admin_memo, $arr_admin_memo_rc[$i]) !== false) {echo 'checked';}?> > 
				<?$memo = $arr_admin_memo_rc[$i]; $memo = str_replace("(R)","",$memo); echo $memo;?>&nbsp&nbsp</label>
			<? } ?>	</div> 
			
			<div name="hm" class="checkbox" ><font color="blue">[가습기]</font><br>
			<?for($i=0;$i<count($arr_admin_memo_hm);$i++) { ?>
				<label><input type="checkbox" value="<?echo $arr_admin_memo_hm[$i]?>" onclick="clickAdminMemo(this,'<?echo $arr_admin_memo_hm[$i];?>');" <?if (strpos($row->admin_memo, $arr_admin_memo_hm[$i]) !== false) {echo 'checked';}?> > 
				<?$memo = $arr_admin_memo_hm[$i]; $memo = str_replace("(H)","",$memo); echo $memo;?>&nbsp&nbsp</label>
			<? } ?>	</div> 
			
			<div name="mc" class="checkbox" ><font color="blue">[물걸레청소기]</font><br>
			<?for($i=0;$i<count($arr_admin_memo_mc);$i++) { ?>
				<label><input type="checkbox" value="<?echo $arr_admin_memo_mc[$i]?>" onclick="clickAdminMemo(this,'<?echo $arr_admin_memo_mc[$i];?>');" <?if (strpos($row->admin_memo, $arr_admin_memo_mc[$i]) !== false) {echo 'checked';}?> > 
				<?$memo = $arr_admin_memo_mc[$i]; $memo = str_replace("(M)","",$memo); echo $memo;?>&nbsp&nbsp</label>
			<? } ?>	</div>

			<div name="hs" class="checkbox" ><font color="blue">[기타]</font><br>
			<?for($i=0;$i<count($arr_admin_memo_hs);$i++) { ?>
				<label><input type="checkbox" value="<?echo $arr_admin_memo_hs[$i]?>" onclick="clickAdminMemo(this,'<?echo $arr_admin_memo_hs[$i];?>');" <?if (strpos($row->admin_memo, $arr_admin_memo_hs[$i]) !== false) {echo 'checked';}?> > 
				<?$memo = $arr_admin_memo_hs[$i]; $memo = str_replace("(S)","",$memo); echo $memo;?>&nbsp&nbsp</label>
			<? } ?>	</div>

			<input type="text" name="admin_memo_etc" class="form-control" value="<?=$admin_memo_etc?>" placeholder="기타 조치사항 입력" autocomplete="off" >
			<input type="hidden" name="admin_memo" class="form-control" value="<?=$admin_memo?>" autocomplete="off">
-->			
		</td>
<!--		
		<td>
			<div class="input-group-btn" >
				<select name="admin_memo"  class="form-control" style="width:50%;min-width:250px;" onchange="changeAdminMemo()">
					<option value="">-- 조치사항 입력 --</option>
					<?for($i=0;$i<count($arr_admin_memo);$i++) { ?>
						<option value="<?echo $arr_admin_memo[$i]?>" <?if($row->admin_memo==$arr_admin_memo[$i]){?>selected<?}?>><?echo $arr_admin_memo[$i]?></option>
					<? } ?>
				</select>
			</div>
		</td>
-->		
	</tr>
	<tr> <!--20210105-->
		<th>유상 수리비용</th>
		<td>
			<div class="form-inline">
			<input type="text" name="price" class="form-control" style="width:100px; text-align:right;" value="<? if($row->price!=0) {echo number_format($row->price);}?>" placeholder="0" autocomplete="off"  oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');" onkeyup="this.value=number_format(this.value)" >
			<? if ($menu==S_AS_REGDONE || $menu==S_AS_FIXING || $menu==S_AS_REGISTERING) { ?>
			
			<a href="javascript:;" class="btn btn-primary" style="margin-left:40px;" onclick="sendEstimate('<?echo $menu;?>')" <?if(($PERMISSION & PERMISSION_CS)!=PERMISSION_CS) { echo 'disabled';}?> >견적서발행</a>
			<? } ?>
			</div>
		</td>
	</tr>
<? if ($menu==S_AS_REGDONE || $menu==S_AS_FIXDONE || $menu==S_AS_NEW) { ?> <!--20220103-->
	<tr>
		<th>담당자명*</th>
		<td>
			<?  if($menu==S_AS_REGDONE || $menu==S_AS_NEW) { //20220117
					$arrPic = array("김영", "이승환", "정승호", "CS쉐어링_천세창", "CS쉐어링_김민서"); ?>
				<div class="form-inline">
					<input name="pic_name" id="pic_name" class="form-control" style="width:200px;" value="<?=$row->pic_name?>" placeholder="담당자명 입력" autocomplete="off" >
					&nbsp;&nbsp;&nbsp;	
					<label class="radio-inline"><input type="radio" name="pic_name_radio" value="<?echo $arrPic[0];?>" onclick="clickPicName('<?echo $arrPic[0];?>', 'pic_name');" ><?=$arrPic[0];?></label>
					<label class="radio-inline"><input type="radio" name="pic_name_radio" value="<?echo $arrPic[1];?>" onclick="clickPicName('<?echo $arrPic[1];?>', 'pic_name');" ><?=$arrPic[1];?></label>	
					<label class="radio-inline"><input type="radio" name="pic_name_radio" value="<?echo $arrPic[2];?>" onclick="clickPicName('<?echo $arrPic[2];?>', 'pic_name');" ><?=$arrPic[2];?></label>
					<label class="radio-inline"><input type="radio" name="pic_name_radio" value="<?echo $arrPic[3];?>" onclick="clickPicName('<?echo $arrPic[3];?>', 'pic_name');" ><?=$arrPic[3];?></label>
					<label class="radio-inline"><input type="radio" name="pic_name_radio" value="<?echo $arrPic[4];?>" onclick="clickPicName('<?echo $arrPic[4];?>', 'pic_name');" ><?=$arrPic[4];?></label>
					
				<?} 
				else { 
					echo $row->pic_name; 
				}?>
				</div>
		</td>
	</tr>
<? } ?>		
	<tr>
		<th class="text-primary">(관리자 전용 메모)</th>
		<td><textarea class="form-control" rows="2" name="admin_desc" placeholder="관리자 전용 메모 입력 (관리자만 볼 수 있습니다)" autocomplete="off"><?=$row->admin_desc?></textarea></td>
	</tr>

	</tbody>
	</table>

	<label style="">제품 회수용 주소</label>
	<table class="table table-bordered">
	<colgroup>
	<col width="15%">
	<col width="*">
	</colgroup>
	<tbody>
	<!--tr>
		<th>주소(출고)*</th>
		<td>
			<div class="input-group">
				<div class="input-group-btn">
					<button class="btn btn-default" type="button" onclick="searchAddr('customer_addr_return','customer_zipcode_return');"><i class="glyphicon glyphicon-search"></i></button>
				</div>
				<input name="customer_addr_return" id="customer_addr_return" class="form-control" value="<?=$row->customer_addr_return?>" placeholder="주소 입력" autocomplete="off">
			</div>
		</td>
	</tr>
	<tr>
		<th>상세주소(출고)*</th>
		<td><input name="customer_addr_detail_return" class="form-control" value="<?=$row->customer_addr_detail_return?>" placeholder="상세주소 입력" autocomplete="off"></td>
	</tr>
	<tr>
		<th>우편번호(출고)</th>
		<td><input name="customer_zipcode_return" id="customer_zipcode_return" class="form-control" style="width:170px;" value="<?if($row->customer_zipcode_return!=null){echo sprintf("%05d", $row->customer_zipcode_return);}?>" placeholder="우편번호" autocomplete="off"></td>
	</tr>
	<tr>
		<th>송장번호(출고)</th>
		<td><input name="parcel_num_return" class="form-control" placeholder="출고용 송장번호(숫자만 입력)" value="<?=$row->parcel_num_return?>" autocomplete="off"></td>
	</tr>
	<tr>
		<th>배송 메시지(출고)</th>
		<td><input name="parcel_memo_return" class="form-control" placeholder="배송 메시지" value="<?=$row->parcel_memo_return?>" autocomplete="off"></td>
	</tr-->

	<tr>
		<th>주소(회수)*</th>
		<td>
			<div class="input-group">
				<div class="input-group-btn">
					<button class="btn btn-default" type="button" onclick="searchAddr('customer_addr','customer_zipcode');"><i class="glyphicon glyphicon-search"></i></button>
				</div>
				<input name="customer_addr" id="customer_addr" class="form-control" value="<?=$row->customer_addr?>" placeholder="주소 입력" autocomplete="off">
			</div>
		</td>
	</tr>
	<tr>
		<th>상세주소(회수)*</th>
		<td><input name="customer_addr_detail" class="form-control" value="<?=$row->customer_addr_detail?>" placeholder="상세주소 입력" autocomplete="off"></td>
	</tr>
	<tr>
		<th>우편번호(회수)*</th>
		<td><input name="customer_zipcode" id="customer_zipcode" class="form-control" value="<?if($row->customer_zipcode!=null){echo sprintf("%05d", $row->customer_zipcode);}?>" style="width:170px;" placeholder="우편번호" autocomplete="off"></td>
	</tr>
	<tr>
		<th>송장번호(회수)</th>
		<td><input name="parcel_num" class="form-control" placeholder="회수용 송장번호(숫자만 입력)" value="<?=$row->parcel_num?>" autocomplete="off"></td>
	</tr>
	<tr>
		<th>배송 메시지(회수)</th>
		<td><input name="parcel_memo" class="form-control" placeholder="배송 메시지" value="<?=$row->parcel_memo?>" autocomplete="off"></td>
	</tr>
	</tbody>	
	</table>

	<label style=""><input type="checkbox" name="check_same_addr" id="check_same_addr" value="" onClick="checkSameAddr()"> 제품 회수용 주소와 동일</label>
	<table class="table table-bordered">
	<colgroup>
	<col width="15%">
	<col width="*">
	</colgroup>
	<tbody>
	<!--tr>
		<th>주소(회수)*</th>
		<td>
			<div class="input-group">
				<div class="input-group-btn">
					<button class="btn btn-default" type="button" onclick="searchAddr('customer_addr','customer_zipcode');"><i class="glyphicon glyphicon-search"></i></button>
				</div>
				<input name="customer_addr" id="customer_addr" class="form-control" value="<?=$row->customer_addr?>" placeholder="주소 입력" autocomplete="off">
			</div>
		</td>
	</tr>
	<tr>
		<th>상세주소(회수)*</th>
		<td><input name="customer_addr_detail" class="form-control" value="<?=$row->customer_addr_detail?>" placeholder="상세주소 입력" autocomplete="off"></td>
	</tr>
	<tr>
		<th>우편번호(회수)*</th>
		<td><input name="customer_zipcode" id="customer_zipcode" class="form-control" value="<?if($row->customer_zipcode!=null){echo sprintf("%05d", $row->customer_zipcode);}?>" style="width:170px;" placeholder="우편번호" autocomplete="off"></td>
	</tr>
	<tr>
		<th>송장번호(회수)</th>
		<td><input name="parcel_num" class="form-control" placeholder="회수용 송장번호(숫자만 입력)" value="<?=$row->parcel_num?>" autocomplete="off"></td>
	</tr>
	<tr>
		<th>배송 메시지(회수)</th>
		<td><input name="parcel_memo" class="form-control" placeholder="배송 메시지" value="<?=$row->parcel_memo?>" autocomplete="off"></td>
	</tr-->

	<tr>
		<th>주소(출고)</th>
		<td>
			<div class="input-group">
				<div class="input-group-btn">
					<button class="btn btn-default" type="button" onclick="searchAddr('customer_addr_return','customer_zipcode_return');"><i class="glyphicon glyphicon-search"></i></button>
				</div>
				<input name="customer_addr_return" id="customer_addr_return" class="form-control" value="<?=$row->customer_addr_return?>" placeholder="주소 입력" autocomplete="off">
			</div>
		</td>
	</tr>
	<tr>
		<th>상세주소(출고)</th>
		<td><input name="customer_addr_detail_return" class="form-control" value="<?=$row->customer_addr_detail_return?>" placeholder="상세주소 입력" autocomplete="off"></td>
	</tr>
	<tr>
		<th>우편번호(출고)</th>
		<td><input name="customer_zipcode_return" id="customer_zipcode_return" class="form-control" style="width:170px;" value="<?if($row->customer_zipcode_return!=null){echo sprintf("%05d", $row->customer_zipcode_return);}?>" placeholder="우편번호" autocomplete="off"></td>
	</tr>
	<tr>
		<th>송장번호(출고)</th>
		<td><input name="parcel_num_return" class="form-control" placeholder="출고용 송장번호(숫자만 입력)" value="<?=$row->parcel_num_return?>" autocomplete="off"></td>
	</tr>
	<tr>
		<th>배송 메시지(출고)</th>
		<td><input name="parcel_memo_return" class="form-control" placeholder="배송 메시지" value="<?=$row->parcel_memo_return?>" autocomplete="off"></td>
	</tr>

	</tbody>	
	</table>


	
	</form>

	<table class="table">
		<tr>
			<td class="text-center">
			<a href="javascript:;" class="btn btn-primary" onClick="sendit();" <?if(($PERMISSION & PERMISSION_CS)!=PERMISSION_CS) { echo 'disabled';}?> >등록</a>
			<? if ($mode==edit) { 
				if ($menu==S_AS_REPORT) {
			?>
				<a href="online_as_report.php" class="btn btn-default" >목록</a>
			 <? } 
				else if ($menu==S_AS_SHIPMENT ) //20230707 목록 추가 
				{
			 ?>		
					<a href="online_as_shipment.php" class="btn btn-default" >목록</a>
			  <?}
				else 
				{ ?>	
				<a href="online_as.php?state=<?echo $row->process_state;?>" class="btn btn-default" >목록</a>
			 <? } ?>		
				<a href="javascript:;" class="btn btn-danger" style="margin-left:40px;" onclick="deleteit('<?echo $menu;?>')" <?if(($PERMISSION & PERMISSION_CS)!=PERMISSION_CS) { echo 'disabled';}?> >삭제</a> 
		 <? } ?>
			</td>
		</tr>
	</table>
	

	<!--table class="table">
		<tr>
			<td class="text-center">
			<a href="javascript:;" class="btn btn-primary" onClick="sendit();" <?//if(($PERMISSION & PERMISSION_CS)!=PERMISSION_CS) { echo 'disabled';}?> >등록</a>
			<? //if ($mode==edit) { 
				//if ($menu==S_AS_REPORT) {?>
				<a href="online_as_report.php" class="btn btn-default" >목록</a>
				<?// } else { ?>	
				<a href="online_as.php?state=<?//echo $row->process_state;?>" class="btn btn-default" >목록</a>
				<?// } ?>		
				<a href="javascript:;" class="btn btn-danger" style="margin-left:40px;" onclick="deleteit('<?//echo $menu;?>')" <?//if(($PERMISSION & PERMISSION_CS)!=PERMISSION_CS) { echo 'disabled';}?> >삭제</a> 
			<? //} ?>
			</td>
		</tr>
	</!table-->


<script type="text/javascript">
function sendit() {
	var form=document.tx_editor_form;
	
	if(form.customer_name.value=="") {
		alert("이름을 입력해 주세요.");
		form.customer_name.focus();
	} 
	else if(form.customer_ph1.value=="") {
		alert("전화번호를 입력해 주세요.");
		form.customer_ph1.focus();
	} 
	else if(form.customer_ph2.value=="") {
		alert("전화번호를 입력해 주세요.");
		form.customer_ph2.focus();
	} 
	else if(form.customer_ph3.value=="") {
		alert("전화번호를 입력해 주세요.");
		form.customer_ph3.focus();
	} 
	else if(form.customer_addr.value=="") {
		alert("회수지 주소를 입력해 주세요.");
		form.customer_addr.focus();
	} 
	else if(form.customer_addr_detail.value=="") {
		alert("회수지 상세주소를 입력해 주세요.");
		form.customer_addr_detail.focus();
	} 
	else if(form.customer_zipcode.value=="") {
		alert("회수지 우편번호를 입력해 주세요.");
		form.customer_zipcode.focus();
	} 
	else if(form.product_name.value=="" || form.product_name.value=="-- 모델명 입력 --") {
		alert("제품 모델명을 입력해 주세요.");
		form.product_name.focus();
	} 
	else if(form.customer_desc.value=="") {
		alert("불량 내용을 입력해 주세요.");
		form.customer_desc.focus();
	} 
	else if(typeof form.pic_name != "undefined" && form.pic_name.value=="") { //20220103
		alert("담당자명을 입력해 주세요.");
		form.pic_name.focus();
	}
	/*else if(form.broken_type.value=="") {
		alert("불량 유형을 선택해 주세요.");
		form.broken_type.focus();
	} */
	else {
		//20210105
		form.price.value=form.price.value.replace(/[^0-9]/g,""); 
		if (form.price.value=="") {
			form.price.value = 0;
		}

		//if (form.product_date.value=="") form.product_date.value = "2020-01-01";
		form.customer_phone.value = form.customer_ph1.value + form.customer_ph2.value + form.customer_ph3.value;
		
		if (form.customer_addr_return.value=="")		{form.customer_addr_return.value = form.customer_addr.value;}
		if (form.customer_addr_detail_return.value=="")	{form.customer_addr_detail_return.value = form.customer_addr_detail.value;}
		if (form.customer_zipcode_return.value=="")		{form.customer_zipcode_return.value = form.customer_zipcode.value;}

		if (form.admin_memo_etc.value != "") {
			form.admin_memo.value = form.admin_memo.value + '[ETC]' + (form.admin_memo_etc.value);
		}

		form.submit();
	}
}

function deleteit(menu) {
	
	if (menu==null)
		return;

	if (confirm("신청서를 삭제할까요?")) {
		var form=document.tx_editor_form;
	
		form.isdel.value = 'y';
		form.action = "online_as_edit_ok.php?from="+menu;
		form.submit();
	}
}

function sendEstimate(menu) {
	
	if (menu==null)
		return;

	if (confirm("견적서를 발행할까요?")) {
		var form=document.tx_editor_form;
		
		if(form.price.value=="0") {
		alert("견적금액이 0원입니다.");
		form.price.focus();
		return;
		} 
		form.process_state.value = 2;
		form.estimate.value = 'y';
		form.price.value=form.price.value.replace(/[^0-9]/g,""); 
		if (form.price.value=="") {
			form.price.value = 0;
		}

		//if (form.product_date.value=="") form.product_date.value = "2020-01-01";
		form.customer_phone.value = form.customer_ph1.value + form.customer_ph2.value + form.customer_ph3.value;
		
		if (form.customer_addr_return.value=="")		{form.customer_addr_return.value = form.customer_addr.value;}
		if (form.customer_addr_detail_return.value=="")	{form.customer_addr_detail_return.value = form.customer_addr_detail.value;}
		if (form.customer_zipcode_return.value=="")		{form.customer_zipcode_return.value = form.customer_zipcode.value;}

		if (form.admin_memo_etc.value != "") {
			form.admin_memo.value = form.admin_memo.value + '[ETC]' + (form.admin_memo_etc.value);
		}
		
		form.action = "online_as_edit_ok.php?from="+menu;
		form.submit();
	}
}

function checkSameAddr()
{
	var form=document.tx_editor_form;
	
	if (document.getElementById("check_same_addr").checked) {
		form.customer_addr_return.value = form.customer_addr.value;
		form.customer_addr_detail_return.value = form.customer_addr_detail.value;
		form.customer_zipcode_return.value = form.customer_zipcode.value;
	} 
	else {
		form.customer_addr_return.value = "";
		form.customer_addr_detail_return.value = "";
		form.customer_zipcode_return.value = "";
	}
	
}

function removeFile()
{
	var form=document.tx_editor_form;
	if (confirm(form.attached_name.value+' 을 삭제할까요?')) {
		form.attached_name.value = "";
		
		document.getElementById("img_link").innerHTML ="";
	}	
}

//20211029 
function changeProductName(obj)
{
	var idx = obj.selectedIndex;
	var product_name_cur_sel = obj.value;

	var arr_as_model = <?php echo json_encode($arr_as_model)?>; 
	var arr_fix_desc = <?php echo json_encode($arr_fix_desc)?>; 
	var product_name = <?php echo json_encode($product_name)?>; 
	var admin_memo = <?php echo json_encode($admin_memo)?>; 
	var fix_desc_unknown = <?php echo json_encode($arr_fix_desc_unknown)?>; 
	var existModel = 0;
	
	for(var i=0; i<arr_as_model.length; i++) 
	{
		for(var j=0; j<arr_as_model[i].length; j++) 
		{
			if (arr_as_model[i][j] == product_name_cur_sel)
			{	

				for(var k=0; k<arr_fix_desc.length; k++)
				{
					if (arr_fix_desc[k][0].indexOf(product_name_cur_sel) >= 0) 
					{
						existModel = 1;
						setadminfixdesc(k, arr_fix_desc, product_name, product_name_cur_sel, admin_memo);
						break;
					}
				}
				break;
			}
		}
	}
	
	if (existModel==0)
	{
		setadminfixdesc(fix_desc_unknown, arr_fix_desc, product_name, product_name_cur_sel, admin_memo);
	}

}

function setadminfixdesc(k, arr_fix_desc, product_name, product_name_cur_sel, admin_memo)
{
	$("#admin_fix_desc").empty();
	var fix_desc_unknown = <?php echo json_encode($arr_fix_desc_unknown)?>; 

	document.getElementById('admin_memo').value = "";

	for (var y=1; y<arr_fix_desc[k].length; y++) 
	{	
		if (k==fix_desc_unknown)
		{
			//alert('1');
			var desc1 = arr_fix_desc[k][y];
			var html = "";

			html += '<div class="checkbox" ><label>';
			html += '<input type="checkbox" value="' + desc1 + '" ';

			html += ' onclick="clickAdminMemo(this, \'' + desc1 + '\')"'; 
			if(product_name==product_name_cur_sel && admin_memo.indexOf(desc1) >= 0) 
			{
				html += " checked";	
			}
			html += ' >';
			html += desc1 + '</label></div>';
		
			$("#admin_fix_desc").append(html);
		}
		else{
			var desc1 = arr_fix_desc[k][y] + ' 수리';
			var desc2 = arr_fix_desc[k][y] + ' 교체';
			var html = "";
			//alert(arr_fix_desc[k][y]);
			html += "<tr><td style='background-color:white; text-align:left; width:150px; border:1px solid #dddddd; padding-left:5px;'>";
			html += arr_fix_desc[k][y].replace('(V)', '');
			html += "</td>";

			html += "<td style='background-color:white; text-align:left; border: 1px solid #dddddd; padding-left: 5px;'>";
			
			html += '<label><input type="checkbox" value="' + desc1 + '"';
			html += ' onclick="clickAdminMemo(this, \'' + desc1 + '\')"';
			if(product_name==product_name_cur_sel && admin_memo.indexOf(desc1) >= 0) 
			{
				html += " checked";	
			}
			html += ' >';
			html += '&nbsp;수리</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';

			html += '<label><input type="checkbox" value="' + desc2 + '"';
			html += ' onclick="clickAdminMemo(this, \'' + desc2 + '\')"';
			if(product_name==product_name_cur_sel && admin_memo.indexOf(desc2) >= 0) 
			{
				html += " checked";
			}
			html += ' >';
			html += '&nbsp;교체</label>';

			html += "</td>";
			html += "</tr>";
			$("#admin_fix_desc").append(html);
		}
	}

	if (product_name==product_name_cur_sel)
	{
		document.getElementById('admin_memo').value = admin_memo;
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

function clickAdminMemo(ckbox, val)
{
	//alert(val);
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
			//document.tx_editor_form.admin_memo.value = (ckbox.value+";") + str;
			document.tx_editor_form.admin_memo.value = (val+";") + str; //20211029 
		}else{
			//document.tx_editor_form.admin_memo.value = str + (ckbox.value+";");
			document.tx_editor_form.admin_memo.value = str + (val+";"); //20211029 
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

//20210113
function maxLengthCheck(object)
{
	if (object.value.length > object.maxLength)
	{
      object.value = object.value.slice(0, object.maxLength);
	}
}

//20220117
function clickPicName(val, targetName)
{
	document.getElementById(targetName).value = val;
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
</script>

<? include('../footer.php');?>