<?
@ini_set('max_execution_time', 900);
include("include.php");
$wallet= new walletJournal($settings['apiK'],$settings['vCode'],$settings['characterID']);
$wallet->parse();
if(!count($wallet->transactions))
	echo "No New Tickets";
else
foreach($wallet->transactions as $trans){
	if($trans['refTypeID']==10){
		$tickets=floor(($trans['amount']/$settings['cost']));
		$char= new charInfo((int)$trans['senderID']);
		$char->parse();
		if($settings['Mode']==3){
			if($char->allianceID==$settings['allianceID'])
				while($tickets>0){
					$result=$db->insertTicket($trans['senderID'],$trans['refID']);
					if($result)
						echo "Giving {$trans['sender']} a Ticket<br>";
					else
						echo "Error Adding Ticket";
					$tickets--;
				}
			
		}elseif($settings['Mode']==2){
			if($char->corpID==$settings['corporationID'])
				while($tickets>0){
					$result=$db->insertTicket($trans['senderID']);
					echo "Giving {$trans['sender']} a Ticket<br>";
					if($result)
						echo "Giving {$trans['sender']} a Ticket<br>";
					else
						echo "Error Adding Key";
					$tickets--;
				}
		
		}elseif($settings['Mode']==1){
			while($tickets>0){
					$result=$db->insertTicket($trans['senderID']);
					echo "Giving {$trans['sender']} a Ticket<br>";
					if($result)
						echo "Giving {$trans['sender']} a Ticket<br>";
					else
						echo "Error Adding Key";
					$tickets--;
				}
		}
		echo"<br>";
	}
}

//checks for tampered tickets
$wallet->parse(true);
$tickets=$db->getTickets(0);
foreach($tickets as $ticket){
	if(!isset($wallet->transactions[$ticket['refID']]))
		$db->removeTicket($ticket['id']);
	else
		if($wallet->transactions[$ticket['refID']]['senderID']!=$ticket['cID'])
			$db->removeTicket($ticket['id']);

}
?>