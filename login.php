<?php
include("include.php");
if($_POST)
	if($db->loginUser($_POST['password'],$_POST['username'])){
		$_SESSION['LOGGED_IN']=true;
		$notice="Login Successful";
		header("location: manage.php");
	}else{
		$_SESSION['LOGGED_IN']=false;
		$notice="Invalid Username or Password";
	}
?>
<!DOCTYPE html>
<head>
<title>Login</title>
<style>
fieldset p{
text-align:center;
color:grey;
}
#container{
width:400px;
margin:50px auto 0px auto;
}
</style>
</head>
<body>

<div id="container">
<form method="POST" action="">
<fieldset>
<legend>Login</legend>
<div id="notice"><?php echo @$notice ?></div>
<label for="username">Username</label>
<input type="text" name="username" />
<br>
<label for="username">Password</label>
<input type="password" name="password" />
<input type="submit" value="Login" />
</fieldset>
</form>
<a href='register'>[Register]</a>
</div>


</body>
</html>