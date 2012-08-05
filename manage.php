<?
include("include.php");
if(!$_SESSION['LOGGED_IN']){ 
	header("location: login.php");
}else{
	if(isset($_GET['lottoNum']))
		$lottoNum=$_GET['lottoNum'];
	if(isset($_GET['endLottery'])){
		if(isset($_GET['sure'])){
			if(isset($_GET['reallysure'])){
				if($db->endLottery())
					header("location:manage.php");
			}else{
				echo"Are You Really Sure<br>";
				die("<a style='width:40px; margin-right:40px;padding-right:40px' href='manage.php'>No<a/><a href='manage.php?endLottery&sure&reallysure'>Yes<a/>");
			
			}
		}else{
			echo"Are You Sure<br>";
			die("<a style='width:40px; margin-right:40px;padding-right:40px' href='manage.php?endLottery&sure'>Yes<a/><a href='manage.php'>No<a/>");
		}
	}elseif(isset($_GET['startLottery'])){
		$db->changeSetting("finished",NULL);
		$db->changeSetting("winner",NULL);
		$lottoNum=$settings['lottoNum']+1;
		$db->changeSetting("nextTicket",1);
		$db->changeSetting("lottoNum",$lottoNum);
			header("location:manage");
	}elseif(isset($_REQUEST['addManager'])){
		if($_POST){
			$cID=new charID($_POST['name']);
			$cID->parse();
			if($cID->id){
				$allowedUsers=$settings['acceptedManagers'].",".$cID->id;
				$db->changeSetting("acceptedManagers",$allowedUsers);
				header("location: editLottery.php");
			}	
		}else{
			$return = <<<HTML
		<form method="POST" action="manage">
		<label for="name">Manager Name</label>
		<input type="text" name="name"/>
		<input type="hidden" name="addManager"/>
		<input type="submit" value="Submit" />
		</form>
HTML;
		$out->addHTML($return);
		}
	$out->echoHTML(true);	
	}elseif(isset($_GET['removeUser'])){
		$db->removeUser($_GET['removeUser']);
		$allowedUsers=array_filter(explode(",",$settings['acceptedManagers']));
		$i=0;
		while($allowedUsers[$i]!=$_GET['removeUser']){
			$i++;
			if($i>count($allowedUsers)-1)
				break;
		}
		if($allowedUsers[$i]==$_GET['removeUser']){
			unset($allowedUsers[$i]);
			$db->changeSetting("acceptedManagers",implode(",",$allowedUsers));
		}
		header("location: editLottery.php");
	}
	$tickets=$db->getTickets(0);
	$charactersRan=array();
	$header="<img height=\"64px\" width=\"64px\" src=\"http://image.eveonline.com/character/{$_SESSION['cID']}_64.jpg\" title=\"{$_SESSION['cName']}\"/> {$_SESSION['cName']}";
	$header.="<a href='editLottery'>Edit Lottery</a> ";
	if($settings['finished']){
		$header.="<a href='?startLottery'>Start New Lottery</a>";
	}else
		$header.="<a href='?endLottery'>Roll for Winner</a>";
	$out->addHeader($header);
	$ticketText=null;
	$out->addHTML("
	<style>
		#character{
			width:120px;
			float:left;
			display:block;
		}
		#balance {
			width:150px;
			float:left;
			display:block;
		}
	</style>");
	$html=null;
	$totalIsk=0;
	$winner=null;
	$characterCount=0;
	$average="Average tickets bought ";
	if($settings['lottoNum']!=$lottoNum){
		$winner=$db->getTickets(-1);
		$settings['winner']=$winner[1]['refID'];
	}
	if($tickets){
		$totalIsk=number_format(count($tickets)*$settings['cost']);
		$ticketsBought=null;
		foreach($tickets as $ticket){
			if(!array_key_exists($ticket['cID'],$charactersRan)&&$ticket['id']>0){
				$char= new charName($ticket['cID']);
				$char->parse();
				$charactersRan[$ticket['cID']]=$ticket['cID'];
				$charTickets=$db->getTickets($ticket['cID']);
				if($charTickets){
					$characterCount++;
					$ticketCount=count($charTickets);
					$i=1;
					$s=null;
					foreach($charTickets as $charTicket){
						if($settings['winner']==$charTicket['id']){
							$ticketText.="<b>".$charTicket['id']."</b>";
							if(isset($_SERVER['HTTP_EVE_TRUSTED'])){
								$winner="\n<a href='manage' onclick=\"CCPEVE.showInfo(1377, ".$ticket['cID'].")\";return false;>".$char->charName."</a> Won with ticket ".$settings['winner'];
								$winner.="<br> \n<a href='manage' onclick=\"CCPEVE.sendMail(".$ticket['cID'].",'Lawn Lottery Winner',' ');return false;\">Send ".$char->charName." A  EVEMail</a><br><br><br>";
							}else{
								$winner="\n".$char->name." Won with ticket ".$settings['winner']."<br><br>";
							}
						}else
							$ticketText.=$charTicket['id'];
						if($i<$ticketCount&&$ticketCount>1)
							$ticketText.=", ";
						$i++;
					}
				}
				if($ticketCount>1)
					$s="s";
				if(strlen($ticketText)>70)
					$ticketText=wordwrap($ticketText, 70, "<br/>\n<div id=\"character\">\"  \"</div>");
				$balance=number_format($db->getBalance($ticket['cID']));
				$ticketsBought.="\n<div id=\"character\">{$char->name}</div><div id=\"balance\">Balance: {$balance}</div> Purchased ticket{$s} {$ticketText}. <br>";
				$ticketText=null;
			}
		}
		$html.=$ticketsBought;
		$average.=count($tickets)/$characterCount;
	}else{
		$average="No Average";
		$html.="\nNo Tickets Purcahsed";
	}
	$out->addHTML(count($tickets)." Tickets Purchased.<br> {$totalIsk} ISK Raised<br><br>".$winner.$average."<br><br>Tickets<br>".$html); 
	$out->echoHTML();	
}
?>