<?php
class output {
	function __construct(){
		$this->html=null;
		$this->error=null;
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
	function addFooter($html){
		$this->footer="<span>".$html."</span>";
	}
	function echoHtml($end=false){
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
?>