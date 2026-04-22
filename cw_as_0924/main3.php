<?php
include_once ("./def_inc.php");
$mod    = M_MAIN;
$menu   = S_MAIN;
include ("./header.php");

$today      = date("Y-m-d");
$tomorrow   = date("Y-m-d", strtotime($today." +1 day"));

$yoil = array("일","월","화","수","목","금","토");
$day = ($yoil[date('w', strtotime($today))]);
$today2     = date("Y년 m월 d일 ").$day."요일";
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>관리자 대시보드</title>
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
            font-size: 1.1rem;
            margin: 0;
            padding: 0;
        }

        /* 전체 화면을 위한 컨테이너 설정 */
        .dashboard-wrapper {
            min-height: 100vh;
            padding: 2.5rem;
            width: 100%;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .glass-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        .fade-in {
            animation: fadeIn 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .stat-link {
            color: #008299;
            text-decoration: none;
            position: relative;
            transition: all 0.2s ease;
            display: inline-block;
        }

        .stat-link:hover {
            color: #005f6e;
            transform: scale(1.05);
        }

        /* 스크롤바 커스텀 */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</head>
<body class="bg-slate-50">

    <div class="dashboard-wrapper fade-in">
        
        <!-- 상단 헤더: 전체 너비 활용 -->
        <div class="flex items-center justify-between mb-10 bg-white p-8 rounded-[2rem] shadow-sm border border-slate-100">
            <div class="flex items-center gap-8">
                <div class="w-20 h-20 bg-blue-600 rounded-3xl flex items-center justify-center shadow-xl shadow-blue-100">
                    <i data-lucide="layout-dashboard" class="w-10 h-10 text-white"></i>
                </div>
                <div>
                    <h1 class="text-5xl font-black tracking-tighter text-slate-900"><?php echo $today2; ?></h1>
                    <p class="text-2xl text-slate-400 font-medium mt-1">CATCHWELL 통합 관리 시스템 현황</p>
                </div>
            </div>
            <div class="flex flex-col items-end gap-3">
                <span class="px-6 py-3 bg-green-50 text-green-600 rounded-2xl text-lg font-bold flex items-center gap-3 border border-green-100">
                    <span class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></span> SYSTEM STATUS: STABLE
                </span>
                <p class="text-slate-400 text-sm font-medium">마지막 업데이트: <?php echo date("H:i:s"); ?></p>
            </div>
        </div>

        <!-- 그리드 시스템: 3컬럼으로 확장하여 넓은 화면 대응 -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8">

            <!-- 1. AS 신청서 섹션 -->
            <?php if (($PERMISSION & PERMISSION_GROUP_CS) == PERMISSION_GROUP_CS) { 
                $table = "as_parcel_service";
                $query = "select count(idx) from $table where process_state = " . ST_DC . " and reg_date = date('$today')";
                $result = mysqli_query($db->db_conn, $query);
                $state0 = mysqli_fetch_array($result);

                $query = "select count(idx) from $table where process_state=".ST_REG_DONE;
                $result = mysqli_query($db->db_conn, $query);
                $state1 = mysqli_fetch_array($result);
            ?>
            <div class="glass-card p-10 rounded-[2.5rem] shadow-xl">
                <div class="flex items-center gap-5 mb-10">
                    <div class="p-4 bg-cyan-100 rounded-2xl text-cyan-600">
                        <i data-lucide="wrench" class="w-8 h-8"></i>
                    </div>
                    <h2 class="text-3xl font-bold text-slate-800 tracking-tight">AS 신청서 현황</h2>
                </div>
                
                <div class="space-y-6 flex-grow">
                    <div class="flex justify-between items-center p-8 bg-slate-50 rounded-3xl border border-slate-100">
                        <span class="text-2xl font-semibold text-slate-500">신규 접수 (입금완료)</span>
                        <a href="online_as/online_as.php?state=<?php echo ST_DC;?>" class="stat-link text-5xl font-black">
                            <?php echo $state0[0]?> <span class="text-xl">건</span>
                        </a>
                    </div>
                    <div class="flex justify-between items-center p-8 bg-slate-50 rounded-3xl border border-slate-100">
                        <span class="text-2xl font-semibold text-slate-500">접수 완료 누적</span>
                        <a href="online_as/online_as.php?state=<?php echo ST_REG_DONE;?>" class="stat-link text-5xl font-black">
                            <?php echo $state1[0]?> <span class="text-xl">건</span>
                        </a>
                    </div>
                </div>

                <div class="mt-10 grid grid-cols-2 gap-4">
                    <a href="online_as/online_as_edit_m.php" class="bg-slate-900 text-white py-6 rounded-2xl font-bold text-xl text-center hover:bg-black transition-all flex items-center justify-center gap-3">
                        <i data-lucide="cpu" class="w-6 h-6"></i> 수리 진행
                    </a>
                    <a href="online_as/online_as_edit_m1.php" class="bg-white border-2 border-slate-200 text-slate-700 py-6 rounded-2xl font-bold text-xl text-center hover:border-slate-400 transition-all flex items-center justify-center gap-3">
                        <i data-lucide="scan-barcode" class="w-6 h-6"></i> 바코드 조회
                    </a>
                </div>
            </div>
            <?php } ?>

            <!-- 2. 반품/교환 신청서 -->
            <?php if (($PERMISSION & PERMISSION_GROUP_CS) == PERMISSION_GROUP_CS) { 
                $table = "cancellation_order";
                $query = "select count(idx) from $table where date='$today' and status=0 ";
                $result = mysqli_query($db->db_conn, $query);
                $cancellation = mysqli_fetch_array($result);
            ?>
            <div class="glass-card p-10 rounded-[2.5rem] shadow-xl">
                <div class="flex items-center gap-5 mb-10">
                    <div class="p-4 bg-red-100 rounded-2xl text-red-600">
                        <i data-lucide="refresh-ccw" class="w-8 h-8"></i>
                    </div>
                    <h2 class="text-3xl font-bold text-slate-800 tracking-tight">반품/교환 현황</h2>
                </div>
                
                <div class="flex-grow flex flex-col items-center justify-center bg-red-50 rounded-[2rem] p-10 border border-red-100">
                    <p class="text-xl font-bold text-red-400 uppercase tracking-[0.2em] mb-4">Daily Total</p>
                    <a href="cancellation/cancellation_list.php" class="stat-link text-8xl font-black text-red-600">
                        <?php echo $cancellation[0]?> <span class="text-3xl">건</span>
                    </a>
                    <p class="text-slate-500 mt-8 font-semibold text-xl">오늘 실시간 접수된 신청 건입니다.</p>
                </div>
            </div>
            <?php } ?>

            <!-- 3. 포토상품평 이벤트 -->
            <?php if (($PERMISSION & PERMISSION_GROUP_SALES) == PERMISSION_GROUP_SALES) { 
                $table = "cs_online_event";
                $query = "select count(idx) from $table where udate between date('$today') and date('$tomorrow')"; 
                $result = mysqli_query($db->db_conn, $query);
                $evt = mysqli_fetch_array($result);
            ?>
            <div class="glass-card p-10 rounded-[2.5rem] shadow-xl">
                <div class="flex items-center gap-5 mb-10">
                    <div class="p-4 bg-purple-100 rounded-2xl text-purple-600">
                        <i data-lucide="images" class="w-8 h-8"></i>
                    </div>
                    <h2 class="text-3xl font-bold text-slate-800 tracking-tight">이벤트 응모</h2>
                </div>
                
                <div class="flex-grow space-y-8">
                    <div class="p-10 border-4 border-dashed border-slate-100 rounded-[2rem] flex flex-col items-center justify-center text-center">
                        <p class="text-2xl font-bold text-slate-400 mb-4">포토상품평 신규 응모</p>
                        <a href="online_event/online_event.php" class="stat-link text-7xl font-black text-purple-600">
                            <?php echo $evt[0]?> <span class="text-2xl">건</span>
                        </a>
                    </div>
                    <div class="bg-purple-50 p-6 rounded-2xl text-center">
                        <p class="text-purple-700 font-medium">마케팅 활용 데이터 적재 중</p>
                    </div>
                </div>
            </div>
            <?php } ?>

            <!-- 4. 출고 처리 -->
            <?php if (($PERMISSION & PERMISSION_GROUP_SHIPMENT) == PERMISSION_GROUP_SHIPMENT) { 
                $table = "shipping_date_new";
                $query = "select count(idx) from $table where date between date('$today') and date('$tomorrow')"; 
                $result = mysqli_query($db->db_conn, $query);
                $evt = mysqli_fetch_array($result);
            ?>
            <div class="glass-card p-10 rounded-[2.5rem] shadow-xl">
                <div class="flex items-center gap-5 mb-10">
                    <div class="p-4 bg-emerald-100 rounded-2xl text-emerald-600">
                        <i data-lucide="truck" class="w-8 h-8"></i>
                    </div>
                    <h2 class="text-3xl font-bold text-slate-800 tracking-tight">출고 처리 현황</h2>
                </div>
                
                <div class="flex-grow flex flex-col justify-center gap-6">
                    <div class="p-10 bg-emerald-50 rounded-[2.5rem] flex justify-between items-center border border-emerald-100">
                        <div>
                            <p class="text-emerald-800 text-2xl font-black uppercase">Today Shipping</p>
                            <p class="text-emerald-600 font-medium text-lg mt-1">당일 출고 예정 데이터</p>
                        </div>
                        <a href="shipment/shipment_new.php" class="stat-link text-7xl font-black text-emerald-700">
                            <?php echo $evt[0]?> <span class="text-2xl">건</span>
                        </a>
                    </div>
                    <div class="p-6 bg-white border border-slate-100 rounded-2xl flex items-center gap-4">
                        <i data-lucide="info" class="w-6 h-6 text-slate-400"></i>
                        <p class="text-slate-500 font-medium">오후 4시 이전 건 정시 출고 원칙</p>
                    </div>
                </div>
            </div>
            <?php } ?>

            <!-- 5. SMS 수신함 (넓게 사용) -->
            <?php if (($PERMISSION & PERMISSION_GROUP_CS) == PERMISSION_GROUP_CS) { ?>
            <div class="glass-card p-10 rounded-[2.5rem] shadow-xl md:col-span-2 xl:col-span-2">
                <div class="flex flex-col md:flex-row items-center justify-between h-full gap-10">
                    <div class="flex items-center gap-8">
                        <div class="w-24 h-24 bg-indigo-100 rounded-[2rem] flex items-center justify-center text-indigo-600 shadow-inner">
                            <i data-lucide="message-square-text" class="w-12 h-12"></i>
                        </div>
                        <div>
                            <h2 class="text-4xl font-black text-slate-800 tracking-tighter">업무용 SMS 수신 대시보드</h2>
                            <p class="text-xl text-slate-400 font-medium mt-2 leading-relaxed">
                                고객으로부터 수신된 사진, 영상 메시지를 실시간으로 확인하고<br>업무에 반영할 수 있는 전용 수신함입니다.
                            </p>
                        </div>
                    </div>
                    <a href="/cw_as/sms/sms_view.php" class="whitespace-nowrap px-12 py-7 bg-indigo-600 text-white rounded-[2rem] font-black text-3xl shadow-2xl shadow-indigo-100 hover:bg-indigo-700 hover:-translate-y-2 transition-all flex items-center gap-5 group">
                        수신함 접속
                        <i data-lucide="chevron-right" class="w-10 h-10 group-hover:translate-x-2 transition-transform"></i>
                    </a>
                </div>
            </div>
            <?php } ?>

        </div>

        <!-- 하단 푸터 영역 -->
        <div class="mt-12 text-center text-slate-400 font-medium border-t border-slate-200 pt-8">
            <p>© CATCHWELL ENTERPRISE RESOURCE PLANNING SYSTEM</p>
            <div class="flex justify-center gap-8 mt-4 text-sm uppercase tracking-[0.3em]">
                <span class="text-blue-500 font-bold">AS Management</span>
                <span>•</span>
                <span class="text-red-500 font-bold">Logistics Tracking</span>
                <span>•</span>
                <span class="text-purple-500 font-bold">CS Integration</span>
            </div>
        </div>
    </div>

    <script>
        // 아이콘 초기화
        lucide.createIcons();
    </script>
</body>
</html>

<?php
include ("./footer.php");
?>