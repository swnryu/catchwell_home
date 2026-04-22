<?

header("Content-Type:text/html;charset=utf-8");

/** Error reporting */
error_reporting(E_ALL);
ini_set('display_errors', FALSE);
ini_set('display_startup_errors', FALSE);


include("../common.php");
include("../def_inc.php");

$db_name	= "as_parcel_service";

//GD함수 업로드
include "../lib/gd.php";

$where = sprintf("where reg_date='%s'", date('Y-m-d'));
$cnt = $db->max($db_name, $where);
$make_reg_num = sprintf("%s-%03d", date('ymd'), $cnt+1);	//yymmdd-xxx

$reg_num = empty($_POST['reg_num']) ? $make_reg_num : $_POST['reg_num'] ; 

$process_state = isset($_POST['process_state']) ? $_POST['process_state'] : 0;
$parcel_num = isset($_POST['parcel_num']) ? $_POST['parcel_num'] : "";
	
$customer_name = isset($_POST['customer_name']) ? $_POST['customer_name'] : "";
if( isset($_POST['hp1']) && isset($_POST['hp2']) && isset($_POST['hp3']) )
    $customer_phone = $_POST['hp1'].$_POST['hp2'].$_POST['hp3'];
else
    $customer_phone = "";

$customer_addr = isset($_POST['customer_addr']) ? $_POST['customer_addr'] : "";
$customer_addr_detail = isset($_POST['customer_addr_detail']) ? $_POST['customer_addr_detail'] : "";
$customer_zipcode = isset($_POST['customer_zipcode']) ? $_POST['customer_zipcode'] : "";
$customer_addr_return = isset($_POST['customer_addr_return']) ? $_POST['customer_addr_return'] : "";
$customer_addr_detail_return = isset($_POST['customer_addr_detail_return']) ? $_POST['customer_addr_detail_return'] : "";
$customer_zipcode_return = isset($_POST['customer_zipcode_return']) ? $_POST['customer_zipcode_return'] : "";

$customer_desc = isset($_POST['customer_desc']) ? $_POST['customer_desc'] : "";
$broken_type_idx = isset($_POST['broken_type']) ? $_POST['broken_type'] : 4;
$broken_type = $predef_broken_type[$broken_type_idx];
//$attached_files = isset($_POST['attached_files']) ? $_POST['attached_files'] : "";
$broken_type_desc = mb_strimwidth($customer_desc, 0, 60, '...', 'UTF-8');

$parcel_memo = isset($_POST['parcel_memo']) ? $_POST['parcel_memo'] : "";
$parcel_memo_return = isset($_POST['parcel_memo_return']) ? $_POST['parcel_memo_return'] : "";

$product_type = isset($_POST['product_type']) ? $_POST['product_type'] : "";
$product_name = isset($_POST['product_name']) ? $_POST['product_name'] : "";
$product_date = isset($_POST['product_date']) ? $_POST['product_date'] : date("Y-m-d");

if( preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2})/", $product_date) ) {
} 
else {
    $product_date = date("Y-m-d"); 
}

$admin_memo = isset($_POST['admin_memo']) ? $_POST['admin_memo'] : "";

// 모바일페이지 구분
$mobileKeyWords = array ('iPhone', 
                         'iPod', 
                         'BlackBerry', 
                         'Android', 
                         'Windows CE', 
                         'Windows CE;', 
                         'LG', 
                         'MOT', 
                         'SAMSUNG', 
                         'SonyEricsson', 
                         'Mobile', 
                         'Symbian', 
                         'Opera Mobi', 
                         'Opera Mini', 
                         'IEmobile');
 
$isMobile = 0;
for($i = 0 ; $i < count($mobileKeyWords) ; $i++)
{
    if(strpos($_SERVER['HTTP_USER_AGENT'],$mobileKeyWords[$i]) == true)
    {
        $return_url	= "https://m.catchwell.com/catchwell/AS/as_application_ok.html?customer_name=".$customer_name."&amp;index_num=".$reg_num;
    }
    else
    {
        $return_url	= "https://www.catchwell.com/catchwell/AS/as_application_ok.html?customer_name=".$customer_name."&amp;index_num=".$reg_num;
    }
}

if( $db->insert($db_name,
    "	reg_num='$reg_num',
        process_state=$process_state,
        customer_name='$customer_name',
        customer_phone='$customer_phone',
        customer_addr='$customer_addr',
        customer_addr_detail='$customer_addr_detail',
        customer_zipcode='$customer_zipcode',
        customer_addr_return='$customer_addr_return',
        customer_addr_detail_return='$customer_addr_detail_return',
        customer_zipcode_return='$customer_zipcode_return',
        customer_desc='$customer_desc',
        broken_type='$broken_type',
        parcel_memo='$parcel_memo',
        parcel_memo_return='$parcel_memo_return',
        product_type='$product_type',
        product_name='$product_name',
        product_date='$product_date'
    "
)) 
{   
    
    
	$table = "as_parcel_service";
	$query2="select * from $table where process_state>3 and customer_phone='$customer_phone' and customer_phone!='01000000000' ";
	$rs2 = mysqli_query($db->db_conn, $query2);
	$cnt = mysqli_num_rows($rs2);

	if ($cnt>0) {
		
		while($row2 = mysqli_fetch_array($rs2)){
			$targetDate = $row2['update_time']; // DB에서 가져온 날짜
			$today = new DateTime(); // 오늘 날짜
			$compareDate = new DateTime($targetDate); // DB에서 불러온 날짜 객체 생성

			$diff = $compareDate->diff($today)->days; // 날짜 차이 계산

			// 30일 이내인지 확인
			if ($diff <= 30 && $compareDate < $today) {
				//$tools->msg($row2['update_time']);// 이전접수일로부터 30일 이내일떄
				
			}
		}
	}
	$tools->msg("AS가 접수되었습니다.");
    // 카카오톡 알림 전송
    ?>
    <form name="kakoHiddenForm" method="post" action="https://csadmin.catchwell.com/cw_as_0924/kakao/kakaoNotification.php">
        <input type="hidden" name="customer_name" value="<?php echo $customer_name ?>">
        <input type="hidden" name="customer_phone" value="<?php echo $customer_phone ?>">
        <input type="hidden" name="register_number" value="<?php echo $reg_num ?>">
        <input type="hidden" name="model_name" value="<?php echo $product_name ?>">
        <input type="hidden" name="broken_type" value="<?php echo $broken_type_desc ?>">
        <input type="hidden" name="return_url" value="<?php echo $return_url ?>">
    </form>
    <script>
        document.kakoHiddenForm.submit();
    </script>
    <?php
}
else
{
    echo "fail.............................";
}

?>