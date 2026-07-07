# cw_as DB 이중화 설계 (마스터-슬레이브 복제)

**날짜:** 2026-07-07  
**대상 프로젝트:** cw_as_0924 (PHP 5.6, MariaDB)

## 개요

현재 `cw_as` DB는 `211.54.90.200:3307` MariaDB 단일 인스턴스에서만 운영되어 단일 장애점(SPOF)이다.  
목적은 **장애 대비(DR/백업)** — 자동 failover는 요구사항이 아니며, 장애 시 수동으로 전환 가능한 수준의 실시간 복제본을 확보한다.

---

## 아키텍처

```
[211.54.90.200]                              [211.54.90.202]
MariaDB 3307 (cw_as)  ---binlog 복제--->   MariaDB10 패키지 (설치돼있으나 미사용)
   운영 서버 (기존 유지)                        cw_as 스키마 추가, read-only 슬레이브
```

- **마스터**: `211.54.90.200:3307` — 기존 운영 서버, 변경은 binlog 활성화 + 복제 계정 추가뿐.
- **슬레이브**: `211.54.90.202`에 기본 설치돼 있는 Synology **MariaDB10 패키지**(현재 미사용 상태)를 그대로 사용. Docker 컨테이너를 새로 만들지 않는다.
- **복제 범위**: `replicate-do-db=cw_as` 필터를 슬레이브 설정에 적용. 현재는 인스턴스가 비어있지만, 추후 다른 스키마가 추가돼도 영향받지 않도록 안전장치로 건다.

---

## 구성 절차

### 1. 마스터(.200) 설정
- MariaDB 설정 파일(my.cnf 또는 Synology 패키지의 해당 설정)에 추가:
  - `log-bin=mysql-bin`
  - `server-id=1`
  - 마스터 측 binlog는 필터링하지 않고 전체 기록 → 필터링은 슬레이브 측 `replicate-do-db=cw_as`로만 적용 (마스터를 단순하게 유지, 추후 다른 슬레이브가 붙어도 유연)
- 설정 반영을 위해 MariaDB 재시작 필요 → **저트래픽 시간대에 진행, 사전 공지**.
  > **(실측)** `/var/packages/MariaDB10/etc/my.cnf`를 확인해보니 `server-id=1`, `log_bin=mysql-bin`, `binlog_format=row`, `expire_logs_days=7`가 이미 모두 설정돼 있어 실제로는 재시작 없이 바로 다음 단계로 진행할 수 있었음. mysql 클라이언트 경로는 `/usr/local/mariadb10/bin/mysql`.
- 복제 전용 계정 생성 후 권한 부여:
  ```sql
  CREATE USER 'repl'@'211.54.90.202' IDENTIFIED BY '<비밀번호>';
  GRANT REPLICATION SLAVE ON *.* TO 'repl'@'211.54.90.202';
  ```
  - 소스 IP를 `.202`로 제한 (와일드카드 금지).
  - **(실측)** `replica`@`211.54.90.202` 계정이 예전 복제 시도의 잔재로 이미 존재하고 있어서, 새로 만들지 않고 비밀번호만 재설정해서 재사용함.
  - **(실측)** 마스터에 비밀번호 검증 플러그인이 걸려 있어 특수문자가 없는 비밀번호는 `ERROR 1819: Include special characters`로 거부됨. 복제 계정 비밀번호에는 반드시 특수문자를 포함해야 함.

### 2. 초기 데이터 동기화
- 구현 착수 시 SSH로 `cw_as` DB 실제 용량 확인 후 방식 확정 (현재는 규모 미확인).
- 기본 방식: `mysqldump --single-transaction --master-data=2 cw_as > cw_as.sql`
- 덤프 파일을 `.202`로 전송 (pscp 또는 SCP) 후 슬레이브 인스턴스에 복원.
- 덤프 안의 `MASTER_LOG_FILE` / `MASTER_LOG_POS` 값을 이후 `CHANGE MASTER TO`에 사용.
- **(실측)** `.202`에서 덤프 파일을 임시로 두는 경로는 `/volume1/tmp`가 아니라 `/tmp`를 사용함 (`/volume1/tmp`는 실제로 존재하지 않는 경로 — `No such file or directory`).
- **(실측)** 실제 덤프 결과: 약 220MB, 31개 테이블. 이때 마스터 binlog 위치는 `mysql-bin.000127` / `315919`였고, 이 값을 그대로 `CHANGE MASTER TO`의 `MASTER_LOG_FILE`/`MASTER_LOG_POS`에 사용해서 복제를 시작함.

### 3. 슬레이브(.202) 설정
- MariaDB10 패키지 설정에 `server-id=2`, `relay-log` 관련 설정 추가 후 패키지 서비스 재시작.
- **(실측)** 복제 필터(`replicate-do-db=cw_as`)는 당초 계획대로 런타임 SQL `CHANGE REPLICATION FILTER REPLICATE_DO_DB=(cw_as);`로 적용하려 했으나, 이 Synology MariaDB10 패키지 빌드(10.11.6)에서는 이 명령이 `ERROR 1064 문법 오류`로 거부됨(권한 문제가 아니라 파싱 자체가 이 빌드에서 지원되지 않는 것으로 보임). 대신 `/var/packages/MariaDB10/etc/my.cnf`에 `replicate-do-db=cw_as`를 정적으로 추가하고 MariaDB10 패키지를 재시작해서 적용함. 최종 my.cnf:
  ```ini
  [mysqld]
  server-id=2
  relay-log=mysqld10-relay-bin
  read_only=1
  replicate-do-db=cw_as
  ```
- **(실측) cwadmin 권한 보강 시 주의사항**: 슬레이브 설정 과정에서 `cwadmin` 계정 권한을 보강하기 위해 `--skip-grant-tables`로 임시 기동했는데, 이때 `GRANT ...;` 를 실행하고 `FLUSH PRIVILEGES;`를 나중에 하면 `ERROR 1290: server is running with --skip-grant-tables option so it cannot execute this statement`로 GRANT 자체가 거부됨. 반드시 **`FLUSH PRIVILEGES;`를 먼저 실행해서 grant 테이블을 메모리에 로드한 뒤 GRANT를 실행**해야 함 (순서: `FLUSH PRIVILEGES` → `GRANT ...` → `FLUSH PRIVILEGES`). 작업 후 종료 시 `mysqladmin shutdown`은 FLUSH PRIVILEGES 이후 새 연결에 대한 인증이 복원되어 access denied로 실패하므로, `kill -TERM <mariadbd_pid>`로 프로세스를 직접 종료해야 함.
- **(실측) `read_only` 권한 이슈**: MariaDB 10.11부터는 `SET GLOBAL read_only`를 변경하려면 `SUPER`만으로는 부족하고 별도의 `READ_ONLY ADMIN` 권한(또는 `ALL PRIVILEGES`)이 필요함. `cwadmin`에는 최종적으로 `.200`과 동일하게 `GRANT ALL PRIVILEGES ON *.* TO 'cwadmin'@'%' WITH GRANT OPTION`을 부여해서 해결.
- 복제 시작:
  ```sql
  CHANGE MASTER TO
    MASTER_HOST='211.54.90.200',
    MASTER_PORT=3307,
    MASTER_USER='repl',
    MASTER_PASSWORD='<비밀번호>',
    MASTER_LOG_FILE='<덤프에서 확인한 파일명>',
    MASTER_LOG_POS=<덤프에서 확인한 위치>,
    MASTER_USE_GTID=no;
  START SLAVE;
  ```
  - 복제 필터는 위에서 my.cnf에 이미 정적으로 적용했으므로 여기서는 별도의 `CHANGE REPLICATION FILTER` 명령이 필요 없음.

### 4. 검증
- `SHOW SLAVE STATUS\G`에서 다음 확인:
  - `Slave_IO_Running: Yes`
  - `Slave_SQL_Running: Yes`
  - `Seconds_Behind_Master: 0`
- 마스터에 테스트 데이터 INSERT 후 슬레이브에 반영되는지 수동 확인.
- **(실측) 검증 완료**: `SHOW SLAVE STATUS`에서 `Slave_IO_Running: Yes`, `Slave_SQL_Running: Yes`, `Seconds_Behind_Master: 0` 확인. 마스터에 테스트 테이블을 만들어 INSERT/DROP을 실행해 슬레이브에 정상적으로 전파되는 것까지 실제로 검증 완료.

---

## 장애 대응 (수동 절차, 자동 알림 없음)

복제 상태는 자동 알림 없이 필요할 때 `SHOW SLAVE STATUS`로 수동 점검한다.

### 마스터(.200) 장애 시 슬레이브로 전환
1. 슬레이브에서 `STOP SLAVE; RESET SLAVE ALL;` (복제 관계 해제, 독립 DB로 전환)
2. `config.php`의 `$DB_HOST`, `$DB_PORT`를 `.202` 인스턴스 값으로 변경 후 배포 (`deploy.ps1`).
3. 애플리케이션이 `.202`를 정상적으로 읽고 쓰는지 확인.

### 마스터(.200) 복구 후 재동기화
1. `.200` 복구 확인 후, `.202`(현재 운영 중)의 최신 데이터를 기준으로 재덤프.
2. `.200`을 새 슬레이브로 구성하거나, 반대로 원래 방향으로 복구할지는 장애 상황에 따라 그때 결정.
3. 재동기화 완료 후 트래픽을 원래 마스터(.200)로 되돌릴지 여부는 운영 판단.

---

## 리스크 / 주의사항

- `.200` MariaDB 재시작으로 인한 짧은 다운타임 발생 (binlog 활성화 시 1회). → **(실측)** 실제로는 재시작이 필요 없었음. `/var/packages/MariaDB10/etc/my.cnf`에 `server-id=1`, `log_bin=mysql-bin`, `binlog_format=row`, `expire_logs_days=7`가 이미 설정돼 있었음 (예전 복제 시도의 잔재로 추정). 다만 향후 유사 작업 시 마스터 설정이 비어있을 가능성을 배제하지 않고 사전 확인은 계속 필요.
- `cw_as` DB 실제 용량 미확인 — 초기 동기화 소요 시간은 구현 시작 시 재산정. → **(실측)** 실제로는 약 220MB, 31개 테이블 규모로 확인, 초기 동기화는 단시간에 완료됨.
- **(실측)** 복제 계정 비밀번호는 마스터의 비밀번호 검증 플러그인 때문에 특수문자를 포함해야 함 (특수문자가 없으면 `ERROR 1819: Include special characters`로 거부됨).
- **(실측)** 이 Synology MariaDB10 패키지 빌드(10.11.6)에서는 `CHANGE REPLICATION FILTER` 런타임 SQL 문법이 지원되지 않음(`ERROR 1064 문법 오류`) — 복제 필터는 반드시 my.cnf에 `replicate-do-db=cw_as`를 정적으로 추가하고 패키지를 재시작하는 방식으로 적용해야 함. 향후 필터 대상 스키마를 바꿔야 하면 런타임 명령이 아니라 my.cnf 수정 + 재시작이 필요함을 유의.
- **(실측)** `--skip-grant-tables`로 임시 기동해 권한을 보강할 때는 `FLUSH PRIVILEGES`를 GRANT 실행 **전에** 한 번 실행해야 하며(순서를 지키지 않으면 GRANT가 `ERROR 1290`으로 거부됨), 작업 종료 시 `mysqladmin shutdown`이 아니라 `kill -TERM <mariadbd_pid>`로 프로세스를 직접 종료해야 함.
- **(실측)** MariaDB 10.11부터 `SET GLOBAL read_only` 변경에는 `SUPER`만으로 부족하고 `READ_ONLY ADMIN`(또는 `ALL PRIVILEGES`) 권한이 필요함 — `cwadmin`에 `.200`과 동일하게 `GRANT ALL PRIVILEGES ON *.* TO 'cwadmin'@'%' WITH GRANT OPTION`을 부여해서 해결함.
- 자동 알림이 없으므로 복제가 끊긴 상태로 장시간 방치될 위험 존재. 주기적 수동 점검 필요 (추후 알림 추가 여지 있음). → 수동 점검용 스크립트 `tools\db_replication\check_slave_status.ps1`가 레포에 추가됨(커밋 완료). `powershell -ExecutionPolicy Bypass -File tools\db_replication\check_slave_status.ps1`로 실행.
- Synology MariaDB10 패키지의 설정 변경 방법(설정 파일 위치, 재시작 방법)은 구현 착수 시 SSH로 직접 확인 필요. → **(실측)** 설정 파일은 `/var/packages/MariaDB10/etc/my.cnf`, mysql 클라이언트 경로는 `/usr/local/mariadb10/bin/mysql`, 임시 파일 경로는 `/tmp` (`/volume1/tmp`는 존재하지 않음).
