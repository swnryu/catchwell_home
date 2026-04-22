<?
/** Error reporting */
error_reporting(E_ALL);
ini_set('display_errors', false);
ini_set('display_startup_errors', false);

include("../def_inc.php");
include("../common_lib.php");

$mod	= M_INBOUND;
$menu	= S_PARTS_EDIT;

include("../header.php");

$db_name	= "cs_shipping_parts";

$return_url = isset($_POST['return_url']) ? $_POST['return_url'] : "cs_parts_edit.php";
//echo $return_url;

$idx = isset($_POST['idx']) ? $_POST['idx'] : 0;

$reg_datetime = isset($_POST['reg_datetime']) ? $_POST['reg_datetime'] : date("Y-m-d hh:mm:ss");
$product_name = isset($_POST['product_name']) ? $_POST['product_name'] : "";
$parts_name = isset($_POST['parts_name_org']) ? $_POST['parts_name_org'] : "";		//20211213
$parts_name_ex = isset($_POST['parts_name_ex']) ? $_POST['parts_name_ex'] : "";		//20211213

$parts_count = isset($_POST['parts_count']) ? $_POST['parts_count'] : 0;
$parts_price = isset($_POST['parts_price']) ? $_POST['parts_price'] : 0;
$status = isset($_POST['status']) ? $_POST['status'] : 0;

$reason = isset($_POST['reason']) ? $_POST['reason'] : "";
$pic_memo = isset($_POST['pic_memo']) ? $_POST['pic_memo'] : "";
$pic_name = isset($_POST['pic_name']) ? $_POST['pic_name'] : "";

$customer_name = isset($_POST['customer_name']) ? $_POST['customer_name'] : "";
$customer_phone = isset($_POST['customer_phone']) ? $_POST['customer_phone'] : "";
$customer_addr = isset($_POST['customer_addr']) ? $_POST['customer_addr'] : "";
$customer_addr_detail = isset($_POST['customer_addr_detail']) ? $_POST['customer_addr_detail'] : "";
$customer_zipcode = isset($_POST['customer_zipcode']) ? $_POST['customer_zipcode'] : "";
$delivery_memo = isset($_POST['delivery_memo']) ? $_POST['delivery_memo'] : "";
$delivery_num = isset($_POST['delivery_num']) ? $_POST['delivery_num'] : "";

$mode = isset($_POST['mode']) ? $_POST['mode'] : "new";


if($mode=="new")
{
	$data = sprintf("product_name='%s', parts_name='%s', parts_name_ex='%s', parts_count=%d, parts_price=%d, status=%d, 
	reason='%s', pic_name='%s', pic_memo='%s', customer_name='%s', customer_phone='%s', 
	customer_addr='%s', customer_addr_detail='%s', customer_zipcode='%s', delivery_memo='%s', delivery_num='%s' ", 
	$product_name, $parts_name, $parts_name_ex, $parts_count, $parts_price, $status, 
	$reason, $pic_name, $pic_memo, $customer_name, $customer_phone, 
	$customer_addr, $customer_addr_detail, $customer_zipcode, $delivery_memo, $delivery_num );

	if( $db->insert($db_name, $data) ) 
	{
		//관리자 로그
		$db->insert("admin_log","userid='$_SESSION[ADMIN_USERID]', contents='cs_parts_new', ip='$_SERVER[REMOTE_ADDR]', udate=now(), comment=''");

		$tools->alertJavaGo("등록 되었습니다.", $return_url);
	}
	else
	{
		$tools->errMsg("데이터베이스 에러 발생(1)");
		return;
	}
}
else if($mode=="edit")
{
	$data = sprintf("product_name='%s', parts_name='%s', parts_name_ex='%s', parts_count=%d, parts_price=%d, status=%d, 
	reason='%s', pic_name='%s', pic_memo='%s', customer_name='%s', customer_phone='%s', 
	customer_addr='%s', customer_addr_detail='%s', customer_zipcode='%s', delivery_memo='%s', delivery_num='%s' where idx=$idx", 
	$product_name, $parts_name, $parts_name_ex, $parts_count, $parts_price, $status, 
	$reason, $pic_name, $pic_memo, $customer_name, $customer_phone, 
	$customer_addr, $customer_addr_detail, $customer_zipcode, $delivery_memo, $delivery_num );
	
	if( $db->update($db_name, $data) ) 
	{
		//관리자 로그
		$db->insert("admin_log","userid='$_SESSION[ADMIN_USERID]', contents='cs_parts_edit', ip='$_SERVER[REMOTE_ADDR]', udate=now(), comment='$idx $ADMIN_NAME'");

		$tools->alertJavaGo("수정 되었습니다.", $return_url);
	}
	else
	{
		$tools->errMsg("데이터베이스 에러 발생(2)");
		return;
	}
	
}
else if ($mode=="del") 
{
	if ($idx==0)
	{
		$tools->errMsg("삭제할 데이터가 없습니다.");
		return;
	}

	if( $db->delete($db_name, "where idx=$idx"))
	{
		//관리자 로그
		$db->insert("admin_log","userid='$_SESSION[ADMIN_USERID]', contents='cs_parts_del', ip='$_SERVER[REMOTE_ADDR]', udate=now(), comment='$idx $ADMIN_NAME'");
		$tools->alertJavaGo("삭제 되었습니다.", $return_url);
	}
	else
	{
		$tools->errMsg("데이터베이스 에러 발생(3)");
		return;
	}

} 


include('../footer.php');
?>