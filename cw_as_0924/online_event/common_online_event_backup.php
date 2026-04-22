<?
	$query = "insert into lab_online_event_backup set idx=$row->idx, date='$row->date', event_name='$row->event_name', homepage_id='$row->homepage_id',
	customer_name='$row->customer_name', customer_phone='$row->customer_phone', customer_zipcode='$row->customer_zipcode', customer_addr='$row->customer_addr', 
	customer_addr_detail='$row->customer_addr_detail', model_name='$row->model_name', market_name='$row->market_name', market_id='$row->market_id', 
	order_date='$row->order_date', order_id='$row->order_id', serial_no='$row->serial_no', file_name='$row->file_name', tracking_num='$row->tracking_num', 
	status=$row->status, gift='$row->gift', memo='$row->memo' "; 

	$result = mysqli_query($db->db_conn, $query);
	
?>