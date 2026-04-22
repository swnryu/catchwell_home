<?
/** Error reporting */
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);


include("../common.php");


function showStatus( $state )
{
    switch( $state )
    {
        case 0 :
            echo "접수중";
            break;
            
        case 1 :
            echo "접수완료";
            break;
            
        case 2 :
            echo "수리중";
            break;
            
        case 3 :
            echo "수리완료";
            break;
            
        case 4 :
            echo "출고완료";
            break;
    }
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
    // $searchBy = "searchbyContact";
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
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="../css/online_as_customer.css">
<style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
    }
    .search-title {
        text-align: center;
        padding: 20px;
    }
    .search-contents {
        padding: 15px;
        background-color: #f9f9f9;
        border-bottom: 1px solid #ddd;
    }
    .search-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }
    .search-table th, .search-table td {
        padding: 12px;
        text-align: center;
        border: 1px solid #ddd;
    }
    .search-table th {
        background-color: #f4f4f4;
    }
    .search-button {
        padding: 5px 10px;
        margin-top: 5px;
        cursor: pointer;
    }
    @media (max-width: 768px) {
        .search-table, .search-table thead, .search-table tbody, .search-table th, .search-table td, .search-table tr {
            display: block;
        }
        .search-table thead tr {
            display: none;
        }
        .search-table tr {
            margin-bottom: 15px;
            border: 1px solid #ddd;
        }
        .search-table td {
            display: flex;
            justify-content: space-between;
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            position: relative;
        }
        .search-table td::before {
            content: attr(data-label);
            flex: 1;
            font-weight: bold;
            padding-right: 10px;
        }
    }
</style>
</head>
<body>
    <div class="search-title">
        <p><strong>A/S 접수조회</strong></p>
    </div>
    <?php
    if( $result_cnt == 0 )
    {
    ?>
        <div class="search-contents">
            <p><i class="far fa-check-square"></i>조회 결과가 없습니다.</p>
        </div>
    <?php
    }
    else
    {
    ?>
        <div class="search-contents">
            <p>&#x25A0; A/S 접수 후 2~3일내 CJ대한통운이나 우체국택배에서 방문하여 고객님의 제품을 수거할 예정입니다.</p>
            <p>&#x25A0; 택배로 수거되는 과정에 제품파손이 없도록 안전한 포장을 부탁드립니다.</p>
        </div>
        <table class="search-table">
            <thead>
            <tr>
                <th>접수번호</th>
                <th>이름</th>
                <th>제품명</th>
                <th>접수일</th>
                <th>진행상태</th>
                <th>운송장번호(회수)</th>
                <th>운송장번호(출고)</th>
            </tr>
            </thead>
            <tbody>
            <?php
                for( $i = 0; $i < $result_cnt; $i++ )
                {
                    mysqli_data_seek( $result, $i );
                    $search_db_row = mysqli_fetch_array( $result );
                    ?>
                    <tr>
                        <td data-label="접수번호"><?php echo $search_db_row[ 'reg_num' ] ?></td>
                        <td data-label="이름"><?php echo $search_db_row[ 'customer_name' ] ?></td>
                        <td data-label="제품명"><?php echo $search_db_row[ 'product_name' ] ?></td>
                        <td data-label="접수일"><?php echo $search_db_row[ 'reg_date' ] ?></td>
                        <td data-label="진행상태"><?php echo showStatus( $search_db_row[ 'process_state' ] ) ?></td>
                        <td data-label="운송장번호(회수)">
                            <?php 
                                $parcel_num = $search_db_row[ 'parcel_num' ];
                                $parcel_num_length = strlen( $parcel_num );
                                if( $parcel_num != "" )
                                {
                                    echo $parcel_num;
                                    
                                    if( $parcel_num_length == 12 )
                                    {
                            ?> 
                                        <input type="button" value="조회" class="search-button"
                                            onclick="window.open('http://nexs.cjgls.com/web/service02_01.jsp?slipno='+'<?php echo $search_db_row[ 'parcel_num' ] ?>',
                                                                 'CJ대한통운 운송장조회',
                                                                 'width=570, height=680, location=no, status=no, scrollbars=yes');">
                                <?php  }
                                    else
                                    { ?>
                                        <input type="button" value="조회" class="search-button"
                                            onclick="window.open('http://service.epost.go.kr/trace.RetrieveRegiPrclDeliv.postal?sid1='+'<?php echo $search_db_row[ 'parcel_num' ] ?>',
                                                                 '우체국택배 운송장조회',
                                                                 'width=570, height=680, location=no, status=no, scrollbars=yes');">
                                <?php  } ?>
                            <?php  } ?>
                        </td>
                        <td data-label="운송장번호(출고)">
                            <?php 
                                $parcel_num_return = $search_db_row[ 'parcel_num_return' ];
                                if( $parcel_num_return != "" )
                                {
                                    echo $parcel_num_return;
                            ?>
                                    <input type="button" value="조회" class="search-button"
                                        onclick="window.open('http://nexs.cjgls.com/web/service02_01.jsp?slipno='+'<?php echo $search_db_row[ 'parcel_num_return' ] ?>',
                                                                'CJ대한통운 운송장조회',
                                                                'width=570, height=680, location=no, status=no, scrollbars=yes');">
                            <?php  } ?>
                        </td>
                    </tr>
                    <?php
                }
            ?>
        </tbody>
    </table>
    <?php
    }
    ?>
</body>
</html>
