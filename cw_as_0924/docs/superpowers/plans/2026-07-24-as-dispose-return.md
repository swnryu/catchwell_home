# A/S 견적 후 폐기/반송 신청 기능 Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** 고객이 A/S 견적 확인 화면(`online_as_estimate.php`)에서 "폐기 신청"/"반송 신청"을 직접 선택할 수 있게 하고, 담당자가 각 상태별 리스트에서 관리할 수 있게 한다.

**Architecture:** `process_state`에 신규 값 2개(10=폐기요청, 11=반송요청) 추가. 신규 처리 핸들러가 상태 변경+이력 기록+Flow 알림을 수행. 관리자 리스트는 기존 `online_as.php?state=X` 제네릭 페이지 재사용.

**Tech Stack:** PHP(구버전, `cw_as` 경로 — PHP 5.6과 유사하게 `??` 등 최신 문법 금지, `isset()?:` 사용), curl(Flow API 자체 포함 호출).

**Spec:** `docs/superpowers/specs/2026-07-24-as-dispose-return-design.md`

## Global Constraints

- **배포 대상은 오직 `\\211.54.90.200\web\cw_as\`(실제 고객이 카카오 링크로 접근하는 서버 경로) 뿐이다.** 이 경로는 우리 로컬 git 저장소(`C:\claude\cw_as_0924`)에 포함되지 않는 별도의 운영 디렉터리이므로, `deploy.ps1`을 사용하지 않는다.
- 이 계획의 각 Task는 **로컬 스테이징 파일**(`C:\claude\cw_as_0924\_cw_as_pending\` 하위)을 만들고 git으로 커밋하는 것까지만 진행한다. **실제 `cw_as` 서버 파일을 덮어쓰는 배포(Task 6)는 사용자가 명시적으로 "배포해줘"라고 할 때만 실행한다.**
- `cw_as`의 `common.php`/`common_lib.php`는 2020년 버전이라 `sendFlowMessage()` 공용 함수가 없다 — 신규 처리 핸들러는 자체 포함(self-contained) Flow 발송 함수를 사용한다 (기존 `cw_as_0924/pg_m/mx_rnoti.php` 패턴과 동일).
- Flow 채팅방ID는 `4186691` (기존 재사용), API 키/발신자ID는 `20251203050955646-6ab56428-4e53-469e-b564-420e2ce4c4c9` / `swryu@catchwell.com`.
- PHP 구문은 이 프로젝트 전체 관례(`isset($x) ? $x : $default`, `??` 금지)를 따른다.

---

## 파일 구조

| 파일 | 작업 | 역할 |
|---|---|---|
| `_cw_as_pending/def_inc.php` | 신규(라이브 파일 복사 후 수정) | 상태값 10/11, 메뉴 상수 추가 |
| `_cw_as_pending/online_as/online_as.php` | 신규(라이브 파일 복사 후 수정) | 신규 상태 → 메뉴 매핑 추가 |
| `_cw_as_pending/header.php` | 신규(라이브 파일 복사 후 수정) | 상단 네비/사이드바에 "폐기요청 보기"/"반송요청 보기" 추가 |
| `_cw_as_pending/online_as/online_as_estimate.php` | 신규(라이브 파일 복사 후 수정) | 폐기/반송 신청 버튼 + 이미 신청됨 안내 |
| `_cw_as_pending/online_as/online_as_dispose_return_ok.php` | 신규 작성 | 처리 핸들러 (상태변경+이력+Flow 알림) |

---

### Task 1: `def_inc.php` — 상태값/메뉴 상수 추가

**Files:**
- Create: `_cw_as_pending/def_inc.php` (라이브 파일을 복사해서 시작)

- [ ] **Step 1: 라이브 파일을 스테이징 위치로 복사**

```bash
mkdir -p "C:/claude/cw_as_0924/_cw_as_pending"
cp "//211.54.90.200/web/cw_as/def_inc.php" "C:/claude/cw_as_0924/_cw_as_pending/def_inc.php"
```

- [ ] **Step 2: 상태 상수 추가**

`_cw_as_pending/def_inc.php`에서 다음 부분을 찾아:

```php
	define("ST_AS_COMPLETED", 4);	//출고
	//define("ST_AS_ALL", 5);	//전체검색 20230707 이건 추가는 했지만 굳이 필요없을듯

	define("ST_CANCELED", 99);		//취소
```

다음으로 교체:

```php
	define("ST_AS_COMPLETED", 4);	//출고
	//define("ST_AS_ALL", 5);	//전체검색 20230707 이건 추가는 했지만 굳이 필요없을듯

	define("ST_DISPOSAL_REQUESTED", 10);	//폐기요청 //20260724
	define("ST_RETURN_REQUESTED", 11);		//반송요청 //20260724

	define("ST_CANCELED", 99);		//취소
```

- [ ] **Step 3: `$proc_state` 배열에 표시명 추가**

다음을 찾아:

```php
	$proc_state		= array("접수중","접수완료","수리중","수리완료","출고","취소");
```

다음으로 교체:

```php
	$proc_state		= array("접수중","접수완료","수리중","수리완료","출고","취소", 10=>"폐기요청", 11=>"반송요청");
```

- [ ] **Step 4: 메뉴 상수 추가**

다음을 찾아:

```php
	define("S_AS_SHIPMENT", "sub_as_shipment");//20230707 출고완료 추가
	define("S_AS_REPORT", "sub_as_report");//20230707 전체검색
```

다음으로 교체:

```php
	define("S_AS_SHIPMENT", "sub_as_shipment");//20230707 출고완료 추가
	define("S_AS_REPORT", "sub_as_report");//20230707 전체검색
	define("S_AS_DISPOSAL", "sub_as_disposal");//20260724 폐기요청
	define("S_AS_RETURN", "sub_as_return");//20260724 반송요청
```

- [ ] **Step 5: 정적 검토**

- 위 4곳의 삽입이 정확히 반영됐는지, 기존 코드가 그 외에는 한 글자도 바뀌지 않았는지 `diff`로 확인:
  ```bash
  diff "//211.54.90.200/web/cw_as/def_inc.php" "C:/claude/cw_as_0924/_cw_as_pending/def_inc.php"
  ```
  위 4개 삽입 블록만 `>`로 나와야 한다.
- `define("ST_DISPOSAL_REQUESTED", 10)`/`define("ST_RETURN_REQUESTED", 11)`가 기존 어떤 상수와도 값이 겹치지 않는지 확인(기존 정의된 값: 0,1,2,3,4,99).

- [ ] **Step 6: 로컬 커밋**

```bash
cd C:\claude\cw_as_0924
git add _cw_as_pending/def_inc.php
git commit -m "feat: cw_as 폐기/반송 신청 기능 - def_inc.php 상태값/메뉴 상수 스테이징"
```

---

### Task 2: `online_as.php` — 상태→메뉴 매핑 추가

**Files:**
- Create: `_cw_as_pending/online_as/online_as.php` (라이브 파일을 복사해서 시작)

**Interfaces:**
- Consumes: Task 1에서 정의한 `ST_DISPOSAL_REQUESTED`, `ST_RETURN_REQUESTED`, `S_AS_DISPOSAL`, `S_AS_RETURN`

- [ ] **Step 1: 라이브 파일 복사**

```bash
mkdir -p "C:/claude/cw_as_0924/_cw_as_pending/online_as"
cp "//211.54.90.200/web/cw_as/online_as/online_as.php" "C:/claude/cw_as_0924/_cw_as_pending/online_as/online_as.php"
```

- [ ] **Step 2: switch문에 case 추가**

다음을 찾아:

```php
	case ST_AS_COMPLETED: 	$menu	= S_AS_COMPLETED; break;
	default: 				$menu	= S_AS_REGISTERING; break;
```

다음으로 교체:

```php
	case ST_AS_COMPLETED: 	$menu	= S_AS_COMPLETED; break;
	case ST_DISPOSAL_REQUESTED: $menu = S_AS_DISPOSAL; break;
	case ST_RETURN_REQUESTED:   $menu = S_AS_RETURN; break;
	default: 				$menu	= S_AS_REGISTERING; break;
```

- [ ] **Step 3: 정적 검토**

```bash
diff "//211.54.90.200/web/cw_as/online_as/online_as.php" "C:/claude/cw_as_0924/_cw_as_pending/online_as/online_as.php"
```
삽입한 2줄 외에는 차이가 없어야 한다. `$where = "where process_state=$state"` 로직은 상태값과 무관하게 이미 동작하므로 별도 수정 불필요함을 확인.

- [ ] **Step 4: 로컬 커밋**

```bash
cd C:\claude\cw_as_0924
git add _cw_as_pending/online_as/online_as.php
git commit -m "feat: cw_as 폐기/반송 신청 기능 - online_as.php 상태 매핑 스테이징"
```

---

### Task 3: `header.php` — 관리자 메뉴 연결

**Files:**
- Create: `_cw_as_pending/header.php` (라이브 파일을 복사해서 시작)

**Interfaces:**
- Consumes: Task 1의 `ST_DISPOSAL_REQUESTED`, `ST_RETURN_REQUESTED`, `S_AS_DISPOSAL`, `S_AS_RETURN`

- [ ] **Step 1: 라이브 파일 복사**

```bash
cp "//211.54.90.200/web/cw_as/header.php" "C:/claude/cw_as_0924/_cw_as_pending/header.php"
```

- [ ] **Step 2: 상단 네비 드롭다운에 링크 추가**

다음을 찾아:

```php
					<li><a href="<?=$site_url?>/online_as/online_as_shipment.php">AS 출고완료</a></li><!--20230707 출고완료 추가 -->
					<li><a href="<?=$site_url?>/online_as/online_as_report.php">AS 전체 검색</a></li><!--20230707 전체검색 -->
```

다음으로 교체:

```php
					<li><a href="<?=$site_url?>/online_as/online_as_shipment.php">AS 출고완료</a></li><!--20230707 출고완료 추가 -->
					<li><a href="<?=$site_url?>/online_as/online_as_report.php">AS 전체 검색</a></li><!--20230707 전체검색 -->
					<li><a href="<?=$site_url?>/online_as/online_as.php?state=<?echo ST_DISPOSAL_REQUESTED;?>">폐기요청 보기</a></li><!--20260724-->
					<li><a href="<?=$site_url?>/online_as/online_as.php?state=<?echo ST_RETURN_REQUESTED;?>">반송요청 보기</a></li><!--20260724-->
```

- [ ] **Step 3: 좌측 사이드바에 링크 추가**

다음을 찾아:

```php
					<a href="<?=$site_url?>/online_as/online_as_shipment.php" class="list-group-item <?if($menu==S_AS_SHIPMENT){?>active<?}?>">AS 출고완료</a><!--20230707-->
					<a href="<?=$site_url?>/online_as/online_as_report.php" class="list-group-item <?if($menu==S_AS_REPORT){?>active<?}?>">AS 전체 검색</a><!--20230707-->

					<a href="<?=$site_url?>/online_as/template/user_manual_as.pdf" class="list-group-item " target="_blank">사용자 매뉴얼</a><!-- 20210803 -->
```

다음으로 교체:

```php
					<a href="<?=$site_url?>/online_as/online_as_shipment.php" class="list-group-item <?if($menu==S_AS_SHIPMENT){?>active<?}?>">AS 출고완료</a><!--20230707-->
					<a href="<?=$site_url?>/online_as/online_as_report.php" class="list-group-item <?if($menu==S_AS_REPORT){?>active<?}?>">AS 전체 검색</a><!--20230707-->
					<a href="<?=$site_url?>/online_as/online_as.php?state=<?echo ST_DISPOSAL_REQUESTED;?>" class="list-group-item <?if($menu==S_AS_DISPOSAL){?>active<?}?>">폐기요청 보기</a><!--20260724-->
					<a href="<?=$site_url?>/online_as/online_as.php?state=<?echo ST_RETURN_REQUESTED;?>" class="list-group-item <?if($menu==S_AS_RETURN){?>active<?}?>">반송요청 보기</a><!--20260724-->

					<a href="<?=$site_url?>/online_as/template/user_manual_as.pdf" class="list-group-item " target="_blank">사용자 매뉴얼</a><!-- 20210803 -->
```

- [ ] **Step 4: 정적 검토**

```bash
diff "//211.54.90.200/web/cw_as/header.php" "C:/claude/cw_as_0924/_cw_as_pending/header.php"
```
위 두 삽입 블록(각 2줄)만 `>`로 나와야 한다. `PERMISSION_GROUP_CS` 가드 블록 안에 있으므로 별도 권한 추가 불필요함을 확인.

- [ ] **Step 5: 로컬 커밋**

```bash
cd C:\claude\cw_as_0924
git add _cw_as_pending/header.php
git commit -m "feat: cw_as 폐기/반송 신청 기능 - header.php 메뉴 연결 스테이징"
```

---

### Task 4: `online_as_estimate.php` — 고객 화면 버튼 추가

**Files:**
- Create: `_cw_as_pending/online_as/online_as_estimate.php` (라이브 파일을 복사해서 시작)

**Interfaces:**
- Produces: `online_as_dispose_return_ok.php`로 POST (`reg_num`, `searchValuePhone`, `action`=`dispose`|`return`)

- [ ] **Step 1: 라이브 파일 복사**

```bash
cp "//211.54.90.200/web/cw_as/online_as/online_as_estimate.php" "C:/claude/cw_as_0924/_cw_as_pending/online_as/online_as_estimate.php"
```

- [ ] **Step 2: 가상계좌 표시 블록을 감싸고 폐기/반송 버튼 추가**

다음 블록을 찾아 (정확히 `<?php if ($vbank && $vbank->P_OID === $repair_oid): ?>`부터 `<?php endif; ?>`까지):

```php
<?php if ($vbank && $vbank->P_OID === $repair_oid): ?>
    <!-- ── 이미 발급된 가상계좌 ── -->
    <div class="vbank-card">
        <div class="vc-title">
            <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            가상계좌 발급 완료
        </div>
        <div class="vbank-row">
            <span class="lbl">은행</span>
            <span class="val"><?php echo htmlspecialchars($vbank->P_FN_NM); ?></span>
        </div>
        <div class="account-box" onclick="copyAcct('<?php echo htmlspecialchars($vbank->P_VACT_NUM); ?>')">
            <span class="account-num"><?php echo htmlspecialchars($vbank->P_VACT_NUM); ?></span>
            <span class="copy-hint">터치하면 계좌번호가 복사됩니다</span>
        </div>
        <div class="vbank-amt">
            입금 금액&nbsp;&nbsp;<strong><?php echo number_format($vbank->P_AMT); ?>원</strong>
        </div>
    </div>

    <div class="notice">
        <div class="n-title">※ 입금 시 주의사항</div>
        <ul>
            <li>반드시 안내된 금액과 동일하게 입금해 주세요.</li>
            <li>입금 확인 후 수리 작업이 자동으로 시작됩니다.</li>
        </ul>
    </div>

<?php elseif ($repair_price > 0): ?>
    <!-- ── 가상계좌 발급 폼 ── -->
    <div class="issue-card">
        <p class="guide">수리 진행을 원하시면 가상계좌를 발급받아 수리비를 입금해 주세요.<br>입금 확인 즉시 수리가 시작됩니다.</p>
        <form name="mobileweb" method="post" accept-charset="euc-kr">
            <input type="hidden" name="P_INI_PAYMENT" value="VBANK">
            <input type="hidden" name="P_MID"         value="CAEcatca07">
            <input type="hidden" name="P_GOODS"       value="수리비">
            <input type="hidden" name="P_EMAIL"       value="">
            <input type="hidden" name="P_NEXT_URL"    value="https://csadmin.catchwell.com/cw_as_0924/pg_m/INImobile_mo_repair_return.php">
            <input type="hidden" name="P_NOTI_URL"    value="https://csadmin.catchwell.com/cw_as_0924/pg_m/mx_rnoti.php">
            <input type="hidden" name="P_CHARSET"     value="utf8">
            <input type="hidden" name="P_RESERVED"    value="vbank_receipt=N&centerCd=Y">
            <input type="hidden" name="P_OID"         value="<?php echo htmlspecialchars($repair_oid); ?>">
            <input type="hidden" name="P_AMT"         value="<?php echo $repair_price; ?>">
            <input type="hidden" name="P_UNAME"       value="<?php echo htmlspecialchars($row_as['customer_name']); ?>">
            <input type="hidden" name="P_MOBILE"      value="<?php echo htmlspecialchars($row_as['customer_phone']); ?>">
            <button type="button" class="btn-issue" onclick="onPay()">
                가상계좌 발급하기
            </button>
        </form>
    </div>

    <div class="notice">
        <div class="n-title">※ 안내사항</div>
        <ul>
            <li>가상계좌는 발급 후 7일 이내에 입금해 주세요.</li>
            <li>입금 확인 후 수리가 시작됩니다.</li>
            <li>수리 거부 시 반송 처리됩니다.</li>
        </ul>
    </div>

<?php else: ?>
    <div class="card">
        <p style="font-size:14px; color:var(--gray-600); text-align:center; padding: 10px 0;">
            수리비가 아직 설정되지 않았습니다.<br>고객센터로 문의해 주세요.
        </p>
    </div>
<?php endif; ?>
```

다음으로 교체 (기존 3-way 분기를 `process_state` 10/11 체크로 한 번 더 감싸고, 그 아래에 폐기/반송 신청 폼을 추가):

```php
<?php if ((int)$row_as['process_state'] === 10): ?>
    <!-- ── 폐기 신청 접수됨 ── -->
    <div class="notice">
        <div class="n-title">폐기 신청이 접수되었습니다</div>
        <ul>
            <li>담당자가 확인 후 처리해 드립니다.</li>
        </ul>
    </div>
<?php elseif ((int)$row_as['process_state'] === 11): ?>
    <!-- ── 반송 신청 접수됨 ── -->
    <div class="notice">
        <div class="n-title">반송 신청이 접수되었습니다</div>
        <ul>
            <li>담당자가 확인 후 처리해 드립니다.</li>
        </ul>
    </div>
<?php else: ?>

<?php if ($vbank && $vbank->P_OID === $repair_oid): ?>
    <!-- ── 이미 발급된 가상계좌 ── -->
    <div class="vbank-card">
        <div class="vc-title">
            <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            가상계좌 발급 완료
        </div>
        <div class="vbank-row">
            <span class="lbl">은행</span>
            <span class="val"><?php echo htmlspecialchars($vbank->P_FN_NM); ?></span>
        </div>
        <div class="account-box" onclick="copyAcct('<?php echo htmlspecialchars($vbank->P_VACT_NUM); ?>')">
            <span class="account-num"><?php echo htmlspecialchars($vbank->P_VACT_NUM); ?></span>
            <span class="copy-hint">터치하면 계좌번호가 복사됩니다</span>
        </div>
        <div class="vbank-amt">
            입금 금액&nbsp;&nbsp;<strong><?php echo number_format($vbank->P_AMT); ?>원</strong>
        </div>
    </div>

    <div class="notice">
        <div class="n-title">※ 입금 시 주의사항</div>
        <ul>
            <li>반드시 안내된 금액과 동일하게 입금해 주세요.</li>
            <li>입금 확인 후 수리 작업이 자동으로 시작됩니다.</li>
        </ul>
    </div>

<?php elseif ($repair_price > 0): ?>
    <!-- ── 가상계좌 발급 폼 ── -->
    <div class="issue-card">
        <p class="guide">수리 진행을 원하시면 가상계좌를 발급받아 수리비를 입금해 주세요.<br>입금 확인 즉시 수리가 시작됩니다.</p>
        <form name="mobileweb" method="post" accept-charset="euc-kr">
            <input type="hidden" name="P_INI_PAYMENT" value="VBANK">
            <input type="hidden" name="P_MID"         value="CAEcatca07">
            <input type="hidden" name="P_GOODS"       value="수리비">
            <input type="hidden" name="P_EMAIL"       value="">
            <input type="hidden" name="P_NEXT_URL"    value="https://csadmin.catchwell.com/cw_as_0924/pg_m/INImobile_mo_repair_return.php">
            <input type="hidden" name="P_NOTI_URL"    value="https://csadmin.catchwell.com/cw_as_0924/pg_m/mx_rnoti.php">
            <input type="hidden" name="P_CHARSET"     value="utf8">
            <input type="hidden" name="P_RESERVED"    value="vbank_receipt=N&centerCd=Y">
            <input type="hidden" name="P_OID"         value="<?php echo htmlspecialchars($repair_oid); ?>">
            <input type="hidden" name="P_AMT"         value="<?php echo $repair_price; ?>">
            <input type="hidden" name="P_UNAME"       value="<?php echo htmlspecialchars($row_as['customer_name']); ?>">
            <input type="hidden" name="P_MOBILE"      value="<?php echo htmlspecialchars($row_as['customer_phone']); ?>">
            <button type="button" class="btn-issue" onclick="onPay()">
                가상계좌 발급하기
            </button>
        </form>
    </div>

    <div class="notice">
        <div class="n-title">※ 안내사항</div>
        <ul>
            <li>가상계좌는 발급 후 7일 이내에 입금해 주세요.</li>
            <li>입금 확인 후 수리가 시작됩니다.</li>
            <li>수리 거부 시 반송 처리됩니다.</li>
        </ul>
    </div>

<?php else: ?>
    <div class="card">
        <p style="font-size:14px; color:var(--gray-600); text-align:center; padding: 10px 0;">
            수리비가 아직 설정되지 않았습니다.<br>고객센터로 문의해 주세요.
        </p>
    </div>
<?php endif; ?>

    <!-- ── 폐기/반송 신청 (가상계좌 발급 여부와 무관하게 항상 노출) ── -->
    <div class="card" style="padding:16px 20px;">
        <p style="font-size:13px; color:var(--gray-600); margin-bottom:12px;">수리를 원하지 않으시면 아래에서 신청해 주세요. 신청 후에는 취소할 수 없습니다.</p>
        <form name="disposeReturnForm" method="post" action="online_as_dispose_return_ok.php">
            <input type="hidden" name="reg_num" value="<?php echo htmlspecialchars($searchData); ?>">
            <input type="hidden" name="searchValuePhone" value="<?php echo htmlspecialchars($searchValuePhone); ?>">
            <input type="hidden" name="action" value="">
            <button type="button" class="btn-issue" style="background:#6b7280; margin-bottom:8px;" onclick="submitDisposeReturn('dispose')">폐기 신청</button>
            <button type="button" class="btn-issue" style="background:#9ca3af;" onclick="submitDisposeReturn('return')">반송 신청</button>
        </form>
    </div>

<?php endif; ?>
```

- [ ] **Step 3: JS 함수 추가**

다음을 찾아:

```html
<script>
function onPay() {
```

다음으로 교체:

```html
<script>
function submitDisposeReturn(action) {
    var label = (action === 'dispose') ? '폐기' : '반송';
    if (!confirm('정말 ' + label + ' 신청하시겠습니까? 신청 후에는 취소할 수 없습니다.')) { return; }
    var f = document.disposeReturnForm;
    f.action.value = action;
    f.submit();
}

function onPay() {
```

- [ ] **Step 4: 정적 검토**

```bash
diff "//211.54.90.200/web/cw_as/online_as/online_as_estimate.php" "C:/claude/cw_as_0924/_cw_as_pending/online_as/online_as_estimate.php"
```

확인할 것:
- 기존 3-way(`if/elseif/else`) 분기가 새 바깥쪽 `if/elseif/else`(process_state 10/11/그외) 안에 그대로 온전히 들어있는지 (블록 하나 통째로 옮긴 것이므로 내용 손실 없어야 함).
- `<?php endif; ?>`가 정확히 2개(안쪽 3-way용 1개 + 바깥쪽 신규용 1개) 늘어났는지.
- `$row_as['process_state']`가 실제로 `SELECT *`로 조회된 컬럼에 존재하는지 (파일 상단 쿼리 확인).
- 폐기/반송 폼의 `reg_num`/`searchValuePhone` hidden 필드값이 페이지 진입시 받은 `$searchData`/`$searchValuePhone`과 정확히 일치하는지 (조작 방지를 위해 처리 핸들러가 재조회 시 동일 값으로 검증할 것이므로 정확해야 함).
- 새 `<script>` 블록의 `submitDisposeReturn` 함수가 기존 `onPay`/`copyAcct`/`openPhoto`/`showToast` 함수를 건드리지 않았는지.

- [ ] **Step 5: 로컬 커밋**

```bash
cd C:\claude\cw_as_0924
git add _cw_as_pending/online_as/online_as_estimate.php
git commit -m "feat: cw_as 폐기/반송 신청 기능 - 고객 견적화면 버튼 스테이징"
```

---

### Task 5: `online_as_dispose_return_ok.php` — 처리 핸들러 신규 작성

**Files:**
- Create: `_cw_as_pending/online_as/online_as_dispose_return_ok.php`

**Interfaces:**
- Consumes: POST `reg_num`, `searchValuePhone`, `action`(`dispose`|`return`) — Task 4의 폼과 필드명 일치해야 함
- Produces: `as_parcel_service.process_state` = 10 또는 11, `as_process_history` 신규 row, Flow 알림(4186691)

- [ ] **Step 1: 파일 작성**

`_cw_as_pending/online_as/online_as_dispose_return_ok.php`:

```php
<?php
error_reporting(E_ALL);

include("../common.php");

$reg_num          = isset($_POST['reg_num']) ? $_POST['reg_num'] : '';
$searchValuePhone = isset($_POST['searchValuePhone']) ? $_POST['searchValuePhone'] : '';
$action           = isset($_POST['action']) ? $_POST['action'] : '';

$redirect_url = "online_as_estimate.php?searchData=" . urlencode($reg_num) . "&searchValuePhone=" . urlencode($searchValuePhone);

if ($reg_num === '' || $searchValuePhone === '' || ($action !== 'dispose' && $action !== 'return')) {
    header("Location: " . $redirect_url);
    exit;
}

$reg_num_esc = mysqli_real_escape_string($db->db_conn, $reg_num);
$phone_esc   = mysqli_real_escape_string($db->db_conn, $searchValuePhone);

$row = $db->object("as_parcel_service", "where reg_num='$reg_num_esc' and customer_phone='$phone_esc'");

if (!$row) {
    header("Location: " . $redirect_url);
    exit;
}

$new_state  = ($action === 'dispose') ? 10 : 11; // ST_DISPOSAL_REQUESTED / ST_RETURN_REQUESTED
$prev_state = (int)$row->process_state;

// 이미 폐기/반송 신청된 건은 중복 처리하지 않음 (알림 중복 발송 방지)
if ($prev_state === 10 || $prev_state === 11) {
    header("Location: " . $redirect_url);
    exit;
}

$update_sql = "UPDATE as_parcel_service SET process_state = $new_state WHERE reg_num = '$reg_num_esc'";
if ($db->result($update_sql)) {
    $changed_by = ($action === 'dispose') ? '고객(폐기신청)' : '고객(반송신청)';
    $db->insert("as_process_history", "as_idx={$row->idx}, reg_num='$reg_num_esc', prev_state=$prev_state, new_state=$new_state, changed_by='$changed_by', changed_at=now()");

    $label = ($action === 'dispose') ? '폐기 신청' : '반송 신청';
    $flow_msg = "[$label]\n"
              . "▸ 접수번호: {$reg_num}\n"
              . "▸ 고객명: {$row->customer_name}\n"
              . "▸ 연락처: {$row->customer_phone}\n"
              . "▸ 모델명: {$row->product_name}\n"
              . "▸ 시간: " . date("Y-m-d H:i:s");
    _sendFlowMessageDisposeReturn(4186691, $flow_msg);
}

header("Location: " . $redirect_url);
exit;

// cw_as의 common_lib.php(2020년 버전)에는 sendFlowMessage()가 없어 자체 포함 함수 사용
// (cw_as_0924/pg_m/mx_rnoti.php의 _sendFlowMessageInline() 패턴과 동일)
function _sendFlowMessageDisposeReturn($chatId, $contents) {
    if (empty($chatId) || empty($contents)) return;

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL            => 'https://api.flow.team/v1/chats/' . (int)$chatId . '/messages',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING       => '',
        CURLOPT_MAXREDIRS      => 10,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST  => 'POST',
        CURLOPT_POSTFIELDS     => 'registerId=' . urlencode('swryu@catchwell.com') . '&contents=' . urlencode($contents),
        CURLOPT_HTTPHEADER     => array(
            'Content-Type: application/x-www-form-urlencoded',
            'x-flow-api-key: 20251203050955646-6ab56428-4e53-469e-b564-420e2ce4c4c9',
        ),
    ));
    curl_exec($curl);
    curl_close($curl);
}
?>
```

- [ ] **Step 2: 정적 검토**

- POST 필드명(`reg_num`,`searchValuePhone`,`action`)이 Task 4의 폼 필드명과 정확히 일치하는지 확인.
- `header("Location: ...")` 이전에 어떤 HTML/텍스트도 출력되지 않는지 확인 (`common.php`가 HTML을 출력하지 않음을 이미 확인함 — `header.php`를 include하지 않으므로 안전).
- `new_state`(10/11)가 Task 1에서 정의한 `ST_DISPOSAL_REQUESTED`/`ST_RETURN_REQUESTED` 값과 일치하는지 (하드코딩된 10/11이지만 주석으로 명시).
- 이미 10/11 상태인 건에 대해 재요청 시 상태변경/이력기록/Flow알림이 전부 스킵되고 조용히 리다이렉트만 되는지 로직을 손으로 트레이스.
- SQL 인젝션 방지: `reg_num`/`searchValuePhone`이 SQL에 들어가기 전 전부 `mysqli_real_escape_string`을 거치는지 확인.

- [ ] **Step 3: 로컬 커밋**

```bash
cd C:\claude\cw_as_0924
git add _cw_as_pending/online_as/online_as_dispose_return_ok.php
git commit -m "feat: cw_as 폐기/반송 신청 기능 - 처리 핸들러 신규 작성"
```

---

### Task 6: 배포 (사용자가 "배포해줘"라고 명시적으로 요청할 때만 실행)

**이 Task는 이 계획의 다른 Task들과 별도로, 사용자의 명시적 배포 요청이 있을 때만 실행한다. 자동으로 이어서 실행하지 않는다.**

- [ ] **Step 1: 배포 직전 라이브 파일 백업**

```bash
ts=$(date +%Y%m%d_%H%M%S)
mkdir -p "C:/claude/cw_as_backup/cw_as_live_${ts}"
cp "//211.54.90.200/web/cw_as/def_inc.php" "C:/claude/cw_as_backup/cw_as_live_${ts}/def_inc.php"
cp "//211.54.90.200/web/cw_as/header.php" "C:/claude/cw_as_backup/cw_as_live_${ts}/header.php"
cp "//211.54.90.200/web/cw_as/online_as/online_as.php" "C:/claude/cw_as_backup/cw_as_live_${ts}/online_as.php"
cp "//211.54.90.200/web/cw_as/online_as/online_as_estimate.php" "C:/claude/cw_as_backup/cw_as_live_${ts}/online_as_estimate.php"
```

- [ ] **Step 2: 스테이징 파일을 라이브 경로로 복사**

```bash
cp "C:/claude/cw_as_0924/_cw_as_pending/def_inc.php" "//211.54.90.200/web/cw_as/def_inc.php"
cp "C:/claude/cw_as_0924/_cw_as_pending/header.php" "//211.54.90.200/web/cw_as/header.php"
cp "C:/claude/cw_as_0924/_cw_as_pending/online_as/online_as.php" "//211.54.90.200/web/cw_as/online_as/online_as.php"
cp "C:/claude/cw_as_0924/_cw_as_pending/online_as/online_as_estimate.php" "//211.54.90.200/web/cw_as/online_as/online_as_estimate.php"
cp "C:/claude/cw_as_0924/_cw_as_pending/online_as/online_as_dispose_return_ok.php" "//211.54.90.200/web/cw_as/online_as/online_as_dispose_return_ok.php"
```

- [ ] **Step 3: 배포 확인**

```bash
curl -s -o /dev/null -w "%{http_code}\n" "https://csadmin.catchwell.com/cw_as/online_as/online_as.php?state=10"
curl -s -o /dev/null -w "%{http_code}\n" "https://csadmin.catchwell.com/cw_as/online_as/online_as_estimate.php"
```
둘 다 200이어야 함(로그인 필요한 `online_as.php`는 302 리다이렉트일 수 있음 — 500만 아니면 됨).

**롤백이 필요하면**: Step 1에서 백업한 `C:\claude\cw_as_backup\cw_as_live_{timestamp}\` 안의 파일들을 그대로 `//211.54.90.200/web/cw_as/`의 원래 위치로 다시 복사한다.

---

## Self-Review 체크리스트

- [x] 설계 문서 1장(상태값)→Task1, 2장(고객화면)→Task4, 3장(처리핸들러)→Task5, 4장(관리자화면)→Task2,3 매핑됨
- [x] 플레이스홀더 없음 — 전체 코드/정확한 삽입 위치 포함
- [x] Task 4의 폼 필드명(`reg_num`,`searchValuePhone`,`action`)과 Task 5의 `$_POST` 읽기가 정확히 일치
- [x] `cw_as`가 git 비추적 별도 서버 경로임을 Global Constraints에 명시, 배포(Task 6)는 별도 승인 필요함을 명시
