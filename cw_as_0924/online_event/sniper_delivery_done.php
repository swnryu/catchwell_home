<?
session_start();

include('../header.php'); 

include ('../PHPExcel/Classes/PHPExcel.php');
include ('../PHPExcel/Classes/PHPExcel/IOFactory.php');

header("Content-type: text/html; charset=utf-8");

require("../kakao/CKakaoNotificationTalkEx.php");
$notiMsg = new CKakaoNotificationTalkEx();


$dbname	= $_POST['dbname'];
$name	= $_POST['name'];
$idx	= $_POST['idx'];
$val	= $_POST['val'];

$cnt_suc = 0;
$noti_cnt = 0;
if($name=="deliverydone") {

	for($i=0;$i<count($idx);$i++) {	
		
        $row = $db->object($dbname, "where idx='$idx[$i]' and status=0 and (tracking_num is NULL or tracking_num='') ");
        if ($row) {

            //$product_name = $row->gift;
            $row2 = $db->object('shipping_date_new', "where status=1 and name='$row->customer_name' and orderid_sabangnet='$idx[$i]' ");

            if ($row2) {

                if ($db->update($dbname, "status=99, tracking_num='$row2->tracking' where idx='$idx[$i]' ")) {
                    
                    if ($row2->tracking != "") 
                    {
                        if (0)
                        {
                            $noti_cnt++;
                        }
                    }
                    $cnt_suc++;
                }
            }

        }
    }
}

?>