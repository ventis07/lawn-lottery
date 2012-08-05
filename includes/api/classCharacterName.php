<?php
Class charName extends eveApi {
	//Add the page and scope for this api call
	function __construct($id) {
		parent::__construct(NULL,NULL);
		$this->scope="eve";
		$this->page="CharacterName.xml.aspx";
		$this->args=array("ids" => $id);
		$this->fetch();
	}
	function parse(){
		$xml=$this->api;
		if($xml->result){
			$xml=$xml->result->rowset->row;
			$this->name=$xml['name'];
		}elseif($xml->error){
			$this->response="[".$xml->error['code']."] ".$xml->error;
			$this->error=true;
		}elseif(!$this->response){	
			$this->response="Unknown Error Has Occured";
		}
	}
}
?>