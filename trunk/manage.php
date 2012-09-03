<?
include("include.php");


if(isset($_GET['logout'])){
	session_destroy();
	header("location:".@$_SESSION['url']);
}
#check if logged in
if(!$_SESSION['LOGGED_IN'])
	header("location: login.php");
$_SESSION['url']="http".((@$_SERVER["HTTPS"])?"s":"")."://".$_SERVER["SERVER_NAME"]."/manage";

$vars['get']=@$_GET;
# header for admin pages
$header="<img height=\"64px\" width=\"64px\" src=\"http://image.eveonline.com/character/{$_SESSION['cID']}_64.jpg\" title=\"{$_SESSION['cName']}\"/> {$_SESSION['cName']}";
$header.="<a href='editLottery'>Edit Lottery</a> ";
if(!@$settings['hide'])
	$header.="<a href='?hide'>Close to Public</a> ";
else
	$header.="<a href='?show'>Open to Public</a> ";
if(@$settings['finished']){
	$header.="<a href='?startLottery'>Start New Lottery</a>";
}else
	$header.="<a href='?endLottery'>Roll for Winner</a>";
if($_GET||isset($_GET['lottoNum']))
	$header.="<a href='manage'>Back</a> ";
else
	$header.="<a href='?pastLotto'>View Past lotteries</a> ";
$header.="<a href='/'>Lotto Main Page</a> ";
$header.="<a href='?logout'>Logout</a> ";
$out->addHeader($header);




# check if ending lotto, verify twice
if(isset($_GET['endLottery'])){
	if(isset($_GET['reallysure'])&&isset($_GET['sure'])){
		$db->changeSetting("lottoEnd",date("Y-m-d H:i:s",time()));
		header("location:manage");
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
		$db->changeSetting("hide",false);
		header("location:manage");
	}
#add a manager to the lotto
}elseif(isset($_REQUEST['addManager'])){
	if($_POST){
		$cID=new charID($_POST['name']);
		$cID->parse();
		if($cID->id!==0){
			$allowedUsers=@$settings['acceptedManagers'].",".$cID->id;
			$db->changeSetting("acceptedManagers",$allowedUsers);
			header("location: editLottery");
		}	
	}
#remove a user from the lotto
}elseif(isset($_GET['removeUser'])){
	$db->removeUser($_GET['removeUser']);
	$allowedUsers=array_filter(explode(",",@$settings['acceptedManagers']));
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
}elseif(isset($_GET['hide'])){
	$db->changeSetting("hide",true);
	header("location:manage");
}elseif(isset($_GET['show'])){
	$db->changeSetting("hide",false);
	header("location:manage");	
#view past lotteries
}elseif(isset($_GET['pastLotto'])){$past=$db->getTickets(-1,true);
	if($past){
		#get stats for all lottos
		$tickets=$db->getTickets(0,true);
		$charactersRan=array();
		$vars['totalUsers']=0;
		$vars['totalTickets']=0;
		foreach($tickets as $ticket){
			if($ticket['id']>0)
				$vars['totalTickets']++;
			if(!array_key_exists($ticket['cID'],$charactersRan)&&$ticket['id']>0){
				$charactersRan[$ticket['cID']]=$ticket['cID'];
				$vars['totalUsers']++;
			}
		}
		$balances=$db->getBalance(0,true);
		$vars['totalBalance']=(float)0;
		foreach($balances as $balance){
			$vars['totalBalance']+=(float)$balance;
		}
		foreach($past as $lotto){
			$vars['lottos'][$lotto['lottoNum']]['pastLottoNum']=$lotto['lottoNum'];
			$vars['lottos'][$lotto['lottoNum']]['pastTicketCount']=count($db->getTickets(0))-1;
			$vars['lottos'][$lotto['lottoNum']]['pastLottoEndDate']=date("Y-m-d",$lotto['id']*-1);
			}
	}
}elseif(@$_GET['lottoNum']||!$_GET){
		# check if looking at past lotto, set variables
	if(@$_GET['lottoNum']){
		$lottoNum=$_GET['lottoNum'];
		$config=$db->getTickets(-1);
		@$settings['winner']=$config[1]['refID'];
		@$settings['lottoName']=$config[1]['lottoName'];
		@$settings['finished']=true;
	}
	#check lotto status
	if(@$settings['hide']){
		$vars['status']=0;
	}elseif(@$settings['finished']){
		$vars['status']=3;
		if(@$settings['winner']){
			$ticket=$db->getTicketById(@$settings['winner']);
			$char= new charName($ticket['cID']);
			$char->parse();
			$vars['winnerName']=$char->name;
			$vars['winnerTicket']=$settings['winner'];
			$vars['winnerID']=$ticket['cID'];
		}
	}elseif((time()-strtotime(@$settings['lottoEnd']))<0){
		$vars['status']=1;
	}elseif(!@$settings['finished']&&(time()-strtotime(@$settings['lottoEnd']))>0){
		$vars['status']=2;
	}
	$vars['lottoName']=@$settings['lottoName'];
	#get tickets
	$tickets=$db->getTickets(0);

	#Set variables
	$charactersRan=array();
	$vars['characterCount']=0;
	$vars['ticketsSold']=(@$settings['finished']?count($tickets)-1:count($tickets));
	#check if there are any tickets
	if($tickets){
		
		#calculate isk
		$vars['totalIsk']=$vars['ticketsSold']*@$settings['ticketPrice'];
		$vars['characterCount']=0;
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
					$vars['characterCount']++;
					$vars['characters'][$ticket['cID']]['ticketCount']=count($charTickets);
					$i=1;
					$vars['characters'][$ticket['cID']]['ticketText']=null;
					#cycle through character tickets
					foreach($charTickets as $charTicket){
						$vars['characters'][$ticket['cID']]['ticketText'].=$charTicket['id'];
						#add commas
						if($i<$vars['characters'][$ticket['cID']]['ticketCount']&&$vars['characters'][$ticket['cID']]['ticketCount']>1)
							$vars['characters'][$ticket['cID']]['ticketText'].=", ";
						$i++;
					}
				}
				#add s for multiple tickets
				if(strlen($vars['characters'][$ticket['cID']]['ticketText'])>130)
					$vars['characters'][$ticket['cID']]['ticketText']=wordwrap($vars['characters'][$ticket['cID']]['ticketText'], 130, "<br/>\n<div id=\"break\">&nbsp;</div>");
				$vars['characters'][$ticket['cID']]['charName']=$char->name;
				$vars['characters'][$ticket['cID']]['balance']=$db->getBalance($ticket['cID']);
			}
		}
		$vars['adminCut']=($vars['totalIsk']*.1)/3;
		$vars['average']=$vars['ticketsSold']/$vars['characterCount'];
	}
}
$out->useTemplate('templates/manage.tpl');
if(isset($past))
	foreach($vars['lottos'] as $vars)
		$out->useTemplate('templates/pastLotto.tpl');
else
	foreach($vars['characters'] as $vars)
		$out->useTemplate('templates/ticketList.tpl');

$out->echoHTML();
	
$db->close();
?>