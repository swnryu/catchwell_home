<?
	$query = "insert into cs_online_event_backup set name='$row->name', hp='$row->hp', zip_new='$row->zip_new', add1='$row->add1', add2='$row->add2', 
    japum='$row->japum', gdate='$row->gdate', shoppingmall='$row->shoppingmall', id='$row->id', nickname='$row->nickname', oid='$row->oid', 
    content='$row->content', bbs_file='$row->file_name', udate='$row->udate', status=0"; 

	$result = mysqli_query($db->db_conn, $query);
	
?>