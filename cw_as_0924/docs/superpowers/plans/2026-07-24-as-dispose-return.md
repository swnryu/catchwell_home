# A/S 견적 후 폐기/반송 신청 기능 Implementation Plan

> **⚠️ 아키텍처 정정으로 이 문서의 원안(Task 1~6, "모든 변경을 `_cw_as_pending`에 스테이징 후 `cw_as`에만 배포")은 폐기되었다.**
> 실제 서버 구조를 재확인한 결과, 관리자측 코드는 원래부터 `cw_as_0924` 저장소(→`cw_as_265`로 배포)에 있어야 했고, 별도의 비-git 서버인 `cw_as`(고객이 카카오 링크로 접근하는 서버)는 `online_as_estimate.php` **한 파일만** 수정하면 되는 것이었다. 아래는 실제로 반영된 최종 구조이며, 원래 Task 1~6은 상세 이력 참고용으로만 하단에 남겨둔다.
>
> 최신 설계는 `docs/superpowers/specs/2026-07-24-as-dispose-return-design.md` 참고 (정정 커밋 `c3f6b53`).

**Goal:** 고객이 A/S 견적 확인 화면(`online_as_estimate.php`)에서 "폐기 신청"/"반송 신청"을 직접 선택할 수 있게 하고, 담당자가 각 상태별 리스트에서 관리할 수 있게 한다.

## 최종 아키텍처 (구현 완료)

| 위치 | 파일 | 배포 대상 | 상태 |
|---|---|---|---|
| `cw_as_0924/def_inc.php` | 상태값(10/11)·메뉴 상수 추가 | `cw_as_265` | 커밋 `da5147a` |
| `cw_as_0924/header.php` | 상단 네비/사이드바 메뉴 연결 | `cw_as_265` | 커밋 `da5147a` |
| `cw_as_0924/online_as/online_as.php` | 상태→메뉴 매핑, 이동버튼 비활성화 | `cw_as_265` | 커밋 `ef0d114` |
| `cw_as_0924/online_as/online_as_dispose_return_ok.php`(신규) | 처리 핸들러 — 상태변경+이력+Flow 알림, 실제 `sendFlowMessage()` 재사용 | `cw_as_265` | 커밋 `da5147a` |
| `_cw_as_pending/online_as/online_as_estimate.php`(로컬 스테이징 사본) | 폐기/반송 신청 폼, 폼 action은 `https://csadmin.catchwell.com/cw_as_265/online_as/online_as_dispose_return_ok.php` 절대 URL | **`cw_as`** (이 파일 하나만) | 커밋 `3ef4aac` |

**핵심 포인트:**
- `cw_as`와 `cw_as_265`는 서로 다른 서버 도메인이므로, 폐기/반송 폼의 `action`과 핸들러의 리다이렉트(`Location:`)는 모두 **절대 URL**로 상대 도메인을 넘나든다. HTML form POST/redirect는 CORS 제약이 없어 이 방식이 가능하다.
- `cw_as_0924/online_as/online_as_dispose_return_ok.php`는 `include("../common_lib.php")`로 실제 공용 `sendFlowMessage()`를 그대로 사용한다 (원안의 "cw_as 전용 자체 포함 Flow 함수"는 불필요해짐).
- 남은 작업: 로컬 Docker(`http://localhost/cw_as_0924/`)에서 관리자측 동작 확인 → 사용자가 "배포해줘"라고 명시할 때만 `cw_as_265`(관리자 4개 파일)와 `cw_as`(estimate 1개 파일)에 배포.

---

## (참고용, 폐기된 원안) 이하 원래 계획

원래는 아래 Task 1~6처럼 **모든** 파일을 `_cw_as_pending/`에 스테이징한 뒤 `cw_as` 서버 한 곳에만 배포하는 구조였다. 이는 `cw_as`가 실제 고객이 접근하는 유일한 서버라는 사실은 맞았지만, 관리자(직원)가 쓰는 리스트/메뉴/처리 핸들러까지 `cw_as`에 넣는 것은 잘못된 판단이었다 — 관리자는 `cw_as_265`를 사용하므로, 그쪽 코드는 `cw_as_0924` 저장소에 있어야 정상 배포된다. 아래 내용은 실행되지 않았으며 상세 코드도 실제 반영본과 다르니 참고만 할 것.

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Architecture (원안, 폐기됨):** `process_state`에 신규 값 2개(10=폐기요청, 11=반송요청) 추가. 신규 처리 핸들러가 상태 변경+이력 기록+Flow 알림을 수행. 관리자 리스트는 기존 `online_as.php?state=X` 제네릭 페이지 재사용. 모든 파일을 `cw_as` 서버 한 곳에만 배포.

**Tech Stack:** PHP(구버전, `cw_as` 경로 — PHP 5.6과 유사하게 `??` 등 최신 문법 금지, `isset()?:` 사용), curl(Flow API 자체 포함 호출).

이하 Task 1~6의 상세 스텝은 `git log`의 `0e41860` 커밋 원본을 참고할 것 (이 문서 갱신 시 중복 게재를 생략함).
