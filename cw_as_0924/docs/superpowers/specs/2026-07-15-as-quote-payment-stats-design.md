# AS 견적/입금 통계 메뉴 설계

날짜: 2026-07-15

## 배경 / 목적

관리자가 통계 메뉴에서 AS 신청 이후 발행된 수리비 견적 건수·금액과, 그에 대한 입금 여부·입금액을 일별/주별/월별로 확인할 수 있어야 한다. 기존 `online_as_stats.php` (AS 처리 통계: 입고/출고/처리기간 중심)와는 성격이 달라(견적·입금이라는 금액/자금 흐름 중심) 별도 메뉴로 분리한다.

## 1. 메뉴 구성

- `def_inc.php`에 메뉴 상수 `S_STATS_QUOTE = "sub_stats_quote"` 추가.
- `header.php`의 통계(`M_STATS`) 사이드바에 링크 추가:
  ```php
  <a href="<?=$site_url?>/online_as/online_as_quote_stats.php" class="list-group-item <?if($menu==S_STATS_QUOTE){?>active<?}?>">AS 견적/입금 통계</a>
  ```
  기존 "AS 통계"와 동일하게 `PERMISSION_ALL` 권한 가드 안에 배치.
- 신규 페이지: `online_as/online_as_quote_stats.php`. `online_as_stats.php`와 동일한 레이아웃 패턴(Bootstrap `.stat-card`/`.chart-box`, CanvasJS)을 재사용한다.

## 2. 데이터 모델 (기존 데이터 재사용, 신규 컬럼 없음)

당초 이 설계는 "견적 발행 시점"을 기록하는 신규 컬럼을 추가하는 안이었으나, 조사 결과 **이미 정확한 이력이 기록되고 있음을 확인**했다. 스키마 변경이나 기존 로직(`online_as_edit_ok.php`) 수정 없이 기존 데이터만으로 구현한다.

### 2.1 견적발행 이벤트는 이미 기록되어 있다

- `online_as_edit.php`의 "견적서발행" 버튼(`sendEstimate()`)은 `price > 0` 확인 후 `process_state = 2`(`ST_FIXING`)로 변경하고 카카오 알림톡으로 견적서를 발송한다.
- `def_inc.php`의 `$proc_state` 배열은 index 2를 "수리중"으로 표기하고 있으나 이는 레거시 오표기이며, 실제 업무 의미는 **"견적완료"**다 (`header.php:120,289`의 메뉴명 "견적완료 보기", `online_as_view.php:392`의 `$state_names[2] = '견적완료'`로 확인).
- `online_as_edit_ok.php`는 `process_state`가 바뀔 때마다 이미 `as_process_history`에 `(as_idx, reg_num, prev_state, new_state, changed_by, changed_at)`을 기록한다. 따라서 **`new_state=2`로 처음 전이된 시각이 곧 견적발행일**이며, 이는 이미 존재하는 데이터다.
- 운영 DB 확인 결과(2026-07-15 기준) `as_process_history`는 2026-05-22부터 쌓여 있고, `new_state=2` 이력 55건, `new_state=9`(수리비입금) 이력 47건이 존재한다. 즉 **2026-05-22 이후 발행된 견적부터 정확히 집계 가능**하다 (그 이전 데이터는 이력이 없어 집계 대상에서 자연히 제외됨 — 별도 처리 불필요, 안내 문구만 표시).

### 2.2 취소 건 제외

`process_state`가 취소(5, `proc_state[5]="취소"`) 또는 `ST_CANCELED`(99, 코드베이스에서 실사용 흔적 없는 레거시 값이지만 안전하게 함께 제외)인 건은 견적/입금 통계 전체에서 제외한다.

## 3. 집계 로직

### 3.1 견적발행 (기준일: `as_process_history.new_state=2`의 최초 `changed_at`)

```sql
SELECT as_idx, MIN(changed_at) as issued_at
FROM as_process_history
WHERE new_state = 2
GROUP BY as_idx
```
이 결과를 `as_parcel_service`와 `as_idx = idx`로 조인하여 견적금액(`price`)과 함께 사용한다. 일별은 `DATE(issued_at)`, 주별/월별은 PHP에서 버킷팅한다 (4장 참조).

### 3.2 입금여부 / 입금일

동일한 패턴으로 `new_state = 9` (ST_REPAIR_PAID)의 최초 이력을 조인:

```sql
SELECT as_idx, MIN(changed_at) as paid_at
FROM as_process_history
WHERE new_state = 9
GROUP BY as_idx
```

### 3.3 입금액

- `TB_INICIS_RETURN`에서 `P_OID = reg_num + '_R'`인 행이 있으면 `P_AMT`(실입금액) 사용.
- 없으면(관리자가 수동으로 상태만 9로 변경한 경우) `as_parcel_service.price`(견적금액)를 입금액으로 간주.

### 3.4 미입금

견적은 발행됐으나(3.1의 `issued_at`이 존재) 3.2 조인 결과가 없는 건.

## 4. 화면 구성

### 4.1 요약 카드 (상단, 이번 달 기준)

- 견적발행 건수 / 금액
- 입금완료 건수 / 금액
- 미입금 건수 / 금액
- 입금율 (입금완료 건수 / 견적발행 건수 %)

### 4.2 기간 탭 (일 / 주 / 월 전환 버튼)

- 일별: 최근 30일
- 주별: 최근 12주
- 월별: 최근 12개월
- 각 탭: 콤보 차트 (막대: 견적금액·입금액 / 라인: 건수), 기존 대시보드 색상(`#337ab7` 견적, `#5cb85c` 입금 등) 재사용.

### 4.3 상세 테이블

선택된 기간 범위에 해당하는 개별 AS 건 목록:

| 접수번호 | 견적일 | 견적금액 | 입금여부 | 입금일 | 입금액 | 고객명 | 담당자 |

접수번호 클릭 시 `online_as_edit.php?idx=...`로 이동(기존 관리 화면 재사용).

### 4.4 데이터 조회 쿼리 (단일 쿼리 + PHP 버킷팅)

일/주/월 세 가지 뷰와 상세 테이블을 모두 하나의 원본 데이터셋에서 만든다 (최근 400일치를 한 번에 조회 후 PHP에서 일/주/월로 묶는다 — 기존 `online_as_stats.php`가 일별은 SQL로, 이후 배열 조립은 PHP로 하는 것과 같은 결).

```sql
SELECT a.idx, a.reg_num, a.price, a.customer_name, a.pic_name,
       q.issued_at, p.paid_at, v.P_AMT as paid_amt_actual
FROM as_parcel_service a
JOIN (
    SELECT as_idx, MIN(changed_at) as issued_at
    FROM as_process_history
    WHERE new_state = 2
    GROUP BY as_idx
) q ON q.as_idx = a.idx
LEFT JOIN (
    SELECT as_idx, MIN(changed_at) as paid_at
    FROM as_process_history
    WHERE new_state = 9
    GROUP BY as_idx
) p ON p.as_idx = a.idx
LEFT JOIN TB_INICIS_RETURN v ON v.P_OID = CONCAT(a.reg_num, '_R')
WHERE a.process_state NOT IN (5, 99)
  AND q.issued_at >= DATE_SUB(NOW(), INTERVAL 400 DAY)
ORDER BY q.issued_at DESC
```

PHP에서 각 행마다 입금액을 `paid_at`이 있으면 `paid_amt_actual > 0 ? paid_amt_actual : price`, 없으면 0으로 계산한 뒤 일/주/월 버킷(배열 키)에 누적한다.

## 5. 에러 처리 / 엣지 케이스

- 견적발행 이력(`new_state=2`)이 없는 건은 INNER JOIN에 의해 자연히 집계 대상에서 제외됨 (아직 견적을 발행하지 않은 접수 건).
- 취소 건 제외 (2.2 참조).
- `TB_INICIS_RETURN` 조회 실패/데이터 없음 시 견적금액으로 폴백 (3.3 참조).
- `as_process_history` 자체가 2026-05-22부터 쌓였으므로, 그 이전에 발행된 견적은 이 통계에 잡히지 않는다. 페이지 상단에 "2026-05-22 이후 발행된 견적부터 집계됩니다" 안내 문구를 표시한다.

## 6. 범위 밖 (Out of scope)

- 택배비(회수 택배비, `process_state=6`) 입금 통계는 이번 범위에 포함하지 않는다 (기존 AS통계 대시보드와 중복 방지, 요청 범위는 수리비 견적/입금에 한정).
- `as_process_history` 도입 이전(2026-05-22 이전) 데이터에 대한 소급 추정은 하지 않는다.
