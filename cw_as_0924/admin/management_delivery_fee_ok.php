<?
include("../def_inc.php");
$mod	= M_SHIPMENT;
$menu	= S_ADMIN_DELIVERY;
include ("../header.php");

if (($PERMISSION & PERMISSION_ALL) != PERMISSION_ALL) {	
//	$tools->alertJavaGo('사용할 수 없습니다(1).', $site_url.'/main.php');
//	exit;
} 

$table = "delivery_package";
$query = "";

$type = isset($_POST['type']) ? $_POST['type'] : 0;
$is_new = isset($_POST['is_new']) ? $_POST['is_new'] : 0;

//echo $type."<br>";
//echo $is_new."<br>";

if ($type==0) //박스 사이즈별 가격
{
	for ($i=0; $i<10; $i++) 
	{
		$idx = isset($_POST['idx'][$i]) ? $_POST['idx'][$i] : 0;
		$model_name = isset($_POST['model_name'][$i]) ? $_POST['model_name'][$i] : "";
		$box_size = isset($_POST['box_size'][$i]) ? $_POST['box_size'][$i] : "";
		$price = isset($_POST['price'][$i]) ? $_POST['price'][$i] : 0;
		$is_new = isset($_POST['is_new'][$i]) ? $_POST['is_new'][$i] : 1;

		if ($model_name == "") 
		{
			continue;
		}

		if ($price > 0)
		{
			if ($idx > 0) 
			{
				$query = "update $table set model_name='$model_name', box_size=$box_size, price=$price where type=0 and idx=$idx";
			}
			else 
			{
				$query = "insert into $table (type, model_name, box_size, price) values(0, '$model_name', $box_size, $price)";
			}
		}
		else
		{//delete
			if ($idx > 0 && $model_name=="" && $box_size=="")
			{
				$query = "delete from $table where type=0 and idx=$idx";
			}
		}
//		echo $query."<br>";
	
		$result = mysqli_query($db->db_conn, $query);
		if ($result==false)
		{
			$tools->errMsg('데이터베이스 업데이트 오류가 발생하였습니다. 관리자에게 문의하세요.');
			
		}

	}

}
else //모델별 가격
{
	$i = isset($_POST['index']) ? $_POST['index'] : 0;
	$idx = isset($_POST['idx'][$i]) ? $_POST['idx'][$i] : 0;
	$model_name = isset($_POST['list_model_name'][$i]) ? $_POST['list_model_name'][$i] : "";
	$box_size = isset($_POST['sel_box_size'][$i]) ? $_POST['sel_box_size'][$i] : "";
	$price = 0; //isset($_POST['list_price'][$i]) ? $_POST['list_price'][$i] : "";

	if ($is_new==1) 
	{
		$query = "insert into $table (type, model_name, box_size, price) values($type, '$model_name', $box_size, $price)";
	}
	else if ($is_new==2) 
	{
		$query = "delete from $table where type=$type and idx=$idx";
	}
	else
	{
		$query = "update $table set model_name='$model_name', box_size=$box_size, price=$price where type=$type and idx=$idx";
	}

	$result = mysqli_query($db->db_conn, $query);
	if ($result==false)
	{
		$tools->errMsg('데이터베이스 업데이트 오류가 발생하였습니다. 관리자에게 문의하세요.');
	}	
}

$return_url = isset($_POST['return_url']) ? $_POST['return_url'] : "management_delivery_fee.php";

$tools->alertJavaGo("저장 되었습니다.", $return_url);

include ("../footer.php");
?>