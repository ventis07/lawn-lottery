<!DOCTYPE html>
<head>
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
<?if(!isset($_SERVER['HTTP_EVE_TRUSTED'])){?>
<div id="container">
<fieldset>
<legend>Ticket Viewer</legend>
<p>Not Ingame</p>
</fieldset>
</div>
<?}elseif($_SERVER['HTTP_EVE_TRUSTED']!="Yes") { ?>
<body onload="CCPEVE.requestTrust('http://*.<?php echo $_SERVER['HTTP_HOST']; ?>');location.reload();">
<div id="container">
<fieldset>
<legend>Ticket Viewer</legend>
<p>Waiting for Trust</p>
</fieldset>
</div>
<?}else{
	include("include.php");
	$tickets=$db->getTickets($_SERVER['HTTP_EVE_CHARID']);
	$ticketText=null;
	$ticketCount=count($tickets);
	$i=1;
	$s="";
	$out->addHTML("<div id=\"container\"><fieldset><legend>Ticket Viewer</legend>");
	$winner=false;
	if($tickets)
	foreach($tickets as $ticket){
		if($settings['winner']==$ticket['id']&&$settings['finished']){
			$winner=true;
		}
		$ticketText.=$ticket['id'];
		if($i<$ticketCount&&$ticketCount>1)
			$ticketText.=",";
		$i++;
	}
	if($winner)
		$out->addHtml("<p>You won with ticket ".$settings['winner']."</p>");
	else
		$out->addHtml("<p>You Did Not Win. Wining Ticket ".$settings['winner']."</p>");
	if($ticketCount>1||$ticketCount==0)
		$s="s";
	$out->addHTML("<p>You have purchased {$ticketCount} ticket{$s}</p>");
	if($ticketCount)
		$out->addHTML("<p>Ticket Number{$s} {$ticketText}</p>");
	$out->addHTML("</fieldset></div>.");
	
	$out->echoHTML();
		
}
?>