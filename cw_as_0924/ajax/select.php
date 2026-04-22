<?
include('../header.php'); 

$dbname	= $_POST['dbname'];
$name	= $_POST['name'];
$idx	= $_POST['idx'];
$val	= $_POST['val'];

/***********************************************************************************************************/

if($name=="trade_stat"){

	$row = $db->object($dbname,"where idx='$idx'");

	//결제완료
	if($val==2){
		$db->update($dbname, "$name='$val', trade_money_ok=now() where idx='$idx'");

	//상품배송중
	}else if($val==3){
		$db->update($dbname, "$name='$val', delivery_day=now() where idx='$idx'");

	//배송완료
	}else if($val==4){
		//포인트 적립
		if($row->userid && $row->trade_save_point !=0) {
			$title="상품구입 주문번호 : ".$row->trade_code;
			$db->insert("cs_point", "userid='$row->userid', title='$title', point='$row->trade_save_point', register=now()");
		}
		$db->update($dbname, "$name='$val', sale_end_day=now() where idx='$idx'");

	//취소완료
	}else if($val==5){

		//포인트 취소
		if($row->userid && $row->trade_use_point>0) {
			$title="상품취소 주문번호 : ".$row->trade_code;
			$db->insert("cs_point", "userid='$row->userid', title='$title', point='$row->trade_use_point', register=now()");
		}

		$db->update($dbname, "$name='$val', cancle_day=now() where idx='$idx'");
	}

}

if($name=="level"){
	$db->update($dbname, "$name='$val' where idx='$idx'");
}

include('../footer.php');
?>