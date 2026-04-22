'use client';

import { useEffect, useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

interface Summary {
  as: { total: number; pending: number };
  cancellation: { today: number };
  shipment: { today: number };
  event: { total: number };
}

export default function DashboardPage() {
  const [summary, setSummary] = useState<Summary | null>(null);
  const [today, setToday] = useState('');

  useEffect(() => {
    const d = new Date();
    const days = ['일', '월', '화', '수', '목', '금', '토'];
    setToday(`${d.getFullYear()}년 ${d.getMonth() + 1}월 ${d.getDate()}일 ${days[d.getDay()]}요일`);

    const token = localStorage.getItem('token');
    fetch(`${process.env.NEXT_PUBLIC_API_URL}/api/dashboard/summary`, {
      headers: { Authorization: `Bearer ${token}` },
    })
      .then((r) => r.json())
      .then(setSummary)
      .catch(console.error);
  }, []);

  const stats = summary
    ? [
        { label: 'AS 처리 대기', value: summary.as.pending, sub: `전체 ${summary.as.total}건`, color: 'text-blue-600' },
        { label: '오늘 반품/교환', value: summary.cancellation.today, sub: '신규 접수', color: 'text-orange-500' },
        { label: '오늘 출고', value: summary.shipment.today, sub: '처리 예정', color: 'text-green-600' },
        { label: '포토이벤트', value: summary.event.total, sub: '전체 응모', color: 'text-purple-600' },
      ]
    : [];

  return (
    <div className="p-6">
      <div className="mb-6">
        <h2 className="text-2xl font-bold text-gray-800">{today}</h2>
        <p className="text-sm text-gray-500 mt-1">오늘의 업무 현황</p>
      </div>

      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        {summary === null ? (
          Array.from({ length: 4 }).map((_, i) => (
            <Card key={i} className="animate-pulse">
              <CardContent className="p-6">
                <div className="h-4 bg-gray-200 rounded w-2/3 mb-3" />
                <div className="h-8 bg-gray-200 rounded w-1/3" />
              </CardContent>
            </Card>
          ))
        ) : (
          stats.map((s) => (
            <Card key={s.label} className="hover:shadow-md transition-shadow">
              <CardHeader className="pb-2">
                <CardTitle className="text-sm font-medium text-gray-500">{s.label}</CardTitle>
              </CardHeader>
              <CardContent>
                <p className={`text-3xl font-bold ${s.color}`}>{s.value.toLocaleString()}</p>
                <p className="text-xs text-gray-400 mt-1">{s.sub}</p>
              </CardContent>
            </Card>
          ))
        )}
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <Card>
          <CardHeader>
            <CardTitle className="text-base">빠른 메뉴</CardTitle>
          </CardHeader>
          <CardContent className="grid grid-cols-2 gap-3">
            {[
              { label: 'AS 수리 업무', href: '/dashboard/as' },
              { label: '반품/교환 목록', href: '/dashboard/cancellation' },
              { label: '출고 처리', href: '/dashboard/shipment' },
              { label: '포토이벤트', href: '/dashboard/event' },
            ].map((item) => (
              <a
                key={item.href}
                href={item.href}
                className="flex items-center justify-center p-3 rounded-lg border border-gray-200 text-sm font-medium text-gray-700 hover:bg-blue-50 hover:border-blue-300 hover:text-blue-700 transition-colors"
              >
                {item.label}
              </a>
            ))}
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle className="text-base">시스템 정보</CardTitle>
          </CardHeader>
          <CardContent className="space-y-2 text-sm text-gray-600">
            <div className="flex justify-between py-1 border-b border-gray-100">
              <span>버전</span><span className="font-medium">2.0.0</span>
            </div>
            <div className="flex justify-between py-1 border-b border-gray-100">
              <span>프레임워크</span><span className="font-medium">Next.js + NestJS</span>
            </div>
            <div className="flex justify-between py-1">
              <span>DB</span><span className="font-medium">MySQL 8.0</span>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  );
}
