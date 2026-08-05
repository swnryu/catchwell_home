-- 협찬/샘플 승인 워크플로우 DB 마이그레이션
-- 실행 순서대로 1회만 실행

-- 1. 기존 출고완료(status=1) 건을 status=2로 이동 (현재 없음, 안전을 위해 포함)
UPDATE cs_sponsored_orders SET status = 2 WHERE status = 1;

-- 2. 승인 정보 컬럼 추가
ALTER TABLE cs_sponsored_orders
  ADD COLUMN approved_at DATETIME NULL          AFTER status,
  ADD COLUMN approved_by VARCHAR(100) NOT NULL DEFAULT '' AFTER approved_at;

-- 상태값 정의:
--   0 = 승인 대기 (담당자 업로드)
--   1 = 승인 완료 (팀장 승인)
--   2 = 출고 완료 (CJ택배 처리)
