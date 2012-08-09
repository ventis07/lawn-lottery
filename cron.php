<?
#prevent cron from taking too long
@ini_set('max_execution_time', 900);

#include basic files
include("include.php");

echo date("Y-m-d H:i:s",time())."<br>\n";
//varriable to hold next ticket
$nextTicket=$settings['nextTicket'];
if($settings['finished']){
	die ("no lottery running");
}

#get wallet journal
$wallet= new walletJournal($settings['apiK'],$settings['vCode'],$settings['characterID']);
$wallet->parse();
# no new tickets, report such
if(!count($wallet->transactions))
	echo "No New Tickets<br>\n";
#Else cycle the tickets
else
foreach($wallet->transactions as $trans){
	#check for donations
	if($trans['refTypeID']==10&&
		$trans['senderID']!=$settings['characterID']&&
			(strtotime($trans['date'])<strtotime($settings['lottoEnd'])
			||$settings['lottoEnd']==0)&&
		(strtotime($trans['date'])>strtotime($settings['lottoStart'])))
	{
		# count tickets
		$tickets=floor(($trans['amount']/$settings['cost']));
		
		#get donator information
		$char= new charInfo((int)$trans['senderID']);
		$char->parse();
		
		#get tickets and balance
		$charTickets=$db->getTickets($trans['senderID']);
		$charBalance=$db->getBalance($trans['senderID'])+$trans['amount'];
		
		#make sure were not over the ticket limit
		if($tickets>($settings['ticketLimit']-count($charTickets))&&$settings['ticketLimit'])
			$tickets=$settings['ticketLimit']-count($charTickets);
		
		#check for alliance authentication
		if($settings['Mode']==3){
			#check if donator is in alliance
			if($char->allianceID==$settings['allianceID'])
			
				# update balance
				$result=$db->updateBalance($trans['senderID'],$charBalance);
				
				#format balance
				$charBalance=number_format($charBalance);
				
				#inform result
				if($result)
					echo "Updating {$trans['sender']}'s balance to {$charBalance}<br>";
				else
					echo "Error updating balance";
					
				#add tickets
				while($tickets>0){
					$result=$db->insertTicket($trans['senderID'],$trans['refID']);
					if($result)
						echo "Giving {$trans['sender']} a Ticket<br>";
					else
						echo "Error Adding Ticket";
					$tickets--;
				}
		#check for corp authentication	
		}elseif($settings['Mode']==2){
			#check if donator is in corp
			if($char->corpID==$settings['corporationID'])
			
				# update balance
				$result=$db->updateBalance($trans['senderID'],$charBalance);
				
				#format balance
				$charBalance=number_format($charBalance);
				
				#inform result
				if($result)
					echo "Updating {$trans['sender']}'s balance to {$charBalance}<br>";
				else
					echo "Error updating balance";
					
				#add tickets
				while($tickets>0){
					$result=$db->insertTicket($trans['senderID']);
					if($result)
						echo "Giving {$trans['sender']} a Ticket<br>";
					else
						echo "Error Adding Key";
					$charTickets[(int)$trans['senderID']]++;
					$tickets--;
				}
		#check for no authentication	
		}elseif($settings['Mode']==1){
		
			# update balance
			$result=$db->updateBalance($trans['senderID'],$charBalance);
			
			#format balance
			$charBalance=number_format($charBalance);
			
			#inform result
			if($result)
				echo "Updating {$trans['sender']}'s balance to {$charBalance}<br>";
			else
				echo "Error updating balance";
				
			#add tickets	
			while($tickets>0){
					$result=$db->insertTicket($trans['senderID']);
					if($result)
						echo "Giving {$trans['sender']} a Ticket<br>";
					else
						echo "Error Adding Key";
					$charTickets[(int)$trans['senderID']]++;
					$tickets--;
				}
		}
		echo"<br>";
	}
}
//stores what the next ticket will be
$db->changeSetting("nextTicket",$nextTicket);
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
if(time()>strtotime($settings['lottoEnd'])&&!$settings['finished']){
	$db->endLottery();
	echo "Ending Lotto";
	$settings['finished']=true;
}
?>