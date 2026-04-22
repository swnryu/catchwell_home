<?

include("../def_inc.php");
$mod	= M_BANNER;
$menu	= S_BANNER;
include ("../header.php");


$return_url	= "banner.php";

//파일업로드경로
$file_dir	 = "files/";
$file_name	 = "";

if( $_FILES['banner_image']['size'] > 0 ) {
	
	$EXT_CHECK = array("php", "php3", "htm", "html", "cgi", "perl");	// 업로드 파일 제한 확장자 추가 가능
	if( $EXT_TMP = explode( ".", $_FILES['banner_image']['name'])) {	 
		foreach ($EXT_CHECK as $value) { 
			if( strstr( $value, strtolower($EXT_TMP[1]))) { 
				$tools->errMsg( strtoupper($EXT_TMP[1])." 은 업로드 할수 없습니다." ); 
			} 
		}
	}
	if( $_FILES['banner_image']['size']  > 1024*1024*5) { 
		$tools->errMsg("업로드 용량 초과입니다.\\n\\n5메가 까지 업로드 가능합니다"); 
		exit(); 
	}
	
	$filename = substr($_FILES['banner_image']['name'],-5);
	$fn = explode(".",$filename); 
	$EXT_TMP = $fn[1]; 
	$file_name	= IMG_BANNER1.".".$EXT_TMP;

	//remove old file
	$arr = glob($file_dir.IMG_BANNER1.".*");
	for ($i=0;$i<count($arr);$i++) {
		@unlink($arr[$i]);	
	}

	//upload
	if( !@move_uploaded_file($_FILES['banner_image']['tmp_name'], $file_dir.$file_name) ) { 
		$tools->errMsg("파일 업로드 에러" . "-" . $_FILES['banner_image']['tmp_name']); 
	}
	else { 
		@unlink($_FILES['banner_image']['tmp_name']);	
	} 

	$tools->alertJavaGo("등록 하였습니다.", $return_url);

} else {
	$tools->errMsg("파일 사이즈 에러" . "-" . $_FILES['banner_image']['tmp_name']); 
//	exit(); 
}




include ("../footer.php");
?>