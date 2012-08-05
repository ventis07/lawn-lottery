<?php
Class charInfo extends eveApi {
	//Add the page and scope for this api call
	function __construct($characterID) {
		parent::__construct(NULL,NULL,$characterID);
		$this->scope="eve";
		$this->page="CharacterInfo.xml.aspx";
		$this->fetch();
	}
	function parse(){
		$xml=$this->api;
		if($xml->result){
			$xml=$xml->result;
			$this->charID=$xml->characterID;
			$this->charName=$xml->characterName;
			$this->race=$xml->race;
			$this->bloodline=$xml->bloodline;
			$this->corpID=$xml->corporationID;
			$this->corp=$xml->corporation;
			$this->corporationDate=$xml->corporationDate;
			$this->allianceID=$xml->allianceID;
			$this->alliance=$xml->alliance;
			$this->alliancenDate=$xml->alliancenDate;
			$this->secStatus=$xml->securityStatus;
		}elseif($xml->error){
			$this->response="[".$xml->error['code']."] ".$xml->error;
			$this->error=true;
		}elseif(!$this->response){	
			$this->response="Unknown Error Has Occured";
		}
	}
}
?>