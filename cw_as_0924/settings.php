<?
include("./def_inc.php");
$mod	= M_SETTING;
$menu	= S_SETTING;
include ("./header.php");

	$table		= "admin_account";
	$query		= "select * from $table where admin_userid='$ADMIN_USERID' LIMIT 1";
	
	$result=mysqli_query($db->db_conn, $query);
	$row = mysqli_fetch_array($result);

?>

	<h4 class="page-header">관리자 기본 정보</h4>

	<form action="settings_ok.php" method="post" name="admin_form" id="admin_form" ENCTYPE="multipart/form-data">
	<!--<input type="hidden" name="mode" value="admin" />-->
	<input type="hidden" name="admin_userid" value="<?=$row[admin_userid];?>" />
	<input type="hidden" name="return_url" value="<?=$_SERVER["REQUEST_URI"]; ?>">
	<table class="table table-bordered">
	<colgroup>
	<col width="15%">
	<col width="35%">
	<col width="15%">
	<col width="35%">
	</colgroup>
	<tbody>
	<tr>
		<th>관리자 아이디*</td>
		<td><?echo $row[admin_userid]?></td>
		<th>관리자 비밀번호*</td>
		<td>
			<input name="admin_passwd" type="password" maxlength="30" class="form-control col-md-10" value="<?=$row[admin_passwd];?>">
			<?if($row[admin_passwd]=="admin"){?><br><br>
				<font color="red">※ 비밀번호를 변경해주시기 바랍니다.(영문/숫자 6자리 이상)</font><!--20210118-->
			<?}?>
		</td>
	</tr>
	<tr>
		<th>이 름*</th>
		<td><input type="text" name="admin_name" class="form-control col-md-10" value="<?=$row[admin_name];?>"></td>
		<th>전화번호
		</th><td><input type="text" name="admin_phone" class="form-control col-md-10" placeholder="- 없이 숫자만 입력" value="<?=$row[admin_phone];?>"></td>
	</tr>
	<tr>
		<th>이메일</th>
		<td colspan="3"><input name="admin_email" type="text" maxlength="200" class="form-control" value="<?=$row[admin_email];?>"></td>
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

	if (admin_form.admin_name.value == "") {
		alert("관리자 이름을 입력해 주세요.");
		admin_form.admin_name.focus();
	}
	else if (admin_form.admin_passwd.value == "") {
		alert("관리자 비밀번호를 입력해 주세요.");
		admin_form.admin_passwd.focus();
	}
/*
	//20210118
	else if (admin_form.admin_passwd.value == admin_form.admin_userid.value) {
		alert("사용할 수 없는 비밀번호입니다.");
		admin_form.admin_passwd.focus();
	}
	else if (admin_form.admin_passwd.value.length < 6) {
		alert("영문/숫자 6자리 이상으로 입력해 주세요.");
		admin_form.admin_passwd.focus();
	}
	else if (	(admin_form.admin_passwd.value.includes("123456")) || 
				(admin_form.admin_passwd.value.includes("000")) || 
				(admin_form.admin_passwd.value.includes("111")) || 
				(admin_form.admin_passwd.value.includes("222")) || 
				(admin_form.admin_passwd.value.includes("333")) || 
				(admin_form.admin_passwd.value.includes("444")) || 
				(admin_form.admin_passwd.value.includes("555")) || 
				(admin_form.admin_passwd.value.includes("666")) || 
				(admin_form.admin_passwd.value.includes("777")) || 
				(admin_form.admin_passwd.value.includes("888")) || 
				(admin_form.admin_passwd.value.includes('999')) 
			) 
	{
		alert("사용할 수 없는 비밀번호입니다.");
		admin_form.admin_passwd.focus();
	}
*/	
	else {
		ans = confirm("저장하시겠습니까?");
		if(ans==true){
			admin_form.submit();
		}
	}
}

</script>

<?
include ("./footer.php");
?>