# Flow 메시지 웹훅 설계

**날짜:** 2026-06-26  
**대상 프로젝트:** cw_as_0924 (PHP 5.6)

## 개요

`common_lib.php`의 `sendFlowMessage()` 함수를 외부에서 HTTP GET 요청으로 호출할 수 있도록 웹훅 엔드포인트를 구현한다.  
주요 사용 목적: curl, Postman, n8n/Zapier 등 자동화 도구에서 Flow 업무시스템 채팅방으로 메시지 전송.

---

## 파일 구조

```
cw_as_0924/
  webhook/
    _auth.php          ← 시크릿 키 검증 (웹훅 공통 모듈)
    flow_message.php   ← Flow 메시지 전송 웹훅 핸들러
  def_inc.php          ← WEBHOOK_SECRET 상수 추가
```

---

## 엔드포인트

```
GET /cw_as_0924/webhook/flow_message.php
```

### 요청 파라미터

| 파라미터 | 필수 | 설명 |
|----------|------|------|
| `secret` | 필수 | 인증용 시크릿 키 |
| `chatId` | 필수 | Flow 채팅방 ID (정수) |
| `contents` | 필수 | 전송할 메시지 내용 |

### 요청 예시

```bash
curl "https://도메인/cw_as_0924/webhook/flow_message.php?secret=MY_SECRET&chatId=12345&contents=테스트메시지"
```

---

## 응답 형식

Content-Type: `application/json`

| 상황 | HTTP 상태 | 응답 Body |
|------|-----------|-----------|
| 성공 | 200 | `{"success": true}` |
| 인증 실패 | 403 | `{"success": false, "error": "Unauthorized"}` |
| 파라미터 누락 | 400 | `{"success": false, "error": "chatId and contents are required"}` |

---

## 인증

- `$_GET['secret']` 값을 `WEBHOOK_SECRET` 상수와 비교
- 불일치 시 즉시 403 반환 후 `exit`
- 시크릿 키는 `def_inc.php`에 `define("WEBHOOK_SECRET", "...")` 형태로 정의
- 주의: 시크릿 키가 URL에 포함되어 서버 액세스 로그에 기록됨 — 내부 용도로만 사용

---

## 처리 흐름

```
GET 요청
  ↓
_auth.php: secret 파라미터 검증
  ↓ 실패 → 403 반환 종료
chatId, contents 파라미터 검증
  ↓ 누락 → 400 반환 종료
sendFlowMessage((int)$chatId, $contents) 호출
  ↓
Flow API → 지정 채팅방에 메시지 전송
  ↓
200 + {"success": true} 반환
```

---

## 구현 파일별 역할

### `webhook/_auth.php`
- `WEBHOOK_SECRET`과 `$_GET['secret']` 비교
- 실패 시 HTTP 403 + JSON 출력 후 `exit`
- 성공 시 아무것도 하지 않음 (흐름 계속)

### `webhook/flow_message.php`
- `def_inc.php`, `common_lib.php` require
- `_auth.php` require (인증)
- `chatId`(정수 변환 후 0이면 무효), `contents`(빈 문자열이면 무효) 파라미터 유효성 검사
- `sendFlowMessage()` 호출
- JSON 응답 출력

### `def_inc.php` 수정
- `WEBHOOK_SECRET` 상수 추가

---

## 보안 고려사항

- 시크릿 키가 URL 쿼리스트링에 포함되어 서버 로그에 평문으로 남음
- 내부 자동화 용도로만 사용하고 외부에 URL 노출 금지
- `hash_equals()`는 PHP 5.6부터 지원하므로 타이밍 공격 방어를 위해 `===` 대신 `hash_equals()` 사용
