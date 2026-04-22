<?	//session_name("CW_AS");
	session_start();
	session_destroy();
	
	$_SESSION['ADMIN_USERID'] = "";
	$_SESSION['ADMIN_NAME'] = "";
	$_SESSION['PERMISSION'] = 0;
	$tools->javaGo('index.php');
?>