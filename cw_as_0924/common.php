<?
	include "config.php";

	@include('lib/basic_class.php');

	//$db = new dbConnect($DB_HOST, $DB_NAME, $DB_USER, $DB_PWD);

	$db = new mysqli_dbConnect($DB_HOST, $DB_NAME, $DB_USER, $DB_PWD);

	include "lib/function.php";

	$tools = new tools();
?>