<?
include_once ("./def_inc.php");
$mod	= M_MAIN;
$menu	= S_MAIN;
include ("./header.php");


$today      = date("Y-m-d");
$tomorrow   = date("Y-m-d", strtotime($today." +1 day"));

$yoil = array("일","월","화","수","목","금","토");
$day = ($yoil[date('w', strtotime($today))]);
$today2     = date("Y년 m월 d일 ").$day."요일";

?>

<h4 class="page-header"><?=$today2?></h4>
<? if (($PERMISSION & PERMISSION_GROUP_CS) == PERMISSION_GROUP_CS) { 
    $table		= "as_parcel_service";

    //$query		= "select count(idx) from $table where process_state=".ST_REGISTERING; //reg_date='$today' AND 
	$query = "select count(idx) from $table where process_state = " . ST_DC . " and reg_date = date('$today')";
    $result=mysqli_query($db->db_conn, $query);
    $state0 = mysqli_fetch_array($result);

    $query		= "select count(idx) from $table where process_state=".ST_REG_DONE; //reg_date='$today' AND 
    $result=mysqli_query($db->db_conn, $query);
    $state1 = mysqli_fetch_array($result); ?>
    
    <div style="border:solid 1px #ddd; padding:10px; border-radius:10px; ">
    <h5 style="font-weight:bold; margin-top:0px; ">AS 신청서</h5>
    <h5>신규 접수 신청(택배비 입금완료)이 <a href="online_as/online_as.php?state=<?echo ST_DC;?>"style="font-weight:bold; font-size: 24px;color:#008299;"><?echo $state0[0]?> 건</a> 있습니다.<br><br>
    접수 완료 <a href="online_as/online_as.php?state=<?echo ST_REG_DONE;?>"style="font-weight:bold; font-size: 24px;color:#008299;"><?echo $state1[0]?> 건</a> 있습니다.</h5>
    <div style="margin-top: 20px;">
        <div style="display: flex;">
            <a href="online_as/online_as_edit_m.php" class="btn btn-success btn-lg" style="padding:15px; margin-right: 10px;">수리 업무 진행</a>
            <a href="online_as/online_as_edit_m1.php" class="btn btn-success btn-lg" style="padding:15px;">바코드 접수 조회</a>
        </div>
    </div>
    </div><br>
<? } ?>

<!--20210322-->
<? if (($PERMISSION & PERMISSION_GROUP_CS) == PERMISSION_GROUP_CS) { 
    $table		= "cancellation_order";

    $query		= "select count(idx) from $table where date='$today' and status=0 ";
    $result=mysqli_query($db->db_conn, $query);
    $cancellation = mysqli_fetch_array($result); ?>
    
    <div style="border:solid 1px #ddd; padding:10px; border-radius:10px; ">
        <h5 style="font-weight:bold; margin-top:0px; ">반품/교환 신청서</h5>
        <h5>오늘 접수 신청이 <a href="cancellation/cancellation_list.php"style="font-weight:bold; font-size: 24px;color:#008299;"><?echo $cancellation[0]?> 건</a> 있습니다.<br><br>
    </div><br>
<? } ?>

<? if (($PERMISSION & PERMISSION_GROUP_SALES) == PERMISSION_GROUP_SALES) { 
    $table		= "cs_online_event";
    
    $query		= "select count(idx) from $table where udate between date('$today') and date('$tomorrow')"; 
    $result=mysqli_query($db->db_conn, $query);
    $evt = mysqli_fetch_array($result); ?>

    <div style="border:solid 1px #ddd; padding:10px; border-radius:10px; ">
        <h5 style="font-weight:bold; margin-top:0px; ">포토상품평 이벤트 응모</h5>
        <h5>신규 접수 신청이 <a href="online_event/online_event.php"style="font-weight:bold; font-size: 24px;color:#008299;"><?echo $evt[0]?> 건</a> 있습니다.<br><br>
    </div><br>
<? } ?>

<!--20210222-->
<? if (($PERMISSION & PERMISSION_GROUP_SHIPMENT) == PERMISSION_GROUP_SHIPMENT) { 
    $table		= "shipping_date_new";
    
    $query		= "select count(idx) from $table where date between date('$today') and date('$tomorrow')"; 
    $result=mysqli_query($db->db_conn, $query);
    $evt = mysqli_fetch_array($result); ?>

    <div style="border:solid 1px #ddd; padding:10px; border-radius:10px; ">
        <h5 style="font-weight:bold; margin-top:0px; ">출고 처리</h5>
        <h5>오늘 출고 처리가 <a href="shipment/shipment_new.php"style="font-weight:bold; font-size: 24px;color:#008299;"><?echo $evt[0]?> 건</a> 있습니다.<br><br>
    </div><br>
<? } ?>

<!--20240405-->
<? if (($PERMISSION & PERMISSION_GROUP_CS) == PERMISSION_GROUP_CS) {  ?>

	<div style="border:solid 1px #ddd; padding:10px; border-radius:10px; ">
        <h5 style="font-weight:bold; margin-top:0px; ">SMS수신</h5>
        <a href="/cw_as/sms/sms_view.php" class="btn btn-success btn-lg " style="padding:15px;">업무용 SMS수신함</a>
    </div><br>
<? } ?>

<?
include ("./footer.php");
?>