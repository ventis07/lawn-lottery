<?php
class output {
	function __construct(){
		$this->html=null;
		$this->error=null;
		$this->style=null;
		$this->javascript=null;
		$this->notice=null;
		$this->header=null;
		$this->footer=null;
	}
	function addHtml($html){
		$this->html.=$html;
	}
	function addError($error){
		if(!$this->error)
			$this->error="<div class=\"error\"><ul><center>Error</center>";
		$this->error.="<li>".$error."</li>";
	}
	function addNotice($notice){
		if(!$this->notice)
			$this->notice="<div class=\"notice\"><ul><center>Notice</center>";
		$this->notice.="<li>".$notice."</li>";
	}
	function addHeader($html){
		$this->header="<span>".$html."</span>";
	}
	function addStyle($html){
		$this->style.=$html;
	}
	function addJavascript($script){
		$this->javascript.=$script;
	}
	#takes template file and pareses it 
	function useTemplate($template){
		global $vars;
		$templateContents=@file_get_contents($template);
		if(!$templateContents){
			$this->html.="Template Could not be loaded";
			return false;
		}		
		$templateContents=preg_replace("!\{\\$(\w+)\|([\s\.*\w]+)\}!e",'perform(\'\2\',\'\1\')',$templateContents);
		#replace variables
		$templateContents=preg_replace("!\{\\$(\w+)\}!e",'@$vars[\'\1\']',$templateContents);
		
		#common statements
		$templateContents=preg_replace("!<if \\$(.*)>!ie",'evalStatement(\'\1\',"if")',$templateContents);
		$templateContents=preg_replace("!<elseif \\$(.*)>!ie",'evalStatement(\'\1\',"elseif")',$templateContents);
		$templateContents=preg_replace("!(.*)<else>(.*)!i",'\1 <?php else{ ?> \2',$templateContents);
		$templateContents=preg_replace("!(.*)</else>(.*)|(.*)</if>(.*)!i",'\1 <?php } ?> \2',$templateContents);

		#remove added slashes
		$templateContents=preg_replace("#(\\\)*(\"){1}#","\"",$templateContents);
		#close uneeded tags
		$templateContents =preg_replace('/\?>\s*<\?php/', '', $templateContents);
		
		#add new lines
		$templateContents = preg_replace('#\?\>([\r\n])#', '?>\1\1', $templateContents);
		
		$this->html.=eval("?>".$templateContents."<?php ");
	}
	function addFooter($html){
		$this->footer="<span>".$html."</span>";
	}
	function echoHtml($end=false){
		$this->html.="<style>".$this->style."</style>\n".$this->html;
		if($this->javascript)
			$this->html.="<script type=\"text/javascript\">".$this->javascript."</script>\n".$this->html;
		if($this->header)
			$this->header.="<hr>";
		if($this->footer)
			$this->footer="<hr>".$this->footer;
		$this->html=$this->header.$this->html.$this->footer;
		if($this->error)
			$this->html=$this->error."</ul></div></br>".$this->html;
		if($this->notice)
			$this->html=$this->notice."</ul></div></br>".$this->html;
		if(!$end)
			echo $this->html;
		else
			die ($this->html);
	}
}
function perform ($action,$input){
	global $vars;
	$matches=array();
	$actions=array();
	$output=$vars[$input];
	while(preg_match("#(\w+)(\s){0,1}(\.*\d*)(.*)#",$action,$actions)>0){
		if(preg_match("#(\w+) (\.*\d+)#",$action,$matches)){
			if($matches[1]=="format")
				$output=number_format($output,$matches[2]);
			if($matches[1]=="add")
				$output=($output+$matches[2]);
			if($matches[1]=="sub")
				$output=($output-$matches[2]);
			if($matches[1]=="div")
				$output=($output/$matches[2]);
			if($matches[1]=="mult")
				$output=($output*$matches[2]);
		}else{
			if($actions[1]=="format")
				$output=number_format($output);
			if($actions[1]=="escape")
				$output=htmlspecialcharacters($output);
		
		}
		if($matches)
			$action=substr($action,strlen($matches[1]." ".$matches[2]));
		else
			$action=substr($action,strlen($actions[1]));
	}
	return $output;

}
function evalStatement($statement,$tag){	
	global $vars;
	$matches=array();
	if(preg_match("#(\w+)([<>=\!]*)(\w*)#",$statement,$matches)){
		if(isset($matches[3])&&substr($matches[3],0,1)=="$")
			$matches[3]=$vars[substr($matches[3],0,1)];
		if(!@$matches[2]){
			if($tag=="if")
				return "<?php if(@\$vars['".$matches[1]."']){ ?>";
			elseif($tag=="elseif")
				return "<?php }elseif(@\$vars['".$matches[1]."']){?>";
		}elseif(@$matches[2]=="="){
			if($tag=="if")
				return "<?php if(@\$vars['".$matches[1]."']=='".$matches[3]."'){?>";
			elseif($tag=="elseif")
				return "<?php }elseif(@\$vars['".$matches[1]."']=='".$matches[3]."'){?>";
		}else{
			if($tag=="if")
				return "<?php if(@\$vars['".$matches[1]."']".$matches[2]."'".$matches[3]."'){?>";
			elseif($tag=="elseif")
				return "<?php }elseif(@\$vars['".$matches[1]."']".$matches[2]."'".$matches[3]."'){?>";
		}
	}
	return "";	
	
}
?>