const API_URL = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:4000';

export async function login(userid: string, password: string) {
  const res = await fetch(`${API_URL}/api/auth/login`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ userid, password }),
  });

  if (!res.ok) {
    const err = await res.json().catch(() => ({}));
    throw new Error(err.message || '로그인에 실패했습니다.');
  }

  return res.json();
}
