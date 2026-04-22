<? 
include("./def_inc.php");
$mod	= M_SETTING;
$menu	= S_SETTING;
include('./header.php');

$db_name = "admin_account";


// 디비입력 쿼리
$sql="admin_passwd='$_POST[admin_passwd]',
admin_name='$_POST[admin_name]',
admin_phone='$_POST[admin_phone]',
admin_email='$_POST[admin_email]'";

$where="where admin_userid='$_POST[admin_userid]'";


// 디비입력
if( $db->cnt($db_name, $where))		{

		if( $db->update($db_name, $sql.$where) ) { 
			//session update
			$_SESSION['ADMIN_USERID']	= $ADMIN_USERID	= $_POST['admin_userid'];
			/*$_SESSION['ADMIN_PASSWD']	=*/ $ADMIN_PASSWD	= $_POST['admin_passwd'];
			$_SESSION['ADMIN_NAME']		= $ADMIN_NAME	= $_POST['admin_name'];
			
			//
			$tools->alertJavaGo("저장 되었습니다. 다시 로그인하세요.",  $_POST['return_url']); //20210118
		} else { 
			$tools->errMsg('저장 오류가 발생하였습니다. 관리자에게 문의하세요.'); 
		}

} else { 
	$tools->errMsg('일치하는 아이디 정보가 없습니다. 관리자에게 문의하세요.'); 
/*	
		if( $db->insert($db_name, $sql) )	{ 
			$tools->alertJavaGo("신규 계정으로 저장 완료 되었습니다.",  $_POST['return_url']);
		} else { 
			$tools->errMsg('비상적으로 입력 되었습니다.'); 
		}
*/
}
	

include('../footer.php');
?>