<?
session_start();

include ("../common.php");
require ("../check_session.php");

$table		= "admin_account";
$query		= "select * from $table where admin_userid='$ADMIN_USERID' LIMIT 1";

$result=mysqli_query($db->db_conn, $query);
$row = mysqli_fetch_array($result);

?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="description" content="">
	<meta name="author" content="">
	<title>Catchwell_CS_Admin</title>

	<link href="../css/sb-admin-2.min.css" rel="stylesheet">

    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/skin/dashboard.css" rel="stylesheet">

	<style type="text/css">
	html,body{height:100%; font-family:"나눔고딕", NanumGothic, "Nanum Gothic","돋움", Dotum, Arial, sans-serif;}	
	form-control-user2{ font-size:.8rem; border-radius:.10rem; padding:1.5rem 1rem; }
	</style>

</head>

<body class="">
<?
	$u_agent = $_SERVER['HTTP_USER_AGENT'];
	$brower = 'Unknown';

	if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent))   {
        $brower = "MSIE";
    }
	else if(preg_match('/rv:11.0/i',$u_agent) && !preg_match('/Opera/i',$u_agent))   {
        $brower = "MSIE";
	}
	else if(preg_match('/Edg/i',$u_agent) && !preg_match('/Opera/i',$u_agent))   {
        $brower = "Edge";
	}
    else if(preg_match('/Firefox/i',$u_agent))   {
        $brower = "Firefox";
    }
    else if(preg_match('/Chrome/i',$u_agent))   {
        $brower = "Chrome";
    }
    else if(preg_match('/Safari/i',$u_agent))    {
        $brower = "Safari";
    }
    else if(preg_match('/Opera/i',$u_agent))    {
        $brower = "Opera";
    }
    else if(preg_match('/Netscape/i',$u_agent))    {
        $brower = "Netscape";
	}

?>




<div class="container" style="border:0px solid red;">
	<div class="row justify-content-center" >
		<div class="col-lg-6" style="border:0px solid blue;">
			<div class="card o-hidden border-0 shadow-lg my-5" >
				<div class="card-body p-0">
					<div class="row">
						<div class="col-lg-12" >
							<div class="p-5">
								<div class="text-center">
									<h1 class="h4 text-gray-900 mb-4">비밀번호 변경</h1>
								</div><br>

								<form action="check_security_ok.php" method="post" name="admin_form" id="admin_form" ENCTYPE="multipart/form-data">
									<input type="hidden" name="admin_userid" value="<?=$row['admin_userid']?>" >
									<input type="hidden" name="return_url" value="../index.php" >

									<table class="table table-bordered">
									<colgroup>
									<col width="30%">
									<col width="70%">
									</colgroup>
									<tbody>
									<span class="text-gray-600"><font color="blue">영문/숫자 포함 8자 이상</font></span>
									<tr>
										<th>현재 비밀번호 <font color="red">*</font></td>
										<td><input name="admin_passwd" type="password" maxlength="30" class="form-control " placeholder="현재 비밀번호 입력" value=""></td>
									</tr>
									<tr>
										<th>신규 비밀번호 <font color="red">*</font></td>
										<td><input name="admin_passwd_new1" type="password" maxlength="30" class="form-control " placeholder="신규 비밀번호 (영문/숫자 포함 8자 이상)" value=""></td>
									</tr>
									<tr>
										<th>신규 비밀번호 확인 <font color="red">*</font></td>
										<td><input name="admin_passwd_new2" type="password" maxlength="30" class="form-control " placeholder="신규 비밀번호 재입력" value=""></td>
									</tr>
									</tbody>
									</table>

									<div class="form-group"><div class="custom-control custom-checkbox ">
									<input type="checkbox" class="custom-control-input" name="privacyCheck" id="privacyCheck" checked >
									<label class="custom-control-label text-gray-600" for="privacyCheck">[개인정보처리방침]에 동의합니다. <font color="red">*</font></label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
									</div>
									
									</div>
								</form><br>

								<table class="table">
									<tr>
									<td class="text-center">
										<a href="javascript:sendit();" class="btn btn-primary">변경하기</a>
										<a href="javascript:history.back();" class="btn btn-default">취소</a>
										<? if (isset($_GET['forward'])) { ?>
											<a href="../main.php" class="btn btn-default">다음에 변경하기</a>
										<? } ?>
									</td>
									</tr>
									
								</table>
								<hr>
								<a href="privacy_agreement_catchwell_2021.pdf" target="_blank"><span style="color:#337ab7; font-weight:normal; ">개인정보처리방침</span></a><br><br>
								
							</div>
						</div>
					</div>
				</div>
	  		</div>
			<div class="text-center" style="margin-top:-30px;">
				<a class="small text-gray-600" >Copyright &copy; 캐치웰. All right reserved.</a><br>
			</div>
		</div>
 	</div>
</div>


<script type="text/javascript">

function sendit() 
{
	var pw = <?php echo json_encode($row['admin_passwd'])?>;
	var id = <?php echo json_encode($row['admin_userid'])?>;
	
//	var regExp = /^[A-Za-z0-9+]*$/; 
	var regExpNum = /[0-9]/; //숫자
	var regExpAlp = /[a-zA-Z]/; //영어


//	alert(regExpAlp.test(admin_form.admin_passwd_new1.value));

	if (admin_form.privacyCheck.checked == false) {
		alert("[개인정보처리방침]에 동의하세요.");
		admin_form.privacyCheck.focus();
	}
	else if (admin_form.admin_passwd.value == "") {
		alert("현재 비밀번호를 입력해 주세요.");
		admin_form.admin_passwd.focus();
	}
/*	else if (pw != admin_form.admin_passwd.value) {
		alert("현재 비밀번호가 일치하지 않습니다.");
		admin_form.admin_passwd.focus();
	} */
	else if (admin_form.admin_passwd_new1.value == "") {
		alert("변경할 신규 비밀번호를 입력해 주세요.");
		admin_form.admin_passwd_new1.focus();
	}
	else if (admin_form.admin_passwd_new2.value == "") {
		alert("변경할 신규 비밀번호를 다시 입력해 주세요.");
		admin_form.admin_passwd_new2.focus();
	}
	else if (admin_form.admin_passwd_new1.value != admin_form.admin_passwd_new2.value) {
		alert("신규 비밀번호가 일치하지 않습니다.");
		admin_form.admin_passwd_new2.focus();
	}
	else if (admin_form.admin_passwd_new1.value == id) {
			alert("아이디와 동일한 비밀번호는 사용할 수 없습니다.");
			admin_form.admin_passwd_new1.focus();
	}
	else if (admin_form.admin_passwd.value == admin_form.admin_passwd_new1.value) {
			alert("사용할 수 없는 비밀번호입니다(1).");
			admin_form.admin_passwd_new1.focus();
	}
	else if (admin_form.admin_passwd_new1.value.length < 8) {
			alert("영문/숫자 포함 8자리 이상으로 입력해 주세요.");
			admin_form.admin_passwd_new1.focus();
	}
	else if (regExpNum.test(admin_form.admin_passwd_new1.value)==false || 
			 regExpAlp.test(admin_form.admin_passwd_new1.value)==false) 
	{
		alert("영문과 숫자를 모두 포함하여 입력해 주세요.");
		admin_form.admin_passwd_new1.focus();
	}
	else if (	(admin_form.admin_passwd_new1.value.includes("000")) || 
				(admin_form.admin_passwd_new1.value.includes("111")) || 
				(admin_form.admin_passwd_new1.value.includes("222")) || 
				(admin_form.admin_passwd_new1.value.includes("333")) || 
				(admin_form.admin_passwd_new1.value.includes("444")) || 
				(admin_form.admin_passwd_new1.value.includes("555")) || 
				(admin_form.admin_passwd_new1.value.includes("666")) || 
				(admin_form.admin_passwd_new1.value.includes("777")) || 
				(admin_form.admin_passwd_new1.value.includes("888")) || 
				(admin_form.admin_passwd_new1.value.includes('999')) ||
				(admin_form.admin_passwd_new1.value.includes("123")) || (admin_form.admin_passwd_new1.value.includes("321")) || 
				(admin_form.admin_passwd_new1.value.includes("234")) || (admin_form.admin_passwd_new1.value.includes("432")) || 
				(admin_form.admin_passwd_new1.value.includes("345")) || (admin_form.admin_passwd_new1.value.includes("543")) || 
				(admin_form.admin_passwd_new1.value.includes("456")) || (admin_form.admin_passwd_new1.value.includes("654")) || 
				(admin_form.admin_passwd_new1.value.includes("567")) || (admin_form.admin_passwd_new1.value.includes("765")) || 
				(admin_form.admin_passwd_new1.value.includes("678")) || (admin_form.admin_passwd_new1.value.includes("876")) || 
				(admin_form.admin_passwd_new1.value.includes("789")) || (admin_form.admin_passwd_new1.value.includes("987")) || 
				(admin_form.admin_passwd_new1.value.includes("890")) || (admin_form.admin_passwd_new1.value.includes("098")) 
			) 
	{
		alert("연속된 숫자는 사용할 수 없습니다.");
		admin_form.admin_passwd_new1.focus();
	} 
	else 
	{ 
		ans = confirm("변경하시겠습니까?");
		if(ans==true)
		{
			admin_form.submit();
		}
		
	}
}

</script>

</body>
</html>


