<?
include("../def_inc.php");
$mod	= M_AS;	
$menu	= $_GET['from'];
include("../header.php");

$idx = $_GET['idx'];
$row = $db->object("as_parcel_service","where idx='$idx'");

$row3 = $db->object("TB_INICIS_RETURN","where P_OID='$row->reg_num'");
$row4 = $db->object("TB_INICIS_NOTI","where P_OID='$row->reg_num'");
//20210213-2회이상 중복접수검색 
//20210220-01000000000 제외
$query2="select count(*) as cnt from as_parcel_service where process_state=4 and customer_phone='$row->customer_phone' and customer_phone!='01000000000'";
$rs2 = mysqli_query($db->db_conn, $query2);
$row2 = mysqli_fetch_object($rs2);
//echo $row2->cnt;

?>

	<h4 class="page-header">상세보기</h4>
<h5>고객 정보</h5>
	<table class="table table-bordered">
	<colgroup>
	<col width="15%">
	<col width="*">
	</colgroup>
	<tbody>
	<tr>
		<th>접수일</th>
		<td><?echo $row->reg_date; ?> <?echo "(최종업데이트 : ".$row->update_time.")"?></td>
	</tr>
	<tr>
		<th>접수번호</th>
		<td><?echo $row->reg_num ?></td>
	</tr>
	<tr>
		<th>처리상태</th>
		<td><?echo $proc_state[$row->process_state] ?></td>
	</tr>
	<tr>
		<th>이 름</th> <span class="badge"><?if($cnt2[0]>0){echo $cnt2[0]+1;}?></span>
		<td style="color:blue;"><?echo $row->customer_name ?><!--span class="badge"><?if($row2->cnt>0){echo $row2->cnt+1;}?></span--></td><!--20210213-->
		<!--td style="color:blue;"><span <?if($row2->cnt==1){echo 'style="background-color:yellow"';} else if($row2->cnt>1){echo 'style="background-color:#FF00FF"';}?> ><?echo $row->customer_name ?></span></td--><!--20210213-->
	</tr>
	<tr>
		<th>휴대폰번호</th>
		<td style="color:blue;"><a href='tel:<?echo $row->customer_phone;?>'><?echo $row->customer_phone ?></a></td>
	</tr>
	</tbody>
	</table>

<h5>제품 정보</h5>
	<table class="table table-bordered">
	<colgroup>
	<col width="15%">
	<col width="*">
	</colgroup>
	<tbody>
	<tr>
		<th>제품 모델명</th>
		<td><?echo nl2br($row->product_name); ?></td>
	</tr>
	<tr>
		<th>제품 구매일</th>
		<td><?echo nl2br($row->product_date); ?></td>
	</tr>
<!--	<tr> 
		<th>첨부파일</th>
		<td><a href="files/<?php echo $row->attached_files; ?>" download><?php echo $row->attached_files;?></a></td>
	</tr>-->
	<!--tr>
		<th>불량 유형</th>
		<td><?echo nl2br($row->broken_type); ?></td>
	</tr-->
	<tr>
		<th>불량 내용</th>
		<td><?echo nl2br($row->customer_desc); ?></td>
	</tr>
	<tr>
		<th>관리자 조치사항</th>
		<td>
			<?
//				echo nl2br($row->admin_memo); 
				$memo = nl2br($row->admin_memo);
				$memo = str_replace("(V)","",$memo);
				$memo = str_replace("(R)","",$memo);
				$memo = str_replace("(H)","",$memo);
				$memo = str_replace("(S)","",$memo);
				$memo = str_replace("(M)","",$memo);
				$memo = str_replace("[ETC]","",$memo);
				echo $memo;
			?>
		</td>
	</tr>
	<tr> <!--20210105-->
		<th>유상 수리비용</th>
		<td><?echo number_format($row->price); ?></td>
	</tr>
	<tr>
		<th>담당자명</th>
		<td><?echo nl2br($row->pic_name); ?></td> <!--20220103-->
	</tr>
	<tr>
		<th class="text-primary">(관리자 전용 메모)</th>
		<td><?echo nl2br($row->admin_desc); ?></td>
	</tr>
	</tbody>
	</table>	

<h5>제품 회수용 주소</h5>
	<table class="table table-bordered">
	<colgroup>
	<col width="15%">
	<col width="*">
	</colgroup>
	<tbody>
	<tr>
		<th>주소(회수)</th>
		<td><?echo $row->customer_addr." ".$row->customer_addr_detail ?></td>
	</tr>
	<tr>
		<th>우편번호(회수)</th>
		<td><?echo sprintf("%05d", $row->customer_zipcode) ?></td>
	</tr>
	<tr>
		<th>송장번호(회수)</th>
		<? $tracking_num = preg_replace("/[^0-9]*/s", "", $row->parcel_num); ?>
		<td><a href="<? if (strlen($tracking_num)==12) {echo constant('TRACKING_CJ').$tracking_num;} else {echo constant('TRACKING_EPOST').$tracking_num;} ?>" target="_blank"><?echo $row->parcel_num ?></a></td>
	</tr>
	<tr>
		<th>배송 메시지(회수)</th>
		<td><?echo $row->parcel_memo ?></td>
	</tr>
	</tbody>
	</table>
	
	
<h5>AS 후 제품 배송주소</h5>
	<table class="table table-bordered">
	<colgroup>
	<col width="15%">
	<col width="*">
	</colgroup>
	<tbody>
	<tr>
		<th>주소(출고)</th>
		<td><?echo $row->customer_addr_return." ".$row->customer_addr_detail_return ?></td>
	</tr>
	<tr>
		<th>우편번호(출고)</th>
		<td><?echo sprintf("%05d", $row->customer_zipcode_return) ?></td>
	</tr>
	<tr>
		<th>송장번호(출고)</th>
		<? $tracking_num_return = preg_replace("/[^0-9]*/s", "", $row->parcel_num_return); ?>
		<td><a href="<? echo constant('TRACKING_CJ').$tracking_num_return?>" target="_blank"><?echo $row->parcel_num_return ?></a></td>
	</tr>
	<tr>
		<th>배송 메시지(출고)</th>
		<td><?echo $row->parcel_memo_return ?></td>
	</tr>
	</tbody>
	</table>

<h5>배송비 정보</h5>
	<table class="table table-bordered">
	<colgroup>
	<col width="15%">
	<col width="*">
	</colgroup>
	<tbody>
	<tr>
		<th>입금자명</th>
		<td><?echo $row3->P_UNAME?></td>
	</tr>
	<tr>
		<th>거래금액</th>
		<td><?echo $row3->P_AMT?></td>
	</tr>
	<tr>
		<th>결재은행</th>
		<td><?echo $row3->P_FN_NM?></td>
	</tr>
	<tr>
		<th>가상계좌번호</th>
		<td><?echo $row3->P_VACT_NUM?></td>
	</tr>
	<tr>
		<th>입금여부</th>
		<td><? 
		if($row4->P_AUTH_DT){
			$timestamp = $row4->P_AUTH_DT;

			// 숫자를 날짜 형식으로 분리
			$year = substr($timestamp, 0, 4); // 연도 (2025)
			$month = substr($timestamp, 4, 2); // 월 (01)
			$day = substr($timestamp, 6, 2); // 일 (07)
			$hour = substr($timestamp, 8, 2); // 시 (16)
			$minute = substr($timestamp, 10, 2); // 분 (08)
			$second = substr($timestamp, 12, 2); // 초 (04)
			// DateTime 형식으로 변환
			$date = DateTime::createFromFormat('Y-m-d H:i:s', "$year-$month-$day $hour:$minute:$second");
			echo "<span style='color: red;'>입금완료</span>, 입금시간 : " . $date->format('Y-m-d H:i:s');
		}
		else{
			echo "<span style='color: blue;'>미입금</span>";
		}
		?>
		</td>
	</tr>
	</tbody>
	</table>


<!-- 2이상 접수 History 20210220 -->
<?
//2회이상 중복접수검색 
//20210220-01000000000 제외
$table = "as_parcel_service";
$query2="select * from $table where process_state>3 and process_state != 6 and customer_phone='$row->customer_phone' and customer_phone!='01000000000' ";
$rs2 = mysqli_query($db->db_conn, $query2);
$cnt = mysqli_num_rows($rs2);

if ($cnt>0) {
?>
<h5 id="as_history">AS History</h5>
<table class="table table-bordered table-hover">
	<colgroup>
	<col width="5%">
	<col width="8%">
	<col width="8%">
	<col width="8%">
	<col width="7%">
	<!--col width="10%"-->
	<col width="*">
	<col width="20%">
	<col width="6%">
	<col width="10%">
	<col width="7%">
	</colgroup>
	<thead>
	<tr>
		<th>N O</th>
		<th>접수번호</th>
		<th>이 름</th>
		<th>모델명</th>
		<th>구매일</th>
		<!--th>불량유형</th-->
		<th>불량내용</th>
		<th>조치사항</th>
		<th>수리비용</th>
		<th>송장번호(발송용)</th>
		<th>최종업데이트</th>
	</tr>
	</thead>
	<tbody>
	<?
		$listNo = 1;
		while($row2 = mysqli_fetch_array($rs2)){
			//$reg_date	= $tools->strDateCut($row2[reg_date], 3);
			$last_update = $tools->strDateCut($row2['update_time'], 3);
			$customer_desc = $tools->strCut_utf($tools->strHtml($row2['customer_desc']), 100);
	?>
	<tr>
		<td class="text-center"><? echo $listNo ?></td>
		<td class="text-center"><? echo $row2['reg_num'] ?></td>
		<td class="text-center"><? echo $row2['customer_name'] ?> </td>
		<td class="text-center"><? echo $row2['product_name'] ?> </td>
		<td class="text-center"><? echo $row2['product_date'] ?> </td>
		<!--td class="text-center"><? echo $row2['broken_type'] ?> </td-->
		<td><? echo $customer_desc ?> </td>
		<td class="text-left">
			<? 
				//echo $row[admin_memo];
				$memo = $row2['admin_memo'];
				$memo = str_replace("(V)","",$memo);
				$memo = str_replace("(R)","",$memo);
				$memo = str_replace("(H)","",$memo);
				$memo = str_replace("(S)","",$memo);
				$memo = str_replace("(M)","",$memo);
				$memo = str_replace("[ETC]","",$memo);
				echo $memo;
			?>
		</td>
		<td class="text-center"><? echo number_format($row2['price']); ?></td>
		<td class="text-center"><? echo $row2['parcel_num_return'] ?> </td>
		<td class="text-center"><? echo $last_update; ?> </td>
	</tr>
	<? 
		$listNo++;
		}
	?>
	</tbody>
</table>
<?
}
?>
<!-- 2이상 접수 History-->

	<table class="table">
		<tr>
			<td class="text-center">
			<?if ($menu==S_AS_REPORT) {?>
			<a href="./online_as_edit.php?idx=<?echo $row->idx ?>&from=<? echo $menu ?>" class="btn btn-default" style="margin-left:10px;" >수정</a>
			<?}?>
			<!-- 20230707 수정 나오게 추가-->
			<?if ($menu==S_AS_SHIPMENT) {?>
			<a href="./online_as_edit.php?idx=<?echo $row->idx ?>&from=<? echo $menu ?>" class="btn btn-default" style="margin-left:10px;" >수정</a>
			<?}?>

			<a href="#" class="btn btn-primary" style="margin-left:10px;" onClick="history.back();return false;">목록</a> 
			<!--<a href="./online_as.php?state=<?echo $row->process_state ?>" class="btn btn-primary" >목록</a> -->
			</td>
		</tr>
	</table>


<? include('../footer.php');?>