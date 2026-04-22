<?
include("../def_inc.php");
$mod	= M_SHIPMENT;
$menu	= S_ADMIN_DELIVERY;
include ("../header.php");


if (($PERMISSION & PERMISSION_ALL) != PERMISSION_ALL) {	
//	$tools->alertJavaGo('사용할 수 없습니다(1).', $site_url.'/main.php');
//	exit;
} 

$table = "delivery_package";

$return_url = "management_delivery_fee.php";

?>


<h4 class="page-header">출고 운임 관리</h4>



<!--CJ 박스타입별 운임-->
<?
$query = "select * from $table where type=0 order by idx asc";
$result = mysqli_query($db->db_conn, $query);
$row_cnt = mysqli_num_rows($result);
$arrBoxType = array();
?>

<div class="">
<div class="row">
<div class="col-sm-4">
<form action="management_delivery_fee_ok.php" method="post" name="boxsize_form" id="boxsize_form" ENCTYPE="multipart/form-data">
<table class="table table-bordered table-hover">
<input type="hidden" name="type" value="0">
<colgroup>
<col width="10%">
<col width="8%">
<col width="12%">
</colgroup>
<thead>
<tr>
	<th>박스타입 <font color="red">*</font></th>
	<th>표시 <font color="red">*</font></th>
	<th>기본운임 <font color="red">*</font></th>
</tr>
</thead>
<tbody>
<? 
	for($i=0;$i<$row_cnt+1;$i++)
	{
		$row = mysqli_fetch_array($result);
		
		array_push($arrBoxType, array($row['model_name'], $row['box_size'], $row['price']) );

	?>	
		<input type="hidden" name="is_new[]" value="<? if($row['idx']>0) {echo '0';} else{echo '1';}?>">
		<input type="hidden" name="idx[]" value="<?=$row['idx'];?>">
		<tr>
			<td class="text-center"><input type="text" class="form-control input-sm" name="model_name[]" value="<?=$row['model_name']?>" placeholder="박스타입"></td>
			<td class="text-center"><input type="text" class="form-control input-sm" name="box_size[]" value="<?=$row['box_size']?>" placeholder="표시"></td>
			<td class="text-center"><input type="text" class="form-control input-sm text-right" name="price[]" value="<?=$row['price']?>" placeholder="운임 가격"></td>
		</tr>
	<? 
	}
?>
</tbody>
</table>
<div class="text-center" style="" >
<button type="submit" class="btn btn-success btn-sm" onclick='return confirm_save();' <?if(($PERMISSION & PERMISSION_SHIPMENT)!=PERMISSION_SHIPMENT) { echo 'disabled';}?> >저장</button>

</div>
</form>
</div>






<!--CJ 모델별 운임단가-->
<?
$query = "select * from $table where type=1 order by idx asc";
$result = mysqli_query($db->db_conn, $query);
$row_cnt = mysqli_num_rows($result);
//echo $row_cnt;
?>
<div class="col-sm-8">
<form action="management_delivery_fee_ok.php" method="post" name="list_form" id="list_form" ENCTYPE="multipart/form-data">
<input type="hidden" name="type" value="1">
<input type="hidden" name="index" value="">
<input type="hidden" name="is_new" value="">
<div class="table-responsive">
<table class="table table-bordered table-hover" style="table-layout:fixed; "> 
<colgroup>
<col width="6%">
<col width="*">
<col width="16%">
<col width="12%">
<col width="12%">
</colgroup>
<thead>
<tr>
    <th>N O</th>
    <th>모델명<font color="red">*</font></th>
    <th>박스타입<font color="red">*</font></th>
	<th>기본운임</th>
	<th>-</th>
</tr>
</thead>
<tbody>
<?
	for($i=0; $i<$row_cnt+1; $i++)
	{
		$row = mysqli_fetch_array($result);
?>
    <tr>
        <td class="text-center"><? echo $i+1 ?><input type="hidden" name="idx[]" value="<?=$row['idx'];?>"></td>
        <td class="text-center">
			<input type="text" class="form-control input-sm" name="list_model_name[]" value="<?=$row['model_name']?>" placeholder="모델명 입력" <?if($row['model_name']!="") {echo 'readonly';}?> >
		</td>
        <td class="text-center">
			<select name="sel_box_size[]" class="form-control input-sm" onchange="onchangeBoxType(0, <?echo $i?>)">
				<?
				for($k=0; $k<count($arrBoxType); $k++) {
					if ($arrBoxType[$k][1] > 0) {
				?>
					<option value="<?=$arrBoxType[$k][1]?>" <?if($row['box_size']==$arrBoxType[$k][1]) { echo 'selected'; $price=$arrBoxType[$k][2]; }?> ><?echo $arrBoxType[$k][0]."(".$arrBoxType[$k][1].")";?></option>
				<?
					}
				}?>
			</select>
		</td>
        <td class="text-center">
			<input type="text" class="form-control input-sm text-right" name="list_price[]" value="<?=$price?>" placeholder="운임단가" readonly>
		</td>
		<td class="text-center">
			<button type="button" onclick="sendit('<?echo $i;?>', '<?echo $row['idx'];?>')" class="btn btn-success btn-xs" <?if(($PERMISSION & PERMISSION_SHIPMENT)!=PERMISSION_SHIPMENT) { echo 'disabled';}?> ><?if($row['model_name']=="") {echo '추가';}else{echo '수정';}?></button>
			<button type="button" onclick="deleteit('<?echo $i;?>', '<?echo $row['idx'];?>')" class="btn btn-danger btn-xs" <?if(($PERMISSION & PERMISSION_SHIPMENT)!=PERMISSION_SHIPMENT) { echo 'disabled';}?> <?if($row['model_name']=="") {echo 'disabled'; } ?> >삭제</button>
		</td>
    </tr>
<?
   }
?>

</tbody>
</table>
</div>
</form>
</div>
</div></div>

<br>






<script type="text/javascript">

function confirm_save()
{
	return confirm('저장할까요?');
}

function onchangeBoxType(index, idx)
{
	var arrBoxType = <?php echo json_encode($arrBoxType)?>; 

	var val_select = document.getElementsByName("sel_box_size[]");
	var sel_idx = val_select[idx].options.selectedIndex;
	//alert(arrBoxType[sel_idx][2]);

	var val_price = document.getElementsByName("list_price[]");
	val_price[idx].value = arrBoxType[sel_idx][2];

}

function sendit(index, idx)
{
	var val_model = document.getElementsByName("list_model_name[]");
	
	if (val_model[index].value == "")
	{
		alert('모델명을 입력하세요.');
		return;
	} 

	if (confirm("저장하시겠습니까?"))
	{
		var form=document.list_form;

		if (idx=="" || typeof idx=='undefined')
		{
			form.is_new.value = 1;
		}
		else 
		{
			form.is_new.value = 0;
		}

		form.index.value = index;
		form.submit();
	}
}

function deleteit(index, idx)
{
	if (confirm("삭제하시겠습니까?"))
	{
		var form=document.list_form;

		form.is_new.value = 2;
		form.index.value = index;

		form.submit();
	}
}
</script>

<?
include ("../footer.php");
?>