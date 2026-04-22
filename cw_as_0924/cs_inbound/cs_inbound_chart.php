<?
include("../def_inc.php");
$mod	= M_INBOUND;	
$menu	= S_INBOUND_CHART;
include("../header.php");
include("cs_inbound_def.php");


$table = "cs_inbound_call";

$dataPoints_type = array();
$dataPoints_product = array();
$dataPoints_date = array();

$date_to = isset($_POST["date_to"]) ? $_POST["date_to"] : date("Y-m-d");
$date_from = isset($_POST["date_from"]) ? $_POST["date_from"] : date("Y-m-d", strtotime($date_to." -1 month"));

$date_to2 = date("Y-m-d", strtotime($date_to." +1 day"));

//total count
$query = "select count(*) as total_cnt from $table where reg_datetime between date('$date_from') and date('$date_to2') " ;
$result	= mysqli_query($db->db_conn, $query);
$row = mysqli_fetch_array($result);
$total_cnt = $row['total_cnt'];
mysqli_free_result($result);
//echo $total_cnt."<br>";


//문의 유형별
$query = "select inquiry_type, count(*) as cnt from $table where reg_datetime between date('$date_from') and date('$date_to2') group by inquiry_type" ;
$result		= mysqli_query($db->db_conn, $query);
//echo $query."<br>";
while($row = mysqli_fetch_array($result)) 
{
    $percent = round( $row['cnt'] / $total_cnt * 100 );
    array_push($dataPoints_type, array("y" => $row['cnt'], "percent" => $percent, "label" => $arr_inbound_call_type[ $row['inquiry_type'] ] ) );
}
mysqli_free_result($result);


//모델별
$query = "select product_name, count(*) as cnt from $table where reg_datetime between date('$date_from') and date('$date_to2') group by product_name" ;
$result		= mysqli_query($db->db_conn, $query);
//echo $query."<br>";
while($row = mysqli_fetch_array($result)) 
{
    $percent = round( $row['cnt'] / $total_cnt * 100 );
    array_push($dataPoints_product, array("y" => $row['cnt'], "percent" => $percent, "label" => $row['product_name']) );
}
mysqli_free_result($result);


//기간별-직전 1개월 동안의 일별 통화량 검색, 통화가 없는 날도 0으로 포함
$query = "select date(reg_datetime) as date, count(*) as cnt from $table where reg_datetime between date('$date_from') and date('$date_to2') GROUP BY date ORDER BY reg_datetime asc" ;
$result = mysqli_query($db->db_conn, $query);
//echo $query."<br>";
$arr_temp = array();
while($row = mysqli_fetch_array($result)) 
{
    $arr_temp = array_merge($arr_temp, array($row['date'] => $row['cnt']) );
}
mysqli_free_result($result);

//
$date1=date_create($date_from);
$date2=date_create($date_to2);
$diff=date_diff($date1,$date2);
$days = $diff->format("%a");
$date_cur = $date_from;
//echo $days."<br>";
for($i=0;$i<$days;$i++)
{
    $key = $date_cur;
    if (isset($arr_temp[$key]))
    {
        array_push($dataPoints_date, array("y" => $arr_temp[$key], "label" => $date_cur) );
//        echo $date_cur."----".$arr_temp[$key]."<br>";
    }
    else
    {
        array_push($dataPoints_date, array("y" => 0, "label" => $date_cur) );
 //       echo $date_cur."----"."0"."<br>";
    }

    $date_cur = date("Y-m-d", strtotime($date_cur." +1 day"));
}


?>


<h4 class="page-header">고객센터 CALL 통계 분석 <span style="color:red">테스트중</span> </h4>

<form method="post" name="search_form" class="form-inline" action="<?=$_SERVER['PHP_SELF'];?>" >
<table class="table table-bordered">
<colgroup>
<col width="15%">
<col width="*">
</colgroup>
<tbody>
    <tr>
    <th>기간 선택</th>
        <td>
            <div class="input-group datetime" style="width:170px;">
                <input type="text" name="date_from" class="form-control input-sm text-center" placeholder="YYYY-MM-DD" value="<?=$date_from?>"/>
                <span class="input-group-addon">
                    <span class="glyphicon glyphicon-calendar"></span>
                </span>
            </div>
            ~
            <div class="input-group datetime" style="width:170px;">
                <input type="text" name="date_to" class="form-control input-sm text-center" placeholder="YYYY-MM-DD" value="<?=$date_to?>"/>
                <span class="input-group-addon">
                    <span class="glyphicon glyphicon-calendar"></span>
                </span>
            </div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        </td>
    </tr>
    <tr>
    <td colspan="2" class="text-center">
        <button type="submit" class="btn btn-primary btn-sm">검색</button>
        <a href="<?=$_SERVER['PHP_SELF'] ?>" class="btn btn-default btn-sm">초기화</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;


    </td>
</tr>
</tbody>
</table>
</form><br><br>

1. 문의 유형별 분석 (Total: <?echo $total_cnt."건"?>)
<div id="chartContainer1" style="text-align:center; height:370px; width:70%; border:0px solid #dddddd;"></div><br><br><br><br><br>

2. 제품별 분석 (Total: <?echo $total_cnt."건"?>)
<div id="chartContainer2" style="text-align:center; height:370px; width:70%; border:0px solid #dddddd;"></div><br><br><br><br><br>

3. 일별 통화량 분석 (Total: <?echo $total_cnt."건"?>)
<div id="chartContainer3" style="text-align:center; height:370px; width:70%; border:0px solid #dddddd;"></div><br><br><br>



<script>
window.onload = function () {

    var chart1 = new CanvasJS.Chart("chartContainer1", {
        animationEnabled: true,
        title: {
            text: "문의 유형별 비교"
        },
        legend:{
            cursor: "pointer",
    		itemclick: explodePie,
            maxWidth: 120,
            itemWidth: 350,
            horizontalAlign: "right", // left, center ,right 
            verticalAlign: "center",  // top, center, bottom
        },
        data: [{
            type: "pie",
            startAngle: 240,
            //showInLegend: true,
            toolTipContent: "{label}: <strong>{percent}% ({y})</strong>",
            indexLabel: "{label} - {percent}% ({y})",
            legendText: "{label}",
            dataPoints: <?php echo json_encode($dataPoints_type, JSON_NUMERIC_CHECK); ?>
        }]

    });
    chart1.render();



    var chart2 = new CanvasJS.Chart("chartContainer2", {
        animationEnabled: true,
        title: {
            text: "제품별 비교"
        },
        legend:{
            cursor: "pointer",
            itemclick: explodePie,
            maxWidth: 120,
            itemWidth: 350,
            horizontalAlign: "right", // left, center ,right 
            verticalAlign: "center",  // top, center, bottom
         },
        data: [{
            type: "pie",
            startAngle: 240,
            //showInLegend: true,
            toolTipContent: "{label}: <strong>{percent}% ({y})</strong>",
            indexLabel: "{label} - {percent}% ({y})",
            legendText: "{label}",
            dataPoints: <?php echo json_encode($dataPoints_product, JSON_NUMERIC_CHECK); ?>
        }]
    });
    chart2.render();



//기간별데이터 차트, 일별/문의별 막대그래프 
    var chart3 = new CanvasJS.Chart("chartContainer3", {
        animationEnabled: true,
        title:{
            text: "일별 통화량 비교"
        },
        axisX:{
            interval: 2,
            intervalType: "day"
        },
        axisY: {
            title: "통화량",
        },
        toolTip: {
        },
        legend: {
        },

        data: [{
            type:"column",
            name: "일별 통화량",
            markerSize: 0,
            showInLegend: true, 
            dataPoints:<?php echo json_encode($dataPoints_date, JSON_NUMERIC_CHECK); ?>
         }]

    });
    chart3.render();



}

function explodePie (e) {
	if(typeof (e.dataSeries.dataPoints[e.dataPointIndex].exploded) === "undefined" || !e.dataSeries.dataPoints[e.dataPointIndex].exploded) {
		e.dataSeries.dataPoints[e.dataPointIndex].exploded = true;
	} else {
		e.dataSeries.dataPoints[e.dataPointIndex].exploded = false;
	}
	e.chart.render();
}

</script>




<script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>


<? include('../footer.php');?>