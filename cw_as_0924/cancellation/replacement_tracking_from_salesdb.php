<?
$SALES_DB_NAME="cw_sales";
$db_sales = new mysqli_dbConnect($DB_HOST, $SALES_DB_NAME, $DB_USER, $DB_PWD);
$table_sales = "B2B_LIST_ORDER";

$tracking_sales = "";

if ($row['type']=="교환" && $row['exchange_order']!="") { //교환건
	//query cw_sales
	$row_sales = $db_sales->object($table_sales, "where COMPANY_NAME='CS교환' and REGISTER_NO='$row[exchange_order]' order by REGISTER_NO desc limit 1 ");
	$tracking_sales = $row_sales->INVOICE_NO;
}
//not used
?>
