<style>
	#character{
		width:160px;
		float:left;
		display:block;
	}
	#balance {
		width:190px;
		float:left;
		display:block;
	}
	#break {
		width:390px;
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
</style>
<if $get['endLotto'](isset)>
	<if $get['endConfirm'](isset)>
		<p>Are You ReallySure</p>
		<a style='width:40px; margin-right:40px;padding-right:40px' href='manage'>No<a/><a href='manage?endLottery&sure&reallysure'>Yes<a/>
	</if>
	<else>
		<p>Are You Sure</p>
		<a style='width:40px; margin-right:40px;padding-right:40px' href='manage?endLottery&sure'>Yes<a/><a href='manage'>No<a/>
	</else>
<elseif $get['startLotto'](isset)>
	<form method="POST" action="manage.php?startLottery">
		<label for="lottoName">Lottery Name</label>
		<input type="text" name="lottoName" />
		<label for="lottoEnd">Lottery Length</label>
		<input type="text" name="lottoEnd" value="30"/>
		<br>
		<input type="submit" value="Create Lotto" />
	</form>
<elseif $get['addManager'](isset)>
	<form method="POST" action="manage">
	<label for="name">Manager Name</label>
	<input type="text" name="name"/>
	<input type="hidden" name="addManager"/>
	<input type="submit" value="Submit" />
	</form>
<elseif $get['pastLotto'](isset)>
	<if $past>
		<div id="stats">Total Isk<br>{$totalBalance|format 2}</div><div id="stats">Unique User<br>{$totalUsers|format}</div><div id="stats">Total Tickets<br>{$totalTickets|format}</div><br><br>
	</if>
	<else>
		No Past Lottos
	</else>
</if>
<else>
	<if $status==0>
		Currently Not Public	<br>
	</if>
	<if $status!=1>
		Completed Lotto: {$lottoName} 	<br>
	</if>
	<else>
		Current Lotto: {$lottoName} 	<br>
	</else>
	{$ticketsSold} Tickets Purchased<br>
	{$totalIsk|format} ISK Raised<br>
	Admin Cut Per Person: {$adminCut|format 2}
	<br><br>
	<if $winner>
		<if $inGame>
			<a href='manage' onclick="CCPEVE.showInfo(1377,{$winnerID})";return false;>{$winnerName}</a> has won with ticket {$winnerTicket}
			<a href='manage' onclick="CCPEVE.sendMail({$winnerID},'Lawn Lottery Winner',' ');return false;\">Send {$winnerName} A  EVEMail</a>
		</if>
		<else>
			{$winnerName} has won with ticket {$winnerTicket}
		</else>
	</if>
	<if $ticketsSold>1>
		Average Tickets Bought : {$ticketAverage}
	</if>
	<else>
		No Average
	</else>
	<br><br>Tickets<br>
</else>