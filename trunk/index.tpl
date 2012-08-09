<!--
This is an example Template
You can use if elseif and else statements within and they will be run if true 
Avaliable variables:
$lottoCharacterID=id of character accepting money
$lottoCharacterName= name of character accepting money
$ticketPrice= price of a single ticket
$page= result of ?page=
$characterID=id of character visiting site if trusted
$characterTicketCount=How many tickets the character has bought if site is trusted
$characterTicketText= comma delimerated string of ticket numbers bought by character if trusted
$ticketsSold= count of tickets all bought tickets
$inGame= bool of whether character is using in game browser
$trusted= bool of whether page is currently trusted
$winnerID= character id of winner if winner has been drawn
$winnerName= name of winner if winner has been drawn
$nextPull= time in seconds between its possible to pull next api, intervals of 30 minutes
$lottoEndSeconds= time the lotto will end in seconds( for javascript functions)
$lottoEnd= time time at which the lotto will end as a date and time
$lottoEndText= how many days hours or minutes until lotto ends as a string


--!>

<if $inGame>
	<if $trusted>
		<if $page=instruction>
			<p>How to participate in the lottery</p>
			<br>
			<p>Ticket Cost: {$ticketPrice|format}<br>
			Send isk to: <a href="?page=instruction" onclick="CCPEVE.showInfo(1377,{$lottoCharacterID});return false;">{$lottoCharacterName}</a></p>
			<br>
			<p>All isk must be sent to Lawn Lotto in multiples of {$ticketPrice|format} for your tickets to be correctly applied
			That means if you send {$ticketPrice|mult 2 format} isk you will recieve 2 Ticket. However, if you send in {$ticketPrice|mult 2 sub .01 format} isk
			and 0.01 isk you will only recieve 1 Ticket. Even though your Total isk sent in is '.{$ticketPrice|format}.'. This is because of the way we track tickets.</p>
			<a id="center" href="index">Back to Main Page</a>
		<elseif $page=contact>
			<div id="about">
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
				<br>
				<div id="equtoTitle">
					Bug Creator
				</div>
				<div id="textanTitle">
					Your Favorite Canadian
				</div>
				<div id="paikauTitle">
					The Pocket Protector
				</div>
			</div>
			<a id="center" href="index">Back to Main Page</a>
		<elseif $page=tickets>
		<center id="title">{$lottoName}</center>
		<div id="container">
			<fieldset>
				<legend>Ticket Viewer</legend>
				<if $status=3>
					<if $winnerID=$characterID>
						<center>You won with ticket {$winnerTicket}</center>
					</if>
					<else>
						<center>{$winnerName} has won with ticket {$winnerTicket}</center>
					</else>
				</if>
				<if $ticketCount=0>
					<p>You have purchased no tickets</p>
				<elseif $ticketCount=1>
					<p>You have purchased 1 ticket</p>
					<p>Ticket Number:</p>
					<p>{$ticketText}</p>
				</if>
				<else>
					<p>You have purchased {$ticketCount} tickets</p>
					<p>Ticket Numbers:</p>
					<p>{$ticketText}</p>
				</else>
				</fieldset>
				</div>
				<center>Send Isk To <a href="?page=tickets" onclick="CCPEVE.showInfo(1377,{$lottoCharacterID});return false;">{$lottoCharacterName}</a></center>
			<a id="center" href="index">Back to Main Page</a>
		</if>
		<else>
			<body onload="update_pull({$nextPull});update_time({$lottoEndSeconds})">
			<center id="pullTimer">Pull Timer</center>
			<img id="centerImg" src='gnome.jpg'></img>
			<center style="color:yellow;">Current Status: {$statusText}</center>
			<center>{$ticketsSold} Tickets Sold</center>
			<center id="countdown">{$lottoEndText}</center>
			<a id="left" href="?page=instruction">Instructions</a>
			<a id="center" href="?page=tickets">View Your Tickets</a>
			<a id="right" href="?page=contact">contact.jpg</a>
			</body>
		</else>
	</if>	
	<else>
		<div id="container">
			<fieldset>
				<legend>Restricted</legend>
				<p>Waiting On Trust</p>
			</fieldset>
		</div>
	</else>
</if>
<else>
	<div id="container">
		<fieldset>
			<legend>Restricted</legend>
			<p>Not using EVE Ingame Browser</p>
		</fieldset>
	</div>
</else>
