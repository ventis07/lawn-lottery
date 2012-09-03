<?php
class output {
	function __construct(){
		$this->html=null;
		$this->box=array();
		$this->style="
			.info, .success, .warning, .error, .validation {
				border: 1px solid;
				margin: 10px 0px;
				max-width:600px;
				margin-left:auto;
				margin-right:auto;
				padding-left:auto;
				padding-right:auto;
				display:block;
				padding:15px 10px 15px 50px;
				color: #9F6000;
				background-color: #FEEFB3;
			}
			.info center, .success center, .warning center, .error center, .validation center{
				font-size:200%
			}
			.info {
				color: #00529B;
				background-color: #BDE5F8;
			}
			.success {
				color: #4F8A10;
				background-color: #DFF2BF;
			}
			.error {
				color: #D8000C;
				background-color: #FFBABA;
			}		
		";
		$this->javascript=null;
		$this->header=null;
		$this->eval=null;
		$this->footer=null;
	}
	function addHtml($html){
		$this->html.=" ?>".$html."<?php ";
	}
	function addBox($message,$type,$classType=false){
		if($type==1){
			$type="Error";
			$class=$type;
		}elseif($type==2){
			$type="Success";
			$class=$type;
		}elseif($type==3){
			$type="Warning";
			$class=$type;
		}elseif($type==4){
			$type="Notice";
			$class=$type;
		}elseif($type==5){
			$type="Info";
			$class=$type;
		}elseif($type==6){
			$type="Validation";
			$class=$type;
		}else{
			$class="Info";
		}
		if($classType)
			$class=$classType;
		if(!@$this->box[$type])
			$this->box[$type]="?><div class=\"$class\"><center>$type</center><ul><?php";
		$this->box[$type].="<li>".$message."</li>";			
	}
	function addHeader($html){
		$this->header="?> <span>".$html."</span><?php ";
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
			$$this->addBox("Template ".$template."Could not be loaded","error");
			return false;
		}		
		$templateContents=preg_replace("!\{\\$(\w+)\|([\s\.*\w]+)\}!e",'perform(\'\2\',\'\1\')',$templateContents);
		#replace variables
		$templateContents=preg_replace("!\{\\$(\w+)\}!e",'@$vars[\'\1\']',$templateContents);
		
		#common statements
		$templateContents=preg_replace("!<if \\$(.*)>!ie",'evalStatement(\'\1\',"if");',$templateContents);
		$templateContents=preg_replace("!<elseif \\$(.*)>!ie",'evalStatement(\'\1\',"elseif");',$templateContents);
		$templateContents=preg_replace("!(.*)<else>(.*)!i",'\1 <?php else{ ?>  \2',$templateContents);
		$templateContents=preg_replace("!(.*)</else>(.*)|(.*)</if>(.*)!i",'\1 <?php } ?>  \2',$templateContents);

		#remove added slashes
		$templateContents=preg_replace("#(\\\)*(\"){1}#","\"",$templateContents);
		#close uneeded tags
		$templateContents =preg_replace('/\?>([\s]*)<\?php/', '\1', $templateContents);
		
		#add new lines
		$templateContents = preg_replace('#\?\>([\r\n])#', '?>\1\1', $templateContents);
		eval("ob_start(); ?>".$templateContents."<?php \$success=true; ob_clean();");
		if(isset($success))
			$this->html.=" ?>".$templateContents."<?php ";
		else{
			$error = error_get_last();
			$this->addBox("Error Loading Template ".$template."<br>Error on Line:".$error['line'],1);
		}
	}
	function addFooter($html){
		$this->footer="?><span>".$html."</span><?php ";
	}
	function echoHtml($end=false){
		global $vars;
		if($this->header)
			$this->html=$this->header." ?><hr><?php ".$this->html;
			
		if($this->box)
		
			foreach($this->box as $message)
				$this->html=$message."</ul></div></br>".$this->html;
				
		$this->html="<style>".$this->style."</style>\n <?php ".$this->html;
		
		if($this->javascript)
			$this->html=" ?><script type=\"text/javascript\">".$this->javascript."</script>\n<?php ".$this->html;
			
		if($this->footer)
			$this->html.="?><hr><?php".$this->footer;
			
			
		#remove added slashes
		$this->html=preg_replace("#(\\\)*(\"){1}#","\"",$this->html);
		#close uneeded tags
		$this->html =preg_replace('/\?>([\s]*)<\?php/', '\1', $this->html);
		
		#add new lines
		$this->html = preg_replace('#\?\>([\r\n])#', '?>\1\1', $this->html);
		eval("ob_start() ?>".$this->html." \$html=ob_get_contents();ob_end_clean();");
		if(!$end)
			echo $html;
		else
			die ($html);
	}
}
function perform ($action,$input){
	global $vars;
	$matches=array();
	$actions=array();
	$output=(isset($vars[$input])?$vars[$input]:null);
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
	if(preg_match("#(\w+)(\[.*\])*([<>=\!]+|\(isset\){1})*(\w*)#",$statement,$matches)){
		if(isset($matches[2])&&$matches[2]){
			$matches[1].="']".substr($matches[2],0,strlen($matches[2])-2);
			eval("var_dump(isset(\$vars['".$matches[1]."']));");
		}
		if(isset($matches[4])&&substr($matches[4],0,1)=="$")
			$matches[4]=$vars[substr($matches[4],0,1)];
		if(!@$matches[3]){
			if($tag=="if")
				return "<?php if(isset(\$vars['".$matches[1]."'])&&\$vars['".$matches[1]."']){ \n?>";
			elseif($tag=="elseif")
				return "<?php }elseif(isset(\$vars['".$matches[1]."'])&&\$vars['".$matches[1]."']){\n ?>";
		}elseif(@$matches[3]=="="){
			if($tag=="if")
				return "<?php if(isset(\$vars['".$matches[1]."'])&&\$vars['".$matches[1]."']=='".$matches[4]."'){ \n?>";
			elseif($tag=="elseif")
				return "<?php }elseif(isset(\$vars['".$matches[1]."'])&&\$vars['".$matches[1]."']=='".$matches[4]."'){\n?>";
		}elseif(@$matches[3]=="(isset)"){
			if($tag=="if")
				return "<?php if(isset(\$vars['".$matches[1]."'])){ \n?>";
			elseif($tag=="elseif")
				return "<?php }elseif(isset(\$vars['".$matches[1]."'])){\n ?>";
		}else{
			if($tag=="if")
				return "<?php if(isset(\$vars['".$matches[1]."'])&&\$vars['".$matches[1]."']".$matches[3]."'".$matches[4]."'){\n?>";
			elseif($tag=="elseif")
				return "<?php }elseif(isset(\$vars['".$matches[1]."'])&&\$vars['".$matches[1]."']".$matches[3]."'".$matches[4]."'){\n?>";
		}
	}
	return "";	
	
}
?>