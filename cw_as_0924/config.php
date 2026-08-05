<?
$DB_HOST="211.54.90.200";//"mariadb"; // 라이브 DB 직접 연결
$DB_PORT=3307;
$DB_USER="cwadmin";
$DB_PWD="Catchwell1!";
$DB_NAME="cw_as";

$ROOT_DIR=$_SERVER['DOCUMENT_ROOT'];

$site_url= (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']==='on' ? "https" : "http")."://".$_SERVER['HTTP_HOST']."/cw_as_0924";
?>