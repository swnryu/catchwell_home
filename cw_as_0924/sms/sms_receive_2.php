<?php
// POST 요청으로 받은 데이터를 파싱합니다.
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

// 발신자와 메시지를 추출합니다.
$sender = $_POST['sender'];

// 저장할 파일 경로
$file_path = "sms_log_2.txt";

// 파일 열기 
$file_handle = fopen($file_path, "r+");
if ($file_handle) {
    // 현재 파일 내용 읽기
    $current_content = fread($file_handle, filesize($file_path));

    // 파일 포인터를 파일의 시작으로 이동
    fseek($file_handle, 0);

    // 새로운 SMS 내용을 파일에 추가
    fwrite($file_handle, $sender . PHP_EOL."\n" . $current_content);

    // 파일 핸들 닫기
    fclose($file_handle);

    echo "새로운 SMS 내용을 파일에 성공적으로 저장했습니다.";
} else {
    echo "파일 열기 실패";
	
	$file_handle = fopen($file_path, "a+");
	
	$current_content = fread($file_handle, filesize($file_path));

    // 파일 포인터를 파일의 시작으로 이동
    fseek($file_handle, 0);

    // 새로운 SMS 내용을 파일에 추가
    fwrite($file_handle, $sender . PHP_EOL."\n");

    // 파일 핸들 닫기
    fclose($file_handle);
}
// SMS 수신 및 처리 완료 메시지를 클라이언트에 응답합니다.
header('Content-Type: application/json');
echo json_encode(['success' => true]);
?>
