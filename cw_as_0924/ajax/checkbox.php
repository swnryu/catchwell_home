<?
include('../header.php'); 

$dbname	= $_POST['dbname'];
$name	= $_POST['name'];
$idx	= $_POST['idx'];
$val	= $_POST['val'];

/***********************************************************************************************************/

if($name=="delete"){
	
	for($i=0;$i<count($idx);$i++) {

		if($dbname=="as_parcel_service"){
			$row = $db->object($dbname, "where idx='$idx[$i]'");
			//@unlink($ROOT_DIR."/online_as/files/".$row->attached_files);
		
			include("../online_as/online_as_backup.php"); //20220214

			$db->delete($dbname,"where idx='$idx[$i]'");
			
			//관리자 로그
			$db->insert("admin_log","userid='$_SESSION[ADMIN_USERID]', contents='del_reg', ip='$_SERVER[REMOTE_ADDR]', udate=now(), comment='$row->reg_num $row->customer_name $row->customer_phone' ");
		} 
		else if($dbname=="cs_online_event"){
			$row = $db->object($dbname, "where idx='$idx[$i]'");

			include("../online_event/online_event_backup.php"); //20220214

			$db->delete($dbname,"where idx='$idx[$i]'");
			$db->insert("admin_log","userid='$_SESSION[ADMIN_USERID]', contents='del_evt', ip='$_SERVER[REMOTE_ADDR]', udate=now(), comment='$idx[$i] $row->name $row->hp' ");
		}
		else if($dbname=="cs_online_event_sniper"){
			$row = $db->object($dbname, "where idx='$idx[$i]'");

			$db->delete($dbname,"where idx='$idx[$i]'");
			$db->insert("admin_log","userid='$_SESSION[ADMIN_USERID]', contents='del_evt_snp', ip='$_SERVER[REMOTE_ADDR]', udate=now(), comment='$idx[$i] $row->name $row->hp' ");
		}
		else if($dbname=="lab_online_event"){
			$row = $db->object($dbname, "where idx='$idx[$i]'");

			include("../online_event/common_online_event_backup.php"); //20220214

			$db->delete($dbname,"where idx='$idx[$i]'");
			$db->insert("admin_log","userid='$_SESSION[ADMIN_USERID]', contents='delete', ip='$_SERVER[REMOTE_ADDR]', udate=now(), comment='$idx[$i] $dbname' ");
		}
		else if($dbname=="shipping_date_new"){ //20210316
			$row = $db->object($dbname, "where idx='$idx[$i]'");

			$db->delete($dbname,"where idx='$idx[$i]'");
			//$db->insert("admin_log","userid='$_SESSION[ADMIN_USERID]', contents='del_shipment', ip='$_SERVER[REMOTE_ADDR]', udate=now(), comment='$idx[$i] $row->name $row->model' ");
		}
		else if($dbname=="cs_inbound_call"){ //20211122
			if ($val=="del_callback") {
				$db->update($dbname, "pic_memo='', customer_name='', customer_phone='', admin_result=0, admin_desc='', admin_name='' where idx='$idx[$i]' ");

				$db->insert("admin_log","userid='$_SESSION[ADMIN_USERID]', contents='cs_call_del_callback', ip='$_SERVER[REMOTE_ADDR]', udate=now(), comment='$idx[$i] $ADMIN_NAME'");
			}else{
				$db->delete($dbname,"where idx='$idx[$i]'");

				$db->insert("admin_log","userid='$_SESSION[ADMIN_USERID]', contents='cs_call_del', ip='$_SERVER[REMOTE_ADDR]', udate=now(), comment='$idx[$i] $ADMIN_NAME'");
			}
		}
		else {
			$db->delete($dbname,"where idx='$idx[$i]'");
			$db->insert("admin_log","userid='$_SESSION[ADMIN_USERID]', contents='delete', ip='$_SERVER[REMOTE_ADDR]', udate=now(), comment='$idx[$i] $dbname' ");
		}
	}
}//삭제

else if($name=="move" || $name=="shipment"){
	
	for($i=0;$i<count($idx);$i++) {

		if($dbname=="as_parcel_service"){
			$row = $db->object($dbname, "where idx='$idx[$i]'");
			//@unlink($ROOT_DIR."/online_as/files/".$row->attached_files);

			$db->update($dbname,"process_state=$val, update_time=now() where idx='$idx[$i]'");
		
			//관리자 로그
//			$db->insert("admin_log","userid='$_SESSION[ADMIN_USERID]', contents='신청서삭제', ip='$_SERVER[REMOTE_ADDR]', udate=now(), comment='$reg_num'");
		}
		else if($dbname=="shipping_date_new"){
			$row = $db->object($dbname, "where idx='$idx[$i]'");

			$db->update($dbname,"status=$val where idx='$idx[$i]'");
		}

	}
}//이동



include('../footer.php');
?>