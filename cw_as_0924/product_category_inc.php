<?

$arr_category_name = array();
$arr_cancellation_model = array();
$arr_as_model = array();

$query = "select * from product_category order by idx asc";

$index = 0;
$result = mysqli_query($db->db_conn, $query);
while ($row = mysqli_fetch_array($result))
{
	$arr_category_name += array($index => $row['category_name']);
	
	//
	$names = $row['model_name'];
	$lastChar = substr($names, -1);
	if ($lastChar==';')
	{
		$names = substr($names, 0, -1);
	}
	$arr = explode(';', $names);
	$arr_cancellation_model += array($index => $arr);

	//
	$names = $row['model_name_as'];
	$lastChar = substr($names, -1);
	if ($lastChar==';')
	{
		$names = substr($names, 0, -1);
	}
	$arr = explode(';', $names);
	$arr_as_model += array($index => $arr);

	$index++;

/*	
	$arr_category_name += array($row['idx'] => $row['category_name']);
	
	$names = $row['model_name'];
	$lastChar = substr($names, -1);
	if ($lastChar==';')
	{
		$names = substr($names, 0, -1);
	}
	$arr = explode(';', $names);
	$arr_cancellation_model += array($row['idx'] => $arr);

	$arr_model_name += array($row['idx'] => $arr);
*/
	unset($arr);
}

mysqli_free_result($result);

unset($index);

?>