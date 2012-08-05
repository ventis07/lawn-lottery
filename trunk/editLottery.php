<?
include("include.php");
if(!$_SESSION['LOGGED_IN']) {
	header("location: login.php");
}else{
$header="<img height=\"64px\" width=\"64px\" src=\"http://image.eveonline.com/character/{$_SESSION['cID']}_64.jpg\" title=\"{$_SESSION['cName']}\"/> {$_SESSION['cName']} |";
$header.="<a href='manage'>Manage Lottery</a>";
$out->addHeader($header);
$out->addHTML("
<style>
	fieldset p{
		text-align:center;
		color:grey;
	}
	#container{
		width:670px;
		margin:50px auto 0px auto;
	}
	label{
		width:120px;
		float:left;
	}
	#vCode{
		width:510px;
	}
</style>

");
$return="<div id=\"container\">
	<fieldset>
	<legend>Lottery Settings</legend>";
if(!$settings['finished']){
	$return.="<center>Can't Edit Lottery While Running</center>";
	$disabled="DISABLED";
}else{
	$disabled="";
}
	if($_POST&&$settings['finished']){
		if(isset($_POST['allianceName'])&&$_POST['allianceName']){
			$allianceID=new charID($_POST['allianceName']);
			$allianceID->parse();
			if(!isset($settings['allianceID'])||($allianceID->id!=$settings['allianceID']))
				if($allianceID->id)
					$db->changeSetting("allianceID",$allianceID->id);
				else
					$out->addHTML("Invalid Alliance Name<br>");
		}elseif(isset($_POST['corporationName'])&&$_POST['corporationName']){
			$corporationID=new charID($_POST['corporationName']);
			$corporationID->parse();
			if(!isset($settings['corporationID'])||($corporationID->id!=$settings['corporationID']))
				if($corporationID->id)
					$db->changeSetting("corporationID",$corporationID->id);
				else
					$out->addHTML("Invalid Corp Name<br>");
		}
		if($_POST['cName']){
			$cID=new charID($_POST['cName']);
			$cID->parse();
			if(!isset($settings['characterID'])||($cID->id!=$settings['characterID']))
				if($cID->id)
					krumo($db->changeSetting("characterID",$cID->id));
				else
					$out->addHTML("Invalid Character Name<br>");
		}
		if(!isset($settings['apiK'])||($_POST['apiK']!=$settings['apiK']))
			$db->changeSetting("apiK",$_POST['apiK']);
		if(!isset($settings['vCode'])||($_POST['vCode']!=$settings['vCode']))
			$db->changeSetting("vCode",$_POST['vCode']);
		if(!isset($settings['Mode'])||($_POST['verificationType']!=$settings['Mode']))
			$db->changeSetting("Mode",$_POST['verificationType']);
		if(!isset($settings['cost'])||($_POST['ticketPrice']!=$settings['cost']))
			$db->changeSetting("cost",$_POST['ticketPrice']);
		if(!isset($settings['redirect'])||($_POST['redirect']!=$settings['redirect']))
			$db->changeSetting("redirect",$_POST['redirect']);
		if(!isset($settings['ticketLimit'])||($_POST['ticketLimit']!=$settings['ticketLimit']))
			$db->changeSetting("ticketLimit",$_POST['ticketLimit']);
		$out->addHTML("Settings Saved");
		$settings=$db->getSettings();
	}
	$s1=null;
	$s2=null;
	$s3=null;
	$cName=new charName($settings['characterID']);
	$cName->parse();
	$return .= <<<HTML
		<form method="POST" action="">
		<label for="ticketPrice">Ticket Price</label>
		<input type="text" name="ticketPrice" value="{$settings['cost']}" {$disabled}/>
		<br>
		<label for="apiK">Api Key</label>
		<input type="text" name="apiK" value="{$settings['apiK']}" {$disabled}/>
		<br>
		<label for="vCode">vCode</label>
		<input id="vCode" type="text" name="vCode" value="{$settings['vCode']}" {$disabled}/>
		<br>
		<label for="vCode">Character</label>
		<input type="text" name="cName" value="{$cName->name}" {$disabled}/>
		<br>
		<label for="ticketLimit">Ticket Limit</label>
		<input type="text" name="ticketLimit" value="{$settings['ticketLimit']}" {$disabled}/>
		<br>
		(0 for none)
		<br>
HTML;
	if($settings['Mode']>1){
		$return.="<label for=\"vCode\">Redirect</label>
		<input type=\"text\" name=\"redirect\" value=\"{$settings['redirect']}\" {$disabled}/>
		<br>";
	}
	if($settings['Mode']==2){
		$corporationName=new charName($settings['corporationID']);
		$corporationName->parse();
		$s2="selected='selected'";
		$return.=<<<HTML
		<label for="corporationName">Corporation</label>
		<input type="text" name="corporationName" value="{$corportionName->name}" {$disabled}/>
		<br>
HTML;
	}elseif($settings['Mode']==3){
		$allianceName=new charName($settings['allianceID']);
		$allianceName->parse();
		$s3="selected='selected'";
		$return.=<<<HTML
		<label for="allianceName">Alliance</label>
		<input type="text" name="allianceName" value="{$allianceName->name}" {$disabled}/>
		<br>
HTML;
	}else{
		$s1="selected='selected'";
	}
	$return.="
		<label for=\"verificationType\">Verification Type</label>
		<select name=\"verificationType\" {$disabled}>
		<option value=1 $s1>None</option>
		<option value=2 $s2>Corporation</option>
		<option value=3 $s3>Alliance</option>
		</select>
		<br><center>
	";
	if($settings['finished'])
		$return.="<input type=\"submit\" value=\"Save\" />";
	$return.="</center>
		</form>";
	$return.="</fieldset><fieldset>
		<legend>Allowed Users</legend>";
	$return.=" <a href='manage.php?addManager'> Add Manager</a><br>";
	$allowedUsers=array_filter(explode(",",$settings['acceptedManagers']));
	foreach($allowedUsers as $user){
		$cID=new charName($user);
		$cID->parse();
		$return.=$cID->name." <a href='manage.php?removeUser=".$user."'> Remove</a><br>";
	
	}
	$out->addHTML($return."</fieldset></div>");
$out->echoHTML();
}
?>

</body>
</html>