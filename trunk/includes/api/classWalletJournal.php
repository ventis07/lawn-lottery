<?php
Class walletJournal extends eveApi {
	//Add the page and scope for this api call
	function __construct($keyID,$vCode,$characterID) {
		parent::__construct($keyID,$vCode,$characterID);
		$this->scope="char";
		$this->page="WalletJournal.xml.aspx";
		$this->expires=null;
		$this->transactions=array();
	}
	//call to retrieve XML and convert to array
	function parse(){
		$xml=$this->fetch();
		if(($xml->result)){
			$xml=$xml->result;
			//cycle through Transactions
			foreach($xml->rowset->row as $transaction){
				$transactions[(string)$transaction['refID']]['redID']=$transaction['refID'];
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
			$this->transactions=$transactions;
			$this->response=true;
		}elseif($xml->error){
			$this->response="[".$xml->error['code']."] ".$xml->error;
			$this->error=true;
		}elseif(!$this->response){	
			$this->response="Unknown Error Has Occured";
		}
	}
}
?>