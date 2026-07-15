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

## 2. 데이터 모델 변경

### 2.1 신규 컬럼: `price_issued_at`

`as_parcel_service` 테이블에 컬럼 추가:

```sql
ALTER TABLE as_parcel_service ADD COLUMN price_issued_at DATETIME NULL AFTER price;
```

별도 마이그레이션 파일 `online_as/alter_table_quote_stats.sql`로 제공한다 (기존 `sponsored/alter_table_approval.sql` 관례를 따름).

### 2.2 기록 시점

`online_as_edit_ok.php`의 등록(insert) 및 수정(update) 처리에서, **price가 0(또는 빈 값)에서 양수로 바뀌는 시점에만** `price_issued_at = NOW()`를 함께 저장한다. 이미 `price_issued_at`이 채워져 있으면 값을 덮어쓰지 않는다 (최초 발행 시점 고정).

의사코드 (update 분기, `online_as_edit_ok.php` 내 `else { //update` 블록):

```php
$new_price = (int)$_POST['price'];
$price_issued_sql = '';
if ($new_price > 0 && (int)$row->price <= 0 && empty($row->price_issued_at)) {
    $price_issued_sql = ", price_issued_at=now()";
}
// $data 문자열에 $price_issued_sql 이어붙이기
```

insert(신규 접수) 분기도 동일 조건(`$price > 0`)이면 `price_issued_at=now()`를 함께 INSERT.

### 2.3 과거 데이터 처리

이 컬럼은 신규 추가이므로 과거 견적 발행 건은 `price_issued_at`이 비어 있다. 이런 건은 신규 통계 페이지의 집계에서 제외하고, 페이지 상단에 안내 문구를 표시한다: "이 통계는 기능 도입일(YYYY-MM-DD) 이후 발행된 견적부터 집계됩니다."

### 2.4 취소 건 제외

`process_state`가 취소(`ST_CANCELED`=99) 또는 5(취소, `proc_state[5]="취소"`)인 건은 견적/입금 통계 전체에서 제외한다.

## 3. 집계 로직

### 3.1 견적발행 (기준일: `price_issued_at`)

```sql
SELECT DATE(price_issued_at) as d, COUNT(*) as cnt, SUM(price) as amt
FROM as_parcel_service
WHERE price_issued_at IS NOT NULL
  AND process_state NOT IN (5, 99)
GROUP BY d
```
(주별/월별은 `YEARWEEK(price_issued_at,1)` / `DATE_FORMAT(price_issued_at,'%Y-%m')`로 그룹화, 기존 `online_as_stats.php`의 주별 집계 패턴 재사용)

### 3.2 입금여부 / 입금일

`as_process_history`에서 `new_state = 9` (ST_REPAIR_PAID)로 전이된 **최초** 이력을 조인:

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

견적은 발행됐으나(`price_issued_at IS NOT NULL`) 위 3.2 조인 결과가 없는 건.

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

## 5. 에러 처리 / 엣지 케이스

- `price_issued_at`이 NULL인 과거 데이터는 통계에서 제외 (2.3 참조).
- 취소 건 제외 (2.4 참조).
- `TB_INICIS_RETURN` 조회 실패/데이터 없음 시 견적금액으로 폴백 (3.3 참조).
- 견적금액이 0인 건(아직 견적 미발행)은애초에 `price_issued_at`이 NULL이므로 자연히 집계 대상에서 제외됨.

## 6. 범위 밖 (Out of scope)

- 택배비(회수 택배비, `process_state=6`) 입금 통계는 이번 범위에 포함하지 않는다 (기존 AS통계 대시보드와 중복 방지, 요청 범위는 수리비 견적/입금에 한정).
- 과거 데이터 소급 적용(예: `as_process_history`나 `admin_log`를 분석해 과거 견적발행일을 추정)은 하지 않는다.
