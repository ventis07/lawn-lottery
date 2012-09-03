<if $ticketCount=1>
	<div id="character">{$charName}</div><div id="balance">Balance: {$balance|format 2}</div>Purchased ticket {$ticketText} <br>
</if>
<else>
	<div id="character">{$charName}</div><div id="balance">Balance: {$balance|format 2}</div>Purchased tickets {$ticketText} <br>
</else>