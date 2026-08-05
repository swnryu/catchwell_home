# cw_as 배포 + 롤백 스크립트
# 사용법:
#   .\deploy.ps1                                        # 변경된 파일 전체 배포
#   .\deploy.ps1 -Files "a.php","subdir/b.php"          # 특정 파일만 배포
#   .\deploy.ps1 -Rollback                              # 최신 백업으로 롤백
#   .\deploy.ps1 -Rollback -BackupId 20260605_143000    # 특정 백업으로 롤백
#   .\deploy.ps1 -List                                  # 백업 목록 조회

param(
    [switch]  $Rollback,
    [switch]  $List,
    [string]  $BackupId = "",
    [string[]]$Files    = @()
)

$ServerPath   = "\\211.54.90.200\web\cw_as_265"
$LocalPath    = "C:\claude\cw_as_0924"
$GitRoot      = "C:\claude"
$GitPrefix    = "cw_as_0924\"
$BackupBase   = "C:\claude\cw_as_backup"
$ExcludeFiles = @("config.php", "deploy.ps1")

New-Item -ItemType Directory -Force -Path $BackupBase | Out-Null

# ── 백업 목록 ──────────────────────────────────────────────
if ($List) {
    $backups = Get-ChildItem $BackupBase -Directory | Sort-Object Name -Descending
    if (!$backups) { Write-Host "백업 없음"; exit }
    Write-Host "백업 목록 ($($backups.Count)개):"
    foreach ($b in $backups) {
        $cnt = (Get-ChildItem $b.FullName -Recurse -File).Count
        Write-Host "  $($b.Name)  ($cnt개 파일)"
    }
    exit
}

# ── 롤백 ───────────────────────────────────────────────────
if ($Rollback) {
    if (!$BackupId) {
        $latest = Get-ChildItem $BackupBase -Directory | Sort-Object Name -Descending | Select-Object -First 1
        if (!$latest) { Write-Host "[오류] 백업 없음"; exit 1 }
        $BackupId = $latest.Name
        Write-Host "최신 백업 사용: $BackupId"
    }
    $backupDir = "$BackupBase\$BackupId"
    if (!(Test-Path $backupDir)) { Write-Host "[오류] 백업 없음: $BackupId"; exit 1 }

    $backupFiles = Get-ChildItem $backupDir -Recurse -File
    if ($backupFiles.Count -eq 0) { Write-Host "백업에 파일 없음 (신규 파일만 배포된 경우)"; exit }

    Write-Host "▶ 롤백 대상 ($($backupFiles.Count)개):"
    foreach ($f in $backupFiles) {
        $rel = $f.FullName.Substring($backupDir.Length + 1)
        $dst = Join-Path $ServerPath $rel
        Copy-Item $f.FullName $dst -Force
        Write-Host "  복원: $rel"
    }
    Write-Host ""
    Write-Host "✓ 롤백 완료: $BackupId"
    exit
}

# ── 배포 ───────────────────────────────────────────────────

# 배포 대상 파일 결정
if ($Files.Count -gt 0) {
    $targetFiles = $Files
} else {
    # git 루트(C:\claude) 기준으로 변경파일 조회 → cw_as_0924\ 접두사 제거
    $modified = git -C $GitRoot diff --name-only 2>$null
    $staged   = git -C $GitRoot diff --cached --name-only 2>$null
    $newFiles = git -C $GitRoot ls-files --others --exclude-standard 2>$null
    $targetFiles = (@($modified) + @($staged) + @($newFiles)) |
                   Where-Object { $_ -ne "" -and $_ -ne $null } |
                   ForEach-Object { $_.Replace("/", "\") } |
                   Where-Object { $_.StartsWith($GitPrefix) } |
                   ForEach-Object { $_.Substring($GitPrefix.Length) } |
                   Sort-Object -Unique
}

if ($targetFiles.Count -eq 0) {
    Write-Host "배포할 변경 파일 없음 (git 기준)"
    exit
}

Write-Host "▶ 배포 대상 ($($targetFiles.Count)개):"
$targetFiles | ForEach-Object { Write-Host "  $_" }
Write-Host ""

# 서버 파일 백업 (덮어쓸 파일만)
$timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
$backupDir = "$BackupBase\$timestamp"
New-Item -ItemType Directory -Force -Path $backupDir | Out-Null

$backed = 0
foreach ($f in $targetFiles) {
    if ($ExcludeFiles -contains (Split-Path $f -Leaf)) { continue }
    $src = Join-Path $ServerPath $f
    if (Test-Path $src) {
        $dst = Join-Path $backupDir $f
        New-Item -ItemType Directory -Force -Path (Split-Path $dst) | Out-Null
        Copy-Item $src $dst -Force
        $backed++
    }
}

if ($backed -gt 0) {
    Write-Host "▶ 백업 완료: ${backed}개 → $BackupBase\$timestamp"
} else {
    Write-Host "▶ 백업: 신규 파일만 있어 백업 없음"
    Remove-Item $backupDir -Force -ErrorAction SilentlyContinue
    $timestamp = ""
}

# 배포
$deployed = 0
$skipped  = 0
foreach ($f in $targetFiles) {
    if ($ExcludeFiles -contains (Split-Path $f -Leaf)) {
        Write-Host "  [제외] $f"
        $skipped++
        continue
    }
    $src = Join-Path $LocalPath $f
    $dst = Join-Path $ServerPath $f
    if (Test-Path $src) {
        New-Item -ItemType Directory -Force -Path (Split-Path $dst) | Out-Null
        Copy-Item $src $dst -Force
        Write-Host "  배포: $f"
        $deployed++
    }
}

# 오래된 백업 10개 초과분 삭제
Get-ChildItem $BackupBase -Directory -ErrorAction SilentlyContinue |
    Sort-Object Name -Descending |
    Select-Object -Skip 10 |
    Remove-Item -Recurse -Force

Write-Host ""
Write-Host "✓ 배포 완료: ${deployed}개 배포, ${skipped}개 제외"
if ($timestamp) {
    Write-Host "  롤백 명령: .\deploy.ps1 -Rollback -BackupId $timestamp"
}
