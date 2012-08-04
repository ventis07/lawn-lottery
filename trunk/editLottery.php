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
		width:660px;
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
if($settings['finished']){
	if($_POST){
		if(isset($_POST['allianceName'])&&$_POST['allianceName']){
			$allianceID=new charID($_POST['allianceName']);
			$allianceID->parse();
			if($allianceID->id!=$settings['allianceID'])
				if($allianceID->id)
					$db->changeSetting("allianceID",$allianceID->id);
				else
					$out->addHTML("Invalid Alliance Name<br>");
		}elseif(isset($_POST['corporationName'])&&$_POST['corporationName']){
			$corporationID=new charID($_POST['corporationName']);
			$corporationID->parse();
			if($corporationID->id!=$settings['corporationID'])
				if($corporationID->id)
					$db->changeSetting("corporationID",$corporationID->id);
				else
					$out->addHTML("Invalid Corp Name<br>");
		}
		if($_POST['cName']){
			$cID=new charID($_POST['cName']);
			$cID->parse();
			if($cID->id!=$settings['characterID'])
				if($cID->id)
					krumo($db->changeSetting("characterID",$cID->id));
				else
					$out->addHTML("Invalid Character Name<br>");
		}
		if($_POST['apiK']!=$settings['apiK'])
			$db->changeSetting("apiK",$_POST['apiK']);
		if($_POST['vCode']!=$settings['vCode'])
			$db->changeSetting("vCode",$_POST['vCode']);
		if($_POST['verificationType']!=$settings['Mode'])
			$db->changeSetting("Mode",$_POST['verificationType']);
		if($_POST['ticketPrice']!=$settings['cost'])
			$db->changeSetting("cost",$_POST['ticketPrice']);
		$out->addHTML("Settings Saved");
	}
	$s1=null;
	$s2=null;
	$s3=null;
	$cName=new charName($settings['characterID']);
	$cName->parse();
	$return = <<<HTML
		<div id="container">
		<fieldset>
		<legend>Lottery Settings</legend>
		<form method="POST" action="">
		<label for="ticketPrice">Ticket Price</label>
		<input type="text" name="ticketPrice" value="{$settings['cost']}"/>
		<br>
		<label for="apiK">Api Key</label>
		<input type="text" name="apiK" value="{$settings['apiK']}"/>
		<br>
		<label for="vCode">vCode</label>
		<input id="vCode" type="text" name="vCode" value="{$settings['vCode']}"/>
		<br>
		<label for="vCode">Character</label>
		<input type="text" name="cName" value="{$cName->name}"/>
		<br>
HTML;
	if($settings['Mode']==2){
		$corporationName=new charName($settings['corporationID']);
		$corporationName->parse();
		$s2="selected='selected'";
		$return.=<<<HTML
		<label for="corporationName">Corporation</label>
		<input type="text" name="corporationName" value="{$corportionName->name}"/>
		<br>
HTML;
	}elseif($settings['Mode']==3){
		$allianceName=new charName($settings['allianceID']);
		$allianceName->parse();
		$s3="selected='selected'";
		$return.=<<<HTML
		<label for="allianceName">Alliance</label>
		<input type="text" name="allianceName" value="{$allianceName->name}"/>
		<br>
HTML;
	}else{
		$s1="selected='selected'";
	}
	$return.="
		<label for=\"verificationType\">Verification Type</label>
		<select name=\"verificationType\">
		<option value=1 $s1>None</option>
		<option value=2 $s2>Corporation</option>
		<option value=3 $s3>Alliance</option>
		</select>
		<br><center>
		<input type=\"submit\" value=\"Save\" />
		</center>
		</form>
		</fieldset>";
	$return.="<fieldset>
		<legend>Allowed Users</legend>";
	$return.=" <a href='manage.php?addManager'> Add Manager</a><br>";
	$allowedUsers=array_filter(explode(",",$settings['acceptedManagers']));
	foreach($allowedUsers as $user){
		$cID=new charName($user);
		$cID->parse();
		$return.=$cID->name." <a href='manage.php?removeUser=".$user."'> Remove</a><br>";
	
	}
	$out->addHTML($return."</fieldset></div>");
}else{
	$out->addHTML("Can't Edit Lottery While its running");
}
$out->echoHTML();
}
?>

</body>
</html>