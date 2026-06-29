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

app.listen(PORT, () => {
  console.log(`서버 시작: http://localhost:${PORT}`);
});
