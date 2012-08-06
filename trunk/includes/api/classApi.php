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
		$this->api=null;
		$this->args=array();
		if(isset($keyID)){$this->args['keyID']=$keyID;}
		if(isset($vCode)){$this->args['vCode']=$vCode;}
		if(isset($characterID)){$this->args['characterID']=$characterID;}
		if(!@$_SESSION['apiStatus']&&!$this->fetch(false)){
			$this->response="Eve Api Down";
			return;
		}
			
	}
	// check for cache and load xml file
	protected function fetch($cache=true){
		# Special Cache Cases
		if(isset($this->args['names'])||isset($this->args['ids'])){
			if(isset($this->args['names']))
				$char=$this->args['names'];
			else
				$char=$this->args['ids'];
			$hash="charName".$char;
		}else
			$hash=hash("md5",$this->keyID.$this->vCode.$this->cID.$this->page);
			
		#check if you should cache this page and if it is cached
		if($cache&&$this->cacheCheck($hash))
			$xml=simplexml_load_file(ROOT."/cache/".$hash.".xml");
		else {
			if(!@$_SESSION['apiStatus']&&$this->page!="ServerStatus.xml.aspx "){
				$this->api->error="ApiDown";
				return false;
			}
			#no cached copy or out of date retrieved new copy
			$url=$this->apiURL."/".$this->scope."/".$this->page;
			$args=$this->args;
			$header="Lawn-Lottery Contact Equto in game or Whinis@gmail.com";
			$req = curl_init();
			curl_setopt($req,CURLOPT_URL,$url);
			curl_setopt($req, CURLOPT_POST, true);
			curl_setopt($req, CURLOPT_POSTFIELDS, $args);
			curl_setopt($req, CURLOPT_HEADER, $header);
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
				
			if ($http_code == 404){ //api failed to load, set api as down
				$_SESSION['apiStatus']=false;
				return false;
			}
			try {
				$xml=new SimpleXMLElement($xml,LIBXML_NOCDATA);
				if(isset($this->args['names'])||isset($this->args['ids'])){
					$date=date("Y-m-d H:i:s",strtotime("+1 years"));
					$xml->cachedUntil=$date;
				}
				if($cache)
					$xml->asXML(ROOT."/cache/".$hash.".xml");
				if($this->page=="ServerStatus.xml.aspx ")
					$_SESSION['apiStatus']=true;
					
			} catch (Exception $e) {    // malformed XML
				$xml=false;
			}
		}
		$this->api=$xml;
	
	}
	// check if the file is cached or if the cache is expired
	protected function cacheCheck($hash){
		date_default_timezone_set('UTC'); 
		if(file_exists(ROOT."/cache/".$hash.".xml")){
			$xml=simplexml_load_file(ROOT."/cache/".$hash.".xml");
			$cacheTime=strtotime($xml->cachedUntil);
			$cacheTime=((time()-$cacheTime)+20);
			if($cacheTime<0)
				return true;
		}
		return false;
	}

}

?>