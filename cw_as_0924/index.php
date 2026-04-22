<!DOCTYPE html>
<html>
<head>

	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="description" content="">
	<meta name="author" content="">
	<title>CATCHWELL ADMIN</title>

	<link href="css/sb-admin-2.min.css" rel="stylesheet">

	<style type="text/css">
	html,body{height:100%; font-family:"나눔고딕", NanumGothic, "Nanum Gothic","돋움", Dotum, Arial, sans-serif;}	
	form-control-user2{ font-size:.8rem; border-radius:.10rem; padding:1.5rem 1rem; }
	</style>

</head>

<body class="bg-gradient-light">
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
//	echo $u_agent."<br>";
//	echo $brower;
?>
<div class="container" style="border:0px solid red;">
	<!-- Outer Row -->
	<div class="row justify-content-center" >
		<div class="col-lg-6" style="border:0px solid blue;">
			<div class="card o-hidden border-0 shadow-lg my-5" >
				<div class="card-body p-0">
					<div class="row">
						<!--<div class="col-lg-6 d-none d-lg-block "> </div> -->
						<div class="col-lg-12" >
							<div class="p-5">
								<div class="text-center">
									<h1 class="h4 text-gray-900 mb-4">CATCHWELL</h1>
								</div>

								<form class="user" action="login_progress.php" method="post"> <!--20220107-->
									<input type="hidden" name="login" value="1" />

									<div class="form-group">
										<input type="text" <?if($brower=='MSIE'){echo 'class="form-control form-control-user2"';} else {echo 'class="form-control form-control-user"';}?> name="admin_userid" id="admin_userid" placeholder="ID" 
										value="<? if(isset($_COOKIE['CW_AS_USERID'])) {echo $_COOKIE['CW_AS_USERID'];}else{echo "";}?>">
									</div>
									<div class="form-group">
										<input type="password" <?if($brower=='MSIE'){echo 'class="form-control form-control-user2"';} else {echo 'class="form-control form-control-user"';}?> name="admin_passwd" id="admin_passwd" autocomplete="off" placeholder="Password" 
										value="<? if(isset($_COOKIE['CW_AS_USERPW'])) {echo $_COOKIE['CW_AS_USERPW'];}else{echo "";}?>">
									</div>

									<div class="form-group">
										<div class="custom-control custom-checkbox small">
											<input type="checkbox" class="custom-control-input" name="customCheck" id="customCheck" <?if($_COOKIE['customCheck']==1){echo "checked";}?>>
											<label class="custom-control-label" for="customCheck">Remember Me</label>
										</div>
									</div>
									<button type="submit" class="btn btn-dark btn-user btn-block">로그인</button>
								</form>
								
								<hr>

								<div class="text-center">
									<a class="small text-gray-600" >아이디 찾기, 비밀번호 찾기는 관리자에게 문의하세요.</a><br>
									
								</div>								
								<!--div class="text-center">
									<a class="small text-gray-600" href="#" data-toggle="tooltip" title="관리자에게 이메일로 문의하세요.">Forgot Password?</a>
								</div>
								<div class="text-center">
									<a class="small text-gray-600" href="#" data-toggle="tooltip" title="관리자에게 이메일로 문의하세요.">Create an Account!</a>
								</div-->
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
<script>
$(document).ready(function(){
  $('[data-toggle="tooltip"]').tooltip();
});



</script>
</body>
</html>


