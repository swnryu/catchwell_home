'use client';

import { useState } from 'react';
import { useRouter } from 'next/navigation';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { login } from '@/lib/api';

export default function LoginPage() {
  const router = useRouter();
  const [userid, setUserid] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  async function handleSubmit(e: React.FormEvent) {
    e.preventDefault();
    setError('');
    setLoading(true);
    try {
      const data = await login(userid, password);
      localStorage.setItem('token', data.access_token);
      localStorage.setItem('user', JSON.stringify(data.user));
      router.push('/dashboard');
    } catch (err: unknown) {
      setError(err instanceof Error ? err.message : '로그인에 실패했습니다.');
    } finally {
      setLoading(false);
    }
  }

  return (
    <div className="min-h-screen flex items-center justify-center">
      <div className="w-full max-w-md px-4">
        <div className="text-center mb-8">
          <h1 className="text-3xl font-bold text-gray-900">CATCHWELL</h1>
          <p className="text-gray-500 mt-1 text-sm">관리자 시스템</p>
        </div>

        <Card className="shadow-lg border-0">
          <CardHeader className="pb-4">
            <CardTitle className="text-lg font-semibold text-center text-gray-700">로그인</CardTitle>
          </CardHeader>
          <CardContent>
            <form onSubmit={handleSubmit} className="space-y-4">
              <div className="space-y-1.5">
                <Label htmlFor="userid">아이디</Label>
                <Input
                  id="userid"
                  type="text"
                  placeholder="아이디를 입력하세요"
                  value={userid}
                  onChange={(e) => setUserid(e.target.value)}
                  required
                  autoFocus
                />
              </div>
              <div className="space-y-1.5">
                <Label htmlFor="password">비밀번호</Label>
                <Input
                  id="password"
                  type="password"
                  placeholder="비밀번호를 입력하세요"
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  required
                />
              </div>

              {error && (
                <p className="text-sm text-red-500 text-center">{error}</p>
              )}

              <Button type="submit" className="w-full" disabled={loading}>
                {loading ? '로그인 중...' : '로그인'}
              </Button>
            </form>
            <p className="text-center text-xs text-gray-400 mt-4">
              아이디/비밀번호 문의는 관리자에게 연락하세요.
            </p>
          </CardContent>
        </Card>

        <p className="text-center text-xs text-gray-400 mt-6">
          Copyright © 캐치웰. All rights reserved.
        </p>
      </div>
    </div>
  );
}
