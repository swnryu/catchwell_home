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
- 복제 전용 계정 생성 후 권한 부여:
  ```sql
  CREATE USER 'repl'@'211.54.90.202' IDENTIFIED BY '<비밀번호>';
  GRANT REPLICATION SLAVE ON *.* TO 'repl'@'211.54.90.202';
  ```
  - 소스 IP를 `.202`로 제한 (와일드카드 금지).

### 2. 초기 데이터 동기화
- 구현 착수 시 SSH로 `cw_as` DB 실제 용량 확인 후 방식 확정 (현재는 규모 미확인).
- 기본 방식: `mysqldump --single-transaction --master-data=2 cw_as > cw_as.sql`
- 덤프 파일을 `.202`로 전송 (pscp 또는 SCP) 후 슬레이브 인스턴스에 복원.
- 덤프 안의 `MASTER_LOG_FILE` / `MASTER_LOG_POS` 값을 이후 `CHANGE MASTER TO`에 사용.

### 3. 슬레이브(.202) 설정
- MariaDB10 패키지 설정에 `server-id=2`, `relay-log` 관련 설정 추가 후 패키지 서비스 재시작.
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
  CHANGE REPLICATION FILTER REPLICATE_DO_DB=(cw_as);
  START SLAVE;
  ```

### 4. 검증
- `SHOW SLAVE STATUS\G`에서 다음 확인:
  - `Slave_IO_Running: Yes`
  - `Slave_SQL_Running: Yes`
  - `Seconds_Behind_Master: 0`
- 마스터에 테스트 데이터 INSERT 후 슬레이브에 반영되는지 수동 확인.

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

- `.200` MariaDB 재시작으로 인한 짧은 다운타임 발생 (binlog 활성화 시 1회).
- `cw_as` DB 실제 용량 미확인 — 초기 동기화 소요 시간은 구현 시작 시 재산정.
- 자동 알림이 없으므로 복제가 끊긴 상태로 장시간 방치될 위험 존재. 주기적 수동 점검 필요 (추후 알림 추가 여지 있음).
- Synology MariaDB10 패키지의 설정 변경 방법(설정 파일 위치, 재시작 방법)은 구현 착수 시 SSH로 직접 확인 필요.
