<?php
Class walletJournal extends eveApi {
	//Add the page and scope for this api call
	function __construct($keyID,$vCode,$characterID) {
		parent::__construct($keyID,$vCode,$characterID);
		$this->scope="char";
		$this->page="WalletJournal.xml.aspx";
		$this->expires=null;
		$this->transactions=array();
		$this->fetch();
	}
	//call to retrieve XML and convert to array
	function parse($ignoreRefID=false){
		global $db,$settings;
		$xml=$this->api;
		if(($xml->result)){
			$xml=$xml->result;
			//cycle through Transactions
			foreach($xml->rowset->row as $transaction){
				if(($transaction['refID']>$settings['lastID'])||$ignoreRefID){
					$transactions[(string)$transaction['refID']]['refID']=$transaction['refID'];
					$transactions[(string)$transaction['refID']]['date']=$transaction['date'];
					$transactions[(string)$transaction['refID']]['refTypeID']=$transaction['refTypeID'];
					$transactions[(string)$transaction['refID']]['sender']=$transaction['ownerName1'];
					$transactions[(string)$transaction['refID']]['senderID']=$transaction['ownerID1'];
					$transactions[(string)$transaction['refID']]['recpt']=$transaction['ownerName2'];
					$transactions[(string)$transaction['refID']]['recptID']=$transaction['ownerID2'];
					$transactions[(string)$transaction['refID']]['argName1']=$transaction['argName'];
					$transactions[(string)$transaction['refID']]['argID1']=$transaction['argID'];
					$transactions[(string)$transaction['refID']]['amount']=$transaction['amount'];
					$transactions[(string)$transaction['refID']]['balance']=$transaction['balance'];
					$transactions[(string)$transaction['refID']]['reason']=$transaction['reason'];
					$transactions[(string)$transaction['refID']]['taxReceiverID']=$transaction['taxReceiverID'];
					$transactions[(string)$transaction['refID']]['taxAmount']=$transaction['taxAmount'];
				}
			}
			if(empty($transactions))
				return null;
			ksort($transactions);
			$this->transactions=$transactions;
			$this->response=true;
			$keysOfTrans=array_keys($transactions);
			$lastID=end($keysOfTrans);
			if(REFID!=$lastID&&!$ignoreRefID)
				$db->changeSetting("lastID",$lastID);
		}elseif($xml->error){
			$this->response="[".$xml->error['code']."] ".$xml->error;
			$this->error=true;
		}elseif(!$this->response){	
			$this->response="Unknown Error Has Occured";
		}
	}
}
?>