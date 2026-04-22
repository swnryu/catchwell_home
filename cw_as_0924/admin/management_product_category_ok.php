<?
include("../def_inc.php");
$mod	= M_SETTING;
$menu	= S_ADMIN_PRODUCT;
include ("../header.php");

if (($PERMISSION & PERMISSION_ALL) != PERMISSION_ALL) {	
	$tools->alertJavaGo('사용할 수 없습니다(1).', $site_url.'/main.php');
	exit;
} 

$table = "product_category";
$query = "";


$is_new = isset($_POST['is_new']) ? $_POST['is_new'] : 0;

//echo $is_new."<br>";



$i = isset($_POST['index']) ? $_POST['index'] : 0;
$idx = isset($_POST['idx'][$i]) ? $_POST['idx'][$i] : 0;
$category_name = isset($_POST['category_name'][$i]) ? $_POST['category_name'][$i] : "";
$model_name = isset($_POST['model_name'][$i]) ? $_POST['model_name'][$i] : "";
$model_name_as = isset($_POST['model_name_as'][$i]) ? $_POST['model_name_as'][$i] : "";

if ($model_name != "")
{
	$lastChar = substr($model_name, -1); 
	if ($lastChar != ';')
	{
		$model_name = $model_name . ';';
	}
}
if ($model_name_as != "")
{
	$lastChar = substr($model_name_as, -1); 
	if ($lastChar != ';')
	{
		$model_name_as = $model_name_as . ';';
	}
}


if ($is_new==1) 
{
	$query = "insert into $table (category_name, model_name, model_name_as) values('$category_name', '$model_name', '$model_name_as')";
}
else if ($is_new==2) 
{
	$query = "delete from $table where idx=$idx";
}
else
{
	$query = "update $table set category_name='$category_name', model_name='$model_name', model_name_as='$model_name_as' where idx=$idx";
}

//echo $query.'<br>';
//exit;

$result = mysqli_query($db->db_conn, $query);
if ($result==false)
{
	$tools->errMsg('데이터베이스 업데이트 오류가 발생하였습니다. 관리자에게 문의하세요.');
}	


$return_url = isset($_POST['return_url']) ? $_POST['return_url'] : "management_product_category.php";

$tools->alertJavaGo("저장 되었습니다.", $return_url);

include ("../footer.php");
?>