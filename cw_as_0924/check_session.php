<?
	if(!isset($_SESSION['ADMIN_USERID']) || !isset($_SESSION['ADMIN_NAME'])) {
		$tools->alertJavaGo('경고! 잘못된 접근입니다\n\n로그인 하세요', $site_url.'/index.php');
	}

	if ($mod == M_AS) {
		if (($_SESSION['PERMISSION'] & PERMISSION_GROUP_CS) != PERMISSION_GROUP_CS) {
			$tools->alertJavaGo('경고! 잘못된 접근입니다\n', $site_url.'/index.php');
		}
	} else if ($mod == M_EVENT) {
		if (($_SESSION['PERMISSION'] & PERMISSION_GROUP_SALES) != PERMISSION_GROUP_SALES) {
			$tools->alertJavaGo('경고! 잘못된 접근입니다\n', $site_url.'/index.php');
		}
	} else if ($mod == M_SHIPMENT) {
		if (($_SESSION['PERMISSION'] & PERMISSION_GROUP_SHIPMENT) != PERMISSION_GROUP_SHIPMENT) {
			$tools->alertJavaGo('경고! 잘못된 접근입니다\n', $site_url.'/index.php');
		}
	} //20210219

	$ADMIN_USERID = $_SESSION['ADMIN_USERID'];
	$ADMIN_NAME = $_SESSION['ADMIN_NAME'];
	$PERMISSION = $_SESSION['PERMISSION'];

?>