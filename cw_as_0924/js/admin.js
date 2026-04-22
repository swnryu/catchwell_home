$( document ).ready(function() {  
	var site_url = "http://"+ location.host;

	//checkbox 전체체크
	$("#allCheck").click(function(){
		if($("#allCheck").prop("checked")) {
			$("input[name=check_list]").prop("checked",true);
		} else {
			$("input[name=check_list]").prop("checked",false);
		}
	})

	//달력
	$('.datetime').datetimepicker({pickTime: false});

	//설명요약
	$('[data-toggle="tooltip"]').tooltip();

	//추가표시(제품)
	$('input[name=icon_arr]').click(function() {
		var form = document.tx_editor_form;
		var items=[];
		$('input[name="icon_arr"]:checkbox:checked').each(function(){
			items.push($(this).val());
		});
		var tmp = items.join('|');
		form.icon.value=tmp;
	});
});

$(function() {
	$(".ajax-select").change(function() {
		
		var dbname	= $(this).attr("data-dbname");
		var idx			= $(this).attr("data-idx");
		var name		= $(this).attr("name");
		var val			= $("option:selected", this).attr("value");

		var postData = 
			{ 
				"dbname": dbname,
				"idx": idx,
				"name": name,
				"val": val
			};

		if(name=="trade_stat"){ 
					if(val==1){var msg = "[결제대기]";}
			else if(val==2){var msg = "[결제완료]";}
			else if(val==3){var msg = "[상품배송중]";}
			else if(val==4){var msg = "[배송완료]";}
			else if(val==5){var msg = "[주문취소]";}	
		}else if(name=="level"){
			var msg = "[회원구분]";
		}
		ans = confirm(msg + " 변경하시겠습니까?");
		if(ans==true){
		$.ajax({
			url : "../ajax/select.php",
			type: "post",
			data: postData,
			success:function(){ 
				location.reload();
			}
		});
		}

	});//.ajax-select


	$(".ajax-checkbox").click(function() {

		var checkboxVal = [];
		$("input[name='check_list']:checked").each(function(i) {
			checkboxVal.push($(this).val());
		});

		var dbname	= $(this).attr("data-dbname");
		var idx			= checkboxVal;
		var name		= $(this).attr("data-name");
		var val			= $(this).attr("data-val");
		var postData = 
			{ 
				"dbname": dbname,
				"idx": idx,
				"name": name,
				"val": val
			};

		if(name=="delete") {
			var msg = "[삭제2]";var msg2 = "하시겠습니까?";
		} 
		
	
		if(  $("input:checkbox[name='check_list']").is(":checked") ){
			ans = confirm(msg + " " + msg2);
			if(ans==true){		
			$.ajax({
				url : "../ajax/checkbox.php",
				type: "post",
				data: postData,
				success:function(obj){ 
					if(dbname=="cs_zzim"){
						//zzim_load();
					}else{
						location.reload();
					}
				}
			});
			}
		}else{
			alert(msg+" "+"항목을 선택하여 주세요.");
		}
	
	});//.ajax-checkbox


	$(".ajax-button").click(function() {

		var dbname	= $(this).attr("data-dbname");
		var name		= $(this).attr("data-name");
		var idx			= $(this).attr("data-idx");
		var val			= $(this).attr("data-val");

		var postData = { 
				"dbname": dbname,
				"name": name,
				"idx": idx,
				"val": val
			};
		$.ajax({
			url : "../ajax/button.php",
			type: "post",
			data: postData,
			success:function(result){ 
				location.reload();
			}
		});
	});//.ajax-button

});

