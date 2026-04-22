<?
include("../def_inc.php");
$mod	= M_BANNER;
$menu	= S_BANNER;
include ("../header.php");

//파일업로드경로
$file_dir	 = "files/";
$file_name	 = "";


if( count(glob($file_dir.IMG_BANNER1.".*")) > 0) {
	$arr1 = glob($file_dir.IMG_BANNER1.".*");
	$arr2 = explode("/", $arr1[0]);
	$file_name = $arr2[1];
}

?>

	<h4 class="page-header">배너 이미지 설정</h4>

	<form action="banner_ok.php" method="post" name="banner_form" id="banner_form" ENCTYPE="multipart/form-data">
	<table class="table table-bordered">
	<colgroup>
	<col width="15%">
	<col width="*">
	</colgroup>
	<tbody>
	<tr>
		<th>배너 이미지 선택(1)</th>
		<td>
			<input type="file" name="banner_image" id="banner_image" accept=".png,.jpg,.bmp,.gif" />
			<a href="files/<?php echo $file_name; ?>" download><?php echo $file_name;?></a>
			<? if ($file_name=="") { ?> [ 권장사이즈 : OOO x OOO ] <? } ?>

			<? if ($file_name!="") {?>
			<div class="row"><br>
			<div class="col-md-4">
				<div class="thumbnail">
					<img src="<?echo $file_dir.$file_name?>" alt="Lights" style="width:100%">
				</div>
			</div>
			</div>
			<?}?>
		</td>
	</tr>
	</tbody>
	</table>
	</form>

	<table class="table">
		<tr>
			<td class="text-center"><a href="javascript:sendit();" class="btn btn-primary">저장하기</a></td>
		</tr>
	</table>



<script type="text/javascript">
function sendit() {
	var form=document.banner_form;
	
	if (form.banner_image.value == "") {
		alert("선택된 파일이 없습니다.");
	} 
	else {
		form.submit();
	}
}
</script>

<?
include ("../footer.php");
?>