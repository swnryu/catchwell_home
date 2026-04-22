<?
session_start();

include "./common.php";
include "def_inc.php";

if($_POST['login']==1){
	
	if ($_POST['admin_userid'] == "") 
	{
		$tools->errMsg('아이디를 입력하세요.');
		exit;
	}
	if ($_POST['admin_passwd'] == "") 
	{
		$tools->errMsg('비밀번호를 입력하세요.');
		exit;
	}

	$query="select * from admin_account where admin_userid='$_POST[admin_userid]' ";
	
	if ($rs=mysqli_query($db->db_conn, $query)) 
	{
		$row=mysqli_fetch_object($rs);
	

		if ( ($_POST['admin_passwd'] == $row->admin_passwd) || 
		     (password_verify($_POST['admin_passwd'], $row->admin_passwd)) )
		{
			if ($row->permission==0)
			{
				$tools->errMsg('사용할 수 없는 계정입니다.\n개인 아이디로 로그인하세요.');
				include "./admin_logout.php";
				exit;
			}

			$ADMIN_USERID			= $row->admin_userid;
			$ADMIN_NAME				= $row->admin_name;
			$PERMISSION				= $row->permission;

			$_SESSION['ADMIN_USERID']	= $ADMIN_USERID;
			$_SESSION['ADMIN_NAME']		= $ADMIN_NAME;
			$_SESSION['PERMISSION']		= $PERMISSION;


			if ($_POST['customCheck'] == 'on') {
				$customCheck = 1;
			} else {
				$customCheck = 0;
			}

			if ($_POST['customCheck']) {
				setcookie("customCheck", $customCheck, time() + (86400 * 30)); // 86400 = 1 day 
				setcookie("CW_AS_USERID", $ADMIN_USERID, time() + (86400 * 30)); // 86400 = 1 day 
				setcookie("CW_AS_USERPW", $ADMIN_PASSWD, time() + (86400 * 30)); // 86400 = 1 day 
			}
			else {
				//remove cookie
				setcookie("customCheck", "", time() - 3600);
				unset($_COOKIE['customCheck']);

				setcookie("CW_AS_USERID", "", time() - 3600);
				unset($_COOKIE['CW_AS_USERID']);

				setcookie("CW_AS_USERPW", "", time() - 3600);
				unset($_COOKIE['CW_AS_USERPW']);
			}

			//관리자 로그
			$db->insert("admin_log","userid='$_SESSION[ADMIN_USERID]', contents='login', ip='$_SERVER[REMOTE_ADDR]', udate=now()");

			/////////////////////////비번 변경 이력이 없거나, 비번 변경후 3개월 경과했거나, 아이디/비번이 같거나, default 비번이거나, 
			if ( ($row->admin_passwd===NULL || $_POST['admin_passwd']===$row->admin_userid."@catchwell.com") ) 
			{
				$tools->alertJavaGo('비밀번호가 초기화되었습니다. 비밀번호 변경 후 사용하세요.(1)','./setting/check_security.php'); exit;
			}
			if ( $row->admin_passwd===$row->admin_userid || password_verify($row->admin_userid, $row->admin_passwd) )
			{
				$tools->alertJavaGo('비밀번호를 변경해 주세요.(2)','./setting/check_security.php?forward='); exit;
			}

/*
			if ( ($row->pw_last_update===NULL || $row->pw_last_update==="") ) 
			{
				$tools->alertJavaGo('비밀번호를 변경해 주세요.(3)','./setting/check_security.php'); exit;
			}
			$date1 = date_create(date($row->pw_last_update));
			$date2 = date_create(date("Y-m-d"));
			$diff=date_diff($date1, $date2);
			$days = $diff->format("%a");
			if ($days > 90) 
			{
				$tools->alertJavaGo('비밀번호를 주기적으로 변경해 주세요.(4)','./setting/check_security.php'); exit;
			}
*/			/////////////////////////

			$tools->javaGo('./main.php');
		}
		else 
		{
			//remove cookie
			setcookie("customCheck", "", time() - 3600);
			unset($_COOKIE['customCheck']);

			setcookie("CW_AS_USERID", "", time() - 3600);
			unset($_COOKIE['CW_AS_USERID']);

			setcookie("CW_AS_USERPW", "", time() - 3600);
			unset($_COOKIE['CW_AS_USERPW']);

			$tools->errMsg('패스워드가 일치하지 않습니다.');
			
			include "./admin_logout.php";			
		}
	} 
	else 
	{
		$tools->errMsg('아이디를 확인하세요.');
	}

}
else if($_GET['logout']==1)
{
	include "./admin_logout.php";
}

?>