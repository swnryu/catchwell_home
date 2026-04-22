<?
include("../def_inc.php");
$mod	= isset($_GET['userid']) ? M_ADMIN : M_SETTING;
$menu	= isset($_GET['userid']) ? S_ADMIN_ID : S_SETTING;
include ("../header.php");

$admin_userid = isset($_GET['userid']) ? $_GET['userid'] : $ADMIN_USERID;
$return_url = isset($_GET['return_url']) ? urldecode($_GET['return_url']) : $_SERVER["REQUEST_URI"];

$table		= "admin_account";
$query		= "select * from $table where admin_userid='$admin_userid' LIMIT 1";

$result=mysqli_query($db->db_conn, $query);
$row = mysqli_fetch_array($result);

?>

	<h4 class="page-header">사용자 기본 정보</h4>

	<form action="setting_ok.php" method="post" name="admin_form" id="admin_form" ENCTYPE="multipart/form-data">
	<input type="hidden" name="admin_userid" value="<?=$row['admin_userid'];?>" />
	<input type="hidden" name="admin_name" value="<?=$row['admin_name'];?>">
	<input type="hidden" name="return_url" value="<?=$return_url;?>">
	<table class="table table-bordered" >
	<colgroup>
	<col width="15%">
	<col width="*">
	</colgroup>
	<tbody>
	<tr>
		<th>아이디 <font color="red">*</font></td>
		<td><?echo $row['admin_userid']?></td>
	</tr>
	<tr>
		<th>이 름 <font color="red">*</font></th>
		<td><?echo $row['admin_name'];?></td>
	</tr>
	<tr>
		<th>전화번호</th>
		<td><input type="text" name="admin_phone" class="form-control" placeholder="- 없이 숫자만 입력" value="<?=$row['admin_phone'];?>"></td>
	</tr>
	<tr>
		<th>E-mail</th>
		<td><input name="admin_email" type="text" class="form-control" placeholder="E-mail 주소 입력" value="<?=$row['admin_email'];?>"></td>
	</tr>
	<?
if (isset($_GET['userid']) && $PERMISSION==PERMISSION_ALL) {
?>	
	<tr>
		<th>권한 <font color="red">*</font></th>
		<td colspan="3">
			<label class="checkbox-inline"><input type="checkbox" name="group_cs" id="group_cs" value="1" <?if(($row['permission'] & PERMISSION_GROUP_CS)==PERMISSION_GROUP_CS) {echo 'checked';}?> >CS</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<label class="checkbox-inline"><input type="checkbox" name="permission_cs" id="permission_cs" value="1" <?if(($row['permission'] & PERMISSION_CS)==PERMISSION_CS) {echo 'checked';}?> >쓰기</label><br>

			<label class="checkbox-inline"><input type="checkbox" name="group_sales" id="group_sales" value="2" <?if(($row['permission'] & PERMISSION_GROUP_SALES)==PERMISSION_GROUP_SALES) {echo 'checked';}?> >영업</label>&nbsp;&nbsp;&nbsp;
			<label class="checkbox-inline"><input type="checkbox" name="permission_sales" id="permission_sales" value="2" <?if(($row['permission'] & PERMISSION_SALES)==PERMISSION_SALES) {echo 'checked';}?> >쓰기</label><br>

			<label class="checkbox-inline"><input type="checkbox" name="group_shipment" id="group_shipment" value="4" <?if(($row['permission'] & PERMISSION_GROUP_SHIPMENT)==PERMISSION_GROUP_SHIPMENT) {echo 'checked';}?> >물류</label>&nbsp;&nbsp;&nbsp;
			<label class="checkbox-inline"><input type="checkbox" name="permission_shipment" id="permission_shipment" value="4" <?if(($row['permission'] & PERMISSION_SHIPMENT)==PERMISSION_SHIPMENT) {echo 'checked';}?> >쓰기</label><br>

			<label class="checkbox-inline"><input type="checkbox" name="group_lab" id="group_lab" value="8" <?if(($row['permission'] & PERMISSION_GROUP_LAB)==PERMISSION_GROUP_LAB) {echo 'checked';}?> >연구소</label>
			<label class="checkbox-inline"><input type="checkbox" name="permission_lab" id="permission_lab" value="8" <?if(($row['permission'] & PERMISSION_LAB)==PERMISSION_LAB) {echo 'checked';}?> >쓰기</label><br>
			
			<input type="hidden" name="permission" class="form-control col-md-5" value="<?=$row['permission'];?>"> <?echo $row['permission'];?>
			
		</td>
	</tr>
<? } else { ?>	
	<input type="hidden" name="permission" class="form-control col-md-5" value="<?=$row['permission'];?>">
<? } ?>		
	</tbody>
	</table>
	</form>

	<table class="table">
		<tr>
			<td class="text-center">
				<a href="javascript:sendit();" class="btn btn-primary">저장하기</a>
<?
if (!isset($_GET['userid'])) {
?>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<a href="check_security.php" class="btn btn-warning">비밀번호 변경</a>
<? } else { ?>
				&nbsp;<a href="javascript:history.back();" class="btn btn-default">취소</a>
<? } ?>	
			</td>
		</tr>
	</table>

<script type="text/javascript">

function sendit() {

	if (admin_form.admin_name.value == "") {
		alert("사용자 이름을 확인해 주세요.");
		admin_form.admin_name.focus();
	}
	else if (admin_form.admin_userid.value == "") {
		alert("사용자 아이디를 확인해 주세요.");
		admin_form.admin_userid.focus();
	}
	else {
		ans = confirm("저장하시겠습니까?");
		if(ans==true)
		{
			if ( document.getElementById('group_cs') != null )
			{
				var group1 = document.getElementById('group_cs').checked;
				var group2 = document.getElementById('group_sales').checked;
				var group3 = document.getElementById('group_shipment').checked;
				var group4 = document.getElementById('group_lab').checked;
				var permission1 = document.getElementById('permission_cs').checked;
				var permission2 = document.getElementById('permission_sales').checked;
				var permission3 = document.getElementById('permission_shipment').checked;
				var permission4 = document.getElementById('permission_lab').checked;

				//group
				var val_group = 0;
				if (group1) {
					val_group += Number(document.getElementById('group_cs').value);
				}
				if (group2) {
					val_group += Number(document.getElementById('group_sales').value);
				}
				if (group3) {
					val_group += Number(document.getElementById('group_shipment').value);
				}
				if (group4) {
					val_group += Number(document.getElementById('group_lab').value);
				}
				val_group = val_group << 4;

				//permission
				var val_permission = 0;
				if (permission1) {
					val_permission += Number(document.getElementById('permission_cs').value);
				}
				if (permission2) {
					val_permission += Number(document.getElementById('permission_sales').value);
				}
				if (permission3) {
					val_permission += Number(document.getElementById('permission_shipment').value);
				}
				if (permission4) {
					val_permission += Number(document.getElementById('permission_lab').value);
				}
				admin_form.permission.value = val_group + val_permission;
			}

			admin_form.submit();
		}
	}
}

</script>

<?
include ("../footer.php");
?>