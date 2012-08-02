<?php
Class apiInfo extends eveApi {
	//Add the page and scope for this api call
	function __construct($keyID,$vCode,$characterID=null) {
		parent::__construct($keyID,$vCode);
		$this->scope="account";
		$this->page="APIKeyInfo.xml.aspx";
		$this->char=array();
		$this->kam=null;
		$this->expires=null;
	}
	function parse(){
		$xml=$this->fetch();
		if(($xml->result)){
			$xml=$xml->result;
			$this->kam=$xml->key['accessMask'];
			$this->kType=$xml->key['type'];
			if($xml->key['expires'])
			$this->expires=$xml->key['expires'];
			// multiple typecast for a simplexml bug
			$chars=(array)$xml->key->rowset;
			$chars=(array)$chars['row'];
			foreach ($chars as $character){
				// Float typecast because simplexml can't be used as a index
				$cID=(int)$character['characterID'];
				$this->char[$cID]['name']=$character['characterName'];
				$this->char[$cID]['cID']=$cID;
				$this->char[$cID]['corpID']=$character['corporationID'];
				$this->char[$cID]['corp']=$character['corporationName'];
			}
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