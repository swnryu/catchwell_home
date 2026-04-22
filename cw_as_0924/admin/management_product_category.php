<?
include("../def_inc.php");
$mod	= M_SETTING;
$menu	= S_ADMIN_PRODUCT;
include ("../header.php");


if (($PERMISSION & PERMISSION_ALL) != PERMISSION_ALL) {	
	$tools->alertJavaGo('사용할 수 없습니다(1).', $site_url.'/main.php');
	exit;
} 




?>


<h4 class="page-header">제품 카테고리 관리</h4>


<?

$table = "product_category";

$query = "select * from $table order by idx asc";
$result = mysqli_query($db->db_conn, $query);
$row_cnt = mysqli_num_rows($result);

?>


<form action="management_product_category_ok.php" method="post" name="product_form" id="product_form" ENCTYPE="multipart/form-data">
<table class="table table-bordered table-hover">
<input type="hidden" name="is_new" value="">
<input type="hidden" name="index" value="">
<colgroup>
<col width="3%">
<col width="12%">
<col width="*">
<col width="*">
<col width="10%">
</colgroup>
<thead>
<tr>
	<th>NO</th>
	<th>카테고리명 <font color="red">*</font></th>
	<th>반품 모델명 (세미콜론으로 모델명 구분) <font color="red">*</font></th>
	<th>A/S 모델명 (세미콜론으로 모델명 구분) <font color="red">*</font></th>
	<th>-</th>
</tr>
</thead>
<tbody>
<? 
	for($i=0;$i<$row_cnt+1;$i++)
	{
		$row = mysqli_fetch_array($result);
	?>	
		<tr>
			<td class="text-center">
				<?=$row['idx']?> 
				<input type="hidden" name="idx[]" value="<?=$row['idx'];?>"> 
			</td>
			<td class="text-center"><input type="text" class="form-control input-sm" name="category_name[]" value="<?=$row['category_name']?>" placeholder="카테고리명 입력"></td>
			<td class="text-center"><textarea class="form-control input-md" row="5" name="model_name[]" autocomplete="off" placeholder="모델명 입력"><?=$row['model_name']?></textarea></td>
			<td class="text-center"><textarea class="form-control input-md" row="5" name="model_name_as[]" autocomplete="off" placeholder="모델명 입력"><?=$row['model_name_as']?></textarea></td>
			<td class="text-center">
				<button type="button" onclick="sendit('<?echo $i;?>', '<?echo $row['idx'];?>')" class="btn btn-success btn-sm" ><?if($row['model_name']=="") {echo '추가';}else{echo '수정';}?></button>
				<button type="button" onclick="deleteit('<?echo $i;?>', '<?echo $row['idx'];?>')" class="btn btn-danger btn-sm" <?if($row['model_name']=="") {echo 'disabled'; } ?> >삭제</button>
			</td>
 		</tr>
		
	<? 
	}
?>
</tbody>
</table>
</form>

<br>


<script type="text/javascript">

function sendit(index, idx)
{
	var val_category = document.getElementsByName("category_name[]");
	if (val_category[index].value == "")
	{
		alert('카테고리명을 입력하세요.');
		return;
	} 

	var val_model = document.getElementsByName("model_name[]");
	if (val_model[index].value == "")
	{
		alert('모델명을 입력하세요.');
		return;
	} 

	if (confirm("저장하시겠습니까?"))
	{
		var form=document.product_form;

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
		var form=document.product_form;

		form.is_new.value = 2;
		form.index.value = index;

		form.submit();
	}
}
</script>

<?
include ("../footer.php");
?>