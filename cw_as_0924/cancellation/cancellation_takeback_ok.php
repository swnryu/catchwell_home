<?
/** Error reporting */
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

include("../def_inc.php");
include("../common_lib.php");

$mod	= M_CANCELLATION;	
$menu	= S_CANCELLATION_NEW;
include("../header.php");

$table_name	= "cancellation_order";
$return_url	= "cancellation_list.php";

$lib = new commonLib();

$idx = $_POST['idx']?$_POST['idx'] : 0;

if($idx==0){ //new

	$date = isset($_POST['date']) ? $_POST['date'] : date("Y-m-d");
	$model_name = isset($_POST['model_name']) ? $_POST['model_name'] : "";
	$order_id = isset($_POST['order_id']) ? $_POST['order_id'] : "";
	$customer_name = isset($_POST['customer_name']) ? $_POST['customer_name'] : "";
	$shopping_mall = isset($_POST['shopping_mall']) ? $_POST['shopping_mall'] : "";
	if ($shopping_mall=="" || $shopping_mall==NULL) {
		$shopping_mall = $_POST['shopping_mall2'];
	}
	
	$type = isset($_POST['type']) ? $_POST['type'] : "";
	$reason = isset($_POST['reason']) ? $_POST['reason'] : "";
	$serial = isset($_POST['serial']) ? $_POST['serial'] : "";
	$memo = isset($_POST['memo']) ? $_POST['memo'] : "";

	$address = isset($_POST['address']) ? $_POST['address'] : "";
	$phone = isset($_POST['phone']) ? $_POST['phone'] : "";
	$tracking = isset($_POST['tracking']) ? $_POST['tracking'] : "";

	$status = isset($_POST['status']) ? $_POST['status'] : 0;

	$date_completed = isset($_POST['date_completed']) ? $_POST['date_completed'] : date('Y-m-d');
	$result_type = isset($_POST['result_type']) ? $_POST['result_type'] : -1;
	$result_memo = isset($_POST['result_memo']) ? $_POST['result_memo'] : "";
	$admin_name = isset($_POST['admin_name']) ? $_POST['admin_name'] : "";

	$exchange_order = isset($_POST['exchange_order']) ? $_POST['exchange_order'] : "";

	if ($status==0) {
		$date_completed = date('Y-m-d');
		$result_type = -1;
	}

	if( $db->insert($table_name,
		"date='$date',
		model_name='$model_name',
		order_id='$order_id',
		customer_name='$customer_name',
		shopping_mall='$shopping_mall',
		address='$address',
		phone='$phone',
		tracking='$tracking',
		type='$type',
		reason='$reason',
		serial='$serial',
		memo='$memo',
		status=$status,
		date_completed='$date_completed',
		result_type=$result_type,
		result_memo='$result_memo',
		admin_name='$admin_name',
		exchange_order='$exchange_order'
		"
		)) 
	{
		//관리자 로그
		$db->insert("admin_log","userid='$_SESSION[ADMIN_USERID]', contents='new_cancellation', ip='$_SERVER[REMOTE_ADDR]', udate=now(), comment='$order_id'");

		$tools->alertJavaGo("등록 되었습니다.", "../shipment/shipment.php" );

	}
}
else {
	
	$date = isset($_POST['date']) ? $_POST['date'] : date("Y-m-d");
	$model_name = isset($_POST['model_name']) ? $_POST['model_name'] : "";
	$order_id = isset($_POST['order_id']) ? $_POST['order_id'] : "";
	$customer_name = isset($_POST['customer_name']) ? $_POST['customer_name'] : "";
	$shopping_mall = isset($_POST['shopping_mall']) ? $_POST['shopping_mall'] : "";
	$shopping_mall2 = isset($_POST['shopping_mall2']) ? $_POST['shopping_mall2'] : "";
	if ($shopping_mall=="" || $shopping_mall==NULL) {
		$shopping_mall = $shopping_mall2;
	}

	$type = isset($_POST['type']) ? $_POST['type'] : "";
	$reason = isset($_POST['reason']) ? $_POST['reason'] : "";
	$serial = isset($_POST['serial']) ? $_POST['serial'] : "";
	$memo = isset($_POST['memo']) ? $_POST['memo'] : "";

	$address = isset($_POST['address']) ? $_POST['address'] : "";
	$phone = isset($_POST['phone']) ? $_POST['phone'] : "";
	$tracking = isset($_POST['tracking']) ? $_POST['tracking'] : "";

	$status = isset($_POST['status']) ? $_POST['status'] : 0;

	$date_completed = isset($_POST['date_completed']) ? $_POST['date_completed'] : "";
	$result_type = isset($_POST['result_type']) ? $_POST['result_type'] : -1;
	$result_memo = isset($_POST['result_memo']) ? $_POST['result_memo'] : "";
	$admin_name = isset($_POST['admin_name']) ? $_POST['admin_name'] : "";

	$exchange_order = isset($_POST['exchange_order']) ? $_POST['exchange_order'] : "";

	$company_name = isset($_POST['company_name']) ? $_POST['company_name'] : "";
	$shipping_date = isset($_POST['shipping_date']) ? $_POST['shipping_date'] : NULL;

	if ($status==0) {
		$date_completed = date('Y-m-d');
		$result_type = -1;
	}

	if($_POST['isdel']=="y") { //remove
		if( $db->delete($table_name, "where idx=$_POST[idx]"))
		{
			//관리자 로그
			$db->insert("admin_log","userid='$_SESSION[ADMIN_USERID]', contents='del_cancellation', ip='$_SERVER[REMOTE_ADDR]', udate=now(), comment='$idx $customer_name $order_id'");

			$tools->alertJavaGo("삭제 되었습니다.", $return_url);
		}
	} 
	else { //update

		$data = "date='$date',
		model_name='$model_name',
		order_id='$order_id',
		customer_name='$customer_name',
		shopping_mall='$shopping_mall',
		address='$address',
		phone='$phone',
		tracking='$tracking',
		type='$type',
		reason='$reason',
		serial='$serial',
		memo='$memo',
		status=$status,
		date_completed='$date_completed',
		result_type=$result_type,
		result_memo='$result_memo',
		admin_name='$admin_name',
		exchange_order='$exchange_order' ";
		
		$data = $data . " where idx=$idx";

		if( $db->update($table_name, $data))
		{
			//관리자 로그
			$db->insert("admin_log","userid='$_SESSION[ADMIN_USERID]', contents='update_cancellation', ip='$_SERVER[REMOTE_ADDR]', udate=now(), comment='$order_id'");

			$tools->alertJavaGo("수정 되었습니다.", $return_url);
		}
	
	}
	
}


include('../footer.php');
?>