<?
/** Error reporting */
error_reporting(E_ALL);
ini_set('display_errors', FALSE);
ini_set('display_startup_errors', FALSE);

include("../def_inc.php");
include("../common_lib.php");

$mod	= M_AS;	
$menu	= isset($_GET['from'])?$_GET['from']:S_AS_NEW;
include("../header.php");

$db_name	= "as_parcel_service";
$return_url	= "online_as.php";

$lib = new commonLib();


//파일업로드경로
$file_dir	 = "files/";

//GD함수 업로드
include "../lib/gd.php";

require("../kakao/CKakaoNotificationTalkEx.php");
$notiMsg = new CKakaoNotificationTalkEx();

if($_POST['mode']=="write"){
	
	if( isset($_FILES['attached_files']['size']) && $_FILES['attached_files']['size'] > 0 ) {
		$EXT_CHECK = array("php", "php3", "htm", "html", "cgi", "perl");	// 업로드 파일 제한 확장자 추가 가능
		if( $EXT_TMP = explode( ".", $_FILES['attached_files']['name'])) {	 
			foreach ($EXT_CHECK as $value) { 
				if( strstr( $value, strtolower($EXT_TMP[1]))) { 
					$tools->errMsg( strtoupper($EXT_TMP[1])." 은 업로드 할수 없습니다." ); 
				} 
			}
		}
		if( $_FILES['attached_files']['size']  > 1024*1024*5) { 
			$tools->errMsg("업로드 용량 초과입니다.\\n\\n5메가 까지 업로드 가능합니다"); 
			exit(); 
		}
		
		$filename = substr($_FILES['attached_files']['name'],-5);
		$fn = explode(".",$filename); 
		$EXT_TMP = $fn[1]; 
		$file_name	= time()."1.".$EXT_TMP;
		$sfile_name = $_FILES['attached_files']['name'];
		
		if( file_exists($file_dir.$file_name)) { //중복체크
			$file_name	= time()."1_".rand(1,9).".".$EXT_TMP;
		}
		
		if( !@move_uploaded_file($_FILES['attached_files']['tmp_name'], $file_dir.$file_name) ) { 
			$tools->errMsg("파일 업로드 에러" . "-" . $_FILES['attached_files']['tmp_name']); 
		}
		else { 
			@unlink($_FILES['attached_files']['tmp_name']);	
		} 
	} else {
		$file_name 	= "";
	}

//
	$where = sprintf("where reg_date='%s'", date('Y-m-d'));
	$max = $db->max($db_name, $where);
	$make_reg_num = sprintf("%s-%03d", date('ymd'), $max+1);	//yymmdd-xxx

	$reg_num = empty($_POST['reg_num']) ? $make_reg_num : $_POST['reg_num'] ; 

	$process_state = isset($_POST['process_state']) ? $_POST['process_state'] : 0;
	$parcel_num = isset($_POST['parcel_num']) ? $_POST['parcel_num'] : "";
	$parcel_num = preg_replace("/[^0-9]*/s","", $parcel_num);
	
	$customer_name = isset($_POST['customer_name']) ? $_POST['customer_name'] : "";
	$customer_phone = isset($_POST['customer_phone']) ? $_POST['customer_phone'] : "";

	$customer_addr = isset($_POST['customer_addr']) ? $_POST['customer_addr'] : "";
	$customer_addr_detail = isset($_POST['customer_addr_detail']) ? $_POST['customer_addr_detail'] : "";
	$customer_zipcode = isset($_POST['customer_zipcode']) ? $_POST['customer_zipcode'] : "";

	$customer_addr_return = isset($_POST['customer_addr_return']) ? $_POST['customer_addr_return'] : $customer_addr;
	$customer_addr_detail_return = isset($_POST['customer_addr_detail_return']) ? $_POST['customer_addr_detail_return'] : $customer_addr_detail;
	$customer_zipcode_return = isset($_POST['customer_zipcode_return']) ? $_POST['customer_zipcode_return'] : $customer_zipcode;
	$parcel_num_return = isset($_POST['parcel_num_return']) ? $_POST['parcel_num_return'] : "";
	$parcel_num_return = preg_replace("/[^0-9]*/s","", $parcel_num_return);

	$customer_desc = isset($_POST['customer_desc']) ? $_POST['customer_desc'] : "";
	$admin_memo = isset($_POST['admin_memo']) ? $_POST['admin_memo'] : "";
	$price = isset($_POST['price']) ? $_POST['price'] : 0; //20210105

	$attached_files = $file_name;//isset($_POST['attached_files']) ? $_POST['attached_files'] : "";
	$attached_name = isset($_POST['attached_name']) ? $_POST['attached_name'] : "";
	
	$parcel_memo = isset($_POST['parcel_memo']) ? $_POST['parcel_memo'] : "";
	$parcel_memo_return = isset($_POST['parcel_memo_return']) ? $_POST['parcel_memo_return'] : "";

	$product_name = isset($_POST['product_name']) ? $_POST['product_name'] : "";
	$product_type = $lib->getProductCategory($product_name, $arr_product_vc, $arr_product_rc, $arr_product_etc);
	
	$product_date = isset($_POST['product_date']) ? $_POST['product_date'] : date("Y-m-d");

	$broken_type_num = isset($_POST['broken_type']) ? $_POST['broken_type'] : 4;
	$broken_type = $predef_broken_type[$broken_type_num];
	$broken_type_desc = mb_strimwidth($customer_desc, 0, 60, '...', 'UTF-8');
	$admin_desc = isset($_POST['admin_desc']) ? $_POST['admin_desc'] : "";
	$pic_name = isset($_POST['pic_name']) ? $_POST['pic_name'] : "";

	if( $db->insert($db_name,
		"	reg_num='$reg_num',
			process_state=$process_state,
			customer_name='$customer_name',
			customer_phone='$customer_phone',

			product_type='$product_type',
			product_name='$product_name',
			product_date='$product_date',

			broken_type='$broken_type',
			customer_desc='$customer_desc',
			
			customer_addr='$customer_addr',
			customer_addr_detail='$customer_addr_detail',
			customer_zipcode=$customer_zipcode,
			parcel_num='$parcel_num',
			parcel_memo='$parcel_memo',
			
			customer_addr_return='$customer_addr_return',
			customer_addr_detail_return='$customer_addr_detail_return',
			customer_zipcode_return=$customer_zipcode_return,
			parcel_num_return='$parcel_num_return',
			parcel_memo_return='$parcel_memo_return',

			admin_memo='$admin_memo',
			price=$price,
			admin_desc='$admin_desc',
			pic_name = '$pic_name'
		"
		)) //20210105
	{
		//관리자 로그
		$db->insert("admin_log","userid='$_SESSION[ADMIN_USERID]', contents='new_reg', ip='$_SERVER[REMOTE_ADDR]', udate=now(), comment='$reg_num'");

		//택배 회수 접수시에만 알림톡이 발송되도록.
		//온라인신청서 작성후 등록하면 접수번호를 알림톡으로 전송
		//[접수완료] 카카오알림톡전송
		if (!DBG_MODE) 
		{ 
			if ($process_state==ST_REGISTERING)
				{
					$tools->msg("택배비 부과 및 A/S 접수가 완료되었습니다.");
					?>
					<form name="kakoHiddenForm" method="post" action="../kakao/kakaoNotification.php" >
						<input type="hidden" name="customer_name" value="<?php echo $customer_name; ?>">
						<input type="hidden" name="customer_phone" value="<?php echo $customer_phone; ?>">
						<input type="hidden" name="register_number" value="<?php echo $reg_num; ?>">
						<input type="hidden" name="model_name" value="<?php echo $product_name; ?>">
						<input type="hidden" name="broken_type" value="<?php echo $broken_type_desc; ?>">
						<input type="hidden" name="return_url" value="<?php echo "../online_as/online_as.php?state=".ST_REGISTERING; ?>">
					</form>
					<script>
						document.kakoHiddenForm.submit();
					</script>
					<?php
				}
				else
				{
					$tools->alertJavaGo("A/S 접수가 완료되었습니다.", "online_as.php?state=".ST_REGISTERING );
				}
		} 
		else {
			$tools->alertJavaGo("A/S 접수가 완료되었습니다.", "online_as.php?state=".ST_REGISTERING );
		}
	}

}

else if($_POST['mode']=="edit"){
	
	$idx = isset($_POST['idx'])?$_POST['idx']:0;
	$row = $db->object($db_name, "where idx='$idx'");


	$customer_name = isset($_POST['customer_name']) ? $_POST['customer_name'] : "";
	$customer_phone = isset($_POST['customer_phone']) ? $_POST['customer_phone'] : "";
	$reg_num = isset($_POST['reg_num']) ? $_POST['reg_num'] : "";
	$product_name = isset($_POST['product_name']) ? $_POST['product_name'] : "";
	$product_date = isset($_POST['product_date']) ? $_POST['product_date'] : date("Y-m-d");

	$broken_type_num = isset($_POST['broken_type']) ? $_POST['broken_type'] : 4;
	$broken_type = $predef_broken_type[$broken_type_num];
	$customer_desc = $_POST['customer_desc'];
	$admin_memo = $_POST['admin_memo'];
	$price = isset($_POST['price']) ? $_POST['price'] : 0; //20210105
	$pic_name = isset($_POST['pic_name']) ? $_POST['pic_name'] : ""; //20220103

	$parcel_num = isset($_POST['parcel_num']) ? $_POST['parcel_num'] : "";
	$parcel_num = preg_replace("/[^0-9]*/s","", $parcel_num);
	$parcel_num_return = isset($_POST['parcel_num_return']) ? $_POST['parcel_num_return'] : "";
	$parcel_num_return = preg_replace("/[^0-9]*/s","", $parcel_num_return);

	$product_type = $lib->getProductCategory($_POST['product_name'], $arr_product_vc, $arr_product_rc, $arr_product_etc);

	if($_POST['isdel']=="y") { //remove

		include("online_as_backup.php"); //20220214

		if( $db->delete($db_name, "where idx=$_POST[idx]"))
		{
			if ($menu==S_AS_REGISTERING)	{$return_url.="?state=".ST_REGISTERING;}
			else if ($menu==S_AS_DC)	{$return_url.="?state=".ST_DC;}
			else if ($menu==S_AS_REGDONE)	{$return_url.="?state=".ST_REG_DONE;}
			else if ($menu==S_AS_FIXDONE)	{$return_url.="?state=".ST_FIX_DONE;}
			else if ($menu==S_AS_COMPLETED)	{$return_url.="?state=".ST_AS_COMPLETED;}

			else if ($menu==S_AS_SHIPMENT)	{$return_url="online_as_shipment.php";}//20230707 추가

			else if ($menu==S_AS_REPORT)	{$return_url="online_as_report.php";}
			else							{$return_url.="?state=".ST_REGISTERING;}
			
			//관리자 로그
			$db->insert("admin_log","userid='$_SESSION[ADMIN_USERID]', contents='del_reg', ip='$_SERVER[REMOTE_ADDR]', udate=now(), comment='$reg_num $customer_name $customer_phone'");

			$tools->alertJavaGo("삭제하였습니다.", $return_url);
		}
	} 
	else { //update
/*		
		if( isset($_FILES['attached_files']['size']) && $_FILES['attached_files']['size'] > 0 ) {
			$EXT_CHECK = array("php", "php3", "htm", "html", "cgi", "perl");	// 업로드 파일 제한 확장자 추가 가능
			if( $EXT_TMP = explode( ".", $_FILES['attached_files']['name'])) {	 
				foreach ($EXT_CHECK as $value) { 
					if( strstr( $value, strtolower($EXT_TMP[1]))) { 
						$tools->errMsg( strtoupper($EXT_TMP[1])." 은 업로드 할수 없습니다." ); 
					} 
				}
			}
			if( $_FILES['attached_files']['size']  > 1024*1024*5) { 
				$tools->errMsg("업로드 용량 초과입니다\\n\\n5메가 까지 업로드 가능합니다"); 
				exit(); 
			}
			
			$filename = substr($_FILES['attached_files']['name'],-5);
			$fn = explode(".",$filename); 
			$EXT_TMP = $fn[1]; 
			$file_name	= time()."1.".$EXT_TMP;
			$sfile_name = $_FILES['attached_files']['name'];
			
			if( !@move_uploaded_file($_FILES['attached_files']['tmp_name'], $file_dir.$file_name) ) { 
				$tools->errMsg("파일 업로드 에러" . "--" . $_FILES['attached_files']['tmp_name']); 
			}
			else { 
				@unlink($_FILES['attached_files']['tmp_name']);	
			} 
		} else {
			$file_name 	= "";
			
			if ($_POST['attached_name']!="") {
				//기존첨부파일명 유지 
				$file_name = $_POST['attached_name'];
			} 
		}
*/		
		$data = "process_state=$_POST[process_state],
				customer_name='$customer_name',
				customer_phone='$customer_phone',

				product_type='$product_type',
				product_name='$_POST[product_name]',
				
				broken_type='$broken_type',
				customer_desc='$_POST[customer_desc]',
				
				customer_addr='$_POST[customer_addr]',
				customer_addr_detail='$_POST[customer_addr_detail]',
				customer_zipcode=$_POST[customer_zipcode],
				parcel_num='$parcel_num',
				parcel_memo='$_POST[parcel_memo]',
				
				customer_addr_return='$_POST[customer_addr_return]',
				customer_addr_detail_return='$_POST[customer_addr_detail_return]',
				customer_zipcode_return=$_POST[customer_zipcode_return],
				parcel_num_return='$parcel_num_return',
				parcel_memo_return='$_POST[parcel_memo_return]',
				admin_memo='$_POST[admin_memo]',
				admin_desc='$_POST[admin_desc]',
				price=$_POST[price]"; //20210105

		if ($row->process_state != $_POST['process_state']) {
			$data = $data . ", update_time=now() ";
		}

		if ($menu==S_AS_REGDONE) { //20220103
			$data = $data . ", pic_name='$pic_name' ";
		}
		
		$data = $data . " where idx=$_POST[idx]";

		if( $db->update($db_name, $data))
		{
			//관리자 로그
			$db->insert("admin_log","userid='$_SESSION[ADMIN_USERID]', contents='update_reg', ip='$_SERVER[REMOTE_ADDR]', udate=now(), comment='$reg_num'");

			//$return_url = "online_as_view.php?idx=".$_POST[idx]."&from=".$menu; //online_as_edit.php?idx=1113&from=sub_as
			$return_url = "online_as.php";

			if ($menu==S_AS_REGISTERING)	{$return_url.="?state=".ST_REGISTERING;}
			else if ($menu==S_AS_DC)	{$return_url.="?state=".ST_DC;}
			else if ($menu==S_AS_REGDONE)	{$return_url.="?state=".ST_REG_DONE;}
			else if ($menu==S_AS_FIXDONE)	{$return_url.="?state=".ST_FIX_DONE;}
			else if ($menu==S_AS_FIXING)	{$return_url.="?state=".ST_FIXING;}
			else if ($menu==S_AS_COMPLETED)	{$return_url.="?state=".ST_AS_COMPLETED;}

			else if ($menu==S_AS_SHIPMENT)	{$return_url="online_as_shipment.php";}//20230707 추가
			
			else if ($menu==S_AS_REPORT)	{$return_url="online_as_report.php";}
			else							{$return_url.="?state=".ST_REGISTERING;}

			if (!DBG_MODE) 
			{ 
				//출고완료 상태로 변경후, 송장번호 입력시 알림톡전송
				//[출고완료] 카카오알림톡전송
				if ( ($row->process_state!=ST_AS_COMPLETED && $_POST['process_state']==ST_AS_COMPLETED) && 
					($row->parcel_num_return=="" && $parcel_num_return!="") )
				{
					$tools->msg("A/S 출고처리가 완료 되었습니다.");

					$memo = $admin_memo;
					$memo = str_replace("(V)","",$memo);
					$memo = str_replace("(R)","",$memo);
					$memo = str_replace("(H)","",$memo);
					$memo = str_replace("(S)","",$memo);
					$memo = str_replace("(M)","",$memo);
					$memo = str_replace("[ETC]","",$memo);

?>
					<form name="kakoHiddenForm" method="post" action="../kakao/kakaoNotification.php" >
						<input type="hidden" name="customer_name" value="<?php echo $customer_name; ?>">
						<input type="hidden" name="customer_phone" value="<?php echo $customer_phone; ?>">
						<input type="hidden" name="register_number" value="<?php echo $reg_num; ?>">
						<input type="hidden" name="model_name" value="<?php echo $product_name; ?>">
						<input type="hidden" name="broken_type" value="<?php echo $broken_type_desc; ?>">
						<input type="hidden" name="admin_memo" value="<?php echo $memo; ?>">
						<input type="hidden" name="parcel_num_return" value="<?php echo $parcel_num_return; ?>">
						<input type="hidden" name="return_url" value="<?php echo "../online_as/online_as.php?state=".ST_FIX_DONE; ?>">
					</form>
					<script>
						document.kakoHiddenForm.submit();
					</script>
<?
				}
			}
			if($_POST['estimate']=="y") { //견적서 카카오알림톡전송
				$memo = $admin_memo;
				$memo = str_replace("(V)","",$memo);
				$memo = str_replace("(R)","",$memo);
				$memo = str_replace("(H)","",$memo);
				$memo = str_replace("(S)","",$memo);
				$memo = str_replace("(M)","",$memo);
				$memo = str_replace("[ETC]","",$memo);
				
				$notiMsg->shipmentNotiMsg_estimate($db,	$customer_phone, $reg_num, $customer_name, 
															$product_name, $customer_desc, $memo);
				$tools->alertJavaGo("견적서 발행이 완료 되었습니다.", $return_url);
			}
			else{
				$tools->alertJavaGo("수정 되었습니다.", $return_url);
			}
		}
	
	}
	
}


include('../footer.php');
?>