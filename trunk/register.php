<?php 
include_once("include.php");
$notice=null;
if($_POST){
	$password = $_POST['password'];
	$rpassword = $_POST['rpassword'];
	if($password && $rpassword){
		if($password == $rpassword){
			$notice=$db->regUser($password,$_SERVER['HTTP_EVE_CHARNAME'],$_SERVER['HTTP_EVE_CHARID']);
			echo "<script type=\"text/javascript\">setTimeout(\"window.location = 'login'\",3000);</script>";
		}else{
			$notice = "Your passwords do not match..";
		}
	}else{
		$notice = "Please fill in both password fields..";
	}
}?>
<!DOCTYPE html>
<head>
<title>Registration</title>
<style>
fieldset p{
	text-align:center;
	color:grey;
}
#container{
	width:300px;
	margin:50px auto 0px auto;
}
label{
	width:120px;
	float:left;
}
</style>
</head>
<?if(!isset($_SERVER['HTTP_EVE_TRUSTED'])){?>
<div id="container">
<fieldset>
<legend>Registration</legend>
<p>Not Ingame</p>
</fieldset>
</div>
<? }elseif($_SERVER['HTTP_EVE_TRUSTED']!="Yes") { ?>
<body onload="CCPEVE.requestTrust('http://*.<?php echo $_SERVER['HTTP_HOST']; ?>');location.reload();">
<div id="container">
<fieldset>
<legend>Registration</legend>
<p>Waiting for Trust</p>
</fieldset>
</div>
<?php
} else{

$charName = $_SERVER['HTTP_EVE_CHARNAME'];
$charID = $_SERVER['HTTP_EVE_CHARID'];
$allowedUsers=array_filter(explode(",",$settings['acceptedManagers']));
if(!in_array($charID,$allowedUsers)){?>
<div id="container">
<fieldset>
<legend>Registration</legend>
<p>Not Allowed to Register</p>
</fieldset>
</div>
<?}else{
?>
	<div id="container">
	<p><?echo $notice ?></p>
	<center>
	<h2>Welcome <?echo $charName ?></h2>
	<div class="charImage"><img height="128px" width="128px" src="http://image.eveonline.com/character/<?echo $charID ?>_128.jpg" title="<?echo $charName ?>"/></div>
	</center>
	<form method="POST" action="">
	<label for="username">Username</label>
	<input type="text" name="username" value=""/>
	<br>
	<label for="password" class='password'>Password</label>
	<input type="password" name="password" />
	<br>
	<label for="rpassword" class='rpassword'>Repeat password</label>
	<input type="password" name="rpassword" />
	<br>
	<center>
	<input type="submit" value="Register" />
	<br>
	</form>
	<a href='login'>[Login]</a>
	</center>
	</div>
<?}
}
?>
</body>
</html>