<?include("include.php");
$out->addHTML('
	<style>
	body{
		background-color:black;
		color:green;
	}
	#centerImg{
		margin-left:auto;
		margin-right:auto;
		padding-left:auto;
		padding-right:auto;
		display:block;
		width:30%;
	}
	#left{
		position: absolute;
		bottom: 25px;
		left: 0px;
	}
	#center{
		text-align:center;
		display:block;
		position: absolute;
		bottom: 25px;
		right: 40%;
		left: 40%;
	}
	#right{
		position: absolute;
		bottom: 25px;
		right: 0px;
	}
	a{
		color:red;
	}
	#title{
		font-size:2em;
	
	}
	#textan, #equto, #paikau {
		display:inline-block;
		width:250px;
		font-size:2.2em;
		text-align:center;
	
	}
	#textan a, #equto a, #paikau a{
		color:green;
		text-decoration:none;
	}
	a img{
		height:50px;
		width:150px;
	}
	fieldset p{
		text-align:center;
		color:grey;
	}
	#container{
		
		width:600px;
		margin:50px auto 0px auto;
	}
	</style>
	');
?>
<!DOCTYPE html>
<?
# Check if ingame if not inform them
if(!isset($_SERVER['HTTP_EVE_TRUSTED'])){?>
<div id="container">
<fieldset>
<legend>Restricted</legend>
<p>Not using EVE Ingame Browser</p>
</fieldset>
</div>
<?
# Check if trusted if not request trust and show waiting page
}elseif(@$_SERVER['HTTP_EVE_TRUSTED']!="Yes") { ?>
<body onload="CCPEVE.requestTrust('http://*.<?php echo $_SERVER['HTTP_HOST']; ?>');location.reload();">
<div id="container">
<fieldset>
<legend>Restricted</legend>
<p>Waiting for Trust</p>
</fieldset>
</div>
<?
# Trusted and in game, get tickets and display winners if winners and tickets purchased
}elseif(isset($_GET['tickets'])){
	# include basic files
	$denied=true;
	
	# check the mode and see if they need to be authenticated
	if($settings['Mode']==2){
		if($_SERVER['HTTP_EVE_CORPID']==$settings['corporationID'])
			$denied=false;
	}elseif($settings['Mode']==3){
		if($_SERVER['HTTP_EVE_ALLIANCEID']==$settings['allianceID'])
			$denied=false;
	}else
		$denied=false;
	# if authenticate or skipped continue to display
	if($denied===false){
	
		#get tickets for current character
		$tickets=$db->getTickets($_SERVER['HTTP_EVE_CHARID']);
		
		#set variables
		$ticketText=null;
		$i=1;
		$s="";
		$winner=false;
		
		#count Tickets
		$ticketCount=count($tickets);
		
		#contain ticket window
		
		$out->addHTML("<center id=\"title\">".$settings['lottoName']."</center>
		<div id=\"container\"><fieldset><legend>Ticket Viewer</legend>");
		
		#if tickets parse them
		if($tickets)
		foreach($tickets as $ticket){
			#see if lottery is finished and if user is a winner
			if($settings['winner']==$ticket['id']&&$settings['finished']){
				$winner=true;
			}
			
			#add ticket number to text
			$ticketText.=$ticket['id'];
			
			#add comma if not last ticket
			if($i<$ticketCount)
				$ticketText.=", ";
			$i++;
		}
		# if lottery finished display if winner or not
		if($settings['finished']&&$settings['winner'])
			if($winner)
				$out->addHtml("<p>You won with ticket ".$settings['winner']."</p>");
			else{
				$ticket=$db->getTicketById($settings['winner']);
				$char= new charName($ticket['cID']);
				$char->parse();
				$out->addHTML("<center>".$char->name." has won with Ticket ".$settings['winner']."</center>");
			}
		
		# check if more than one ticket
		if($ticketCount>1||$ticketCount==0)
			$s="s";
		#if string length over 70 wrap it
		if(strlen($ticketText)>70)
			$ticketText=wordwrap($ticketText, 40, "<br/>\n");
		
		#announce number of tickets purchased
		$out->addHTML("<p>You have purchased {$ticketCount} ticket{$s}</p>");
		
		# if tickets show the tickets bought
		if($ticketCount){
			$out->addHTML("<p>Ticket Number{$s}:</p>");
			$out->addHTML("<p>".$ticketText."</p>");
		}
		# close container
		$out->addHTML("</fieldset></div>");
	}else
		#redirect to given page
		header("location:{$settings['redirect']}");
	
	#send out buffer
	$char= new charName($settings['characterID']);
	$char->parse();
	$out->addHTML("<center>Send Isk To <a href='index' onclick=\"CCPEVE.showInfo(1377, ".$settings['characterID'].")\";return false;>".$char->name."</a></center>");
	$out->addHTML('<a id="center" href="index"><img src="back.jpg" alt="Back to Main Page" /></a>');
		
}elseif(isset($_GET['instruction'])){

	$out->addHTML('<a id="center" href="index"><img src="back.jpg" alt="Back to Main Page" /></a>');
}elseif(isset($_GET['contact'])){
	$out->addHTML('
		<div id="equto">
			<img height="128px" width="128px" src="http://image.eveonline.com/character/688546564_128.jpg" title="Equto"/><br>
			<a href="?about" onclick="CCPEVE.showInfo(1377,688546564);return false;">Equto</a>
		</div>
		<div id="textan">
			<img height="128px" width="128px" src="http://image.eveonline.com/character/1650854497_128.jpg" title="Textan"/><br>
			<a href="?about" onclick="CCPEVE.showInfo(1377,1650854497);return false;">Textan</a>
		</div>
		<div id="paikau">
			<img height="128px" width="128px" src="http://image.eveonline.com/character/1998881408_128.jpg" title="Paikau"/><br>
			<a href="?about" onclick="CCPEVE.showInfo(1377,1998881408);return false;"> Paikau</a>
		</div>
	');
	$out->addHTML('<a id="center" href="index"><img src="back.jpg" alt="Back to Main Page" /></a>');
}else{
	$out->addHTML('
	<img id="centerImg" src="title.png"/>
	<img id="centerImg" src="gnome.jpg"/>
	');
	$out->addHTML('
		<script type="text/javascript">
		function update_time(time){
			day="";
			hour="";
			min="";
			sec="";
			output="";
			D=new Date().getTime()/1000;
			trainingTime=time-D;
			days = Math.floor(trainingTime/(24*60*60));
			hours =Math.floor((trainingTime-(days*24*60*60))/(60*60));
			mins = Math.floor(((trainingTime-(hours*60*60))-(days*24*60*60))/60);
			secs = Math.floor(((trainingTime-(mins*60))-(hours*60*60))-(days*24*60*60));
			if(days){
				if (days ==1)  output += days +" day"; else
				if (days > 1)  output += days +" days";
			}else if (hours){
				if (hours>0&&output!="") output +=", "
				if (hours ==1) output += hours +" hour"; else
				if (hours > 1) output += hours +" hours";
			}else{
				if (mins>0&&output!="") output +=", "
				if (mins ==1)  output += mins +" minute"; else
				if (mins > 1)  output += mins +" minutes";
				if (secs>0&&output!="") output +=", "
				if (secs ==1)  output += secs +" second"; else
				if (secs > 1)  output += secs +" seconds";
			}
			
			if(trainingTime<=0){
				document.getElementById("countdown").innerHTML="Awaiting Dice Roll";
				document.getElementById("countdown").style.color="yellow";
				document.getElementById("current").innerHTML="Current Status: <div style=\"display:inline; color:red;\"> Completed</div>";
			}else{
				document.getElementById("countdown").innerHTML=output+" Remaining";
				var t=setTimeout("update_time("+time+")",1000);
			}
		}
		</script>');
	if($settings['finished']){
		$ticket=$db->getTicketById($settings['winner']);
		$char= new charName($ticket['cID']);
		$char->parse();
		$out->addHTML("<center id='current'>Current Status: Completed</center>");
		$out->addHTML("<center style='color:green;'>Congratulations! ".$char->name." has won with Ticket ".$settings['winner']."</center>");
	}elseif(!$settings['finished']&&(floor((strtotime($settings['lottoEnd'])-time())/86400))>0){
		$out->addHTML("<center id='current'>Current Status: Running</center>");
		$out->addHTML("<center id=\"countdown\">".(floor((strtotime($settings['lottoEnd'])-time())/86400))." Day Remaining</center>");
	}elseif(!$settings['finished']&&(floor((strtotime($settings['lottoEnd'])-time())/3600))>0){
		$out->addHTML("<center id='current'>Current Status: Running</center>");
		$out->addHTML("<center id=\"countdown\" style='color:red;'>".(floor((strtotime($settings['lottoEnd'])-time())/3600))." Hour Remaining</center>");
	}elseif(!$settings['finished']&&(floor((strtotime($settings['lottoEnd'])-time())/60))>0){
		$out->addHTML("<center id='current'>Current Status: Running</center>");
		$out->addHTML("<center id=\"countdown\" style='color:red;'>".(floor((strtotime($settings['lottoEnd'])-time())/60))." Minute Remaining</center>");
	}elseif((!$settings['finished']&&(floor((strtotime($settings['lottoEnd'])-time())/3600))<0)||$settings['finished']){
		$out->addHTML("<center id='current'>Current Status: <div style=\"display:inline; color:red;\"> Completed</div></center>");
		$out->addHTML("<center style=\"color:yellow;\">Awaiting Dice Roll</center>");
	}
	$out->addHTML("
	<script type='text/javascript'>update_time(".(strtotime($settings['lottoEnd'])).")</script>
	<center>".($settings['finished']?count($db->getTickets(0))-1:count($db->getTickets(0)))." Tickets Sold</center>");

	$out->addHTML('<div>
	<a id="left" href="index.php?instruction"><img src="instruct.jpg" alt="Instructions" /></a>
	<a id="center" href="index.php?tickets"><img src="view.jpg" alt="View Your Tickets" /></a>
	<a id="right" href="index.php?contact"><img src="contact.jpg" alt="Contact Us" /></a>
	</div>
	');

}
$out->echoHTML();
?>