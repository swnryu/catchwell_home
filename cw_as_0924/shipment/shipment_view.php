<?
error_reporting(E_ALL);
include("../def_inc.php");
include("event_def.php");

$mod	= M_SHIPMENT;
$menu	= isset($_GET['status']) ? S_SHIPMENT_NEW : S_SHIPMENT; 

include("../header.php");

$table = "shipping_date_new";
$idx = isset($_GET['idx']) ? $_GET['idx'] : 0;
$del = isset($_GET['del']) ? $_GET['del'] : 0;
$oid = isset($_GET['oid']) ? $_GET['oid'] : "";


//삭제
if ( (($PERMISSION & PERMISSION_ALL)==PERMISSION_ALL || $ADMIN_USERID == "lsd153") && $del!==0 && $idx!==0 )
{
	if ( $db->delete($table, "where idx='$idx' ") )
	{
		$db->insert("admin_log","userid='$_SESSION[ADMIN_USERID]', contents='del_shipment', ip='$_SERVER[REMOTE_ADDR]', udate=now(), comment='$idx $oid' ");

		$tools->alertJavaGo("삭제 되었습니다.", "shipment.php");
		exit;
	}
}


if ( isset($_GET['status']) && isset($_POST['tracking']) && $_POST['tracking']!="" )
{//wirte to database 

	$tracking = preg_replace("/[^0-9]*/s", "", $_POST['tracking']);

	if ($db->update($table, "status=1, tracking='$tracking' where idx='$idx' and status=0 and (tracking='' or tracking is null) "))
	{
		$url = "shipment_new.php";
		$tools->alertJavaGo("출고완료 되었습니다.",$url);
	}
}
else
{
	$row = $db->object($table, "where idx='$idx'");
}


?>

<h4 class="page-header">상세보기 (<?echo $row->idx?>)</h4>
<h5>출고 정보</h5>
	<table class="table table-bordered">
	<colgroup>
	<col width="15%">
	<col width="*">
	</colgroup>
	<tbody>
	<tr>
		<th>배송리스트</th>
		<td><a href="download_file.php?download_filename=<? echo $row->filename;?>"><?echo $row->filename; ?></a></td>
	</tr>
	<tr>
		<th>출고일</th>
		<td style="color:blue;"><?echo $row->date; ?></td>
	</tr>
	<tr>
		<th>모델명</th>
		<td style="color:blue;"><?echo $row->model; ?></td>
	</tr>
	<tr>
		<th>구매처</th>
		<td><?echo $row->mall; ?></td>
	</tr>
	<tr>
        <th>주문번호</th>
		<td><?echo $row->orderid; ?></td>
	</tr>
	<tr>
        <th>주문번호 사방넷</th>
		<td><?echo $row->orderid_sabangnet; ?></td>
	</tr>
	<tr>
        <th>시리얼번호</th>
		<td><?echo $row->serial; ?></td>
	</tr>
	<tr>
        <th>메모</th>
		<td><?echo $row->memo; ?></td>
	</tr>
	</tbody>
	</table>

<h5>배송 정보</h5>
	<table class="table table-bordered">
	<colgroup>
	<col width="15%">
	<col width="*">
	</colgroup>
	<tbody>
	<tr>
        <th>이름</th>
		<td><?echo $row->name; ?></td>
	</tr>
	<tr>
        <th>전화번호</th>
		<td><?echo $row->phone1; ?></td>
	</tr>
	<tr>
        <th>휴대폰</th>
		<td><?echo $row->phone2; ?></td>
	</tr>
	<tr>
        <th>주소</th>
		<td><?echo $row->address; ?></td>
	</tr>
	<tr>
        <th>배송메시지</th>
		<td><?echo $row->deliverymemo; ?></td>
	</tr>
	<tr>
        <th>송장번호</th>
		<? $tracking_num = preg_replace("/[^0-9]*/s", "", $row->tracking); ?>
		<td><a href="<? if (strlen($tracking_num)==12) {echo constant('TRACKING_CJ').$tracking_num;} else {echo constant('TRACKING_EPOST').$tracking_num;} ?>" target="_blank"><?echo $row->tracking; ?></a>
		<?
			if ($row->tracking=="" && $row->status == 0)
			{?>
				<form method="post" action="shipment_view.php?status=&idx=<?echo $row->idx?>" name="tx_editor_form" enctype="multipart/form-data" >
					<input type="text" name="tracking" value="<?echo $row->tracking;?>">
					<button type="submit" class="btn btn-default btn-xs " <?if(($PERMISSION & PERMISSION_SHIPMENT)!=PERMISSION_SHIPMENT) { echo 'disabled';}?> >출고완료 처리</button>
				</form>
			<?}
		?>		
		</td>
	</tr>
	</tbody>
	</table>





<table class="table">
		<tr>
			<td class="text-center">
			<form method="post" action="shipment_view.php" name="del_form" enctype="multipart/form-data" >
				<a href="javascript:;" class="btn btn-primary" onClick="takeback();" <?if(($PERMISSION & PERMISSION_ALL) == PERMISSION_ALL) { echo 'enable';}?> >반품/교환 처리</a>
				<? if ($idx) { ?>
					<a href="#" class="btn btn-default" onClick="history.back();return false;">목록</a>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<a href="javascript:;" class="btn btn-danger" onClick="sendit();" <?if(($PERMISSION & PERMISSION_ALL) == PERMISSION_ALL) { echo 'enable';}?> >삭제</a>
				<? } ?>
				</form>
			</td>
		</tr>
	</table>



<script type="text/javascript">
function sendit() {
	if (confirm("삭제된 데이터는 복구할 수 없습니다. 삭제할까요?")) 
	{
		if (confirm("정말 삭제할까요?")) 
		{
			var form=document.del_form;
			form.action = "shipment_view.php?del=1&oid=<?echo $row->orderid?>&idx=<?echo $row->idx?>";
			form.submit();
		}
	}
}
function takeback() {
	if (confirm("반품/교환 처리 하시겠습니까?")) 
	{
		var form=document.del_form;
		form.action = "../cancellation/cancellation_takeback.php?takeback_idx=<?echo $row->idx?>";
		document.del_form.submit();
	}
}
</script>


<? include('../footer.php');?>