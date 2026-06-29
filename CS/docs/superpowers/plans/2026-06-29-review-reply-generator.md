# 네이버 리뷰 답글 생성기 구현 계획

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Claude API로 네이버 고객 리뷰 답글을 자동 생성하는 로컬 웹앱 구축

**Architecture:** Express 서버가 Claude API를 호출해 답글을 생성하고 JSON 파일로 히스토리를 관리한다. 프론트엔드는 `public/index.html` 단일 파일로, 서버가 정적 파일로 서빙한다.

**Tech Stack:** Node.js, Express 4.x, @anthropic-ai/sdk, crypto (Node 내장 — uuid 대신 사용)

## Global Constraints

- Node.js 18 이상
- `@anthropic-ai/sdk` 최신 버전
- `express` 4.x
- 포트: 3000 (로컬 전용)
- 히스토리 최대 100건 유지 (초과 시 오래된 항목 삭제)
- UI 히스토리 표시: 최근 20건
- Claude 모델: `claude-haiku-4-5-20251001` (빠르고 저렴)
- 답글 길이: 3~5문장
- 브랜드명: 캐치웰

---

## 파일 맵

| 파일 | 역할 |
|------|------|
| `package.json` | 의존성 및 스크립트 |
| `.env` | ANTHROPIC_API_KEY 저장 |
| `.env.example` | 키 템플릿 (git 커밋용) |
| `.gitignore` | .env, node_modules, data/ 제외 |
| `server.js` | Express 서버 전체 (라우팅 + Claude 호출 + 히스토리 I/O) |
| `data/history.json` | 히스토리 저장 (서버 시작 시 없으면 자동 생성) |
| `public/index.html` | 전체 UI (CSS/JS 인라인) |

---

### Task 1: 프로젝트 초기 설정

**Files:**
- Create: `package.json`
- Create: `.env.example`
- Create: `.gitignore`

**Interfaces:**
- Produces: `npm run dev` 명령으로 서버 시작 가능한 환경

- [ ] **Step 1: package.json 작성**

```json
{
  "name": "review-reply-generator",
  "version": "1.0.0",
  "main": "server.js",
  "scripts": {
    "start": "node server.js",
    "dev": "node --watch server.js"
  },
  "dependencies": {
    "@anthropic-ai/sdk": "^0.102.0",
    "dotenv": "^16.4.7",
    "express": "^4.21.2"
  }
}
```

- [ ] **Step 2: .env.example 작성**

```
ANTHROPIC_API_KEY=sk-ant-api03-여기에_실제_키_입력
PORT=3000
```

- [ ] **Step 3: .env 파일 생성 (실제 키 입력)**

`.env` 파일을 `.env.example`을 복사해서 만들고 실제 `ANTHROPIC_API_KEY` 값을 입력한다.

```
ANTHROPIC_API_KEY=sk-ant-api03-실제키값
PORT=3000
```

- [ ] **Step 4: .gitignore 작성**

```
node_modules/
.env
data/
```

- [ ] **Step 5: 의존성 설치**

```bash
cd C:\claude\CS
npm install
```

Expected: `node_modules/` 생성, `package-lock.json` 생성

- [ ] **Step 6: 디렉토리 생성**

```bash
mkdir public
mkdir data
```

- [ ] **Step 7: 커밋**

```bash
git add package.json package-lock.json .env.example .gitignore
git commit -m "chore: 프로젝트 초기 설정"
```

---

### Task 2: server.js — 기반 구조 + 히스토리 유틸

**Files:**
- Create: `server.js`
- Create: `data/history.json` (서버 기동 시 자동 생성)
- Create: `public/index.html` (임시 placeholder)

**Interfaces:**
- Produces:
  - `loadHistory(): Array` — history.json 읽어 배열 반환
  - `saveHistory(entries: Array): void` — 배열을 history.json에 저장
  - `GET /` — `public/index.html` 서빙
  - 서버가 `http://localhost:3000`에서 기동

- [ ] **Step 1: server.js 기반 구조 작성**

```js
require('dotenv').config();
const express = require('express');
const fs = require('fs');
const path = require('path');
const crypto = require('crypto');
const Anthropic = require('@anthropic-ai/sdk');

const app = express();
const PORT = process.env.PORT || 3000;
const HISTORY_PATH = path.join(__dirname, 'data', 'history.json');
const MAX_HISTORY = 100;

app.use(express.json());
app.use(express.static(path.join(__dirname, 'public')));

if (!process.env.ANTHROPIC_API_KEY) {
  console.warn('[경고] ANTHROPIC_API_KEY가 설정되지 않았습니다. .env 파일을 확인하세요.');
}

const client = new Anthropic({ apiKey: process.env.ANTHROPIC_API_KEY });

function loadHistory() {
  if (!fs.existsSync(HISTORY_PATH)) {
    fs.writeFileSync(HISTORY_PATH, JSON.stringify([], null, 2), 'utf8');
  }
  try {
    return JSON.parse(fs.readFileSync(HISTORY_PATH, 'utf8'));
  } catch {
    return [];
  }
}

function saveHistory(entries) {
  fs.writeFileSync(HISTORY_PATH, JSON.stringify(entries, null, 2), 'utf8');
}

app.listen(PORT, () => {
  console.log(`서버 시작: http://localhost:${PORT}`);
});
```

- [ ] **Step 2: 임시 public/index.html 작성 (동작 확인용)**

```html
<!DOCTYPE html>
<html lang="ko">
<head><meta charset="UTF-8"><title>리뷰 답글 생성기</title></head>
<body><h1>리뷰 답글 생성기</h1><p>준비 중...</p></body>
</html>
```

- [ ] **Step 3: 서버 기동 확인**

```bash
npm run dev
```

Expected: `서버 시작: http://localhost:3000` 출력  
브라우저에서 `http://localhost:3000` 접속 → "리뷰 답글 생성기" 텍스트 확인

- [ ] **Step 4: 커밋**

```bash
git add server.js public/index.html
git commit -m "feat: Express 서버 기반 구조 및 히스토리 유틸 추가"
```

---

### Task 3: POST /api/generate — Claude API 호출

**Files:**
- Modify: `server.js` — `/api/generate` 라우트 추가

**Interfaces:**
- Consumes: `loadHistory()`, `saveHistory()`, `client` (Anthropic 인스턴스), `crypto.randomUUID()`
- Produces:
  - `POST /api/generate` → `{ reply: string, sentiment: string, id: string }`
  - 에러 시 `{ error: string }` with 400 or 500

- [ ] **Step 1: /api/generate 라우트를 server.js에 추가**

`app.listen(...)` 바로 위에 다음 코드를 추가한다:

```js
app.post('/api/generate', async (req, res) => {
  const { review, customGuide } = req.body;

  if (!review || !review.trim()) {
    return res.status(400).json({ error: '리뷰 내용을 입력해주세요.' });
  }

  const systemPrompt = `당신은 네이버 스마트스토어 고객 리뷰에 답글을 작성하는 CS 전문가입니다.
다음 원칙을 따릅니다:
- 공식적이고 정중한 어조 유지
- 답글 길이: 3~5문장
- 네이버 리뷰 답글 형식에 맞게 작성
- 브랜드명: 캐치웰
- 긍정 리뷰: 진심 어린 감사 + 재방문/재구매 유도
- 부정 리뷰: 공감 + 사과 + 구체적 해결책 또는 고객센터 문의 안내
- 중립 리뷰: 감사 + 추가 문의 유도
${customGuide ? `\n추가 지침:\n${customGuide}` : ''}

반드시 아래 JSON 형식으로만 응답하세요. 마크다운 코드블록 없이 순수 JSON만 출력:
{"sentiment":"positive|negative|neutral","reply":"답글 텍스트"}`;

  try {
    const message = await client.messages.create({
      model: 'claude-haiku-4-5-20251001',
      max_tokens: 1024,
      system: systemPrompt,
      messages: [
        { role: 'user', content: `다음 고객 리뷰에 대한 답글을 작성해주세요:\n\n${review}` }
      ]
    });

    const rawText = message.content[0].text.trim();
    let parsed;
    try {
      parsed = JSON.parse(rawText);
    } catch {
      return res.status(500).json({ error: 'Claude 응답 파싱 실패. 다시 시도해주세요.' });
    }

    const { sentiment, reply } = parsed;
    const id = crypto.randomUUID();
    const entry = {
      id,
      createdAt: new Date().toISOString(),
      review: review.trim(),
      reply,
      sentiment,
      customGuide: customGuide || ''
    };

    const history = loadHistory();
    history.unshift(entry);
    saveHistory(history.slice(0, MAX_HISTORY));

    return res.json({ reply, sentiment, id });
  } catch (err) {
    console.error('[/api/generate 오류]', err.message);
    return res.status(500).json({ error: 'Claude API 호출 실패. 잠시 후 다시 시도해주세요.' });
  }
});
```

- [ ] **Step 2: curl로 API 동작 확인**

서버가 실행 중인 상태에서 새 터미널에서:

```bash
curl -X POST http://localhost:3000/api/generate \
  -H "Content-Type: application/json" \
  -d "{\"review\": \"배송이 너무 느렸어요. 일주일이나 걸렸습니다.\"}"
```

Expected: `{"reply":"...답글 텍스트...","sentiment":"negative","id":"uuid-값"}` 형태의 JSON 응답

- [ ] **Step 3: 빈 리뷰 에러 확인**

```bash
curl -X POST http://localhost:3000/api/generate \
  -H "Content-Type: application/json" \
  -d "{\"review\": \"\"}"
```

Expected: `{"error":"리뷰 내용을 입력해주세요."}` with HTTP 400

- [ ] **Step 4: 커밋**

```bash
git add server.js
git commit -m "feat: POST /api/generate Claude API 연동"
```

---

### Task 4: GET/DELETE /api/history — 히스토리 엔드포인트

**Files:**
- Modify: `server.js` — `/api/history` GET, DELETE 라우트 추가

**Interfaces:**
- Consumes: `loadHistory()`, `saveHistory()`
- Produces:
  - `GET /api/history` → 최근 20건 배열
  - `DELETE /api/history` → `{ ok: true }`

- [ ] **Step 1: /api/history 라우트를 server.js에 추가**

`/api/generate` 라우트 바로 아래에 추가:

```js
app.get('/api/history', (req, res) => {
  const history = loadHistory();
  res.json(history.slice(0, 20));
});

app.delete('/api/history', (req, res) => {
  saveHistory([]);
  res.json({ ok: true });
});
```

- [ ] **Step 2: 히스토리 조회 확인**

```bash
curl http://localhost:3000/api/history
```

Expected: Task 3에서 저장된 항목이 포함된 JSON 배열

- [ ] **Step 3: 히스토리 삭제 확인**

```bash
curl -X DELETE http://localhost:3000/api/history
curl http://localhost:3000/api/history
```

Expected: 삭제 후 `[]` 반환

- [ ] **Step 4: 커밋**

```bash
git add server.js
git commit -m "feat: GET/DELETE /api/history 엔드포인트 추가"
```

---

### Task 5: public/index.html — 전체 UI

**Files:**
- Modify: `public/index.html` — 임시 placeholder를 실제 UI로 교체

**Interfaces:**
- Consumes: `POST /api/generate`, `GET /api/history`, `DELETE /api/history`
- Produces: 완성된 웹앱 UI

- [ ] **Step 1: public/index.html 전체 교체**

```html
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>네이버 리뷰 답글 생성기</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f5f6fa; color: #333; }
    .container { max-width: 760px; margin: 0 auto; padding: 24px 16px; }
    h1 { font-size: 1.4rem; font-weight: 700; margin-bottom: 24px; color: #1a1a2e; }
    .card { background: #fff; border-radius: 12px; padding: 20px; margin-bottom: 16px; box-shadow: 0 1px 4px rgba(0,0,0,0.08); }
    .card h2 { font-size: 0.85rem; font-weight: 600; color: #666; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px; }
    .toggle-btn { background: none; border: none; font-size: 0.85rem; color: #03c75a; cursor: pointer; padding: 0; margin-bottom: 8px; }
    textarea { width: 100%; border: 1px solid #e0e0e0; border-radius: 8px; padding: 12px; font-size: 0.95rem; resize: vertical; font-family: inherit; outline: none; transition: border-color 0.2s; }
    textarea:focus { border-color: #03c75a; }
    #reviewInput { min-height: 120px; }
    #customGuideInput { min-height: 70px; display: none; }
    #replyOutput { min-height: 100px; background: #f9fffe; color: #1a1a2e; }
    .btn { display: inline-flex; align-items: center; gap: 6px; padding: 10px 20px; border-radius: 8px; border: none; font-size: 0.95rem; font-weight: 600; cursor: pointer; transition: opacity 0.15s; }
    .btn:disabled { opacity: 0.5; cursor: not-allowed; }
    .btn-primary { background: #03c75a; color: #fff; width: 100%; justify-content: center; margin-top: 10px; }
    .btn-copy { background: #f0f0f0; color: #333; font-size: 0.85rem; padding: 6px 14px; }
    .btn-delete { background: none; border: 1px solid #e0e0e0; color: #999; font-size: 0.8rem; padding: 5px 12px; border-radius: 6px; cursor: pointer; }
    .reply-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; }
    .sentiment-badge { font-size: 0.75rem; padding: 3px 10px; border-radius: 20px; font-weight: 600; }
    .badge-positive { background: #e6f7ed; color: #03c75a; }
    .badge-negative { background: #fef0f0; color: #e53935; }
    .badge-neutral { background: #f0f0f0; color: #888; }
    .history-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
    .history-item { border: 1px solid #e8e8e8; border-radius: 8px; margin-bottom: 8px; overflow: hidden; }
    .history-summary { display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; cursor: pointer; background: #fafafa; font-size: 0.88rem; }
    .history-summary:hover { background: #f0f0f0; }
    .history-detail { display: none; padding: 12px 14px; background: #fff; border-top: 1px solid #e8e8e8; font-size: 0.88rem; }
    .history-detail.open { display: block; }
    .history-label { font-size: 0.75rem; color: #888; margin-bottom: 3px; }
    .history-text { white-space: pre-wrap; line-height: 1.5; margin-bottom: 10px; }
    .error-msg { color: #e53935; font-size: 0.88rem; margin-top: 8px; }
    .copy-success { color: #03c75a; font-size: 0.82rem; margin-left: 8px; }
    .spinner { display: none; width: 16px; height: 16px; border: 2px solid #fff; border-top-color: transparent; border-radius: 50%; animation: spin 0.6s linear infinite; }
    @keyframes spin { to { transform: rotate(360deg); } }
  </style>
</head>
<body>
<div class="container">
  <h1>네이버 리뷰 답글 생성기</h1>

  <div class="card">
    <button class="toggle-btn" onclick="toggleGuide()">▸ 커스텀 지침 (선택)</button>
    <textarea id="customGuideInput" placeholder="예) 제품 관련 문의는 고객센터(1588-0000)로 안내해주세요."></textarea>
  </div>

  <div class="card">
    <h2>고객 리뷰</h2>
    <textarea id="reviewInput" placeholder="네이버 리뷰 내용을 여기에 붙여넣으세요..."></textarea>
    <div id="generateError" class="error-msg"></div>
    <button class="btn btn-primary" id="generateBtn" onclick="generateReply()">
      <div class="spinner" id="spinner"></div>
      <span id="btnText">답글 생성</span>
    </button>
  </div>

  <div class="card" id="replyCard" style="display:none">
    <div class="reply-header">
      <h2>생성된 답글</h2>
      <div style="display:flex;align-items:center;gap:8px">
        <span class="sentiment-badge" id="sentimentBadge"></span>
        <button class="btn btn-copy" onclick="copyReply()">복사</button>
        <span class="copy-success" id="copySuccess"></span>
      </div>
    </div>
    <textarea id="replyOutput" readonly></textarea>
  </div>

  <div class="card">
    <div class="history-header">
      <h2>히스토리 (최근 20건)</h2>
      <button class="btn-delete" onclick="clearHistory()">전체 삭제</button>
    </div>
    <div id="historyList"></div>
  </div>
</div>

<script>
  const SENTIMENT_LABEL = { positive: '긍정', negative: '부정', neutral: '중립' };
  const SENTIMENT_CLASS = { positive: 'badge-positive', negative: 'badge-negative', neutral: 'badge-neutral' };

  function toggleGuide() {
    const el = document.getElementById('customGuideInput');
    const btn = document.querySelector('.toggle-btn');
    const open = el.style.display === 'block';
    el.style.display = open ? 'none' : 'block';
    btn.textContent = (open ? '▸' : '▾') + ' 커스텀 지침 (선택)';
  }

  async function generateReply() {
    const review = document.getElementById('reviewInput').value.trim();
    const customGuide = document.getElementById('customGuideInput').value.trim();
    const errEl = document.getElementById('generateError');
    errEl.textContent = '';

    if (!review) { errEl.textContent = '리뷰 내용을 입력해주세요.'; return; }

    const btn = document.getElementById('generateBtn');
    const spinner = document.getElementById('spinner');
    const btnText = document.getElementById('btnText');
    btn.disabled = true;
    spinner.style.display = 'block';
    btnText.textContent = '생성 중...';

    try {
      const res = await fetch('/api/generate', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ review, customGuide })
      });
      const data = await res.json();
      if (!res.ok) { errEl.textContent = data.error || '오류가 발생했습니다.'; return; }

      document.getElementById('replyOutput').value = data.reply;
      const badge = document.getElementById('sentimentBadge');
      badge.textContent = SENTIMENT_LABEL[data.sentiment] || data.sentiment;
      badge.className = 'sentiment-badge ' + (SENTIMENT_CLASS[data.sentiment] || '');
      document.getElementById('replyCard').style.display = 'block';
      document.getElementById('copySuccess').textContent = '';
      loadHistory();
    } catch {
      errEl.textContent = '서버 연결 오류가 발생했습니다.';
    } finally {
      btn.disabled = false;
      spinner.style.display = 'none';
      btnText.textContent = '답글 생성';
    }
  }

  function copyReply() {
    const text = document.getElementById('replyOutput').value;
    navigator.clipboard.writeText(text).then(() => {
      const el = document.getElementById('copySuccess');
      el.textContent = '복사됨!';
      setTimeout(() => { el.textContent = ''; }, 2000);
    });
  }

  async function loadHistory() {
    try {
      const res = await fetch('/api/history');
      const items = await res.json();
      const list = document.getElementById('historyList');
      if (!items.length) { list.innerHTML = '<p style="color:#aaa;font-size:0.85rem">아직 생성된 답글이 없습니다.</p>'; return; }
      list.innerHTML = items.map((item, i) => `
        <div class="history-item">
          <div class="history-summary" onclick="toggleDetail(${i})">
            <span>${new Date(item.createdAt).toLocaleString('ko-KR')} &nbsp;|&nbsp; <span class="sentiment-badge ${SENTIMENT_CLASS[item.sentiment] || ''}">${SENTIMENT_LABEL[item.sentiment] || item.sentiment}</span></span>
            <span style="color:#aaa;font-size:0.8rem">${item.review.slice(0,30)}${item.review.length > 30 ? '...' : ''} ▸</span>
          </div>
          <div class="history-detail" id="detail-${i}">
            <div class="history-label">리뷰</div>
            <div class="history-text">${escHtml(item.review)}</div>
            <div class="history-label">답글</div>
            <div class="history-text">${escHtml(item.reply)}</div>
            <button class="btn btn-copy" onclick="copyText(${i}, this)" data-text="${escAttr(item.reply)}">복사</button>
          </div>
        </div>`).join('');
    } catch { /* 무시 */ }
  }

  function toggleDetail(i) {
    const el = document.getElementById('detail-' + i);
    el.classList.toggle('open');
  }

  function copyText(i, btn) {
    const text = btn.getAttribute('data-text');
    navigator.clipboard.writeText(text).then(() => {
      btn.textContent = '복사됨!';
      setTimeout(() => { btn.textContent = '복사'; }, 2000);
    });
  }

  async function clearHistory() {
    if (!confirm('히스토리를 전부 삭제할까요?')) return;
    await fetch('/api/history', { method: 'DELETE' });
    loadHistory();
  }

  function escHtml(s) { return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
  function escAttr(s) { return s.replace(/"/g,'&quot;'); }

  loadHistory();
</script>
</body>
</html>
```

- [ ] **Step 2: 브라우저에서 전체 기능 확인**

`http://localhost:3000` 접속 후 다음을 순서대로 확인:
1. "고객 리뷰" 텍스트영역에 리뷰 붙여넣기
2. "답글 생성" 클릭 → 로딩 스피너 표시 → 답글 출력 및 감정 배지 표시
3. "복사" 클릭 → "복사됨!" 표시 → 클립보드에 텍스트 복사 확인
4. 히스토리에 항목 추가 확인, 펼치기/접기 동작 확인
5. 커스텀 지침 토글 → 지침 입력 후 답글 생성 → 지침 반영 여부 확인
6. "전체 삭제" → 확인 팝업 → 히스토리 초기화 확인

- [ ] **Step 3: 커밋**

```bash
git add public/index.html
git commit -m "feat: 리뷰 답글 생성기 UI 완성"
```

---

### Task 6: 최종 연기 및 README

**Files:**
- Create: `README.md`

- [ ] **Step 1: README.md 작성**

```markdown
# 네이버 리뷰 답글 생성기

Claude API를 사용해 네이버 고객 리뷰에 대한 답글을 자동 생성하는 로컬 웹앱.

## 시작하기

1. `.env.example`을 복사해 `.env` 파일 생성 후 API 키 입력
2. `npm install`
3. `npm run dev`
4. 브라우저에서 `http://localhost:3000` 접속

## 사용 방법

1. 네이버 리뷰 내용을 복사해 붙여넣기
2. "답글 생성" 클릭
3. 생성된 답글 확인 후 "복사" 클릭
4. 네이버 답글창에 붙여넣기
```

- [ ] **Step 2: 최종 커밋**

```bash
git add README.md
git commit -m "docs: README 추가 및 구현 완료"
```
