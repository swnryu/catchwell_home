# Flow 메시지 웹훅 구현 계획

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** `sendFlowMessage()`를 외부에서 HTTP GET 요청으로 호출할 수 있는 웹훅 엔드포인트 구현

**Architecture:** 시크릿 키 인증 모듈(`_auth.php`)과 핸들러(`flow_message.php`) 2파일 구조. 인증 모듈은 `require`로 포함되어 실패 시 즉시 종료.

**Tech Stack:** PHP 5.6, curl (기존 `sendFlowMessage` 내부 사용)

## Global Constraints

- PHP 5.6 문법만 사용 (`??` 연산자 금지, `isset() ?:` 사용)
- `json_encode()` 배열은 `array()` 문법 사용
- 기존 프로젝트 require 경로 패턴 준수 (`dirname(__DIR__)` 기반)

---

## 파일 구조

| 파일 | 작업 | 역할 |
|------|------|------|
| `def_inc.php` | 수정 | `WEBHOOK_SECRET` 상수 추가 |
| `webhook/_auth.php` | 생성 | 시크릿 키 검증 공통 모듈 |
| `webhook/flow_message.php` | 생성 | Flow 메시지 전송 웹훅 핸들러 |

---

### Task 1: 인증 모듈

**Files:**
- Modify: `def_inc.php`
- Create: `webhook/_auth.php`

**Interfaces:**
- Produces: `_auth.php` — require 시 `$_GET['secret']`를 `WEBHOOK_SECRET`과 검증, 실패 시 403 JSON 출력 후 `exit`

- [ ] **Step 1: def_inc.php에 WEBHOOK_SECRET 상수 추가**

`def_inc.php`의 Flow API 상수 블록 바로 아래에 추가:

```php
// Flow 업무시스템 API
define("FLOW_API_KEY",   "20251203050955646-6ab56428-4e53-469e-b564-420e2ce4c4c9");
define("FLOW_SENDER_ID", "swryu@catchwell.com");

// Webhook
define("WEBHOOK_SECRET", "i7FTH8PmHlYX0tEKQegxMaNe2sM_VBSLxdwL1kM2pGA");
```

- [ ] **Step 2: webhook/ 디렉터리 생성 확인**

```bash
ls cw_as_0924/webhook/
# 없으면 생성
mkdir cw_as_0924/webhook
```

- [ ] **Step 3: webhook/_auth.php 생성**

```php
<?php
$_secret = isset($_GET['secret']) ? $_GET['secret'] : '';
if (!hash_equals(WEBHOOK_SECRET, $_secret)) {
    header('Content-Type: application/json');
    http_response_code(403);
    echo json_encode(array('success' => false, 'error' => 'Unauthorized'));
    exit;
}
```

- [ ] **Step 4: 인증 실패 동작 수동 확인**

브라우저 또는 curl로 잘못된 키 요청:
```bash
curl -i "http://localhost/cw_as_0924/webhook/flow_message.php?secret=wrong&chatId=1&contents=test"
```
예상 응답:
```
HTTP/1.1 403 Forbidden
{"success":false,"error":"Unauthorized"}
```
(flow_message.php는 Task 2에서 생성하므로 Task 2 완료 후 검증)

- [ ] **Step 5: 커밋**

```bash
git add def_inc.php webhook/_auth.php
git commit -m "feat: Flow 웹훅 시크릿 키 상수 및 인증 모듈 추가"
```

---

### Task 2: 웹훅 핸들러

**Files:**
- Create: `webhook/flow_message.php`

**Interfaces:**
- Consumes: `_auth.php` (인증), `sendFlowMessage(int $chatId, string $contents)` from `common_lib.php`
- Produces: GET `/cw_as_0924/webhook/flow_message.php?secret=&chatId=&contents=`

- [ ] **Step 1: webhook/flow_message.php 생성**

```php
<?php
$_root = dirname(__DIR__);
require_once $_root . '/def_inc.php';
require_once $_root . '/common_lib.php';
require_once __DIR__ . '/_auth.php';

header('Content-Type: application/json');

$_chatId   = isset($_GET['chatId'])   ? (int)$_GET['chatId']   : 0;
$_contents = isset($_GET['contents']) ? trim($_GET['contents']) : '';

if ($_chatId === 0 || $_contents === '') {
    http_response_code(400);
    echo json_encode(array('success' => false, 'error' => 'chatId and contents are required'));
    exit;
}

sendFlowMessage($_chatId, $_contents);

echo json_encode(array('success' => true));
```

- [ ] **Step 2: 파라미터 누락 동작 확인**

```bash
curl -i "http://localhost/cw_as_0924/webhook/flow_message.php?secret=i7FTH8PmHlYX0tEKQegxMaNe2sM_VBSLxdwL1kM2pGA&chatId=&contents="
```
예상:
```
HTTP/1.1 400 Bad Request
{"success":false,"error":"chatId and contents are required"}
```

- [ ] **Step 3: 인증 실패 동작 확인**

```bash
curl -i "http://localhost/cw_as_0924/webhook/flow_message.php?secret=wrong&chatId=12345&contents=테스트"
```
예상:
```
HTTP/1.1 403 Forbidden
{"success":false,"error":"Unauthorized"}
```

- [ ] **Step 4: 정상 전송 확인**

실제 Flow chatId와 시크릿 키로 요청 (chatId는 실제 값으로 교체):
```bash
curl -i "http://localhost/cw_as_0924/webhook/flow_message.php?secret=i7FTH8PmHlYX0tEKQegxMaNe2sM_VBSLxdwL1kM2pGA&chatId=실제chatId&contents=웹훅+테스트"
```
예상:
```
HTTP/1.1 200 OK
{"success":true}
```
Flow 채팅방에서 메시지 수신 확인.

- [ ] **Step 5: 커밋**

```bash
git add webhook/flow_message.php
git commit -m "feat: Flow 메시지 웹훅 핸들러 구현"
```
