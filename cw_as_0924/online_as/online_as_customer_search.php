<?
/** Error reporting */
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

include("../common.php");

// 진행 상태에 따른 디자인 배지 출력을 위한 함수 수정
function showStatusBadge( $state )
{
    $label = "";
    $colorClass = "";
    
    switch( $state )
    {
        case 0 : $label = "접수중"; $colorClass = "status-0"; break;
        case 1 : $label = "접수완료"; $colorClass = "status-1"; break;
        case 2 : $label = "수리중"; $colorClass = "status-2"; break;
        case 3 : $label = "수리완료"; $colorClass = "status-3"; break;
        case 4 : $label = "출고완료"; $colorClass = "status-4"; break;
        case 5 : $label = "취소";    $colorClass = "status-5"; break;
        case 6 : $label = "택배비입금"; $colorClass = "status-6"; break;
        case 7 : $label = "배송완료"; $colorClass = "status-7"; break;
        case 8 : $label = "미처리";  $colorClass = "status-8"; break;
        case 9 : $label = "수리비입금"; $colorClass = "status-9"; break;
        default: $label = "확인불가"; $colorClass = "status-default"; break;
    }
    echo "<span class='status-badge $colorClass'>$label</span>";
}

$searchBy = $_GET['searchBy'];
if( $searchBy == "searchbyRegisterNo" )
{
    $strSearchCondition = 'reg_num';
    $searchData = $_GET['searchData'];
    $search_sql = "SELECT * FROM as_parcel_service where $strSearchCondition='$searchData' ORDER BY reg_date DESC";
}
else
{
    $searchData = $_GET['searchValueName'];
    $searchData2 = $_GET['searchValuePhone'];
    $strSearchCondition = 'customer_name';
    $strSearchCondition2 = 'customer_phone';
    $search_sql = "SELECT * FROM as_parcel_service where $strSearchCondition='$searchData' AND $strSearchCondition2='$searchData2' ORDER BY reg_date DESC";
}

$result = $db->result( $search_sql );
$result_cnt = mysqli_num_rows( $result );
?>

<!doctype html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>A/S 접수조회 결과</title>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2563eb;
            --bg-color: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
        }

        body {
            font-family: 'Pretendard', -apple-system, BlinkMacSystemFont, system-ui, Roboto, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            margin: 0;
            padding: 20px;
            line-height: 1.6;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-main);
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        /* 공지사항 박스 */
        .notice-card {
            background-color: #eff6ff;
            border-left: 4px solid var(--primary-color);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .notice-card p {
            margin: 5px 0;
            font-size: 0.95rem;
            color: #1e40af;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        /* 테이블 스타일 */
        .table-container {
            background: var(--card-bg);
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            border: 1px solid var(--border-color);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        thead {
            background-color: #f1f5f9;
        }

        th {
            padding: 15px;
            font-weight: 600;
            font-size: 0.85rem;
            color: var(--text-muted);
            text-transform: uppercase;
            border-bottom: 1px solid var(--border-color);
        }

        td {
            padding: 15px;
            font-size: 0.95rem;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
        }

        tr:last-child td {
            border-bottom: none;
        }

        /* 상태 뱃지 */
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .status-0 { background-color: #f3f4f6; color: #4b5563; } /* 접수중 */
        .status-1 { background-color: #dcfce7; color: #166534; } /* 접수완료 */
        .status-2 { background-color: #fef9c3; color: #854d0e; } /* 수리중 */
        .status-3 { background-color: #dbeafe; color: #1e40af; } /* 수리완료 */
        .status-4 { background-color: #ede9fe; color: #5b21b6; } /* 출고완료 */
        .status-5 { background-color: #fee2e2; color: #991b1b; } /* 취소 */
        .status-6 { background-color: #f3e8ff; color: #6b21a8; } /* 택배비입금 */
        .status-7 { background-color: #d1fae5; color: #065f46; } /* 배송완료 */
        .status-8 { background-color: #ffedd5; color: #9a3412; } /* 미처리 */
        .status-9 { background-color: #fef3c7; color: #92400e; } /* 수리비입금 */
        .status-default { background-color: #f1f5f9; color: #64748b; }

        /* 조회 버튼 */
        .btn-trace {
            background-color: #ffffff;
            border: 1px solid #cbd5e1;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.2s;
            margin-left: 5px;
            color: var(--text-main);
        }

        .btn-trace:hover {
            background-color: #f1f5f9;
            border-color: #94a3b8;
        }

        /* 결과 없음 */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: var(--card-bg);
            border-radius: 12px;
            border: 1px dashed var(--border-color);
        }

        .empty-state i {
            font-size: 3rem;
            color: #cbd5e1;
            margin-bottom: 15px;
        }

        /* 모바일 대응 (반응형 카드 레이아웃) */
        @media (max-width: 850px) {
            .table-container {
                background: transparent;
                box-shadow: none;
                border: none;
            }
            
            table, thead, tbody, th, td, tr {
                display: block;
            }

            thead {
                display: none;
            }

            tr {
                background: var(--card-bg);
                margin-bottom: 15px;
                border-radius: 10px;
                padding: 15px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.05);
                border: 1px solid var(--border-color);
            }

            td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 8px 0;
                border-bottom: 1px solid #f1f5f9;
                text-align: right;
            }

            td:last-child {
                border-bottom: none;
            }

            td::before {
                content: attr(data-label);
                font-weight: 600;
                color: var(--text-muted);
                font-size: 0.85rem;
                text-align: left;
            }
            
            .btn-trace {
                padding: 6px 12px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1><i class="fa-solid fa-clipboard-list" style="color:var(--primary-color)"></i> A/S 접수조회 결과</h1>
    </div>

    <?php if( $result_cnt == 0 ) { ?>
        <!-- 결과가 없을 때 -->
        <div class="empty-state">
            <i class="fa-regular fa-face-frown"></i>
            <p style="font-size: 1.1rem; font-weight: 600;">조회 결과가 없습니다.</p>
            <p style="color: var(--text-muted); font-size: 0.9rem;">입력하신 정보를 다시 한번 확인해 주세요.</p>
            <button onclick="history.back()" class="btn-trace" style="margin-top:20px; padding:10px 20px;">돌아가기</button>
        </div>
    <?php } else { ?>
        <!-- 결과가 있을 때 안내문 -->
        <div class="notice-card">
            <p><i class="fa-solid fa-truck-fast"></i> <span>A/S 접수 후 2~3일 내 지정 택배사(CJ대한통운/우체국)에서 방문하여 제품을 수거할 예정입니다.</span></p>
            <p><i class="fa-solid fa-box-open"></i> <span>택배 수거 과정에서 제품이 파손되지 않도록 안전하고 꼼꼼한 포장을 부탁드립니다.</span></p>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>접수번호</th>
                        <th>이름</th>
                        <th>제품명</th>
                        <th>접수일</th>
                        <th>진행상태</th>
                        <th>운송장(회수)</th>
                        <th>운송장(출고)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    for( $i = 0; $i < $result_cnt; $i++ ) {
                        mysqli_data_seek( $result, $i );
                        $search_db_row = mysqli_fetch_array( $result );
                    ?>
                    <tr>
                        <td data-label="접수번호"><strong><?php echo $search_db_row[ 'reg_num' ] ?></strong></td>
                        <td data-label="이름"><?php echo $search_db_row[ 'customer_name' ] ?></td>
                        <td data-label="제품명"><?php echo $search_db_row[ 'product_name' ] ?></td>
                        <td data-label="접수일"><?php echo substr($search_db_row[ 'reg_date' ], 0, 10) ?></td>
                        <td data-label="진행상태">
                            <?php showStatusBadge( $search_db_row[ 'process_state' ] ) ?>
                        </td>
                        <td data-label="운송장(회수)">
                            <?php 
                            $parcel_num = $search_db_row[ 'parcel_num' ];
                            if( $parcel_num != "" ) {
                                echo "<span style='font-family:monospace'>" . $parcel_num . "</span>";
                                $len = strlen($parcel_num);
                                if( $len == 12 ) { ?>
                                    <button class="btn-trace" onclick="window.open('https://trace.cjlogistics.com/web/detail.jsp?slipno=<?php echo $parcel_num ?>','CJtrace','width=640,height=680');">조회</button>
                                <?php } else { ?>
                                    <button class="btn-trace" onclick="window.open('http://service.epost.go.kr/trace.RetrieveRegiPrclDeliv.postal?sid1=<?php echo $parcel_num ?>','PostTrace','width=570,height=680');">조회</button>
                                <?php }
                            } else { echo "<span style='color:#ccc'>-</span>"; }
                            ?>
                        </td>
                        <td data-label="운송장(출고)">
                            <?php 
                            $parcel_num_return = $search_db_row[ 'parcel_num_return' ];
                            if( $parcel_num_return != "" ) {
                                echo "<span style='font-family:monospace'>" . $parcel_num_return . "</span>"; ?>
                                <button class="btn-trace" onclick="window.open('https://trace.cjlogistics.com/web/detail.jsp?slipno=<?php echo $parcel_num_return ?>','CJtraceReturn','width=640,height=680');">조회</button>
                            <?php } else { echo "<span style='color:#ccc'>-</span>"; }
                            ?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    <?php } ?>
    
    <div style="text-align: center; margin-top: 30px;">
        <p style="font-size: 0.85rem; color: var(--text-muted);">&copy; <?php echo date("Y"); ?> 고객지원센터. All rights reserved.</p>
    </div>
</div>

</body>
</html>