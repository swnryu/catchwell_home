# cw_as DB 이중화 (마스터-슬레이브 복제) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** `cw_as` DB를 `211.54.90.200`(마스터)에서 `211.54.90.202`(슬레이브, 기존 MariaDB10 패키지)로 실시간 복제하여, 장애 시 수동 전환이 가능한 상태를 만든다.

**Architecture:** MariaDB 표준 binlog 기반 마스터-슬레이브 복제. 마스터(.200)는 이미 binlog가 활성화돼 있어 변경 불필요. 슬레이브(.202)는 기존에 설치된 MariaDB10 패키지를 그대로 쓰되, 예전에 시도했다가 깨진 복제를 처음부터 다시 구성한다. 복제 범위는 `replicate-do-db=cw_as`로 한정한다.

**Tech Stack:** MariaDB 10.3.21(마스터) / MariaDB 10.11.6(슬레이브), Synology DSM MariaDB10 패키지, PuTTY plink/pscp (Windows에서 SSH 원격 실행).

## Global Constraints

- 마스터(.200)는 운영 중인 라이브 서버 — 재시작·다운타임을 유발하는 작업 금지 (binlog는 이미 켜져 있어 재시작 불필요).
- 슬레이브(.202)의 MariaDB10 인스턴스에는 `cw_as` 외에도 `cw_as_dev`, `cw_sales`, `cw_sales_dev`, `cw_shipment`, `PDAService`, `test_jinae` 스키마가 있다. 이들은 모두 예전 복제 시도의 잔재(오래된 복제본)이며 라이브 트래픽은 전부 `.200`으로 간다 (`config.php`의 `$DB_HOST="211.54.90.200"` 확인됨). **`cw_as` 스키마만 건드리고 나머지는 그대로 둔다** (범위 최소화).
- 모든 원격 명령은 PuTTY로 실행: `plink.exe`는 `C:\Program Files\PuTTY\plink.exe`, `pscp.exe`는 `C:\Program Files\PuTTY\pscp.exe`.
- 접속 정보:
  | 대상 | Host | SSH Port | SSH 계정/비밀번호 | DB 계정 |
  |---|---|---|---|---|
  | 마스터 | 211.54.90.200 | 211 | swryu / skj4138 | cwadmin / Catchwell1! (포트 3307) |
  | 슬레이브 | 211.54.90.202 | 211 | swryu / Skj41382621!! | cwadmin / Catchwell1! (포트 3306) |
- 마스터의 mysql 클라이언트 경로: `/usr/local/mariadb10/bin/mysql`, `mysqldump`. 슬레이브도 동일 경로.
- 각 작업 실행 전 이전 작업의 검증 결과를 확인하고 진행한다 (원격 운영 작업이라 실패 시 되돌리기 어려움).

---

### Task 1: 슬레이브(.202) cwadmin 계정 권한 보강

현재 `.202`의 `cwadmin` 계정은 `SUPER`, `REPLICATION CLIENT`, `REPLICATION SLAVE` 권한이 없어서 `CHANGE MASTER TO` / `START SLAVE` / `SHOW SLAVE STATUS`를 실행할 수 없다. `root`/`admin_user`의 비밀번호는 알 수 없으므로 `--skip-grant-tables`로 임시 기동해 권한을 보강한다.

이 작업 중 `.202`의 MariaDB10 인스턴스가 몇 초간 재시작된다. 이 인스턴스는 현재 라이브 트래픽이 없는(예전 복제 잔재만 있는) 상태이므로 영향은 없다.

**대상:** `211.54.90.202` (SSH), MariaDB10 패키지

- [ ] **Step 1: 현재 권한 상태 확인 (재확인)**

```bash
"C:\Program Files\PuTTY\plink.exe" -ssh -P 211 -batch -pw "Skj41382621!!" swryu@211.54.90.202 "/usr/local/mariadb10/bin/mysql -h127.0.0.1 -P3306 -ucwadmin -p'Catchwell1!' -e \"SHOW GRANTS FOR CURRENT_USER();\""
```

Expected: `SUPER`, `REPLICATION CLIENT`, `REPLICATION SLAVE` 가 목록에 없음 (현재 상태 재확인용).

- [ ] **Step 2a: 원격 서버에 셸 스크립트 파일을 만들어서 저장 (따옴표 중첩 문제를 피하기 위해 한 줄 명령 대신 스크립트 파일 사용)**

```bash
"C:\Program Files\PuTTY\plink.exe" -ssh -P 211 -batch -pw "Skj41382621!!" swryu@211.54.90.202 "cat > /tmp/fix_cwadmin_priv.sh << 'SCRIPT_EOF'
#!/bin/bash
set -e
/usr/syno/bin/synopkg stop MariaDB10
sleep 3
/usr/local/mariadb10/bin/mysqld_safe --datadir=/var/packages/MariaDB10/target/mysql --skip-grant-tables --skip-networking --socket=/run/mysqld/mysqld10_recovery.sock --pid-file=/run/mysqld/mysqld10_recovery.pid &
sleep 5
/usr/local/mariadb10/bin/mysql --socket=/run/mysqld/mysqld10_recovery.sock -e \"FLUSH PRIVILEGES; GRANT SUPER, RELOAD, REPLICATION CLIENT, REPLICATION SLAVE ON *.* TO 'cwadmin'@'%'; FLUSH PRIVILEGES;\"
/usr/local/mariadb10/bin/mysqladmin --socket=/run/mysqld/mysqld10_recovery.sock shutdown
sleep 3
/usr/syno/bin/synopkg start MariaDB10
SCRIPT_EOF
chmod +x /tmp/fix_cwadmin_priv.sh"
```

- [ ] **Step 2b: 저장된 스크립트를 sudo로 실행**

```bash
"C:\Program Files\PuTTY\plink.exe" -ssh -P 211 -batch -pw "Skj41382621!!" swryu@211.54.90.202 "echo 'Skj41382621!!' | sudo -S /tmp/fix_cwadmin_priv.sh"
```

실행 중 서비스가 몇 초간 내려갔다가 다시 올라온다. 명령이 끝난 뒤 바로 Step 3으로 상태를 확인한다.

- [ ] **Step 2c: 임시 스크립트 삭제**

```bash
"C:\Program Files\PuTTY\plink.exe" -ssh -P 211 -batch -pw "Skj41382621!!" swryu@211.54.90.202 "rm /tmp/fix_cwadmin_priv.sh"
```

- [ ] **Step 3: 패키지가 정상 기동됐는지 확인**

```bash
"C:\Program Files\PuTTY\plink.exe" -ssh -P 211 -batch -pw "Skj41382621!!" swryu@211.54.90.202 "echo 'Skj41382621!!' | sudo -S /usr/syno/bin/synopkg status MariaDB10"
```

Expected: `"status":"running"`

- [ ] **Step 4: 권한이 실제로 부여됐는지 확인**

```bash
"C:\Program Files\PuTTY\plink.exe" -ssh -P 211 -batch -pw "Skj41382621!!" swryu@211.54.90.202 "/usr/local/mariadb10/bin/mysql -h127.0.0.1 -P3306 -ucwadmin -p'Catchwell1!' -e \"SHOW GRANTS FOR CURRENT_USER();\""
```

Expected: `GRANT ... SUPER, ... REPLICATION SLAVE, ... REPLICATION CLIENT ... ON *.* TO cwadmin@%` 포함.

---

### Task 2: 마스터(.200) 복제 계정 비밀번호 재설정

기존 `replica`@`211.54.90.202` 계정이 이미 `REPLICATION SLAVE ON *.*` 권한으로 존재하지만(예전 설정 잔재), 비밀번호를 모르므로 새로 설정한다.

**대상:** `211.54.90.200` (SSH)

**Interfaces:**
- Produces: 새 비밀번호 `<REPL_PW>` — Task 5에서 `CHANGE MASTER TO` 시 사용.

- [ ] **Step 1: 새 비밀번호 생성 (로컬에서)**

```bash
openssl rand -base64 18 | tr -d '=/+' 
```

출력된 문자열을 `<REPL_PW>`로 사용한다 (이후 단계에 그대로 대입, 어디에도 커밋하지 않음).

- [ ] **Step 2: 마스터에서 비밀번호 재설정**

```bash
"C:\Program Files\PuTTY\plink.exe" -ssh -P 211 -batch -pw "skj4138" swryu@211.54.90.200 "/usr/local/mariadb10/bin/mysql -h127.0.0.1 -P3307 -ucwadmin -p'Catchwell1!' -e \"ALTER USER 'replica'@'211.54.90.202' IDENTIFIED BY '<REPL_PW>'; FLUSH PRIVILEGES;\""
```

- [ ] **Step 3: 권한 재확인**

```bash
"C:\Program Files\PuTTY\plink.exe" -ssh -P 211 -batch -pw "skj4138" swryu@211.54.90.200 "/usr/local/mariadb10/bin/mysql -h127.0.0.1 -P3307 -ucwadmin -p'Catchwell1!' -e \"SHOW GRANTS FOR 'replica'@'211.54.90.202';\""
```

Expected: `GRANT REPLICATION SLAVE ON *.* TO 'replica'@'211.54.90.202'`

---

### Task 3: 슬레이브(.202)의 기존 cw_as 백업 후 초기화

`.202`의 현재 `cw_as`(214.7MB, 25테이블)는 예전 복제 잔재라 폐기 대상이지만, 혹시 몰라 안전 백업을 먼저 떠둔다.

**대상:** `211.54.90.202` (SSH)

- [ ] **Step 1: 안전 백업 덤프**

```bash
"C:\Program Files\PuTTY\plink.exe" -ssh -P 211 -batch -pw "Skj41382621!!" swryu@211.54.90.202 "/usr/local/mariadb10/bin/mysqldump -h127.0.0.1 -P3306 -ucwadmin -p'Catchwell1!' cw_as > /volume1/tmp/cw_as_202_stale_backup_$(date +%Y%m%d_%H%M%S).sql && ls -la /volume1/tmp/cw_as_202_stale_backup_*.sql"
```

Expected: 파일이 생성되고 크기가 0보다 큼 (대략 200MB대).

- [ ] **Step 2: 기존 cw_as 스키마 삭제 후 빈 스키마로 재생성**

```bash
"C:\Program Files\PuTTY\plink.exe" -ssh -P 211 -batch -pw "Skj41382621!!" swryu@211.54.90.202 "/usr/local/mariadb10/bin/mysql -h127.0.0.1 -P3306 -ucwadmin -p'Catchwell1!' -e \"DROP DATABASE cw_as; CREATE DATABASE cw_as CHARACTER SET utf8;\""
```

- [ ] **Step 3: 빈 스키마 확인**

```bash
"C:\Program Files\PuTTY\plink.exe" -ssh -P 211 -batch -pw "Skj41382621!!" swryu@211.54.90.202 "/usr/local/mariadb10/bin/mysql -h127.0.0.1 -P3306 -ucwadmin -p'Catchwell1!' -e \"SELECT COUNT(*) AS table_count FROM information_schema.tables WHERE table_schema='cw_as';\""
```

Expected: `table_count = 0`

---

### Task 4: 마스터(.200)에서 cw_as 덤프 생성 및 슬레이브로 전송

**대상:** `211.54.90.200` → 로컬 → `211.54.90.202`

**Interfaces:**
- Consumes: 없음 (마스터 데이터는 현재 상태 그대로 사용)
- Produces: `cw_as.sql` 덤프 파일 (마스터의 binlog 파일명/위치 포함) — Task 5에서 복원 + `CHANGE MASTER TO`에 사용.

- [ ] **Step 1: 마스터에서 덤프 생성 (--master-data=2로 binlog 위치 기록)**

```bash
"C:\Program Files\PuTTY\plink.exe" -ssh -P 211 -batch -pw "skj4138" swryu@211.54.90.200 "/usr/local/mariadb10/bin/mysqldump -h127.0.0.1 -P3307 -ucwadmin -p'Catchwell1!' --single-transaction --master-data=2 --routines --triggers cw_as > /volume1/tmp/cw_as_master_dump.sql && ls -la /volume1/tmp/cw_as_master_dump.sql"
```

Expected: 파일 생성 확인, 크기 약 170MB대.

- [ ] **Step 2: 덤프 파일 안의 binlog 위치 확인 (기록해둘 것)**

```bash
"C:\Program Files\PuTTY\plink.exe" -ssh -P 211 -batch -pw "skj4138" swryu@211.54.90.200 "head -30 /volume1/tmp/cw_as_master_dump.sql | grep -A1 'CHANGE MASTER TO'"
```

Expected 예시: `CHANGE MASTER TO MASTER_LOG_FILE='mysql-bin.000127', MASTER_LOG_POS=NNNNN;` — 이 파일명/위치 값을 Task 5에서 사용.

- [ ] **Step 3: 마스터의 덤프 파일을 로컬로 내려받기**

```bash
"C:\Program Files\PuTTY\pscp.exe" -P 211 -pw "skj4138" swryu@211.54.90.200:/volume1/tmp/cw_as_master_dump.sql "C:\claude\cw_as_0924\cw_as_master_dump.sql"
```

- [ ] **Step 4: 로컬 덤프 파일을 슬레이브로 업로드**

```bash
"C:\Program Files\PuTTY\pscp.exe" -P 211 -pw "Skj41382621!!" "C:\claude\cw_as_0924\cw_as_master_dump.sql" swryu@211.54.90.202:/volume1/tmp/cw_as_master_dump.sql
```

- [ ] **Step 5: 마스터와 로컬의 임시 덤프 파일 정리, 슬레이브에는 복원 전까지 유지**

```bash
"C:\Program Files\PuTTY\plink.exe" -ssh -P 211 -batch -pw "skj4138" swryu@211.54.90.200 "rm /volume1/tmp/cw_as_master_dump.sql"
rm "C:\claude\cw_as_0924\cw_as_master_dump.sql"
```

---

### Task 5: 슬레이브(.202)에 복원 및 복제 시작

**대상:** `211.54.90.202` (SSH)

**Interfaces:**
- Consumes: Task 4의 `/volume1/tmp/cw_as_master_dump.sql`, `MASTER_LOG_FILE`/`MASTER_LOG_POS` 값. Task 2의 `<REPL_PW>`.

- [ ] **Step 1: 덤프 복원**

```bash
"C:\Program Files\PuTTY\plink.exe" -ssh -P 211 -batch -pw "Skj41382621!!" swryu@211.54.90.202 "/usr/local/mariadb10/bin/mysql -h127.0.0.1 -P3306 -ucwadmin -p'Catchwell1!' cw_as < /volume1/tmp/cw_as_master_dump.sql"
```

- [ ] **Step 2: 복원된 테이블 수 확인 (마스터와 일치해야 함)**

```bash
"C:\Program Files\PuTTY\plink.exe" -ssh -P 211 -batch -pw "Skj41382621!!" swryu@211.54.90.202 "/usr/local/mariadb10/bin/mysql -h127.0.0.1 -P3306 -ucwadmin -p'Catchwell1!' -e \"SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='cw_as';\""
```

Expected: 마스터와 동일한 테이블 수(Task 4 실행 시점 마스터 조회 결과와 비교, 이 계획 작성 시점 기준 31개).

- [ ] **Step 3: 복제 필터 설정 및 CHANGE MASTER TO 실행 (Task 4 Step 2에서 확인한 실제 파일명/위치로 교체)**

```bash
"C:\Program Files\PuTTY\plink.exe" -ssh -P 211 -batch -pw "Skj41382621!!" swryu@211.54.90.202 "/usr/local/mariadb10/bin/mysql -h127.0.0.1 -P3306 -ucwadmin -p'Catchwell1!' -e \"STOP SLAVE; RESET SLAVE ALL; CHANGE REPLICATION FILTER REPLICATE_DO_DB=(cw_as); CHANGE MASTER TO MASTER_HOST='211.54.90.200', MASTER_PORT=3307, MASTER_USER='replica', MASTER_PASSWORD='<REPL_PW>', MASTER_LOG_FILE='<Task4에서 확인한 파일명>', MASTER_LOG_POS=<Task4에서 확인한 위치>; START SLAVE;\""
```

- [ ] **Step 4: 슬레이브 상태 확인**

```bash
"C:\Program Files\PuTTY\plink.exe" -ssh -P 211 -batch -pw "Skj41382621!!" swryu@211.54.90.202 "/usr/local/mariadb10/bin/mysql -h127.0.0.1 -P3306 -ucwadmin -p'Catchwell1!' -e \"SHOW SLAVE STATUS\\G\""
```

Expected: `Slave_IO_Running: Yes`, `Slave_SQL_Running: Yes`, `Seconds_Behind_Master: 0`, `Last_Error:` (빈 값)

- [ ] **Step 5: 슬레이브 측 임시 덤프 파일 정리**

```bash
"C:\Program Files\PuTTY\plink.exe" -ssh -P 211 -batch -pw "Skj41382621!!" swryu@211.54.90.202 "rm /volume1/tmp/cw_as_master_dump.sql"
```

---

### Task 6: 복제 동작 검증 (실제 데이터 전파 테스트)

**대상:** `211.54.90.200` → `211.54.90.202`

- [ ] **Step 1: 마스터에 테스트 행 삽입 (기존 관리자 테이블에 무해한 값 — `admin` 테이블 대신 존재 여부가 확실한 `online_as` 사용 안 하고, 복제 테스트용 임시 테이블 사용)**

```bash
"C:\Program Files\PuTTY\plink.exe" -ssh -P 211 -batch -pw "skj4138" swryu@211.54.90.200 "/usr/local/mariadb10/bin/mysql -h127.0.0.1 -P3307 -ucwadmin -p'Catchwell1!' cw_as -e \"CREATE TABLE IF NOT EXISTS repl_test (id INT PRIMARY KEY, note VARCHAR(50)); REPLACE INTO repl_test VALUES (1, 'replication-check');\""
```

- [ ] **Step 2: 몇 초 대기 후 슬레이브에서 확인**

```bash
"C:\Program Files\PuTTY\plink.exe" -ssh -P 211 -batch -pw "Skj41382621!!" swryu@211.54.90.202 "/usr/local/mariadb10/bin/mysql -h127.0.0.1 -P3306 -ucwadmin -p'Catchwell1!' cw_as -e \"SELECT * FROM repl_test;\""
```

Expected: `id=1, note=replication-check` 행이 보임.

- [ ] **Step 3: 마스터에서 테스트 테이블 삭제 (양쪽 모두 정리)**

```bash
"C:\Program Files\PuTTY\plink.exe" -ssh -P 211 -batch -pw "skj4138" swryu@211.54.90.200 "/usr/local/mariadb10/bin/mysql -h127.0.0.1 -P3307 -ucwadmin -p'Catchwell1!' cw_as -e \"DROP TABLE repl_test;\""
```

- [ ] **Step 4: 슬레이브에서도 삭제가 전파됐는지 확인**

```bash
"C:\Program Files\PuTTY\plink.exe" -ssh -P 211 -batch -pw "Skj41382621!!" swryu@211.54.90.202 "/usr/local/mariadb10/bin/mysql -h127.0.0.1 -P3306 -ucwadmin -p'Catchwell1!' cw_as -e \"SHOW TABLES LIKE 'repl_test';\""
```

Expected: 결과 없음 (테이블이 사라졌음, DDL도 정상 복제됨).

---

### Task 7: 운영 점검 스크립트를 레포에 추가

수동 점검(알림 없음, 사용자 확정 사항)을 위해 슬레이브 상태를 한 번에 확인하는 스크립트를 레포에 추가한다.

**Files:**
- Create: `tools/db_replication/check_slave_status.ps1`

- [ ] **Step 1: 스크립트 작성**

```powershell
# tools/db_replication/check_slave_status.ps1
# 사용법: powershell -ExecutionPolicy Bypass -File tools\db_replication\check_slave_status.ps1
$plink = "C:\Program Files\PuTTY\plink.exe"
$result = & $plink -ssh -P 211 -batch -pw "Skj41382621!!" swryu@211.54.90.202 "/usr/local/mariadb10/bin/mysql -h127.0.0.1 -P3306 -ucwadmin -p'Catchwell1!' -e `"SHOW SLAVE STATUS\G`""

Write-Host $result

if ($result -match "Slave_IO_Running:\s*Yes" -and $result -match "Slave_SQL_Running:\s*Yes") {
    Write-Host "`n[OK] 복제 정상 동작 중" -ForegroundColor Green
} else {
    Write-Host "`n[경고] 복제가 중단됐거나 확인 불가 — 위 SHOW SLAVE STATUS 출력을 확인하세요" -ForegroundColor Red
}
```

- [ ] **Step 2: 실행해서 정상 동작 확인**

```bash
powershell -ExecutionPolicy Bypass -File "C:\claude\cw_as_0924\tools\db_replication\check_slave_status.ps1"
```

Expected: `[OK] 복제 정상 동작 중` 출력.

- [ ] **Step 3: 커밋**

```bash
cd "C:\claude\cw_as_0924"
git add tools/db_replication/check_slave_status.ps1
git commit -m "ops: cw_as DB 복제 상태 수동 점검 스크립트 추가"
```

---

### Task 8: 설계 문서에 실측값 반영 및 장애 전환 절차 확정

Task 1~6에서 확인한 실제 경로/계정/버전 정보를 스펙 문서에 반영하고, 장애 전환 절차를 최종 확정한다.

**Files:**
- Modify: `docs/superpowers/specs/2026-07-07-db-replication-design.md`

- [ ] **Step 1: 설계 문서의 "구성 절차" 섹션에 실측값 반영**

`docs/superpowers/specs/2026-07-07-db-replication-design.md`의 "1. 마스터(.200) 설정" 섹션에 다음 내용 추가:

```markdown
> **실측 확인 (2026-07-07):** 마스터는 이미 `log-bin=mysql-bin`, `server-id=1`, `binlog_format=row`, `expire_logs_days=7`가 설정되어 재시작 없이 그대로 사용. mysql 클라이언트 경로: `/usr/local/mariadb10/bin/mysql`. 슬레이브는 기존에 설치된 MariaDB10 패키지(10.11.6, 포트 3306)를 사용하며 `server-id=2`, `relay-log`, `read_only=1`도 이미 설정돼 있었음(예전 복제 시도 잔재). `cw_as` 외 `cw_as_dev`/`cw_sales`/`cw_sales_dev`/`cw_shipment`/`PDAService`/`test_jinae` 스키마도 같은 인스턴스에 존재하나 모두 예전 복제 잔재이며 손대지 않음.
```

- [ ] **Step 2: 장애 대응 섹션에 점검 스크립트 경로 추가**

"장애 대응 (수동 절차, 자동 알림 없음)" 섹션 시작 부분에 추가:

```markdown
정기 점검: `powershell -ExecutionPolicy Bypass -File tools\db_replication\check_slave_status.ps1` 실행으로 `SHOW SLAVE STATUS` 확인.
```

- [ ] **Step 3: 커밋**

```bash
cd "C:\claude\cw_as_0924"
git add docs/superpowers/specs/2026-07-07-db-replication-design.md
git commit -m "docs: DB 이중화 설계 문서에 실측 구성값 반영"
```
