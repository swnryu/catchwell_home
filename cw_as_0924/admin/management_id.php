<?
include("../def_inc.php");
$mod	= M_SETTING;
$menu	= S_ADMIN_ID;
include ("../header.php");


if (($PERMISSION & PERMISSION_ALL) != PERMISSION_ALL) {	
	$tools->alertJavaGo('사용할 수 없습니다(1).', $site_url.'/main.php');
	exit;
} 

$table = "admin_account";
$query = "";

$userid = isset($_GET['userid']) ? $_GET['userid'] : "";
$fn = isset($_GET['fn']) ? $_GET['fn'] : "";



//$query = "select * from $table order by idx, pw_last_update asc";
$query = "select * from $table order by idx, admin_name asc";
$result = mysqli_query($db->db_conn, $query);

$return_url = $_SERVER["REQUEST_URI"];//"management_id.php";

//echo $query;

?>


<h4 class="page-header">관리자 계정 관리</h4>




<form action="management_id.php" method="post" name="add_id_form" id="add_id_form" ENCTYPE="multipart/form-data">
<table class="table table-bordered table-hover" id="add_id" name="add_id" >
<colgroup>
<col width="12%">
<col width="12%">
<col width="12%">
<col width="12%">
<col width="20%">
<col width="18%">
<col width="8%">
</colgroup>
<thead>
<tr>
    <th>이름 <font color="red">*</font></th>
    <th>아이디 <font color="red">*</font></th>
	<th>비밀번호 <font color="red">*</font></th>
    <th>전화번호</th>
    <th>이메일</th>
    <th>권한 <font color="red">*</font></th>
	<th>계정추가</th>
</tr>
</thead>
<tbody>
<tr>
	<td class="text-center"><input type="text" class="form-control input-sm" name="admin_name" value="" placeholder="사용자 이름"></td>
	<td class="text-center"><input type="text" class="form-control input-sm" name="admin_userid" value="" placeholder="아이디 입력"></td>
	<td class="text-center"><input type="text" class="form-control input-sm" name="admin_passwd" value="" placeholder="영문/숫자 포함 8자이상"></td>
	<td class="text-center"><input type="text" class="form-control input-sm" name="admin_phone" value="" placeholder="전화번호 입력"></td>
	<td class="text-center"><input type="text" class="form-control input-sm" name="admin_email" value="" placeholder="이메일 입력"></td>
	<td class="text-left">
		<label class="checkbox-inline"><input type="checkbox" name="group_cs" id="group_cs" value="1" >CS</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<label class="checkbox-inline"><input type="checkbox" name="permission_cs" id="permission_cs" value="1" >쓰기</label><br>

		<label class="checkbox-inline"><input type="checkbox" name="group_sales" id="group_sales" value="2" >영업</label>&nbsp;&nbsp;&nbsp;
		<label class="checkbox-inline"><input type="checkbox" name="permission_sales" id="permission_sales" value="2" >쓰기</label><br>

		<label class="checkbox-inline"><input type="checkbox" name="group_shipment" id="group_shipment" value="4" >물류</label>&nbsp;&nbsp;&nbsp;
		<label class="checkbox-inline"><input type="checkbox" name="permission_shipment" id="permission_shipment" value="4" >쓰기</label><br>

		<label class="checkbox-inline"><input type="checkbox" name="group_lab" id="group_lab" value="8" >연구소</label>
		<label class="checkbox-inline"><input type="checkbox" name="permission_lab" id="permission_lab" value="8" >쓰기</label><br>
		
		<input type="hidden" class="form-control input-sm" name="permission" value="">
	</td>
	<td class="text-center"><a href="javascript:addit();" class="btn btn-success btn-sm" style="" >추가</a></td>
</tr>
</tbody>
</table>
</form>
<br>





<hr>
<form action="management_id_ok.php" method="post" name="list_form" id="list_form" ENCTYPE="multipart/form-data">
<input type="hidden" name="userid" value="" />
<input type="hidden" name="fn" value="" />
<div class="table-responsive">
<table class="table table-bordered table-hover" style="table-layout:fixed">
<colgroup>
<col width="4%">
<col width="10%">
<col width="12%">
<col width="12%">
<col width="12%">
<col width="15%">
<col width="6%">
<col width="10%">
<col width="6%">
<col width="8%">
<col width="6%">
</colgroup>
<thead>
<tr>
    <th>N O</th>
    <th>이름</th>
    <th>아이디</th>
	<th>비밀번호</th>
    <th>전화번호</th>
    <th>이메일</th>
	<th>권한</th>
	<th>비번 업데이트</th>
    <th>수정</th>
    <th>비번 초기화</th>
    <th>삭제</th>
</tr>
</thead>
<tbody>
<?
	while($row = mysqli_fetch_array($result)) {
?>
        <tr <?if($row['permission']==0) {echo "style='background-color:#eee;color:#aaa;'";}?> >
        <td class="text-center"><? echo $row['idx'] ?></td>
        <td class="text-center" style="color:blue;"><? echo $row['admin_name'] ?> <? if($row['permission']==PERMISSION_ALL) {?><span class="label label-warning">SU</span><?}?> </td>
        <td class="text-center"><? echo $row['admin_userid'] ?></td>
		<td class="text-center" style="white-space:nowrap;  text-overflow:ellipsis; overflow:hidden"><? echo $tools->setMasking("right4", $row['admin_passwd']); ?></td>
        <td class="text-center"><? echo $row['admin_phone'] ?></td>
        <td class="text-center"><? echo $row['admin_email'] ?></td>
		<td class="text-center"><? echo $row['permission'] ?></td>
		<td class="text-center"><? if ($row['pw_last_update']!=NULL){ echo date('Y-m-d', strtotime($row['pw_last_update'])); } ?></td>

		<td class="text-center"><a href="../setting/setting.php?userid=<? echo $row['admin_userid']."&return_url=".urlencode($return_url); ?>" class="btn btn-default btn-sm" <?if($ADMIN_USERID==$row['admin_userid'] && $row['permission']==255) {echo 'disabled';}?>>수정</a></td>
		<td class="text-center"><a href="javascript:defaultpw('<? echo $row['admin_userid']; ?>');" class="btn btn-warning btn-sm" <?if($ADMIN_USERID==$row['admin_userid'] && $row['permission']==255) {echo 'disabled';}?>>초기화</a></td>
		<td class="text-center"><a href="javascript:deleteit('<? echo $row['admin_userid']; ?>');" class="btn btn-danger btn-sm" <?if($ADMIN_USERID==$row['admin_userid'] && $row['permission']==255) {echo 'disabled';}?>>삭제</a></td>
        </tr>

<?
   }
?>
</tbody>
</table>
</div>
</form>
<br>





<script type="text/javascript">

function defaultpw(userid) 
{
	if (confirm("비밀번호를 초기화 하시겠습니까?") == true)
	{
		document.list_form.userid.value = userid;
		document.list_form.fn.value = "default";
		document.list_form.action = "management_id_ok.php";
		document.list_form.submit();
	}
}

function deleteit(userid) 
{
	if (confirm(userid + " 계정을 삭제하시겠습니까?\n삭제된 계정은 복구할 수 없습니다.") == true)
	{
		document.list_form.userid.value = userid;
		document.list_form.fn.value = "delete";
		document.list_form.action = "management_id_ok.php";
		document.list_form.submit();
	}
}

function addit()
{
	if (confirm("계정을 추가하시겠습니까?") == true)
	{
		

		document.list_form.userid.value = "";
		document.list_form.fn.value = "";

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

		document.add_id_form.permission.value = val_group + val_permission;
		document.add_id_form.action = "management_id_ok.php";
		document.add_id_form.submit();
	}
}

</script>

<?
include ("../footer.php");
?>