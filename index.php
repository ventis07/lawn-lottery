<?include("include.php");

#calculate important variables
$minute=(int)date("i",time());
if($minute>=30){
	$nextPull=strtotime((date("H",time())+1).":00")+60;
}else{
	$nextPull=strtotime((date("H",time())).":30")+60;
}
#time of next api pull in seconds
$vars['nextPull']=$nextPull;

#get settings such as lotto status, name and winner information
if(@$settings['finished']){
	$vars['statusText']="Completed";
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
	$vars['statusText']="Running";
	$vars['status']=1;
}elseif(!@$settings['finished']&&(time()-strtotime(@$settings['lottoEnd']))>0){
	$vars['statusText']="Awaiting dice Roll";
	$vars['status']=2;
}
$vars['lottoName']=@$settings['lottoName'];



#lotto ending times and text
$vars['lottoEnd']=@$settings['lottoEnd'];
$vars['lottoEndSeconds']=strtotime(@$settings['lottoEnd']);
$vars['lottoSecondsLeft']=strtotime(@$settings['lottoEnd'])-time();


#lott end text
$vars['lottoEndText']="";
if(@$settings['lottoEnd']=0){
	$var['lottoEndText']="No End Set";
}elseif(!@$settings['finished']&&(floor((strtotime(@$settings['lottoEnd'])-time())/86400))>0){
	$var['lottoEndText']=(floor((strtotime(@$settings['lottoEnd'])-time())/86400))." Days Remaining";
}elseif(!@$settings['finished']&&(floor((strtotime(@$settings['lottoEnd'])-time())/3600))>0){
	$var['lottoEndText']=(floor((strtotime(@$settings['lottoEnd'])-time())/3600))." Hours Remaining";
}elseif(!@$settings['finished']&&(floor((strtotime(@$settings['lottoEnd'])-time())/60))>0){
	$var['lottoEndText']=(floor((strtotime(@$settings['lottoEnd'])-time())/60))." Minutes Remaining";
}elseif((!@$settings['finished']&&(floor((strtotime(@$settings['lottoEnd'])-time())/3600))<0)||@$settings['finished']){
	$var['lottoEndText']="Complete";
}

#ticket information
$vars['ticketPrice']=$settings['ticketPrice'];
$vars['ticketsSold']=(@$settings['finished']?count($db->getTickets(0))-1:count($db->getTickets(0)));

#settings for main page
$char= new charName(@$settings['characterID']);
$char->parse();
$vars['lottoCharacterName']=(string)$char->name;
$vars['lottoCharacterID']=@$settings['characterID'];

# Check if ingame if not inform them
if(!isset($_SERVER['HTTP_EVE_TRUSTED'])){
	$vars['inGame']=false;
	$vars['trusted']=false;
# Check if trusted if not request trust and show waiting page
}elseif(@$_SERVER['HTTP_EVE_TRUSTED']!="Yes") {
	$vars['inGame']=true;
	$vars['trusted']=false;
# Trusted and in game, get tickets and display winners if winners and tickets purchased
}else{
	$vars['inGame']=true;
	$vars['trusted']=true;
}
#get current page
$vars['page']=null;
if(isset($_GET['page']))
	$vars['page']=$_GET['page'];

#get character information if trusted
$vars['characterTicketText']=null;
$vars['characterTicketCount']=null;
$vars['characterID']=@$_SERVER['HTTP_EVE_CHARID'];
if($vars['trusted']){
	#get tickets for current character
		$tickets=$db->getTickets($vars['characterID']);
		
		#set variables
		$ticketText=null;
		
		#count Tickets
		$ticketCount=count($tickets);
		
		#if tickets parse them
		if($ticketCount){
			foreach($tickets as $ticket){
				#add ticket number to text
				$ticketText.=$ticket['id'];
				
				#add comma if not last ticket
				if($i<$ticketCount)
					$ticketText.=", ";
				$i++;
			}
			#if string length over 70 wrap it
			if(strlen($ticketText)>70)
				$ticketText=wordwrap($ticketText, 40, "<br/>\n");
		}
		$vars['characterTicketText']=$ticketText;
		$vars['characterTicketCount']=$ticketCount;
		
	#check if redirect is enabled and if you should be redirected	
	$denied=true;

	# check the mode and see if they need to be authenticated
	if(@$settings['vType']==2){
		if($_SERVER['HTTP_EVE_CORPID']==@$settings['corporationID'])
			$denied=false;
	}elseif(@$settings['vType']==3){
		if($_SERVER['HTTP_EVE_ALLIANCEID']==@$settings['allianceID'])
			$denied=false;
	}else
		$denied=false;
	# if authenticate or skipped continue to display
	if($denied!==false){
		//header("location:{$settings['redirect']}");
	}
}
	
#output information
$out->addStyle('
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
		width:55%;
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
		width:200px;
		font-size:2.2em;
		text-align:center;
	
	}
	#textanTitle, #equtoTitle, #paikauTitle {
		display:inline-block;
		width:200px;
		font-size:1.4em;
		color:purple;
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
	#about	{
		display:block;
		margin-left: auto;
		margin-right: auto;
		text-align:center;
		width: 50em;
	}
	#pullTimer{
		font-size:70%;
	}
');

#add javscript
$out->addJavascript('
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
					document.getElementById("status").innerHTML="Current Status: <div style=\"display:inline; color:red;\"> Completed</div>";
				}else{
					document.getElementById("countdown").innerHTML=output+" Remaining";
					var t=setTimeout("update_time("+time+")",1000);
				}
			}
			function update_pull(time){
				min="";
				sec="";
				output="";
				D=new Date().getTime()/1000;
				trainingTime=time-D;
				mins = Math.floor(trainingTime/60);
				secs = Math.floor(trainingTime-(mins*60));
				if(mins){
					if (mins ==1)  output += mins +" minute"; else
					if (mins > 1)  output += mins +" minutes";
				}else{
					if (secs ==1)  output += secs +" second"; else
					if (secs > 1)  output += secs +" seconds";
				}
				
				if(trainingTime<=0){
					document.location="index";
				}else{
					document.getElementById("pullTimer").innerHTML=output+" Until Next Api Pull";
					var t=setTimeout("update_pull("+time+")",1000);
				}
			}
');

$out->useTemplate('index.tpl');
$out->echoHTML();
?>