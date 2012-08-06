<?
include("include.php");

#check if logged in
if(!$_SESSION['LOGGED_IN'])
	header("location: login.php");

#style info
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
		#lottoName{
			display:inline;
			margin-left:10px;
			margin-right:30px:
			padding-left:10px;
			padding-right:30px;
		
		}
		a{
			margin-left:10px;
			margin-right:10px:
			padding-left:10px;
			padding-right:10px;
		}
		#stats{
			display:inline-block;
			text-align:center;
			margin-left:10px;
			margin-right:10px:
			padding-left:10px;
			padding-right:10px;
		}
	</style>");	
	
	
# header for admin pages
$header="<img height=\"64px\" width=\"64px\" src=\"http://image.eveonline.com/character/{$_SESSION['cID']}_64.jpg\" title=\"{$_SESSION['cName']}\"/> {$_SESSION['cName']}";
$header.="<a href='editLottery'>Edit Lottery</a> ";
if($settings['finished']){
	$header.="<a href='?startLottery'>Start New Lottery</a>";
}else
	$header.="<a href='?endLottery'>Roll for Winner</a>";
if($_GET||isset($_GET['lottoNum']))
	$header.="<a href='manage'>Back</a> ";
else
	$header.="<a href='?pastLotto'>View Past lotteries</a> ";
$out->addHeader($header);
# check if ending lotto, verify twice
if(isset($_GET['endLottery'])){
	if(isset($_GET['sure'])){
		if(isset($_GET['reallysure'])){
			$db->changeSetting("lottoEnd",date("Y-m-d H:i:s",time()));
			header("location:manage.php");
		}else{
			$out->addHeader("Are You Really Sure<br>");
			$out->addHeader("<a style='width:40px; margin-right:40px;padding-right:40px' href='manage.php'>No<a/><a href='manage.php?endLottery&sure&reallysure'>Yes<a/>");
		
		}
	}else{
		$out->addHeader("Are You Sure<br>");
		$out->addHeader("<a style='width:40px; margin-right:40px;padding-right:40px' href='manage.php?endLottery&sure'>Yes<a/><a href='manage.php'>No<a/>");
	}
# check if starting a lotto
}elseif(isset($_GET['startLottery'])){
	if(@$_POST['lottoName']){
		$lottoName=$_POST['lottoName'];
		$db->changeSetting("finished",false);
		$db->changeSetting("winner",false);
		$db->changeSetting("nextTicket",1);
		$db->changeSetting("lottoEnd",date("Y-m-d H:i:s",strtotime("+ ".$_POST['lottoEnd']." days")));
		$db->changeSetting("lottoStart",date("Y-m-d H:i:s",time()));
		$db->changeSetting("lottoNum",$lottoNum+1);
		$db->changeSetting("lottoName",$lottoName);
		header("location:manage");
	}else{
		$out->addHeader('<form method="POST" action="manage.php?startLottery">
			<label for="lottoName">Lottery Name</label>
		<input type="text" name="lottoName" />
		<label for="lottoEnd">Lottery Length</label>
		<input type="text" name="lottoEnd" value="30"/>
		<br>
		<input type="submit" value="Create Lotto" />
		</form>');
	}
#add a manager to the lotto
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
#remove a user from the lotto
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
	
#view past lotteries
}elseif(isset($_GET['pastLotto'])){
	$past=$db->getTickets(-1,true);
	if($past){
		#get stats for all lottos
		$tickets=$db->getTickets(0,true);
		$charactersRan=array();
		$totalUsers=0;
		$totalTickets=0;
		foreach($tickets as $ticket){
			if($ticket['id']>0)
				$totalTickets++;
			if(!array_key_exists($ticket['cID'],$charactersRan)&&$ticket['id']>0){
				$charactersRan[$ticket['cID']]=$ticket['cID'];
				$totalUsers++;
			}
		}
		$balances=$db->getBalance(0,true);
		$totalBalance=(float)0;
		foreach($balances as $balance){
			$totalBalance+=(float)$balance;
		}
		$out->addHTML('<div id="stats">Total Isk<br>'.number_format($totalBalance,2).'</div><div id="stats">Unique User<br>'.number_format($totalUsers).'</div><div id="stats">Total Tickets<br>'.number_format($totalTickets).'</div><br><br>');
		foreach($past as $lotto){
			$lottoNum=$lotto['lottoNum'];
			$ticketCount=count($db->getTickets(0))-1;
			$out->addHTML("# ".$lotto['lottoNum']."<div id='lottoName'>".$lotto['lottoName']."</div> End Date: ".date("Y-m-d",$lotto['id']*-1)."  Tickets Sold: ".$ticketCount."   <a href='manage.php?lottoNum=".$lotto['lottoNum']."'> View Lotto</a><br>");
		}
	}else
		$out->addHTML("No Past Lotteries");
}elseif(@$_GET['lottoNum']||!$_GET){
		# check if looking at past lotto, set variables
	if(@$_GET['lottoNum']){
		$lottoNum=$_GET['lottoNum'];
		$config=$db->getTickets(-1);
		$settings['winner']=$config[1]['refID'];
		$settings['lottoName']=$config[1]['lottoName'];
		$settings['finished']=true;
	}

	#get tickets
	$tickets=$db->getTickets(0);

	#Set variables
	$charactersRan=array();
	$ticketText=null;
	$html=null;
	$totalIsk=0;
	$winner=null;
	$characterCount=0;
	$average="Average tickets bought ";
	
	#check if there are any tickets
	if($tickets){
		
		#calculate isk
		$totalIsk=number_format(($settings['finished']?count($tickets)-1:count($tickets))*$settings['cost']);
		$ticketsBought=null;
		
		#cycle through tickets
		foreach($tickets as $ticket){
			#check if character already has tickets if so skip
			if(!array_key_exists($ticket['cID'],$charactersRan)&&$ticket['id']>0){
			
				#get character name 
				$char= new charName($ticket['cID']);
				$char->parse();
				
				#set as having tickets
				$charactersRan[$ticket['cID']]=$ticket['cID'];
		
				#get tickets
				$charTickets=$db->getTickets($ticket['cID']);
				
				#if has tickets continue
				if($charTickets){
					
					#add to number of people who bout tickets
					$characterCount++;
					$ticketCount=count($charTickets);
					$i=1;
					$s=null;
					
					#cycle through character tickets
					foreach($charTickets as $charTicket){
						
						#check if they are the winner
						if($settings['winner']==$charTicket['id']){
							$ticketText.="<b>".$charTicket['id']."</b>";
							if(isset($_SERVER['HTTP_EVE_TRUSTED'])){
								$winner="\n<a href='manage' onclick=\"CCPEVE.showInfo(1377, ".$ticket['cID'].")\";return false;>".$char->name."</a> Won with ticket ".$settings['winner'];
								$winner.="<br> \n<a href='manage' onclick=\"CCPEVE.sendMail(".$ticket['cID'].",'Lawn Lottery Winner',' ');return false;\">Send ".$char->name." A  EVEMail</a><br><br><br>";
							}else{
								$winner="\n".$char->name." Won with ticket ".$settings['winner']."<br><br>";
							}
						}else
							$ticketText.=$charTicket['id'];
						
						#add commas
						if($i<$ticketCount&&$ticketCount>1)
							$ticketText.=", ";
						$i++;
					}
				}
				#add s for multiple tickets
				if($ticketCount>1)
					$s="s";
				if(strlen($ticketText)>70)
					$ticketText=wordwrap($ticketText, 70, "<br/>\n<div id=\"character\">&nbsp;</div><div id=\"balance\">&nbsp;</div>");
				$balance=number_format($db->getBalance($ticket['cID']));
				$ticketsBought.="\n<div id=\"character\">{$char->name}</div><div id=\"balance\">Balance: {$balance}</div> Purchased ticket{$s} {$ticketText} <br>";
				$ticketText=null;
			}
		}
		$html.=$ticketsBought;
		$average.=($settings['finished']?count($tickets)-1:count($tickets))/$characterCount;
	}else{
		$average="No Average";
		$html.="\nNo Tickets Purcahsed";
	}
	if($settings['finished']||$settings['lottoNum']!=$lottoNum)
		$ended="Completed";
	else
		$ended="Current";
	$out->addHTML(@$notice."{$ended} Lotto: {$settings['lottoName']}<br>".($settings['finished']?count($tickets)-1:count($tickets))." Tickets Purchased<br> {$totalIsk} ISK Raised<br><br>".$winner.$average."<br><br>Tickets<br>".$html);
	}
$out->echoHTML();	

?>