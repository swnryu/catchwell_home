<?php
error_reporting(E_ALL);
include("../def_inc.php");

$mod = M_MAIN;
$menu = S_MAIN;

include("../header.php");

$message = ''; // 메시지를 저장할 변수

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customerPhone = $_POST['customerPhone'];

    require_once("../kakao/CKakaoNotificationTalkEx.php");
    $notiMsg = new CKakaoNotificationTalkEx();
    
    // 발송 후 true가 반환되면 메시지 저장
    if ($notiMsg->NotiMsg_picture_get($customerPhone, 1)) {
        $message = "카카오 알림톡을 발송하였습니다.";
    }
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>카카오 알림톡 발송 센터 (PC)</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Pretendard:wght@400;500;600;700&display=swap');
        
        body {
            font-family: 'Pretendard', sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
            font-size: 1.1rem; /* 전체 기본 폰트 크기 상향 */
        }

        .kakao-yellow { background-color: #FEE500; }
        .kakao-label { color: #191919; }
        
        /* PC 전용 애니메이션 및 레이아웃 최적화 */
        .desktop-container {
            min-height: calc(100vh - 120px);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .fade-in {
            animation: fadeIn 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .toast-active {
            animation: slideInRight 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes slideInRight {
            from { transform: translate(100%, 0); opacity: 0; }
            to { transform: translate(0, 0); opacity: 1; }
        }

        /* 호버 이펙트 */
        .btn-hover-effect:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="p-6">

    <div class="desktop-container">
        <div class="max-w-7xl w-full grid grid-cols-1 lg:grid-cols-12 gap-8 fade-in">
            
            <!-- 왼쪽 섹션: 상태 및 안내 (Column 5) -->
            <div class="lg:col-span-5 space-y-6">
                <div class="glass-card p-10 rounded-[2.5rem] shadow-xl h-full flex flex-col">
                    <div class="flex items-center gap-5 mb-10">
                        <div class="w-16 h-16 kakao-yellow rounded-2xl flex items-center justify-center shadow-sm">
                            <i data-lucide="message-square" class="w-8 h-8 kakao-label"></i>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold tracking-tight text-slate-900">CS 알림톡 발송</h1>
                            <p class="text-lg text-slate-500 font-medium">관리자 전용 대시보드</p>
                        </div>
                    </div>

                    <div class="space-y-8 flex-grow">
                        <div class="bg-slate-50 p-8 rounded-3xl border border-slate-100">
                            <h2 class="text-base font-bold text-slate-400 uppercase tracking-wider mb-5 flex items-center gap-2">
                                <i data-lucide="info" class="w-5 h-5"></i>
                                고객 안내 가이드
                            </h2>
                            <div class="space-y-6">
                                <p class="text-2xl leading-relaxed font-semibold text-slate-700">
                                    "문의 주신 내용 검토를 위해 문제가 발생한 <span class="text-blue-600 underline underline-offset-8">사진 또는 영상</span>을 전송 요청하는 메시지입니다."
                                </p>
                                <ul class="space-y-3 text-lg text-slate-500">
                                    <li class="flex items-start gap-3">
                                        <i data-lucide="check-circle-2" class="w-6 h-6 text-green-500 mt-1"></i>
                                        <span>사진 수신 전용 채팅방으로 연결됩니다.</span>
                                    </li>
                                    <li class="flex items-start gap-3">
                                        <i data-lucide="check-circle-2" class="w-6 h-6 text-green-500 mt-1"></i>
                                        <span>채팅 상담 불가 안내가 포함되어 있습니다.</span>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="p-8 bg-yellow-50 rounded-3xl border border-yellow-100">
                            <p class="text-lg text-yellow-800 leading-relaxed flex gap-4">
                                <i data-lucide="alert-triangle" class="w-6 h-6 shrink-0 mt-1"></i>
                                <span><strong>주의:</strong> 잘못된 번호로 발송되지 않도록 입력 후 다시 한번 확인해 주시기 바랍니다.</span>
                            </p>
                        </div>
                    </div>

                    <div class="mt-10 pt-8 border-t border-slate-100 flex justify-between items-center text-sm text-slate-400 font-medium">
                        <span>CATCHWELL SUPPORT</span>
                        <span class="flex items-center gap-2"><span class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></span> 시스템 정상동작 중</span>
                    </div>
                </div>
            </div>

            <!-- 오른쪽 섹션: 입력 폼 (Column 7) -->
            <div class="lg:col-span-7">
                <div class="glass-card p-12 rounded-[2.5rem] shadow-xl h-full flex flex-col justify-center">
                    <form id="sendForm" method="POST" action="" class="space-y-10 max-w-xl mx-auto w-full">
                        <div class="text-center mb-6">
                            <h3 class="text-2xl font-bold text-slate-800 italic">수신 정보 입력</h3>
                            <p class="text-slate-500 text-lg mt-2">발송할 고객의 핸드폰 번호를 입력하세요.</p>
                        </div>

                        <div class="space-y-4">
                            <label for="customerPhone" class="text-lg font-bold text-slate-600 ml-1">전화번호 (휴대폰)</label>
                            <div class="group relative">
                                <div class="absolute left-6 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-yellow-600 transition-colors">
                                    <i data-lucide="smartphone" class="w-8 h-8"></i>
                                </div>
                                <input 
                                    type="text" 
                                    id="customerPhone" 
                                    name="customerPhone" 
                                    placeholder="010-0000-0000"
                                    maxlength="13"
                                    required
                                    class="w-full pl-16 pr-8 py-7 bg-slate-50 border-2 border-slate-100 rounded-[1.5rem] focus:bg-white focus:border-yellow-400 transition-all outline-none text-4xl font-bold tracking-[0.15em] placeholder:text-slate-300 shadow-inner"
                                >
                            </div>
                        </div>

                        <div class="pt-6">
                            <button 
                                type="submit" 
                                id="submitBtn"
                                class="btn-hover-effect w-full kakao-yellow py-7 rounded-[1.5rem] font-black text-2xl kakao-label shadow-lg shadow-yellow-200 transition-all flex items-center justify-center gap-4 group"
                            >
                                <span id="btnText">알림톡 즉시 발송</span>
                                <i data-lucide="arrow-right" class="w-8 h-8 group-hover:translate-x-2 transition-transform"></i>
                            </button>
                        </div>

                        <div class="flex items-center justify-center gap-8 opacity-40 grayscale hover:grayscale-0 transition-all cursor-default pt-6">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/e/e3/KakaoTalk_logo.svg" alt="Kakao" class="h-8">
                            <div class="h-6 w-px bg-slate-400"></div>
                            <span class="text-xs font-black uppercase tracking-[0.3em] text-slate-600">Enterprise Talk Service</span>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- 우측 상단 토스트 알림 (크기 조정) -->
    <div 
        id="toast" 
        class="fixed top-10 right-10 hidden bg-white border border-slate-100 text-slate-800 px-8 py-6 rounded-3xl shadow-2xl flex items-center gap-5 z-50 min-w-[400px] border-l-[10px] border-l-green-500"
    >
        <div id="toastIcon" class="p-3 bg-green-100 text-green-600 rounded-2xl">
            <i data-lucide="check-circle" class="w-8 h-8"></i>
        </div>
        <div>
            <p class="text-sm font-bold text-slate-400 uppercase tracking-tighter">System Message</p>
            <p id="toastMessage" class="font-bold text-xl"></p>
        </div>
    </div>

    <script>
        // 아이콘 초기화
        lucide.createIcons();

        // 전화번호 자동 하이픈 (PC 사용성 개선)
        const phoneInput = document.getElementById('customerPhone');
        phoneInput.addEventListener('input', function(e) {
            let val = e.target.value.replace(/[^0-9]/g, '');
            if (val.length > 3 && val.length <= 7) {
                val = val.slice(0, 3) + '-' + val.slice(3);
            } else if (val.length > 7) {
                val = val.slice(0, 3) + '-' + val.slice(3, 7) + '-' + val.slice(7);
            }
            e.target.value = val;
        });

        // 엔터키 제출 방지 및 세련된 처리
        phoneInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('submitBtn').click();
            }
        });

        // 폼 제출 로딩 처리
        document.getElementById('sendForm').onsubmit = function() {
            const btn = document.getElementById('submitBtn');
            const btnText = document.getElementById('btnText');
            btn.disabled = true;
            btn.classList.replace('kakao-yellow', 'bg-slate-200');
            btn.classList.add('cursor-not-allowed', 'shadow-none');
            btnText.innerHTML = '<span class="animate-pulse">발송 처리 중...</span>';
        };

        // PC용 사이드 토스트 알림
        function showToast(msg) {
            const toast = document.getElementById('toast');
            const toastMsg = document.getElementById('toastMessage');
            
            toastMsg.innerText = msg;
            toast.classList.remove('hidden');
            toast.classList.add('toast-active');

            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(20px)';
                toast.style.transition = 'all 0.5s ease';
                setTimeout(() => {
                    toast.classList.add('hidden');
                    toast.style = '';
                }, 500);
            }, 4000);
        }

        // PHP 메시지 실행
        window.onload = function() {
            <?php if ($message): ?>
                showToast("<?php echo $message; ?>");
            <?php endif; ?>
        };
    </script>
</body>
</html>

<?php
include('../footer.php');
?>