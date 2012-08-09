<?
include("include.php");
# check if logged in
if(!$_SESSION['LOGGED_IN']) {
	header("location: login.php");
}else{
# add login bar
$header="<img height=\"64px\" width=\"64px\" src=\"http://image.eveonline.com/character/{$_SESSION['cID']}_64.jpg\" title=\"{$_SESSION['cName']}\"/> {$_SESSION['cName']} |";
$header.="<a href='manage'>Manage Lottery</a>";
$out->addHeader($header);
# add style sheet
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
</style>");
$out->addHTML('
<script type="text/javascript">
	function showFields()
	{
		selected=document.getElementById("vType").selectedIndex;
		switch(selected){
			case 0:
				document.getElementById("alliance").style.display="none";
				document.getElementById("corporation").style.display="none";
				document.getElementById("redirect").style.display="none";
			break;
			case 1:
				document.getElementById("alliance").style.display="none";
				document.getElementById("corporation").style.display="block";
				document.getElementById("redirect").style.display="block";
			break;
			case 2:
				document.getElementById("alliance").style.display="block";
				document.getElementById("corporation").style.display="none";
				document.getElementById("redirect").style.display="block";
			break;
		
		}
	}
</script>

');

# make a container for settings
$return="<div id=\"container\">
	<fieldset>
	<legend>Lottery Settings</legend>";
#make sure lotto isn't running
if(!@$settings['finished']){
	$return.="<center>Can't Edit Lottery While Running</center>";
	$disabled="DISABLED";
}else{
	$disabled="";
}
# if settings changed continue
	if($_POST){
		#check if lotto is ended
		if(@$settings['finished']){
			# has alliance information changed
			if(isset($_POST['allianceName'])&&$_POST['allianceName']){
				$allianceID=new charID($_POST['allianceName']);
				$allianceID->parse();
				if(!isset($settings['allianceID'])||($allianceID->id!=$settings['allianceID']))
					if($allianceID->id)
						$db->changeSetting("allianceID",$allianceID->id);
					else
						$out->addHTML("Invalid Alliance Name<br>");
			# has corp information changed
			}elseif(isset($_POST['corporationName'])&&$_POST['corporationName']){
				$corporationID=new charID($_POST['corporationName']);
				$corporationID->parse();
				if(!isset($settings['corporationID'])||($corporationID->id!=$settings['corporationID']))
					if($corporationID->id)
						$db->changeSetting("corporationID",$corporationID->id);
					else
						$out->addHTML("Invalid Corp Name<br>");
			}
			#has holding character changed
			if($_POST['cName']){
				$cID=new charID($_POST['cName']);
				$cID->parse();
				if(!isset($settings['characterID'])||($cID->id!=$settings['characterID']))
					if($cID->id)
						$db->changeSetting("characterID",$cID->id);
					else
						$out->addHTML("Invalid Character Name<br>");
			}
			#cycle through other settings
			if(!isset($settings['apiK'])||($_POST['apiK']!=$settings['apiK']))
				$db->changeSetting("apiK",$_POST['apiK']);
			if(!isset($settings['vCode'])||($_POST['vCode']!=$settings['vCode']))
				$db->changeSetting("vCode",$_POST['vCode']);
			if(!isset($settings['vType'])||($_POST['vType']!=$settings['Mode']))
				$db->changeSetting("vType",$_POST['vType']);
			if(!isset($settings['cost'])||($_POST['ticketPrice']!=$settings['cost']))
				$db->changeSetting("cost",$_POST['ticketPrice']);
			}
		# settings that can be changes while lotto is in progress
		if(!isset($settings['redirect'])||($_POST['redirect']!=$settings['redirect']))
			$db->changeSetting("redirect",$_POST['redirect']);
		if(!isset($settings['ticketLimit'])||($_POST['ticketLimit']!=$settings['ticketLimit']))
			$db->changeSetting("ticketLimit",$_POST['ticketLimit']);
		# check if lotto end time has changed
		if(!isset($settings['lottoEnd'])||(date("Y-m-d H:i:s",strtotime("+ ".$_POST['lottoEnd']." days"))!=$settings['lottoEnd'])){
			if($_POST['lottoEnd']===0)
				$db->changeSetting("lottoEnd",$_POST['lottoEnd']);
			elseif(floor((strtotime(@$settings['lottoEnd'])-time())/86400)!=$_POST['lottoEnd'])
				$db->changeSetting("lottoEnd",date("Y-m-d H:i:s",strtotime("+ ".$_POST['lottoEnd']." days")));
		}
		#out put message and reload settings
		$out->addHTML("Settings Saved");
		$settings=$db->getSettings();
	}
	#S variables
	$s1=null;
	$s2=null;
	$s3=null;
	# get holding characters name
	$cName=new charName(@$settings['characterID']);
	$cName->parse();
	
	#calculate lotto end time
	if(@$settings['lottoEnd'])
		$lottoEnd=floor((strtotime(@$settings['lottoEnd'])-time())/86400);
	else
		$lottoEnd=0;
	$allianceName="";
	$corportionName=null;
	if(@$settings['Mode']==2){
		$corporationName=new charName(@$settings['corporationID']);
		$corporationName->parse();
		$corportionName=$corportionName->name;
		$s2="selected='selected'";
		$return.=<<<HTML
		
HTML;
	# check if alliance verification
	}elseif(@$settings['Mode']==3){
		$allianceName=new charName(@$settings['allianceID']);
		$allianceName->parse();
		$allianceName=$allianceName->name;
		$s3="selected='selected'";
		
	}
	# display settings
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
		<input type="text" name="ticketLimit" value="{$settings['ticketLimit']}"/>
		<br>
		(0 for none)
		<br>
		<label for="lottoEnd">Lottery Length</label>
		<input type="text" name="lottoEnd" value="{$lottoEnd}"/>
		<br>
		(in days 0 for no end)
		<br>
		<div id="redirect">
		<label for="redirect">Redirect</label>
		<input type="text" name="redirect" value="{$settings['redirect']}"/>
		<br>
		</div>
		<div id="corporation">
		<label for="corporationName">Corporation</label>
		<input type="text" name="corporationName" value="{$corportionName}" {$disabled}/>
		<br>
		</div>
		<div id="alliance">
		<label for="allianceName">Alliance</label>
		<input type="text" name="allianceName" value="{$allianceName}" {$disabled}/>
		<br>
		</div>
HTML;
	$return.="
		<label for=\"vType\">Verification Type</label>
		<select id= \"vType\" name=\"vType\" onchange=\"showFields();\" {$disabled}>
		<option value=1 $s1>None</option>
		<option value=2 $s2>Corporation</option>
		<option value=3 $s3>Alliance</option>
		</select>
		<br><center>
	";
		$return.=<<<HTML
		<script type="text/javascript">showFields();</script>
HTML;
	$return.="<input type=\"submit\" value=\"Save\" />";
	$return.="</center>
		</form>";
		
	# display allowed users
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