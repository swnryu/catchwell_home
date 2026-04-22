<?php
include_once ("./def_inc.php");
$mod = M_MAIN;
$menu = S_MAIN;
include ("./header.php");

$today = date("Y-m-d");
$tomorrow = date("Y-m-d", strtotime($today." +1 day"));

$yoil = array("일","월","화","수","목","금","토");
$day = ($yoil[date('w', strtotime($today))]);
$today2 = date("Y년 m월 d일 ").$day."요일";
?>

<style>
    .dashboard-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 20px;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }
    
    .dashboard-header {
        background: linear-gradient(135deg, #4a5568 0%, #2d3748 100%);
        color: white;
        padding: 30px 40px;
        border-radius: 16px;
        margin-bottom: 30px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    
    .dashboard-header h1 {
        margin: 0;
        font-size: 2.2rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .dashboard-header .date-icon {
        width: 32px;
        height: 32px;
        background: rgba(255,255,255,0.2);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }
    
    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 24px;
        margin-bottom: 30px;
    }
    
    .dashboard-card {
        background: white;
        border-radius: 16px;
        padding: 28px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        border: 1px solid rgba(0,0,0,0.06);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .dashboard-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 30px rgba(0,0,0,0.12);
    }
    
    .dashboard-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--card-color, #4a5568);
        border-radius: 16px 16px 0 0;
    }
    
    .card-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
    }
    
    .card-icon {
        width: 52px;
        height: 52px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        color: white;
        background: var(--card-color, #4a5568);
    }
    
    .card-title {
        font-size: 1.4rem;
        font-weight: 700;
        color: #2d3748;
        margin: 0;
    }
    
    .card-content {
        margin-bottom: 24px;
    }
    
    .stat-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #f7fafc;
    }
    
    .stat-item:last-child {
        border-bottom: none;
    }
    
    .stat-label {
        font-size: 1.1rem;
        color: #718096;
        font-weight: 500;
    }
    
    .stat-value {
        font-size: 1.6rem;
        font-weight: 700;
        color: var(--card-color, #4a5568);
    }
    
    .stat-link {
        color: var(--card-color, #4a5568);
        text-decoration: none;
        font-weight: 600;
        transition: all 0.2s ease;
    }
    
    .stat-link:hover {
        transform: scale(1.05);
        text-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .card-actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }
    
    .btn-modern {
        background: var(--card-color, #4a5568);
        color: white;
        border: none;
        padding: 14px 28px;
        border-radius: 10px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
        font-size: 1rem;
    }
    
    .btn-modern:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        color: white;
        text-decoration: none;
    }
    
    .btn-secondary {
        background: #f7fafc;
        color: #4a5568;
        border: 2px solid #e2e8f0;
    }
    
    .btn-secondary:hover {
        background: #edf2f7;
        color: #2d3748;
    }
    
    /* Card specific colors - 회색조 통일 */
    .card-as { --card-color: #4a5568; }
    .card-return { --card-color: #2d3748; }
    .card-event { --card-color: #718096; }
    .card-shipment { --card-color: #4a5568; }
    .card-sms { --card-color: #2d3748; }
    
    @media (max-width: 768px) {
        .dashboard-container {
            padding: 16px;
        }
        
        .dashboard-header {
            padding: 20px 24px;
        }
        
        .dashboard-header h1 {
            font-size: 1.7rem;
        }
        
        .dashboard-grid {
            grid-template-columns: 1fr;
            gap: 16px;
        }
        
        .dashboard-card {
            padding: 20px;
        }
        
        .card-actions {
            flex-direction: column;
        }
        
        .btn-modern {
            justify-content: center;
            width: 100%;
        }
    }
</style>

<div class="dashboard-container">
    <div class="dashboard-header">
        <h1>
            <div class="date-icon">📅</div>
            <?php echo $today2; ?> 대시보드
        </h1>
    </div>

    <div class="dashboard-grid">
        <?php if (($PERMISSION & PERMISSION_GROUP_CS) == PERMISSION_GROUP_CS): 
            $table = "as_parcel_service";

            $query = "select count(idx) from $table where process_state = " . ST_DC . " and reg_date = date('$today')";
            $result = mysqli_query($db->db_conn, $query);
            $state0 = mysqli_fetch_array($result);

            $query = "select count(idx) from $table where process_state=".ST_REG_DONE;
            $result = mysqli_query($db->db_conn, $query);
            $state1 = mysqli_fetch_array($result);
        ?>
            <div class="dashboard-card card-as">
                <div class="card-header">
                    <div class="card-icon">🔧</div>
                    <h3 class="card-title">AS 신청서</h3>
                </div>
                
                <div class="card-content">
                    <div class="stat-item">
                        <span class="stat-label">신규 접수 (택배비 입금완료)</span>
                        <a href="online_as/online_as.php?state=<?php echo ST_DC; ?>" class="stat-link">
                            <span class="stat-value"><?php echo $state0[0]; ?></span> 건
                        </a>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">접수 완료</span>
                        <a href="online_as/online_as.php?state=<?php echo ST_REG_DONE; ?>" class="stat-link">
                            <span class="stat-value"><?php echo $state1[0]; ?></span> 건
                        </a>
                    </div>
                </div>
                
                <div class="card-actions">
                    <a href="online_as/online_as_edit_m.php" class="btn-modern">
                        ⚙️ 수리 업무 진행
                    </a>
                    <a href="online_as/online_as_edit_m1.php" class="btn-modern btn-secondary">
                        📊 바코드 접수 조회
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <?php if (($PERMISSION & PERMISSION_GROUP_CS) == PERMISSION_GROUP_CS): 
            $table = "cancellation_order";
            $query = "select count(idx) from $table where date='$today' and status=0 ";
            $result = mysqli_query($db->db_conn, $query);
            $cancellation = mysqli_fetch_array($result);
        ?>
            <div class="dashboard-card card-return">
                <div class="card-header">
                    <div class="card-icon">↩️</div>
                    <h3 class="card-title">반품/교환 신청서</h3>
                </div>
                
                <div class="card-content">
                    <div class="stat-item">
                        <span class="stat-label">오늘 접수 신청</span>
                        <a href="cancellation/cancellation_list.php" class="stat-link">
                            <span class="stat-value"><?php echo $cancellation[0]; ?></span> 건
                        </a>
                    </div>
                </div>
                
                <div class="card-actions">
                    <a href="cancellation/cancellation_list.php" class="btn-modern">
                        📋 신청서 확인
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <?php if (($PERMISSION & PERMISSION_GROUP_SALES) == PERMISSION_GROUP_SALES): 
            $table = "cs_online_event";
            $query = "select count(idx) from $table where udate between date('$today') and date('$tomorrow')"; 
            $result = mysqli_query($db->db_conn, $query);
            $evt = mysqli_fetch_array($result);
        ?>
            <div class="dashboard-card card-event">
                <div class="card-header">
                    <div class="card-icon">🎉</div>
                    <h3 class="card-title">포토상품평 이벤트</h3>
                </div>
                
                <div class="card-content">
                    <div class="stat-item">
                        <span class="stat-label">신규 접수 신청</span>
                        <a href="online_event/online_event.php" class="stat-link">
                            <span class="stat-value"><?php echo $evt[0]; ?></span> 건
                        </a>
                    </div>
                </div>
                
                <div class="card-actions">
                    <a href="online_event/online_event.php" class="btn-modern">
                        🎊 이벤트 관리
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <?php if (($PERMISSION & PERMISSION_GROUP_SHIPMENT) == PERMISSION_GROUP_SHIPMENT): 
            $table = "shipping_date_new";
            $query = "select count(idx) from $table where date between date('$today') and date('$tomorrow')"; 
            $result = mysqli_query($db->db_conn, $query);
            $evt = mysqli_fetch_array($result);
        ?>
            <div class="dashboard-card card-shipment">
                <div class="card-header">
                    <div class="card-icon">📦</div>
                    <h3 class="card-title">출고 처리</h3>
                </div>
                
                <div class="card-content">
                    <div class="stat-item">
                        <span class="stat-label">오늘 출고 처리</span>
                        <a href="shipment/shipment_new.php" class="stat-link">
                            <span class="stat-value"><?php echo $evt[0]; ?></span> 건
                        </a>
                    </div>
                </div>
                
                <div class="card-actions">
                    <a href="shipment/shipment_new.php" class="btn-modern">
                        🚚 출고 관리
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <?php if (($PERMISSION & PERMISSION_GROUP_CS) == PERMISSION_GROUP_CS): ?>
            <div class="dashboard-card card-sms">
                <div class="card-header">
                    <div class="card-icon">💬</div>
                    <h3 class="card-title">SMS 수신</h3>
                </div>
                
                <div class="card-content">
                    <div class="stat-item">
                        <span class="stat-label">업무용 SMS 관리</span>
                        <span class="stat-value">📱</span>
                    </div>
                </div>
                
                <div class="card-actions">
                    <a href="/cw_as/sms/sms_view.php" class="btn-modern">
                        📨 SMS 수신함
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include ("./footer.php"); ?>