<?
include("../def_inc.php");
include("event_def.php");
$mod	= M_EVENT;
$menu	= S_EVENT_SNIPER; 
include("../header.php");

$idx = $_GET['idx'];

$mode = isset($_POST['mode']) ? $_POST['mode'] : '';
if ($mode=='save') { //save
    //save
    $row = $db->update("cs_online_event_sniper", "status=$_POST[status], admin_memo='$_POST[admin_memo]', tracking_num='$_POST[tracking_num]' where idx='$idx'" );

    //redirect
    unset($_POST['mode']);
    unset($_POST['status']);
    unset($_POST['admin_memo']);
    unset($_POST['tracking_num']);
    
    $tools->alertJavaGo("저장 되었습니다.", $_SERVER['PHP_SELF'].'?idx='.$idx);
//  $tools->alertJavaGo("저장 되었습니다.", 'online_event_view.php?idx='.$idx);

}

$row = $db->object("cs_online_event_sniper","where idx='$idx'");

?>


<h4 class="page-header">포토상품평 이벤트 신청서 수정 - 스나이퍼</h4>

<form method="post" name="edit_form" class="form" action="<?=$_SERVER['PHP_SELF'].'?idx='.$idx;?>" >
<input type="hidden" name="mode" value="save">
<table class="table table-bordered">
<colgroup>
<col width="15%">
<col width="*">
</colgroup>
<tbody>
<tr>
    <th>상 태</th>
    <td>
        <label class="radio-inline"><input type="radio" name="status" value=<?echo STATUS_NULL;?> <?if($row->status==null || $row->status == STATUS_NULL){echo "checked";} ?>>처리중</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <!--label class="radio-inline"><input type="radio" name="status" value=<?echo STATUS_OK;?> <?if($row->status!=null && $row->status == STATUS_OK){echo "checked";} ?>>적합</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <label class="radio-inline"><input type="radio" name="status" value=<?echo STATUS_NOTOK;?> <?if($row->status!=null && $row->status == STATUS_NOTOK){echo "checked";} ?>>부적합</label>&nbsp;&nbsp;&nbsp;-->
        <label class="radio-inline"><input type="radio" name="status" value=<?echo STATUS_DONE;?> <?if($row->status!=null && $row->status == STATUS_DONE){echo "checked";} ?>>발송완료</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    </td>
</tr>
<tr>
    <th>송장번호</th>
    <td><input name="tracking_num" class="form-control" value="<?=$row->tracking_num?>" placeholder="송장번호" autocomplete="off"></td>
</tr>
<tr>
    <th>관리자 메모</th>
    <td><textarea class="form-control" rows="2" name="admin_memo" placeholder="관리자 메모를 입력" autocomplete="off"><?=$row->admin_memo?></textarea></td>
</tr>
<tr>
    <td colspan="2" class="text-center">
        <button type="submit" class="btn btn-primary " >등록</button>&nbsp;&nbsp;&nbsp;
        <a href="#" class="btn btn-default " onClick="history.back();return false;">목록</a>
	</td>
</tr>
</tbody>
</table>
</form>
    

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
    <th>쇼핑몰아이디 (닉네임)</th>
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
    <span id="DivContents"><img src="data_sniper/<?=$row->bbs_file?>"></span><br>
    <?if($row->bbs_file){ echo '<a href="./download_event_sniper.php?idx='.$row->idx.'&download=1">'.$row->bbs_file.'</a>';}?>
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
            <a href="#" class="btn btn-default" onClick="history.back();return false;">목록</a>
        </td>
    </tr>
</table>























<? include('../footer.php');?>