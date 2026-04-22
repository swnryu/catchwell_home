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
$default_pw = "";
$query = "";

$userid = isset($_POST['userid']) ? $_POST['userid'] : "";
$fn = isset($_POST['fn']) ? $_POST['fn'] : "";


if ($userid!="")
{
	if ($fn=='default')
	{
		$default_pw = $userid."@catchwell.com";
		$password = password_hash($default_pw, PASSWORD_DEFAULT);
		$query = "update $table set admin_passwd='$password', pw_last_update=NULL  where admin_userid='$userid' ";
		$result=mysqli_query($db->db_conn, $query);
		if ($result==false)
		{
			$tools->errMsg('데이터베이스 업데이트 오류가 발생하였습니다. 관리자에게 문의하세요.(1)'); 
		}
	}
	else if ($fn=='delete')
	{
		$query = "delete from $table where admin_userid='$userid' ";
		$result=mysqli_query($db->db_conn, $query);
		if ($result==false)
		{
			$tools->errMsg('데이터베이스 업데이트 오류가 발생하였습니다. 관리자에게 문의하세요.(2)'); 
		}

	}
}
else if (isset($_POST['admin_userid']))
{
	$admin_userid = isset($_POST['admin_userid']) ? $_POST['admin_userid'] : "";
	$admin_passwd = isset($_POST['admin_passwd']) ? $_POST['admin_passwd'] : "";
	$admin_name = isset($_POST['admin_name']) ? $_POST['admin_name'] : "";
	$admin_phone = isset($_POST['admin_phone']) ? $_POST['admin_phone'] : "";
	$admin_email = isset($_POST['admin_email']) ? $_POST['admin_email'] : "";
	$permission = isset($_POST['permission']) ? $_POST['permission'] : "";
	

	//idx
	$result = mysqli_query($db->db_conn, "SELECT MAX(idx) as idx FROM $table");
	$row = mysqli_fetch_array($result);
	$new_idx = $row['idx'] + 1;
	mysqli_free_result($result);

	//
	$password = password_hash($admin_passwd, PASSWORD_DEFAULT);

	//add
	$query = "insert into $table value($new_idx, '$admin_userid', '$password', '$admin_name', '$admin_phone', '$admin_email', $permission, now(), NULL )";
	$result = mysqli_query($db->db_conn, $query);

	if ($result==false)
	{
		$tools->errMsg('데이터베이스 업데이트 오류가 발생하였습니다. 관리자에게 문의하세요.(3)'); 
	}

}


$return_url = isset($_POST['return_url']) ? $_POST['return_url'] : "management_id.php";

$tools->alertJavaGo("완료 되었습니다.", $return_url);

include ("../footer.php");
?>