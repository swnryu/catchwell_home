<?
	include ("../common.php");

	$table = "shipping_date_new";
	
	$order_id = isset($_GET['oid'])?$_GET['oid'] : "";

	$row = $db->object($table, "where orderid='$order_id' or orderid_sabangnet='$order_id' and status=1 order by idx desc limit 1 ");

	if ($row==NULL) {
		$result = array(
			'success' => 'fail'
		);
	} 
	else {
		$result = array(
			'success' => 'ok', 
			'name' => $row->name,
			'mall' => $row->mall,
			'oid_sabangnet' => $row->orderid_sabangnet,
			'model' => $row->model,
			'phone1' => $row->phone1,
			'phone2' => $row->phone2,
			'address' => $row->address,
			'tracking' => $row->tracking
		);
	
	}

	echo json_encode($result, JSON_FORCE_OBJECT);
?>
