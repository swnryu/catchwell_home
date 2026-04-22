<?
include $_SERVER['DOCUMENT_ROOT']."/cw_as/common.php";

$idx2 = $_GET['idx']; 


// 파일 다운로드
if( $_GET['download'] ) {
	$row	= $db->object( "cs_online_event", "where idx=$idx2", "bbs_file" );
	

//	$fn = iconv("UTF-8", "euc-kr", $row->bbs_file);
//	$bbs_file = iconv("UTF-8","euc-kr", urlencode($row->bbs_file));
	$fn = $row->bbs_file;
	$bbs_file = $row->bbs_file;
	$file_dir = $ROOT_DIR."/cw_as/online_event/data";
	$ftype = "application/octet-stream";
	
	if(eregi("(MSIE 5.0|MSIE 5.1|MSIE 5.5|MSIE 6.0)", $HTTP_USER_AGENT)){ 
		Header("Content-type: $ftype"); 
		Header("Content-Length: ".filesize("$file_dir/$fn"));     
		Header("Content-Disposition: attachment;  filename=$bbs_file");   
		Header("Content-Transfer-Encoding: binary");   
		Header("Pragma: no-cache");   
		Header("Expires: 0");   
	} else { 
		Header("Content-type: file/unknown");     
		Header("Content-Length: ".filesize("$file_dir/$bbs_file"));     
		Header("Content-Disposition: attachment;  filename=$fn");   
		Header("Content-Description: PHP3 Generated Data"); 
		Header("Pragma: no-cache"); 
		Header("Expires: 0"); 
	}
	if ($fp = fopen("$file_dir/$fn", "rb")) { 
		if (!fpassthru($fp)) fclose($fp); 
		exit(); 
	}

} else {
	$tools->errMsg('경 고 !!!\n\n비정상적으로 접근했습니다.');
}


?>
