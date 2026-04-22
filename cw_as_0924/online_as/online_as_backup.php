<?
	//move to backup table
	if ($row->price===NULL) $row->price = 0;
	$query = "insert into as_parcel_service_backup 
		(reg_num, process_state, customer_name, customer_phone, product_type, product_name, product_date, broken_type, customer_desc, customer_addr, customer_addr_detail,
		customer_zipcode, parcel_num, parcel_memo, customer_addr_return, customer_addr_detail_return, customer_zipcode_return, parcel_num_return, parcel_memo_return, admin_memo,
		price, admin_desc, pic_name) values ('$row->reg_num', $row->process_state, '$row->customer_name', '$row->customer_phone', '$row->product_type', '$row->product_name', '$row->product_date', 
		'$row->broken_type', '$row->customer_desc', '$row->customer_addr', '$row->customer_addr_detail', $row->customer_zipcode, '$row->parcel_num', '$row->parcel_memo', 
		'$row->customer_addr_return', '$row->customer_addr_detail_return', $row->customer_zipcode_return, '$row->parcel_num_return', '$row->parcel_memo_return', '$row->admin_memo', 
		$row->price, '$row->admin_desc', '$row->pic_name')";

	$result = mysqli_query($db->db_conn, $query);
	
?>
