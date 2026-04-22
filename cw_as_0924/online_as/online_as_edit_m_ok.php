<?
/** Error reporting */
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

include("../def_inc.php");
$mod	= M_AS;	
$menu	= S_AS_M;
include("../header.php");

$db_name	= "as_parcel_service";

$idx = isset($_POST['idx'])?$_POST['idx']:0;

$product_name = isset($_POST['product_name'])?$_POST['product_name']:"";
$price = isset($_POST['price']) ? $_POST['price'] : 0; //20210105

$admin_desc = isset($_POST['admin_desc']) ? $_POST['admin_desc'] : ""; //20211101

$pic_name = isset($_POST['pic_name']) ? $_POST['pic_name'] : ""; //20220224

if ($product_name == "" || $product_name == NULL) {
	$data = "process_state=$_POST[process_state], admin_memo='$_POST[admin_memo]', price=$price, pic_name='$pic_name', admin_desc='$admin_desc', update_time=now() where idx=$_POST[idx]"; //20210105
} else {
	$data = "process_state=$_POST[process_state], admin_memo='$_POST[admin_memo]', product_name='$product_name', price=$price, pic_name='$pic_name', admin_desc='$admin_desc', update_time=now() where idx=$_POST[idx]"; //20210105
}


if($idx) {
	
	if( $db->update($db_name, $data))
//		"	process_state=$_POST[process_state],
//			admin_memo='$_POST[admin_memo]'
//			where idx=$_POST[idx]
//		"
//		))
	{
//			$tools->alertJavaGo("수정하였습니다.", $return_url);
?>
			<div style="margin-top:25%;text-align:center;"><h3>등록 성공. 처리중입니다.....</h3></div>
			<script type='text/javascript'>
			setTimeout("location.href='online_as_edit_m.php'", 200); 
			</script>
<?
	}
	else {
?>
		<div style="margin-top:25%;text-align:center;"><h3 style="color:#f00;">등록 실패. 오류가 발생하였습니다.</h3></div>
		<script type='text/javascript'>
		location.href = 'javascript:history.back()';
		</script>
<?		
	}
}


include('../footer.php');
?>