<!DOCTYPE html>
<head>
<style>
fieldset p{
text-align:center;
color:grey;
}
#container{
width:600px;
margin:50px auto 0px auto;
}
</style>
</head>
<?
# Check if ingame if not inform them
if(!isset($_SERVER['HTTP_EVE_TRUSTED'])){?>
<div id="container">
<fieldset>
<legend>Ticket Viewer</legend>
<p>Not Ingame</p>
</fieldset>
</div>
<?
# Check if trusted if not request trust and show waiting page
}elseif($_SERVER['HTTP_EVE_TRUSTED']!="Yes") { ?>
<body onload="CCPEVE.requestTrust('http://*.<?php echo $_SERVER['HTTP_HOST']; ?>');location.reload();">
<div id="container">
<fieldset>
<legend>Ticket Viewer</legend>
<p>Waiting for Trust</p>
</fieldset>
</div>
<?
# Trusted and in game, get tickets and display winners if winners and tickets purchased
}else{
	# include basic files
	include("include.php");
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
		$out->addHTML("<div id=\"container\"><fieldset><legend>Ticket Viewer</legend>");
		
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
		if($settings['finished'])
			if($winner)
				$out->addHtml("<p>You won with ticket ".$settings['winner']."</p>");
			else
				$out->addHtml("<p>You Did Not Win.<br> Wining Ticket ".$settings['winner']."</p>");
		
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
	$out->echoHTML();
		
}
?>