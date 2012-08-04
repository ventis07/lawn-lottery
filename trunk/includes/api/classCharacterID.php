<?php
Class charID extends eveApi {
	//Add the page and scope for this api call
	function __construct($names) {
		parent::__construct(NULL,NULL);
		$this->scope="eve";
		$this->page="CharacterID.xml.aspx";
		$this->args=array('names'=>$names);
	}
	function parse(){
		$xml=$this->fetch();
		if(($xml->result)){
			$xml=$xml->result->rowset->row;
			$this->id=$xml['characterID'];
		}elseif($xml->error){
			$this->response="[".$xml->error['code']."] ".$xml->error;
			$this->error=true;
		}elseif(!$this->response){	
			$this->response="Unknown Error Has Occured";
		}
	}
}
?>