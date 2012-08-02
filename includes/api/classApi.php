<?php
Class eveApi{
	// 
	function __construct($keyID,$vCode,$characterID=null){
		$this->keyID=$keyID;
		$this->vCode=$vCode;
		$this->cID=$characterID;
		$this->apiURL="http://api.eveonline.com";
		
		// check if server is online
		$this->scope="server";
		$this->page="ServerStatus.xml.aspx ";
		$this->response=null;
		$this->args=array();
		if(!$this->fetch(2)){
			$this->response="Eve Api Down";
			return;
		}
			
	}
	// check for cache and load xml file
	protected function fetch($cache=true){
		$hash=hash("md5",$this->keyID.$this->vCode.$this->cID.$this->page);
		if($cache===2)
			$hash="apiStatus";
		if($cache&&$this->cacheCheck($hash))
			$xml=simplexml_load_file(ROOT."/cache/".$hash.".xml");
		else {
			$url=$this->apiURL."/".$this->scope."/".$this->page;
			$args=$this->args;
			$args['keyID']=$this->keyID;
			$args['vCode']=$this->vCode;
			if(isset($this->cID))
				$args['characterID ']=$this->cID;
			$req = curl_init();
			curl_setopt($req,CURLOPT_URL,$url);
			curl_setopt($req, CURLOPT_POST, true);
			curl_setopt($req, CURLOPT_POSTFIELDS, $args);
			curl_setopt($req, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($req, CURLOPT_TIMEOUT, 30);
			$xml = curl_exec($req);
			
			$http_code = curl_getinfo($req,  CURLINFO_HTTP_CODE);
			$http_errno = curl_errno($req);
			curl_close($req);

			if ($http_errno != 0) 
				return false;

			if ($http_code != 200)       // major api failure
				return false;
			try {
				$xml=new SimpleXMLElement($xml,LIBXML_NOCDATA);
				if($cache)
					$xml->asXML(ROOT."/cache/".$hash.".xml");
			} catch (Exception $e) {    // malformed XML
				$xml=false;
			}
		}
			return $xml;
	
	}
	// check if the file is cached or if the cache is expired
	protected function cacheCheck($hash){
		date_default_timezone_set('UTC'); 
		if(file_exists(ROOT."/cache/".$hash.".xml")){
			$xml=simplexml_load_file(ROOT."/cache/".$hash.".xml");
			$cacheTime=strtotime($xml->cachedUntil);
			$cacheTime=((time()-$cacheTime)-20);
			if($cacheTime<0)
				return true;
		}
		return false;
	}

}

?>