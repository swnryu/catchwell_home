<?php
/**
 * CKakaoNotificationTalkEx 클래스의 notiMsg_new 함수 테스트 스크립트
 * 실제 DB 연결 없이 가상 객체(Mock)를 사용하여 테스트합니다.
 */

// 0. 에러 디버깅 설정 (500 에러 원인을 확인하기 위해 추가)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. 클래스 파일 포함 (파일명 및 경로 확인 필수)
// 파일이 같은 폴더에 있는지 확인하세요. 만약 다른 경로라면 절대 경로 등을 사용해야 합니다.
if (!file_exists('CKakaoNotificationTalkEx.php')) {
    die("오류: 'CKakaoNotificationTalkEx.php' 파일을 찾을 수 없습니다. 경로를 확인해주세요.");
}
require_once 'CKakaoNotificationTalkEx.php';

// 2. 가상 데이터베이스(Mock) 객체 생성
// 함수 내부에서 $db->query() 또는 $db->prepare() 등을 호출할 경우를 대비한 가상 객체입니다.
$db = "555";
echo "<pre>--- 가상 DB 객체 준비 완료 ---\n";

// 3. 테스트 데이터 준비
$customerName  = "홍길동";              // 수신자 이름
$customerPhone = "01044474353";       // 수신자 번호
$regNumber     = "20231024-001";      // 접수 번호
$modelName     = "아이폰 15 프로";     // 모델명
$brokenType    = "액정 파손";          // 고장 유형
$adminMemo     = "수리 완료 후 발송 예정"; // 관리자 메모
$parcelNo      = "1234567890";        // 운송장 번호
$return_url    = "https://yourdomain.com/status"; // 결과 확인 URL
$pic_name      = "정승호";    // 담당자 이름

// 4. 클래스 인스턴스 생성 및 함수 호출
if (!class_exists('CKakaoNotificationTalkEx')) {
    die("오류: 'CKakaoNotificationTalkEx' 클래스가 정의되지 않았습니다.");
}
$kakao = new CKakaoNotificationTalkEx();

echo "--- 알림톡 발송 테스트 시작 (Local Mock 모드) ---\n";

try {
    // 함수 존재 여부 확인
    if (!method_exists($kakao, 'notiMsg_new')) {
        throw new Exception("'notiMsg_new' 메서드가 클래스 내에 존재하지 않습니다.");
    }

    // 함수 호출
    $result = $kakao->notiMsg_new(
        $db, 
        $customerName, 
        $customerPhone, 
        $regNumber, 
        $modelName, 
        $brokenType, 
        $adminMemo, 
        $parcelNo, 
        $return_url, 
        $pic_name
    );

    // 5. 결과 출력
    echo "\n--- 실행 결과 ---\n";
    if ($result) {
        echo "성공: 함수가 값을 반환했습니다.\n";
        var_dump($result);
    } else {
        echo "참고: 함수가 false를 반환했거나 반환값이 없습니다. (내부 로직 확인 필요)\n";
    }

} catch (Error $e) {
    // 클래스가 없거나 메서드가 정의되지 않은 경우 등의 치명적 오류 처리
    echo "치명적 오류: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    // 일반 예외 처리
    echo "예외 발생: " . $e->getMessage() . "\n";
}

echo "--- 테스트 종료 ---</pre>\n";
?>