# tools/db_replication/check_slave_status.ps1
# 사용법: powershell -ExecutionPolicy Bypass -File tools\db_replication\check_slave_status.ps1
$plink = "C:\Program Files\PuTTY\plink.exe"
$result = & $plink -ssh -P 211 -batch -pw "Skj41382621!!" swryu@211.54.90.202 "/usr/local/mariadb10/bin/mysql -h127.0.0.1 -P3306 -ucwadmin -p'Catchwell1!' -e 'SHOW SLAVE STATUS\G'"

Write-Host $result

if ($result -match "Slave_IO_Running:\s*Yes" -and $result -match "Slave_SQL_Running:\s*Yes") {
    Write-Host "`n[OK] 복제 정상 동작 중" -ForegroundColor Green
} else {
    Write-Host "`n[경고] 복제가 중단됐거나 확인 불가 — 위 SHOW SLAVE STATUS 출력을 확인하세요" -ForegroundColor Red
}
