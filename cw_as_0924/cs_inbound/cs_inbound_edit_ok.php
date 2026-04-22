<?
/** Error reporting */
error_reporting(E_ALL);
ini_set('display_errors', false);
ini_set('display_startup_errors', false);

include("../def_inc.php");
include("../common_lib.php");

$mod	= M_INBOUND;
$menu	= S_INBOUND_EDIT;

include("../header.php");

$db_name	= "cs_inbound_call";

$return_url = isset($_POST['return_url']) ? $_POST['return_url'] : "cs_inbound_edit.php";
//echo $return_url;

$idx = isset($_POST['idx']) ? $_POST['idx'] : 0;

$reg_datetime = isset($_POST['reg_datetime']) ? $_POST['reg_datetime'] : date("Y-m-d hh:mm:ss");
$product_name = isset($_POST['product_name']) ? $_POST['product_name'] : "";
$inquiry_type = isset($_POST['inquiry_type']) ? $_POST['inquiry_type'] : 1;

$black_consumer = isset($_POST['black_consumer']) ? $_POST['black_consumer'] : 0;
$black_consumer_desc = isset($_POST['black_consumer_desc']) ? $_POST['black_consumer_desc'] : "";

$pic_name = isset($_POST['pic_name']) ? $_POST['pic_name'] : "";
$pic_memo = isset($_POST['pic_memo']) ? $_POST['pic_memo'] : "";

$customer_name = isset($_POST['customer_name']) ? $_POST['customer_name'] : "";
$customer_phone = isset($_POST['customer_phone']) ? $_POST['customer_phone'] : "";
$admin_result = isset($_POST['admin_result']) ? $_POST['admin_result'] : 0;
$admin_desc = isset($_POST['admin_desc']) ? $_POST['admin_desc'] : "";
$admin_name = isset($_POST['admin_name']) ? $_POST['admin_name'] : "";


if($_POST['mode']=="new")
{
	$data = sprintf("product_name='%s', inquiry_type=%d, black_consumer=%d, black_consumer_desc='%s', pic_name='%s', pic_memo='%s', customer_name='%s', customer_phone='%s', admin_result=%d, admin_desc='%s', admin_name='%s' ", 
					 $product_name, $inquiry_type, $black_consumer, $black_consumer_desc, $pic_name, $pic_memo, $customer_name, $customer_phone, $admin_result, $admin_desc, $admin_name );

	if( $db->insert($db_name, $data) ) 
	{
		//관리자 로그
		$db->insert("admin_log","userid='$_SESSION[ADMIN_USERID]', contents='cs_call_new', ip='$_SERVER[REMOTE_ADDR]', udate=now(), comment=''");

		$tools->alertJavaGo("등록 되었습니다.", $return_url);
	}
	else
	{
		$tools->errMsg("데이터베이스 에러 발생(1)");
		return;
	}
}
else if($_POST['mode']=="edit")
{
	$data = sprintf("product_name='%s', inquiry_type=%d, black_consumer=%d, black_consumer_desc='%s', pic_name='%s', pic_memo='%s', customer_name='%s', customer_phone='%s', admin_result=%d, admin_desc='%s', admin_name='%s' where idx=$idx", 
					 $product_name, $inquiry_type, $black_consumer, $black_consumer_desc, $pic_name, $pic_memo, $customer_name, $customer_phone, $admin_result, $admin_desc, $admin_name );

	if( $db->update($db_name, $data) ) 
	{
		//관리자 로그
		$db->insert("admin_log","userid='$_SESSION[ADMIN_USERID]', contents='cs_call_edit', ip='$_SERVER[REMOTE_ADDR]', udate=now(), comment='$idx $ADMIN_NAME'");

		$tools->alertJavaGo("수정 되었습니다.", $return_url);
	}
	else
	{
		$tools->errMsg("데이터베이스 에러 발생(2)");
		return;
	}
}
else if ($_POST['mode']=="del") 
{
	if ($idx==0)
	{
		$tools->errMsg("삭제할 데이터가 없습니다.");
		return;
	}

	if( $db->delete($db_name, "where idx=$idx"))
	{
		//관리자 로그
		$db->insert("admin_log","userid='$_SESSION[ADMIN_USERID]', contents='cs_call_del', ip='$_SERVER[REMOTE_ADDR]', udate=now(), comment='$idx $ADMIN_NAME'");
		$tools->alertJavaGo("삭제 되었습니다.", $return_url);
	}
	else
	{
		$tools->errMsg("데이터베이스 에러 발생(3)");
		return;
	}
} 
else if ($_POST['mode']=="delcallback") 
{
	if ($idx==0)
	{
		$tools->errMsg("삭제할 데이터가 없습니다.");
		return;
	}

	$data = sprintf("pic_memo='%s', customer_name='%s', customer_phone='%s', admin_result=%d, admin_desc='%s', admin_name='%s' where idx=$idx", 
					 "", "", "", 0, "", "" );

	if( $db->update($db_name, $data) ) 
	{
		//관리자 로그
		$db->insert("admin_log","userid='$_SESSION[ADMIN_USERID]', contents='cs_call_del_callback', ip='$_SERVER[REMOTE_ADDR]', udate=now(), comment='$idx $ADMIN_NAME'");

		$tools->alertJavaGo("삭제 되었습니다.", $return_url);
	}
	else
	{
		$tools->errMsg("데이터베이스 에러 발생(4)");
		return;
	}	
} 

include('../footer.php');
?>