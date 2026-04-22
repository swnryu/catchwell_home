<?
include("../def_inc.php");
include("event_def.php");
$mod	= M_EVENT;	
$menu	= S_EVENT;
include("../header.php");

$idx = $_GET['idx'];
$row = $db->object("cs_online_event","where idx='$idx'");

?>

	<h4 class="page-header">포토상품평 이벤트 신청서 (<?echo $row->idx?>)</h4>

	<table class="table table-bordered">
	<colgroup>
	<col width="15%">
	<col width="*">
	</colgroup>
	<tbody>
	<tr>
		<th>상 태</th>
		<td><? if($row->status==null){echo '처리중';} else if($row->status==STATUS_NULL){echo '처리중';} else if($row->status==STATUS_OK){echo '적합';} else if($row->status==STATUS_NOTOK){echo '부적합';} else if($row->status==STATUS_DONE){echo '발송완료';} ?></td>
	</tr>
	<tr>
		<th>송장번호</th>
		<td><a href="<? echo constant('TRACKING_CJ').$row->tracking_num?>" target="_blank"><?echo $row->tracking_num ?></a></td>
	</tr>
	<!-- 20230426 추가 되어야 할것 db추가-->
	<tr>
		<th>핀번호</th>
		<!--td><a href="<?// echo constant('TRACKING_CJ').$row->pin_num?>" target="_blank"><?//echo $row->pin_num ?></a></!td-->
		<td><?echo $row->pin_num ?></td>
	</tr>
	<!-- 20230426 관리자메모 대신 사은품 변경 -->
	<tr>
		<th>사은품</th>
		<td><?echo $row->gift ?></td>
	</tr>
	<!--tr>
		<th>관리자 메모</th>
		<td><?//echo $row->admin_memo ?></td>
	</!tr-->

	</tbody>
	</table>


	<table class="table table-bordered">
	<colgroup>
	<col width="15%">
	<col width="*">
	</colgroup>
	<tbody>
	<tr>
		<th>접수일</th>
		<td><?echo $row->udate ?></td>
	</tr>
	<tr>
		<th>이 름</th>
		<td><?echo $row->name ?></td>
	</tr>
	<tr>
		<th>휴대폰번호</th>
		<td><?echo $row->hp ?></td>
	</tr>
	<tr>
		<th>배송주소</th>
		<td><?echo $row->zip_new ?> <?=$row->add1?> <?=$row->add2?></td>
	</tr>
	<tr>
		<th>제품명</th>
		<td><?echo $row->japum ?></td>
	</tr>
	<tr>
		<th>구입일</th>
		<td><?echo $row->gdate ?></td>
	</tr>
	<tr>
		<th>구입쇼핑몰</th>
		<td><a href=<?echo constant("URL-".$row->shoppingmall);?> target="_blank"><?echo $row->shoppingmall ?></a></td>
	</tr>
	<tr>
		<th>쇼핑몰아이디</th>
		<td><?echo $row->id; if($row->nickname) {echo ' ('.$row->nickname.')';} ?></td>
	</tr>
	<tr>
		<th>주문번호</th>
		<td><?echo $row->oid ?></td>
	</tr>
	<tr>
		<th>메 모</th>
		<td><?echo nl2br($row->content); ?></td>
	</tr>
	<tr> 
		<th>첨부파일</th>
		<td>
		<span id="DivContents"><img src="data/<?=$row->bbs_file?>"></span><br>
		<?if($row->bbs_file){ echo '<a href="./download_event.php?idx='.$row->idx.'&download=1">'.$row->bbs_file.'</a>';}?>
		</td>
	</tr>


<script>
function imgResize()
{
 // DivContents 영역에서 이미지가 maxsize 보다 크면 자동 리사이즈 시켜줌
maxsize = 750; // 가로사이즈 ( 다른값으로 지정하면됨)
var content = document.getElementById("DivContents");
 var img = content.getElementsByTagName("img");
 for(i=0; i<img.length; i++ )
{



if ( eval('img['+i+'].width > maxsize') )
{
var heightSize = ( eval('img['+i+'].height')*maxsize )/eval('img['+i+'].width') ;
 eval('img['+i+'].width = maxsize') ;
 eval('img['+i+'].height = heightSize') ;
}
 }
}
window.onload = imgResize;
</script>

	</tbody>
	</table>


	<table class="table">
		<tr>
			<td class="text-center">
				<!--a href="./online_event_edit.php?idx=<?=$idx?>" class="btn btn-primary">수정</a>&nbsp;&nbsp;&nbsp;-->
				<a href="#" class="btn btn-default" onClick="history.back();return false;">목록</a>
			</td>
		</tr>
	</table>


<? include('../footer.php');?>