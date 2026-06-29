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
