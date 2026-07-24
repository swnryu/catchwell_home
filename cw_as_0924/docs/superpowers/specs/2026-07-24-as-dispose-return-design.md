# A/S 견적 후 폐기/반송 신청 기능 설계

날짜: 2026-07-24

## 배경 / 목적

A/S 접수 후 견적서 발행 시 고객에게 카카오 알림톡("견적서 확인하기" 버튼, `online_as_estimate.php` 링크)이 발송된다. 현재 이 페이지에는 수리비 가상계좌 발급 기능만 있고, 고객이 수리를 원하지 않을 때(폐기 또는 반송을 원할 때) 선택할 수 있는 기능이 없다 (페이지 안내문구엔 "수리 거부 시 반송 처리됩니다"라고 되어 있으나 실제 구현은 없음). 이번 작업으로 고객이 견적 확인 화면에서 폐기/반송을 직접 신청할 수 있게 하고, 담당자가 각 상태별로 별도 리스트에서 관리할 수 있게 한다.

## ⚠️ 배포 경로 (매우 중요)

`online_as_estimate.php`로 연결되는 카카오 알림톡 링크는 `kakao/CKakaoNotificationTalkEx.php`의 `shipmentNotiMsg_estimate()`에 **`https://csadmin.catchwell.com/cw_as/...`로 하드코딩**되어 있다. 이 프로젝트에서 그동안 다뤄온 `cw_as_265`(관리자 UI 배포 대상)나 `cw_as_0924`(로컬 폴더명, 이니시스 PG 콜백 경로)와는 **완전히 다른, 세 번째 서버 물리 경로**(`\\211.54.90.200\web\cw_as\`)다.

- `cw_as`의 `common.php`/`common_lib.php`는 2020년 버전이라 `sendFlowMessage()` 등 공용 함수가 없다. `def_inc.php`(2025-02)/`header.php`(2024-10)는 비교적 최신이며 이 프로젝트와 동일한 메뉴 상수/네비 패턴을 그대로 쓴다.
- `config.php`가 `$DB_HOST="localhost"`로 별도 연결하지만 `$DB_NAME="cw_as"`로 동일 데이터베이스를 가리키므로 **DB 스키마(테이블)는 공유** — 이번 기능은 기존 `as_parcel_service.process_state` 컬럼에 새 값만 추가하는 것이라 스키마 변경이 전혀 없다.
- **이번 기능의 모든 변경 파일(고객 페이지, 처리 핸들러, 관리자 메뉴)은 `cw_as` 경로에만 반영한다.** `cw_as_265`/`cw_as_0924`는 이번 작업 범위에서 제외.
- `sendFlowMessage()`가 없으므로 신규 처리 핸들러에는 `mx_rnoti.php` 때와 동일하게 자체 포함(self-contained) Flow 발송 함수를 작성한다.

## 1. 신규 상태값

`process_state`에 다음 2개 값을 추가한다 (기존 0~9, 99 사용 중, 10/11 비어있음 확인됨):

```php
define("ST_DISPOSAL_REQUESTED", 10);  // 폐기요청
define("ST_RETURN_REQUESTED", 11);    // 반송요청
```

`$proc_state` 배열에도 `10=>"폐기요청", 11=>"반송요청"` 추가.

## 2. 고객 화면 (`online_as/online_as_estimate.php`)

기존 "가상계좌 발급하기" 버튼 영역 아래에 두 버튼 추가:
- "폐기 신청" — 클릭 시 확인창("정말 폐기 신청하시겠습니까? 취소할 수 없습니다") → 확인 시 신규 처리 페이지로 POST(`reg_num`, `action=dispose`)
- "반송 신청" — 동일 패턴, `action=return`

**표시 조건**: 이미 `process_state`가 10 또는 11(이미 신청 완료)이면 버튼 대신 "폐기 신청이 접수되었습니다" / "반송 신청이 접수되었습니다" 안내만 표시. 그 외의 경우(가상계좌 발급 여부와 무관하게) 두 버튼 모두 노출 — 가상계좌를 이미 발급받은 고객도 마음 바꿔 폐기/반송을 선택할 수 있어야 한다는 요건 반영.

## 3. 처리 핸들러 (`online_as/online_as_dispose_return_ok.php`, 신규)

- `reg_num`, `action`(dispose/return)을 POST로 받아 `as_parcel_service`에서 조회.
- `action=dispose` → `process_state=10`(`ST_DISPOSAL_REQUESTED`), `action=return` → `process_state=11`(`ST_RETURN_REQUESTED`)로 업데이트.
- `as_process_history`에 `(as_idx, reg_num, prev_state, new_state, changed_by='고객(폐기신청)' 또는 '고객(반송신청)', changed_at=now())` 기록 — 기존 감사 추적 관례 그대로.
- 자체 포함 Flow 발송 함수로 채팅방 **4186691**에 즉시 알림:
  ```
  [폐기 신청] 또는 [반송 신청]
  ▸ 접수번호: {reg_num}
  ▸ 고객명: {customer_name}
  ▸ 연락처: {customer_phone}
  ▸ 모델명: {product_name}
  ▸ 시간: {처리시각}
  ```
- 처리 후 고객에게 "신청이 접수되었습니다" 안내 화면으로 이동(리다이렉트 또는 같은 페이지에 안내 표시).

## 4. 관리자 화면

기존 `online_as.php?state=X` 제네릭 리스트 페이지를 그대로 재사용한다 (신규 리스트 페이지를 만들 필요 없음 — `online_as.php`가 이미 `where process_state=$state` 기반으로 동작).

- `def_inc.php`에 `S_AS_DISPOSAL="sub_as_disposal"`, `S_AS_RETURN="sub_as_return"` 메뉴 상수 추가.
- `online_as.php`의 상태→메뉴 매핑 `switch($state)`에 `case ST_DISPOSAL_REQUESTED: $menu = S_AS_DISPOSAL; break;`, `case ST_RETURN_REQUESTED: $menu = S_AS_RETURN; break;` 추가 (사이드바 활성 메뉴 표시가 정확해짐).
- `header.php`의 "AS신청서 관리" 드롭다운과 좌측 사이드바에 "폐기요청 보기"(`online_as.php?state=<?echo ST_DISPOSAL_REQUESTED;?>`), "반송요청 보기"(`state=<?echo ST_RETURN_REQUESTED;?>`) 링크 추가.

## 5. 에러 처리 / 엣지 케이스

- 이미 폐기/반송 신청된 건에 재요청 시 처리 핸들러에서 현재 `process_state`가 10/11이면 중복 처리하지 않고 "이미 신청되었습니다" 안내만 표시 (Flow 알림 중복 발송 방지).
- `reg_num`이 존재하지 않거나 `customer_phone`이 일치하지 않으면(다른 고객번호로 접근 시도) 처리하지 않고 에러 표시 — 기존 `online_as_estimate.php`의 `searchData`+`searchValuePhone` 조회 패턴과 동일한 보안 수준 유지.
- 폐기/반송 신청 후 담당자가 실제 처리를 완료하면 이후 상태(예: 완료 처리)로 어떻게 전환할지는 이번 범위에 포함하지 않는다 — 우선 "요청 접수 + 리스트 관리"까지만 구현하고, 완료 처리 방식은 실제 운영해보고 후속 결정.

## 6. 범위 밖 (Out of scope)

- 반송 시 실제 택배 회수/배송 프로세스 자동화는 포함하지 않는다 (기존 회수 택배비 흐름과는 별개 — 폐기/반송 "신청"까지만).
- 폐기/반송 요청 완료(처리 종료) 상태로의 전환 기능은 이번 범위에 포함하지 않는다.
- `cw_as_265`/`cw_as_0924`로의 반영은 포함하지 않는다 (배포 경로 참고).
