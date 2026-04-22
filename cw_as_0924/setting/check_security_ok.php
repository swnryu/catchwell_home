<?
session_start();

//error_reporting(E_ALL);
//ini_set('display_errors', false);
//ini_set('display_startup_errors', false);

include ("../common.php");
require ("../check_session.php");

$table		= "admin_account";


$return_url = isset($_POST['return_url']) ? $_POST['return_url'] : "../index.php";


//
$query="select * from admin_account where admin_userid='$_POST[admin_userid]' ";
if ($rs=mysqli_query($db->db_conn, $query)) 
{
	$row=mysqli_fetch_object($rs);
	
	if ( (!password_verify($_POST['admin_passwd'], $row->admin_passwd)) && ($_POST['admin_passwd'] != $row->admin_passwd) ) 
	{
		$tools->errMsg('현재 비밀번호가 일치하지 않습니다.'); 	
	}

}
else
{
	$tools->errMsg('아이디를 확인하세요.'); 	
}



//
$password = isset($_POST['admin_passwd_new1']) ? $_POST['admin_passwd_new1'] : "";
if ($password=="")
{
	$tools->errMsg('신규 비밀번호를 다시 확인하세요.'); 
}
$encrypted_password = password_hash($password, PASSWORD_DEFAULT);
//$sql="admin_passwd='$encrypted_password' ";

//$where = "where admin_userid='$_POST[admin_userid]' and admin_passwd='$_POST['admin_passwd']' ";


//
$query = "update $table set admin_passwd='$encrypted_password', pw_last_update=now() where admin_userid='$_POST[admin_userid]' ";
//echo $query."<br>";

//exit;

if (mysqli_query($db->db_conn, $query)) 
{
	$_SESSION['ADMIN_USERID'] = "";
	$_SESSION['ADMIN_NAME'] = "";
	$_SESSION['PERMISSION'] = 0;


	session_destroy();

	echo "<script language='javascript'> alert('변경 되었습니다. 다시 로그인하세요'); location.replace('$return_url'); </script>";
}
else
{
	$tools->errMsg('변경중 오류가 발생하였습니다. 관리자에게 문의하세요.'); 

}


?>