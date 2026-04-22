<? 
include("../def_inc.php");
$mod	= M_SETTING;
$menu	= S_SETTING;
include('../header.php');

$db_name = "admin_account";


// 디비입력 쿼리
$sql="admin_name='$_POST[admin_name]',
admin_phone='$_POST[admin_phone]',
admin_email='$_POST[admin_email]',
permission='$_POST[permission]' ";

$where="where admin_userid='$_POST[admin_userid]'";


if( $db->cnt($db_name, $where))
{
	if( $db->update($db_name, $sql.$where) ) 
	{ 
		$tools->alertJavaGo("저장 되었습니다.",  $_POST['return_url']); //20210118
	} 
	else 
	{ 
		$tools->errMsg('저장 오류가 발생하였습니다. 관리자에게 문의하세요.'); 
	}
} 
else 
{ 
	$tools->errMsg('일치하는 아이디 정보가 없습니다. 관리자에게 문의하세요.'); 
}
	

include('../footer.php');
?>