<?
error_reporting(E_ALL);
include("../def_inc.php");

$mod	= M_SHIPMENT;
$menu	= S_SHIPMENT_FILES; 

include("../header.php");



$table			= "shipping_date_new";
$listScale		= 10;
$pageScale		= 10;


$startPage = isset($_GET['startPage'])?$_GET['startPage']:0;
if( !$startPage ) { $startPage = 0; }


$date_from = isset($_GET["date_from"]) ? $_GET["date_from"] : "";
$date_to = isset($_GET["date_to"]) ? $_GET["date_to"] : "";


if ($date_to == "") {
    $date_to = isset($_POST["date_to"]) ? $_POST["date_to"] : date("Y-m-d");
}
if ($date_from == "") {
    $date_from = isset($_POST["date_from"]) ? $_POST["date_from"] : date("Y-m-d", strtotime($date_to." -30 day"));
}
$date_to2 = date("Y-m-d", strtotime($date_to." +0 day"));

$totalPage = floor($startPage / ($listScale * $pageScale));
$query		= "select DISTINCT filename, COUNT(*) as cnt, date from $table where filename is not null and status=1 and date between date('$date_from') and date('$date_to2') GROUP BY date, filename ";

$result		= mysqli_query($db->db_conn, $query);
$totalList	= mysqli_num_rows($result);

$query.=" order by idx, filename desc ";
$query.="LIMIT $startPage, $listScale";
$result		= mysqli_query($db->db_conn, $query);

if( $startPage ) { $listNo = $totalList - $startPage; } else { $listNo = $totalList; }

$param_url = "date_from=".$date_from."&date_to=".$date_to;

?>


<h4 class="page-header">배송리스트 파일 조회 (<? echo date('Y-m-d');?>)</h4>

<!--form method="post" name="search_form" class="form-inline" action="<?=$_SERVER['PHP_SELF'];?>" >
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
            </div>
            
        </td>
    </tr>
    <tr>
</tr>
<tr>
    <td colspan="2" class="text-center">
        <button type="submit" class="btn btn-primary btn-sm">조회</button>&nbsp;
        <a href="<?=$_SERVER['PHP_SELF'] ?>" class="btn btn-default btn-sm">초기화</a>
    </td>
</tr>
</tbody>
</table>
</form>


<div class="table-responsive">
<table class="table table-bordered table-hover">
<colgroup>
<col width="5%">
<col width="10%">
<col width="*">
<col width="10%">
<col width="10%">
</colgroup>

<thead>
<tr>
    <th>N O</th>
    <th>출고일</th>
    <th>파일명</th>
    <th>출고수량</th>
    <th>다운로드</th>
</tr>
</thead>
<tbody>

<?
	while($row = mysqli_fetch_array($result)) {
?>
        <tr>
        <td class="text-center"><? echo $listNo ?></td>
        <td class="text-center"><? echo $row['date'] ?></td>
        <td class="text-center"><? echo $row['filename']  ?></td>
        <td class="text-center"><? echo $row['cnt']  ?></td>
		<td class="text-center"><a href="download_file.php?download_filename=<?echo $row['filename'];?>" class="btn btn-primary btn-sm">다운로드</a></td>
        </tr>

<?
        $listNo--;
   }
?>

</tbody>
</table>
</div>


<div class="text-center">
    <ul class="pagination">
    <?
        if( $totalList > $listScale ) {
            if( $startPage+1 > $listScale*$pageScale ) {
                $prePage = $startPage - $listScale * $pageScale;
                echo "<li><a href='$_SERVER[PHP_SELF]?$param_url&startPage=$prePage'><span aria-hidden='true'>&laquo;</span></a></li>";
            }
            for( $j=0; $j<$pageScale; $j++ ) {
                $nextPage = ($totalPage * $pageScale + $j) * $listScale;
                $pageNum = $totalPage * $pageScale + $j+1;
                if( $nextPage < $totalList ) {
                    if( $nextPage!= $startPage ) {
                        echo "<li><a href='$_SERVER[PHP_SELF]?$param_url&startPage=$nextPage'>$pageNum</a></li>";
                    } else {
                        echo "<li class='active'><a href='javascript:;'>$pageNum</a></li>";
                    }
                }
            }
            if( $totalList > (($totalPage+1) * $listScale * $pageScale)) {
                $nNextPage = ($totalPage+1) * $listScale * $pageScale;
                
                echo "<li><a href='$_SERVER[PHP_SELF]?$param_url&startPage=$nNextPage'><span aria-hidden='true'>&raquo;</span></a></li>";
                
            }
        }
        if( $totalList <= $listScale) {
            echo "<li class='active'><a href='javascript:;' >1</a></li>";
        }
    ?>
    </ul>
</div-->



<link href="shipment_calendar.css" rel="stylesheet" >

<?
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');
$month = isset($_GET['month']) ? $_GET['month'] : date('m');
$day = date('d', strtotime('$year-$month-01'));

$date = "$year-$month-$day";
$start_week = date('w', strtotime($date));

$prev_year = date('Y', strtotime($date." -1 month"));
$prev_month = date('m', strtotime($date." -1 month"));

$next_year = date('Y', strtotime($date." +1 month"));
$next_month = date('m', strtotime($date." +1 month"));

?>
    <form method="post" name="search_form" class="form-inline" action="<?=$_SERVER['PHP_SELF'];?>" >
        <input type="hidden" name="year" class="form-control input-sm" value="<?=$year?>"/>
        <input type="hidden" name="month" class="form-control input-sm" value="<?=$month?>"/>
    </form>

    <div class="form-group" style="border:0px solid red; display:flex; justify-content:center; align-items:center;" >
        <a href="<?=$_SERVER['PHP_SELF']."?year=".$prev_year."&month=".$prev_month ?>" class="btn btn-default btn-sm"> << </a>      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <a href="<?=$_SERVER['PHP_SELF']?>" class="btn btn-default btn-lg" > <?echo $year."년 ". $month . "월"; ?> </a>     &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <a href="<?=$_SERVER['PHP_SELF']."?year=".$next_year."&month=".$next_month ?>" class="btn btn-default btn-sm"> >> </a>
    </div>

	<div class="calendar" data-toggle="calendar">
        <div class="row" style="height:30px;">
            <div class="col-xs-12 calendar-dayofweek" style="color:#ccc;">일</div>
            <div class="col-xs-12 calendar-dayofweek">월</div>
            <div class="col-xs-12 calendar-dayofweek">화</div>
            <div class="col-xs-12 calendar-dayofweek">수</div>
            <div class="col-xs-12 calendar-dayofweek">목</div>
            <div class="col-xs-12 calendar-dayofweek">금</div>
            <div class="col-xs-12 calendar-dayofweek" style="color:#ccc;">토</div>
        </div>
<?
        $date_cur = $date;
        for($r=0; $r<6; $r++) {
            
?>
		<div class="row">
<?
            for($dayofweek=0; $dayofweek<7; $dayofweek++) {
                
?>
            <div class="col-xs-12 calendar-day" style="<?if($dayofweek==0 || $dayofweek==6) {echo "color:#ccc;";} ?>" > 
				
                
                <?
                if ($r==0 && $dayofweek<$start_week) {
                }
                else {
                    $m1 = date('m', strtotime($date_cur));
                    $m2 = date('m', strtotime($date_cur." +1 day"));
                    //echo $m1."-----".$m2."-----".$month."<br>";
                    if ($month==$m1) {
                        
                        ?> <time datetime="<?echo $date_cur;?>"><? echo date('d', strtotime($date_cur)); ?></time> <?

                        //$date_cur 으로 파일 검색 파일명 : 3월 31일 배송리스트1.xlsx
                        
                        //select DISTINCT filename, date FROM `shipping_date_new` WHERE date='2021-03-29' GROUP by filename
                        $query		= "select DISTINCT filename, date FROM $table WHERE date='$date_cur' GROUP by filename order by date ";
                        $result		= mysqli_query($db->db_conn, $query);

                        while($row = mysqli_fetch_array($result)) {
                            //echo $row['filename'] . '<br>';

                        ?>
                        <div class="events">
                            <div class="event">
                            <h4><a href="download_file.php?download_filename=<? echo $row['filename']; ?>"><? echo $row['filename']; ?></a></h4>
                            </div>
                        </div>
                        <?

                        }
                        
                        
                        //echo date('d', strtotime($date_cur)) . '<br>';
                    } 

                   // echo "today: ".$date_cur.'<br>';
                   $date_cur = date('Y-m-d', strtotime($date_cur." +1 day"));
                   // echo "next day: ".$date_cur.'<br>';
                }
                
                ?>
                
                
			</div>
<?
            }
?>
        </div>
        
<?
        }        
?>        

	</div>


<? include('../footer.php');?>