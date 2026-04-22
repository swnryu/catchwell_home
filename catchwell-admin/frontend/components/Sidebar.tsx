'use client';

import Link from 'next/link';
import { usePathname, useRouter } from 'next/navigation';
import { cn } from '@/lib/utils';

const menus = [
  {
    label: 'AS 관리',
    icon: '🔧',
    href: '/dashboard/as',
  },
  {
    label: '반품/교환',
    icon: '↩️',
    href: '/dashboard/cancellation',
  },
  {
    label: '출고 처리',
    icon: '📦',
    href: '/dashboard/shipment',
  },
  {
    label: '포토이벤트',
    icon: '📸',
    href: '/dashboard/event',
  },
  {
    label: 'SMS 수신함',
    icon: '💬',
    href: '/dashboard/sms',
  },
  {
    label: '관리자 설정',
    icon: '⚙️',
    href: '/dashboard/settings',
  },
];

export default function Sidebar() {
  const pathname = usePathname();
  const router = useRouter();

  function handleLogout() {
    localStorage.clear();
    router.push('/');
  }

  const user = typeof window !== 'undefined'
    ? JSON.parse(localStorage.getItem('user') || '{}')
    : {};

  return (
    <aside className="w-60 min-h-screen bg-gray-900 text-white flex flex-col">
      <div className="px-6 py-5 border-b border-gray-700">
        <h1 className="text-lg font-bold tracking-wide">CATCHWELL</h1>
        <p className="text-xs text-gray-400 mt-0.5">관리자 시스템</p>
      </div>

      <nav className="flex-1 px-3 py-4 space-y-1">
        {menus.map((menu) => (
          <Link
            key={menu.href}
            href={menu.href}
            className={cn(
              'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-colors',
              pathname === menu.href || pathname.startsWith(menu.href + '/')
                ? 'bg-blue-600 text-white'
                : 'text-gray-300 hover:bg-gray-800 hover:text-white'
            )}
          >
            <span>{menu.icon}</span>
            <span>{menu.label}</span>
          </Link>
        ))}
      </nav>

      <div className="px-4 py-4 border-t border-gray-700">
        <div className="text-xs text-gray-400 mb-3">
          {user.name} 님
        </div>
        <button
          onClick={handleLogout}
          className="w-full text-left text-xs text-gray-400 hover:text-white transition-colors"
        >
          로그아웃
        </button>
      </div>
    </aside>
  );
}
