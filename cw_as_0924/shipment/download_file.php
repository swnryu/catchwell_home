<?
include("../common.php");

$download_filename = $_GET['download_filename']; 
$download_dir = isset($_GET['download_dir']) ? $_GET['download_dir'] : "files"; 

//$download_filename= mb_convert_encoding($download_filename, 'euc-kr', 'UTF-8');

// 파일 다운로드
if( $download_filename != "" ) {

	$file_dir = $download_dir;
	$ftype = "application/octet-stream";

	$fileName_encoded = mb_convert_encoding($download_filename, 'euc-kr', 'UTF-8');

	if(eregi("(MSIE 5.0|MSIE 5.1|MSIE 5.5|MSIE 6.0)", $HTTP_USER_AGENT)){ 
		Header("Content-type: $ftype"); 
		Header("Content-Length: ".filesize("$file_dir/$download_filename"));     
		Header("Content-Disposition: attachment;  filename=$fileName_encoded");   
		Header("Content-Transfer-Encoding: binary");   
		Header("Pragma: no-cache");   
		Header("Expires: 0");   
	} else { 
		Header("Content-type: file/unknown");     
		Header("Content-Length: ".filesize("$file_dir/$download_filename"));     
		Header("Content-Disposition: attachment;  filename=$fileName_encoded");   
		Header("Content-Description: PHP3 Generated Data"); 
		Header("Pragma: no-cache"); 
		Header("Expires: 0"); 
	}
	if ($fp = fopen("$file_dir/$download_filename", "rb")) { 
		if (!fpassthru($fp)) fclose($fp); 
		exit(); 
	}

} 
else {
	$tools->errMsg('오류! 다운로드할 파일명을 확인하세요.');
}


?>
