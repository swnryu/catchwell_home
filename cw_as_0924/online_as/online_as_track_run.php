<?php
session_start();
include('../common.php');
require('../check_session.php');
include('../def_inc.php');

if (($PERMISSION & PERMISSION_ALL) != PERMISSION_ALL) {
    die('권한이 없습니다.');
}

$site_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
          . '://' . $_SERVER['HTTP_HOST'] . '/cw_as_0924';
?>
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<title>CJ 배송추적 수동 실행</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { background: #1a1a2e; color: #e0e0e0; font-family: 'Courier New', monospace; font-size: 14px; padding: 24px; }
h2 { color: #00d4ff; margin-bottom: 16px; font-size: 18px; }
#controls { margin-bottom: 12px; display: flex; gap: 10px; align-items: center; }
#btnRun {
    padding: 9px 24px; background: #00897b; color: #fff;
    border: none; border-radius: 4px; cursor: pointer; font-size: 14px;
}
#btnRun:hover:not(:disabled) { background: #00695c; }
#btnRun:disabled { background: #555; cursor: not-allowed; }
#status { font-size: 13px; color: #aaa; }
#progress-bar-wrap { width: 300px; height: 6px; background: #333; border-radius: 3px; display: none; }
#progress-bar { height: 6px; background: #00897b; border-radius: 3px; width: 0%; transition: width 0.3s; }
#terminal {
    background: #0d0d0d; border: 1px solid #333; border-radius: 6px;
    padding: 16px; min-height: 200px; max-height: 72vh;
    overflow-y: auto; line-height: 1.8;
}
.line       { white-space: pre-wrap; word-break: break-all; color: #ccc; }
.line.start { color: #fff; font-weight: bold; }
.line.info  { color: #00bcd4; }
.line.done  { color: #4caf50; }
.line.mid   { color: #8bc34a; }
.line.err   { color: #f44336; }
.line.skip  { color: #ff9800; }
.line.end   { color: #fff; font-weight: bold; }
#footer { margin-top: 16px; }
a.btn-back {
    display: inline-block; padding: 8px 20px; background: #333;
    color: #ccc; text-decoration: none; border-radius: 4px; font-family: sans-serif; font-size: 13px;
}
a.btn-back:hover { background: #444; color: #fff; }
</style>
</head>
<body>
<h2>CJ 배송추적 수동 실행</h2>
<div id="controls">
    <button id="btnRun">▶ 실행</button>
    <span id="status">실행 버튼을 누르면 배송조회를 시작합니다.</span>
    <div id="progress-bar-wrap"><div id="progress-bar"></div></div>
</div>
<div id="terminal"></div>
<div id="footer">
    <a href="<?php echo $site_url; ?>/online_as/online_as_tracking.php" class="btn-back">← 돌아가기</a>
</div>

<script>
var running = false;

function log(msg, cls) {
    var t = document.getElementById('terminal');
    var d = document.createElement('div');
    d.className = 'line' + (cls ? ' ' + cls : '');
    d.textContent = msg;
    t.appendChild(d);
    t.scrollTop = t.scrollHeight;
}

function setProgress(done, total) {
    var wrap = document.getElementById('progress-bar-wrap');
    var bar  = document.getElementById('progress-bar');
    wrap.style.display = 'block';
    bar.style.width = (total > 0 ? Math.round(done / total * 100) : 0) + '%';
}

function ajax(params, cb) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'online_as_track_chunk.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status === 200) {
            try { cb(null, JSON.parse(xhr.responseText)); }
            catch(e) { cb('JSON 파싱 오류: ' + xhr.responseText); }
        } else {
            cb('HTTP ' + xhr.status);
        }
    };
    xhr.onerror = function() { cb('네트워크 오류'); };
    var body = Object.keys(params).map(function(k) {
        return encodeURIComponent(k) + '=' + encodeURIComponent(params[k]);
    }).join('&');
    xhr.send(body);
}

document.getElementById('btnRun').addEventListener('click', function() {
    if (running) return;
    running = true;

    var btn    = this;
    var status = document.getElementById('status');
    btn.disabled = true;
    document.getElementById('terminal').innerHTML = '';
    document.getElementById('progress-bar-wrap').style.display = 'none';
    document.getElementById('progress-bar').style.width = '0%';

    var now = function() { return '[' + new Date().toTimeString().slice(0,8) + '] '; };

    log(now() + '=== CJ 배송추적 수동 실행 시작 ===', 'start');
    status.textContent = '대상 목록 조회 중...';

    // 1단계: 처리 대상 idx 목록 가져오기
    ajax({ action: 'list' }, function(err, res) {
        if (err || !res.ok) {
            log('목록 조회 실패: ' + (err || res.msg), 'err');
            btn.disabled = false; running = false; return;
        }

        var idxs  = res.idxs;
        var total = idxs.length;
        var done  = 0;
        var cntDone = 0, cntSkip = 0, cntErr = 0;

        log(now() + '대상: ' + total + '건', 'info');

        if (total === 0) {
            log(now() + '=== 처리할 건이 없습니다 ===', 'end');
            btn.disabled = false; running = false;
            status.textContent = '완료.';
            return;
        }

        // 2단계: 1건씩 순차 처리
        function processNext() {
            if (done >= total) {
                log(now() + '=== 완료: 배송완료 ' + cntDone + '건 / 건너뜀 ' + cntSkip + '건 / 오류 ' + cntErr + '건 ===', 'end');
                btn.disabled = false; running = false;
                status.textContent = '완료. 다시 실행하려면 버튼을 누르세요.';
                return;
            }

            var idx = idxs[done];
            status.textContent = '처리 중... (' + (done + 1) + '/' + total + ')';
            setProgress(done, total);

            ajax({ action: 'process', idx: idx }, function(err, res) {
                done++;
                setProgress(done, total);

                if (err) {
                    log(now() + 'ERR  idx=' + idx + ' ' + err, 'err');
                    cntErr++;
                } else if (res.skip) {
                    log(now() + 'SKIP idx=' + idx + ' ' + res.msg, 'skip');
                    cntSkip++;
                } else if (res.err) {
                    log(now() + 'ERR  idx=' + idx + ' ' + res.msg, 'err');
                    cntErr++;
                } else {
                    var cls = res.status === 2 ? 'done' : (res.status === 1 ? 'mid' : '');
                    log(now() + '    idx=' + idx + ' ' + res.reg_num + ' ' + res.label + ' [' + res.scanNm + ']', cls);
                    if (res.status === 2) cntDone++;
                }

                // 500ms 간격으로 다음 건 처리
                setTimeout(processNext, 500);
            });
        }

        processNext();
    });
});
</script>
</body>
</html>
