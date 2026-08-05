<?
	error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
	include "config.php";

	@include('lib/basic_class.php');

	//$db = new dbConnect($DB_HOST, $DB_NAME, $DB_USER, $DB_PWD);

	$db = new mysqli_dbConnect($DB_HOST, $DB_NAME, $DB_USER, $DB_PWD, $DB_PORT);

	include "lib/function.php";

	$tools = new tools();
?>