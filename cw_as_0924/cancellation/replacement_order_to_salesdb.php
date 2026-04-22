<?
	include ("../common.php");

	//$SALES_DB_NAME="cw_as_dev";
	$SALES_DB_NAME="cw_sales";
	$db_sales = new mysqli_dbConnect($DB_HOST, $SALES_DB_NAME, $DB_USER, $DB_PWD);

	$table = "B2B_LIST_ORDER";

	$idx = isset($_POST['idx']) ? $_POST['idx'] : ""; //cancellation_order.idx

	$model_name = isset($_POST['model_name']) ? $_POST['model_name'] : "";
	$model_acc = isset($_POST['model_acc']) ? $_POST['model_acc'] : "CS관리자";
	$order_mrket = isset($_POST['order_mrket']) ? $_POST['order_mrket'] : "";
	$order_no = isset($_POST['order_no']) ? $_POST['order_no'] : "";
	$sabang_no = isset($_POST['sabang_no']) ? $_POST['sabang_no'] : "";
	$company_name = isset($_POST['company_name']) ? $_POST['company_name'] : "CS교환"; //업체 온라인발주시스템의 ID: CS교환
	$receipt_name = isset($_POST['receipt_name']) ? $_POST['receipt_name'] : "";
	$receipt_mobile = isset($_POST['receipt_mobile']) ? $_POST['receipt_mobile'] : "";
	$receipt_address = isset($_POST['receipt_address']) ? $_POST['receipt_address'] : "";
	
	$model_name .= "_새상품 재출고";

	$data = "MODEL_NAME='$model_name',  
	MODEL_ACC='$model_acc',  
	MODEL_COUNT='1',  
	ORDER_MARKET='$order_mrket',  
	ORDER_NO='$order_no',
	COMPANY_NAME='$company_name',  
	RECEIPT_NAME='$receipt_name',
	INVOICE_NO='',
	RECEIPT_MOBILE='$receipt_mobile',  
	RECEIPT_TEL='',  
	RECEIPT_ADDRESS='$receipt_address',  
	MESSAGE='',  
	SABANG_NO='$sabang_no' ";
	
	if ($db_sales->insert($table, $data)) {
		
		$row = $db_sales->object($table, "where COMPANY_NAME='$company_name' and RECEIPT_NAME='$receipt_name' and RECEIPT_MOBILE='$receipt_mobile' and ORDER_NO='$order_no' order by REGISTER_NO desc limit 1 ");
		if ($row==NULL) {
			$result = array(
				'success' => 'fail'
			);
		} 
		else {

			if ($idx != "") {
				//cancellation_order 테이블 update 
				$db->update('cancellation_order', "exchange_order='$row->REGISTER_NO' where idx=$idx " );
			}

			$result = array(
				'success' => 'ok', 
				'REGISTER_NO' => $row->REGISTER_NO
			);
		}
	}
	else {
		$result = array(
			'success' => 'fail'
		);
	}

	echo json_encode($result, JSON_FORCE_OBJECT);
	
?>
