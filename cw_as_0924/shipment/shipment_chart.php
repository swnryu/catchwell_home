<?
error_reporting(E_ALL);
include("../def_inc.php");

$mod	= M_SHIPMENT;
$menu	= S_SHIPMENT_CHART; 

include("../header.php");

$chart_type = isset($_POST['chart_type']) ? $_POST['chart_type'] : 0;
//echo $chart_type;

$table = "shipping_date_new";
?>


<h4 class="page-header">출고 데이터 차트</h4>
<br>
<form method="post" name="chart_form" class="form-inline" action="<?=$_SERVER['PHP_SELF'];?>" >
    <label class="radio-inline"><input type="radio" name="chart_type" value="0" <? if($chart_type==0) {echo 'checked';} ?> >모델별 출고량 비교</label>
    <label class="radio-inline"><input type="radio" name="chart_type" value="1" <? if($chart_type==1) {echo 'checked';} ?> >기간별 출고량 비교</label>
    <!--label class="radio-inline"><input type="radio" name="chart_type" value="2" <? if($chart_type==2) {echo 'checked';} ?> >기간별/모델별 출고량 비교</label-->
</form><br><br>

<script>
$("input[name='chart_type']:radio").change(function () {
    var btn = this.value;

    var form=document.chart_form;
    form.submit();

});
</script>

<?

if ($chart_type==0) {
        
    $arr_year1 = array();
    $arr_year2 = array();
    $arr_year3 = array();
    //$query="select model, YEAR(date) as year, COUNT(*) as cnt FROM $table GROUP BY model, YEAR(date) ORDER BY model"; 
    $query="select year(date) as year, model, count(*) as cnt from $table GROUP BY year(date), model having cnt>=100 order by date, model";
    //echo $query.'<br>'.'<br>';

    $result	= mysqli_query($db->db_conn, $query);

    $arr_data = array();
    while($row = mysqli_fetch_array($result)) {
        array_push($arr_data, array($row['model'], $row['cnt'], $row['year']) );
    }

    for($i=0;$i<count($arr_data);$i++) {
/*
        if ($arr_data[$i][2]==2018) {
            array_push($arr_year1 , array("label" => $arr_data[$i][0], "y" => $arr_data[$i][1]) );
            array_push($arr_year2 , array("label" => $arr_data[$i][0], "y" => '0') );
            array_push($arr_year3 , array("label" => $arr_data[$i][0], "y" => '0') );

        } else if ($arr_data[$i][2]==2019) {
            $exist = 0;
            for($j=0;$j<count($arr_year1);$j++) {
                if ($arr_year1[$j]['label'] == $arr_data[$i][0]) {
                    $exist = $j;
    //                echo $arr_year1[$j]['label'] . "+++++++ Exist"."<br>";
                }
            }

            if ($exist == 0) {
                array_push($arr_year1 , array("label" => $arr_data[$i][0], "y" => '0') );
                array_push($arr_year2 , array("label" => $arr_data[$i][0], "y" => $arr_data[$i][1]) );
                array_push($arr_year3 , array("label" => $arr_data[$i][0], "y" => '0') );
            } else {
                $arr_year2[$exist]['y'] = $arr_data[$i][1];
            }
        } else*/ if ($arr_data[$i][2]==2021) {
/*          //////////////////////////////////////////////////////////
            $exist = 0;
            for($j=0;$j<count($arr_year1);$j++) {
                if ($arr_year1[$j]['label'] == $arr_data[$i][0]) {
                    $exist = $j;
    //                echo $arr_year1[$j]['label'] . "+++++++ Exist"."<br>";
                }
            }
            if ($exist == 0) {
                array_push($arr_year1 , array("label" => $arr_data[$i][0], "y" => '0') );
                array_push($arr_year2 , array("label" => $arr_data[$i][0], "y" => '0') );
                array_push($arr_year3 , array("label" => $arr_data[$i][0], "y" => $arr_data[$i][1]) );
            } else {
                $arr_year3[$exist]['y'] = $arr_data[$i][1];
            }
*/
            //////////////////////////////////////////////////////////
            $exist = 0;
/*            for($j=0;$j<count($arr_year2);$j++) {
                if ($arr_year2[$j]['label'] == $arr_data[$i][0]) {
                    $exist = $j;
    //                echo $arr_year1[$j]['label'] . "+++++++ Exist"."<br>";
                }
            }*/
            if ($exist == 0) {
                array_push($arr_year1 , array("label" => $arr_data[$i][0], "y" => '0') );
                array_push($arr_year2 , array("label" => $arr_data[$i][0], "y" => '0') );
                array_push($arr_year3 , array("label" => $arr_data[$i][0], "y" => $arr_data[$i][1]) );
            } else {
                $arr_year3[$exist]['y'] = $arr_data[$i][1];
            }

        } 
    }

    //echo var_dump($arr_year1)."<br><br>";
    //echo var_dump($arr_year2);

}
else if ($chart_type==1) {
    $arr_year1 = array();
    $arr_year2 = array();
    $arr_year3 = array();
    
    $query = "select year(date) as year, month(date) as month, count(*) as cnt from $table group by year(date), month(date)";
    //echo $query.'<br>'.'<br>';

    $result	= mysqli_query($db->db_conn, $query);

    while($row = mysqli_fetch_array($result)) {

        if ($row['year'] == 2018) {
            array_push($arr_year1, array("x" => $row['month'], "y" => $row['cnt']) );

//           echo $row['year']."==>". $row['month']."==>". $row['cnt']."<br>";
        } else if ($row['year'] == 2019) {
            array_push($arr_year2, array("x" => $row['month'], "y" => $row['cnt']) );

//           echo $row['year']."==>". $row['month']."==>". $row['cnt']."<br>";
        } else if ($row['year'] == 2021) {
            array_push($arr_year3, array("x" => $row['month'], "y" => $row['cnt']) );

//           echo $row['year']."==>". $row['month']."==>". $row['cnt']."<br>";
        }
        
    }
}
else if ($chart_type==2) {

    $arr_year1 = array();
    $arr_year2 = array();
    
    $query = "select year(date) as year, month(date) as month, count(*) as cnt from $table group by year(date), month(date)";
    //echo $query.'<br>'.'<br>';

    $result	= mysqli_query($db->db_conn, $query);

    while($row = mysqli_fetch_array($result)) {

        if ($row['year'] == 2018) {
            array_push($arr_year1, array("x" => $row['month'], "y" => $row['cnt']) );

//           echo $row['year']."==>". $row['month']."==>". $row['cnt']."<br>";
        } else if ($row['year'] == 2019) {
            array_push($arr_year2, array("x" => $row['month'], "y" => $row['cnt']) );

//           echo $row['year']."==>". $row['month']."==>". $row['cnt']."<br>";
        }
    }
}
?>

    <div id="chartContainer"  style="height: 400px; width: 100%; <?if($chart_type==0){echo "display:block";}else{echo "display:none";}?>" ></div>
    <div id="chartContainer1" style="height: 400px; width: 100%; <?if($chart_type==1){echo "display:block";}else{echo "display:none";}?>"></div>
    <div id="chartContainer2" style="height: 400px; width: 100%; <?if($chart_type==2){echo "display:block";}else{echo "display:none";}?>"></div>


<script>
window.onload = function () {
    var minY = 0;
    var maxY = 10000;

//    alert( new Date(2014, 10, 01) ); //Sat Nov 01 2014 00:00:00 GMT+0900 (대한민국 표준시)

var chart = new CanvasJS.Chart("chartContainer", {

	animationEnabled: true,
	title:{
		text: "모델별 출고량 비교"
	},	
    axisX:{
         interval: 1,
     },
	axisY: {
         title: "출고량",
         minimum : minY,
         maximum : maxY
	},
	toolTip: {
		shared: true
	},
	legend: {
		cursor:"pointer",
		itemclick: toggleDataSeries
	},
	data: [/*{
        type: "column",
         name: "2018년도",
         legendText: "2018년도 출고량",
         showInLegend: true, 
         dataPoints:<?php echo json_encode($arr_year1, JSON_NUMERIC_CHECK); ?>
	},
	{
        type: "column",	
         name: "2019년도",
         legendText: "2019년도 출고량",
         showInLegend: true,
         dataPoints:<?php echo json_encode($arr_year2, JSON_NUMERIC_CHECK); ?>
	},*/
	{
        type: "column",	
         name: "2021년도",
         legendText: "2021년도 출고량",
         showInLegend: true,
         dataPoints:<?php echo json_encode($arr_year3, JSON_NUMERIC_CHECK); ?>
	}]
});
chart.render();


var chart1 = new CanvasJS.Chart("chartContainer1", {

animationEnabled: true,
title:{
    text: "기간별 출고량 비교"
},	
axisX:{
     interval: 1,
     suffix: " 월",
},
axisY: {
     title: "출고량",
},
toolTip: {
    shared: true
},
legend: {
    cursor:"pointer",
    itemclick: toggleDataSeries
},
data: [{
     type:"line",
     name: "2018년도",
     legendText: "2018년도 출고량",
     markerSize: 0,
     showInLegend: true, 
     dataPoints:<?php echo json_encode($arr_year1, JSON_NUMERIC_CHECK); ?>
},
{
     type: "line",	
     name: "2019년도",
     legendText: "2019년도 출고량",
     markerSize: 0,
     showInLegend: true,
     dataPoints:<?php echo json_encode($arr_year2, JSON_NUMERIC_CHECK); ?>
},
{
     type: "line",	
     name: "2021년도",
     legendText: "2021년도 출고량",
     markerSize: 0,
     showInLegend: true,
     dataPoints:<?php echo json_encode($arr_year3, JSON_NUMERIC_CHECK); ?>
}]
});
chart1.render();


var chart2 = new CanvasJS.Chart("chartContainer2", {

animationEnabled: true,
title:{
    text: "월간 출고량 변화"
},	
axisY: {
     title: "출고량"
},
axisX: {
     xValueType: "dateTime"
},
toolTip: {
    shared: true
},
legend: {
    cursor:"pointer",
    itemclick: toggleDataSeries
},
data: [{
     type:"line",
     name: "2018년도",
     legendText: "2018년도 출고량",
     markerSize: 0,
     showInLegend: true, 
     dataPoints:<?php echo json_encode($arr_year1, JSON_NUMERIC_CHECK); ?>
},
{
     type: "line",	
     name: "2019년도",
     legendText: "2019년도 출고량",
     markerSize: 0,
     showInLegend: true,
     dataPoints:<?php echo json_encode($arr_year2, JSON_NUMERIC_CHECK); ?>
},
{
     type: "column",	
     //name: "2019년도",
     //legendText: "2019년도 출고량",
     markerSize: 0,
     showInLegend: true,
     dataPoints:<?php echo json_encode($arr_year3, JSON_NUMERIC_CHECK); ?>
}]
});
chart2.render();


function toggleDataSeries(e) {
	if (typeof(e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
		e.dataSeries.visible = false;
	}
	else {
		e.dataSeries.visible = true;
	}
	chart.render();
}

}
</script>





<? include('../footer.php');?>