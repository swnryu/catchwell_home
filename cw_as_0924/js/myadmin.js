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
	$(".ajax-checkbox").click(function() {

		var checkboxVal = [];
		$("input[name='check_list']:checked").each(function(i) {
			checkboxVal.push($(this).val());
		});

		var dbname		= $(this).attr("data-dbname");
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
			
			var msg = "[삭제]";var msg2 = "하시겠습니까?";

			if(  $("input:checkbox[name='check_list']").is(":checked") ){
				ans = confirm(msg + " " + msg2);
				if(ans==true){	
					
					ans = confirm("삭제한 데이터는 복구할 수 없습니다.\r\n[삭제] 하시겠습니까?");
					if(ans==true){

				$.ajax({
					
					url : "../ajax/checkbox.php",
					type: "post",
					data: postData,
					success:function(obj){ 
						location.reload();
					}
					
				});
				
					}
				}
			}else{
				alert(msg+" "+"항목을 선택하여 주세요.");
			}
		}
		else if(name=="move") {
			
//			val = $( "#move_item option:selected" ).val();

			var msg = "[이동]";var msg2 = "하시겠습니까?";

			if(  $("input:checkbox[name='check_list']").is(":checked") ){
				ans = confirm(msg + " " + msg2);
				if(ans==true){	
				$.ajax({
					
					url : "../ajax/checkbox.php",
					type: "post",
					data: postData,
					success:function(obj){ 
						location.reload();
					}
					
				});
				}
			}else{
				alert(msg+" "+"항목을 선택하여 주세요.");
			}
		}
		else if(name=="shipment") {
			
			var msg = "[출고완료 처리]";var msg2 = "하시겠습니까?";

			if(  $("input:checkbox[name='check_list']").is(":checked") ){
				ans = confirm(msg + " " + msg2);
				if(ans==true){	
				$.ajax({
					
					url : "../ajax/checkbox.php",
					type: "post",
					data: postData,
					success:function(obj){ 
						location.reload();
					}
					
				});
				}
			}else{
				alert(msg+" "+"항목을 선택하여 주세요.");
			}
		}

		else if(name=="export2excel") {
			
			var msg = "선택 항목을 [엑셀 다운로드]";var msg2 = "하시겠습니까?";

			if(  $("input:checkbox[name='check_list']").is(":checked") ){
				ans = confirm(msg + " " + msg2);
				if(ans==true){
				$.ajax({
					url : "../ajax/checkbox_dl_excel.php",
					type: "post",
					data: postData,
					success:function(result){ 
						//AS_YYYYMMDD.xlsx
						var dt = new Date();
						var y = dt.getFullYear().toString();
						var m = (dt.getMonth()+1).toString();
						var d = (dt.getDate().toString());

						if (dt.getMonth()+1 < 10)	m = "0"+(dt.getMonth()+1).toString();
						if (dt.getDate() < 10)		d = "0"+(dt.getDate().toString());

						var filename = "../temp"+"/AS_"+y+m+d+".xlsx";

						var win = window.open(filename, "_blank");
						location.reload();

					}
				});
				}
			}else{
				alert(msg+" "+"항목을 선택하여 주세요.");
			}
		} 
	
	});//.ajax-checkbox

	



});


